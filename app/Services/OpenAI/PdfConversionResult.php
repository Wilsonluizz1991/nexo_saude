<?php

namespace App\Services\OpenAI;

class PdfConversionResult
{
    public function __construct(
        public readonly array $imagePaths,
        public readonly ?int $totalPages,
        public readonly int $analyzedPages,
        public readonly bool $partial,
    ) {
    }
}
