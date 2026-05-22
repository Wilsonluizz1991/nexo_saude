<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Http\Requests\AtualizarPerfilPublicoRequest;
use Illuminate\Support\Str;

class PerfilPublicoController extends Controller
{
    public function edit()
    {
        return view('interno.perfil-publico', ['perfil' => auth()->user()->corretorPerfil]);
    }

    public function update(AtualizarPerfilPublicoRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('foto')) {
            $data['foto_path'] = $request->file('foto')->store('corretores', 'public');
        }
        $data['especialidades'] = collect(explode(',', $data['especialidades'] ?? ''))->map(fn ($item) => trim($item))->filter()->values()->all();
        unset($data['foto']);

        auth()->user()->corretorPerfil()->updateOrCreate(
            ['user_id' => auth()->id()],
            array_merge($data, ['slug' => Str::slug($data['slug'])])
        );

        return back()->with('status', 'Perfil público atualizado.');
    }
}
