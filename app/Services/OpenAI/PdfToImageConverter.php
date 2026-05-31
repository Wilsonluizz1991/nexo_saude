<?php

namespace App\Services\OpenAI;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

class PdfToImageConverter
{
    public function convert(UploadedFile $file, int $maxPages): PdfConversionResult
    {
        $maxPages = max(1, $maxPages);
        $tempDir = storage_path('app/private/documentos/ia-pdf/'.uniqid('pdf_', true));
        File::ensureDirectoryExists($tempDir);

        try {
            $totalPages = $this->countPages($file->getRealPath());
            $limit = $totalPages ? min($maxPages, $totalPages) : $maxPages;
            $paths = $this->convertWithAvailableDriver($file->getRealPath(), $tempDir, $limit);

            if ($paths === []) {
                throw new RuntimeException('Nenhum conversor de PDF para imagem esta disponivel.');
            }

            return new PdfConversionResult(
                imagePaths: $paths,
                totalPages: $totalPages,
                analyzedPages: count($paths),
                partial: $totalPages !== null && $totalPages > count($paths),
            );
        } catch (\Throwable $e) {
            File::deleteDirectory($tempDir);
            throw $e;
        }
    }

    public function cleanup(PdfConversionResult $result): void
    {
        foreach ($result->imagePaths as $path) {
            $directory = dirname($path);
            if (str_contains($directory, storage_path('app/private/documentos/ia-pdf'))) {
                File::deleteDirectory($directory);
            }
        }
    }

    private function convertWithAvailableDriver(string $pdfPath, string $tempDir, int $limit): array
    {
        if ($pdftoppm = $this->popplerExecutable('pdftoppm')) {
            return $this->convertWithPdftoppm($pdfPath, $tempDir, $limit, $pdftoppm);
        }

        if (class_exists(\Imagick::class)) {
            return $this->convertWithImagick($pdfPath, $tempDir, $limit);
        }

        if ($this->commandExists('magick')) {
            return $this->convertWithMagick($pdfPath, $tempDir, $limit);
        }

        return [];
    }

    private function convertWithImagick(string $pdfPath, string $tempDir, int $limit): array
    {
        $paths = [];

        for ($page = 0; $page < $limit; $page++) {
            $image = new \Imagick();
            $image->setResolution(180, 180);
            $image->readImage($pdfPath.'['.$page.']');
            $image->setImageFormat('jpeg');
            $image->setImageCompressionQuality(88);
            $image->setImageBackgroundColor('white');
            $image = $image->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);

            $path = $tempDir.DIRECTORY_SEPARATOR.'page-'.($page + 1).'.jpg';
            $image->writeImage($path);
            $image->clear();
            $image->destroy();

            $paths[] = $path;
        }

        return $paths;
    }

    private function convertWithPdftoppm(string $pdfPath, string $tempDir, int $limit, string $executable): array
    {
        $prefix = $tempDir.DIRECTORY_SEPARATOR.'page';
        $process = new Process([$executable, '-jpeg', '-r', '180', '-f', '1', '-l', (string) $limit, $pdfPath, $prefix]);
        $process->setTimeout(60);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->logProcessFailure('pdftoppm', $this->sanitizedPdftoppmCommand($executable, $limit), $process);
            throw new RuntimeException('Falha ao converter PDF com pdftoppm.');
        }

        return $this->collectGeneratedImages($tempDir);
    }

    private function convertWithMagick(string $pdfPath, string $tempDir, int $limit): array
    {
        $pages = $pdfPath.'[0-'.($limit - 1).']';
        $output = $tempDir.DIRECTORY_SEPARATOR.'page-%02d.jpg';
        $process = new Process(['magick', '-density', '180', $pages, '-background', 'white', '-alpha', 'remove', '-quality', '88', $output]);
        $process->setTimeout(60);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->logProcessFailure('magick', 'magick -density 180 [pdf-pages] -background white -alpha remove -quality 88 [output]', $process);
            throw new RuntimeException('Falha ao converter PDF com ImageMagick.');
        }

        return $this->collectGeneratedImages($tempDir);
    }

    private function collectGeneratedImages(string $tempDir): array
    {
        return collect(File::files($tempDir))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg'], true))
            ->sortBy(fn ($file) => $file->getFilename())
            ->map(fn ($file) => $file->getPathname())
            ->values()
            ->all();
    }

    private function countPages(string $pdfPath): ?int
    {
        if ($pdfinfo = $this->popplerExecutable('pdfinfo')) {
            $process = new Process([$pdfinfo, $pdfPath]);
            $process->setTimeout(10);
            $process->run();

            if ($process->isSuccessful() && preg_match('/^Pages:\s*(\d+)/mi', $process->getOutput(), $matches)) {
                return (int) $matches[1];
            }

            if (! $process->isSuccessful()) {
                $this->logProcessFailure('pdfinfo', $this->sanitizedPdfinfoCommand($pdfinfo), $process);
            }
        }

        $content = @file_get_contents($pdfPath);
        if ($content === false) {
            return null;
        }

        preg_match_all('/\/Type\s*\/Page\b/', $content, $matches);

        return count($matches[0]) ?: null;
    }

    private function commandExists(string $command): bool
    {
        $arguments = PHP_OS_FAMILY === 'Windows'
            ? ['where', $command]
            : ['sh', '-lc', 'command -v '.escapeshellarg($command)];
        $process = new Process($arguments);
        $process->setTimeout(5);
        $process->run();

        return $process->isSuccessful();
    }

    private function popplerExecutable(string $name): ?string
    {
        $binPath = trim((string) config('services.poppler.bin_path'));

        if ($binPath !== '') {
            $executable = $this->configuredPopplerExecutable($binPath, $name);
            if (! File::exists($executable)) {
                Log::warning('Executavel Poppler configurado nao encontrado', [
                    'driver' => $name,
                    'command' => basename($executable),
                    'exit_code' => null,
                    'stderr' => 'Arquivo inexistente em POPPLER_BIN_PATH.',
                ]);

                throw new RuntimeException("Executavel {$name} nao encontrado em POPPLER_BIN_PATH.");
            }

            return $executable;
        }

        return $this->commandExists($name) ? $name : null;
    }

    private function configuredPopplerExecutable(string $binPath, string $name): string
    {
        $binPath = rtrim($binPath, "\\/");
        $windowsExecutable = $binPath.DIRECTORY_SEPARATOR.$name.'.exe';

        if (PHP_OS_FAMILY === 'Windows' || File::exists($windowsExecutable)) {
            return $windowsExecutable;
        }

        return $binPath.DIRECTORY_SEPARATOR.$name;
    }

    private function logProcessFailure(string $driver, string $command, Process $process): void
    {
        Log::warning('Falha no conversor de PDF para IA documental', [
            'driver' => $driver,
            'command' => $command,
            'exit_code' => $process->getExitCode(),
            'stderr' => str($process->getErrorOutput())->limit(1000)->toString(),
        ]);
    }

    private function sanitizedPdfinfoCommand(string $executable): string
    {
        return basename($executable).' [pdf]';
    }

    private function sanitizedPdftoppmCommand(string $executable, int $limit): string
    {
        return basename($executable).' -jpeg -r 180 -f 1 -l '.$limit.' [pdf] [output_prefix]';
    }
}
