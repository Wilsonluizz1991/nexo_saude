<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CorretorPerfil extends Model
{
    protected $table = 'corretor_perfis';

    protected $fillable = ['user_id', 'slug', 'public_hash', 'foto_path', 'nome_publico', 'bio', 'especialidades', 'cidade_regiao', 'cidade', 'estado', 'anos_experiencia', 'publico_ativo', 'mensagem_primeiro_contato_whatsapp'];

    protected function casts(): array
    {
        return ['especialidades' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function gerarSlugPublico(string $nome, ?int $ignorarId = null): string
    {
        $base = Str::slug($nome) ?: 'corretor';
        $slug = $base;

        if (! self::slugExiste($slug, $ignorarId)) {
            return $slug;
        }

        do {
            $slug = $base.'-'.Str::lower(Str::random(3));
        } while (self::slugExiste($slug, $ignorarId));

        return $slug;
    }

    public static function gerarHashPublico(): string
    {
        do {
            $hash = Str::upper(Str::random(14));
        } while (self::where('slug', $hash)->orWhere('public_hash', $hash)->exists());

        return $hash;
    }

    public static function buscarPorIdentificadorPublico(string $identificador): ?self
    {
        return self::where('slug', $identificador)
            ->orWhere('public_hash', $identificador)
            ->first();
    }

    private static function slugExiste(string $slug, ?int $ignorarId = null): bool
    {
        return self::where('slug', $slug)
            ->when($ignorarId, fn ($query) => $query->whereKeyNot($ignorarId))
            ->exists();
    }
}
