<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Proposta;
use Illuminate\Support\Facades\Storage;

class PropostaPublicaController extends Controller
{
    public function show(string $token)
    {
        $propostas = Proposta::query()
            ->with('operadora')
            ->where('public_group_token', $token)
            ->latest()
            ->get();

        abort_if($propostas->isEmpty(), 404);

        return view('publico.propostas.show', [
            'token' => $token,
            'propostas' => $propostas,
            'indicacao' => $propostas->first()->indicacao,
        ]);
    }

    public function download(string $token, Proposta $proposta)
    {
        abort_unless(hash_equals((string) $proposta->public_group_token, $token), 404);
        abort_unless($proposta->arquivo_pdf_path && Storage::disk('public')->exists($proposta->arquivo_pdf_path), 404);

        $nomeArquivo = str($proposta->titulo ?: 'proposta-comercial')->slug()->append('.pdf')->toString();

        return Storage::disk('public')->response($proposta->arquivo_pdf_path, $nomeArquivo, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
