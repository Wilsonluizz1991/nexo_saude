<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServicoPerfilUsuario
{
    public function atualizar(User $user, array $dados, Request $request): void
    {
        if ($request->boolean('remover_foto')) {
            $user->update(['avatar_path' => null]);
            $dados['foto_path'] = null;
        }

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('corretores', 'public');
            $user->update(['avatar_path' => $path]);
            $dados['foto_path'] = $path;
        }

        $user->update([
            'name' => $dados['name'],
            'telefone' => $dados['telefone'] ?? null,
            'email' => $dados['email'],
        ]);

        $user->corretorPerfil()->updateOrCreate(['user_id' => $user->id], [
            'slug' => Str::slug($dados['slug']),
            'nome_publico' => $dados['name'],
            'bio' => $dados['bio'] ?? null,
            'especialidades' => collect(explode(',', $dados['especialidades'] ?? ''))->map(fn ($item) => trim($item))->filter()->values()->all(),
            'cidade_regiao' => trim(($dados['cidade'] ?? '').'/'.($dados['estado'] ?? ''), '/'),
            'cidade' => $dados['cidade'] ?? null,
            'estado' => strtoupper($dados['estado'] ?? ''),
            'anos_experiencia' => $dados['anos_experiencia'] ?? 0,
            'foto_path' => $dados['foto_path'] ?? $user->corretorPerfil?->foto_path,
            'publico_ativo' => true,
        ]);
    }
}
