<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Http\Requests\AtualizarPerfilPublicoRequest;
use App\Models\CorretorPerfil;
use Illuminate\Support\Facades\Storage;

class PerfilPublicoController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        $perfil = $user->corretorPerfil()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'slug' => CorretorPerfil::gerarSlugPublico($user->name),
                'public_hash' => CorretorPerfil::gerarHashPublico(),
                'nome_publico' => $user->name,
            ]
        );

        return view('interno.perfil-publico', [
            'perfil' => $perfil,
        ]);
    }

    public function update(AtualizarPerfilPublicoRequest $request)
    {
        $user = auth()->user();
        $perfil = $user->corretorPerfil()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'slug' => CorretorPerfil::gerarSlugPublico($user->name),
                'public_hash' => CorretorPerfil::gerarHashPublico(),
                'nome_publico' => $user->name,
            ]
        );

        $data = $request->validated();

        if ($request->boolean('remover_foto')) {
            if ($perfil->foto_path && Storage::disk('public')->exists($perfil->foto_path)) {
                Storage::disk('public')->delete($perfil->foto_path);
            }

            if ($user->avatar_path && $user->avatar_path !== $perfil->foto_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $data['foto_path'] = null;

            $user->forceFill([
                'avatar_path' => null,
            ])->save();
        }

        if ($request->hasFile('foto')) {
            if ($perfil->foto_path && Storage::disk('public')->exists($perfil->foto_path)) {
                Storage::disk('public')->delete($perfil->foto_path);
            }

            if ($user->avatar_path && $user->avatar_path !== $perfil->foto_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $path = $request->file('foto')->store('corretores', 'public');

            $data['foto_path'] = $path;

            $user->forceFill([
                'avatar_path' => $path,
            ])->save();
        }

        $data['especialidades'] = collect(explode(',', $data['especialidades'] ?? ''))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();

        unset($data['foto']);
        unset($data['remover_foto']);

        $user->corretorPerfil()->updateOrCreate(
            ['user_id' => $user->id],
            array_merge($data, [
                'slug' => $perfil->slug ?: CorretorPerfil::gerarSlugPublico($data['nome_publico'] ?? $user->name),
                'public_hash' => $perfil->public_hash ?: CorretorPerfil::gerarHashPublico(),
            ])
        );

        return back()->with('status', 'Perfil público atualizado.');
    }
}
