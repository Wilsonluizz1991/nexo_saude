<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('corretor_perfis', function (Blueprint $table) {
            if (! Schema::hasColumn('corretor_perfis', 'public_hash')) {
                $table->string('public_hash')->nullable()->unique()->after('slug');
            }
        });

        DB::table('corretor_perfis')
            ->leftJoin('users', 'users.id', '=', 'corretor_perfis.user_id')
            ->select('corretor_perfis.id', 'corretor_perfis.slug', 'corretor_perfis.public_hash', 'corretor_perfis.nome_publico', 'users.name')
            ->orderBy('corretor_perfis.id')
            ->get()
            ->each(function ($perfil): void {
                $hashAtual = $perfil->public_hash;

                if (! $hashAtual && is_string($perfil->slug) && preg_match('/^[A-Z0-9]{14}$/', $perfil->slug)) {
                    $hashAtual = $perfil->slug;
                }

                if (! $hashAtual) {
                    do {
                        $hashAtual = Str::upper(Str::random(14));
                    } while (DB::table('corretor_perfis')->where('public_hash', $hashAtual)->where('id', '!=', $perfil->id)->exists());
                }

                $nome = $perfil->nome_publico ?: $perfil->name ?: 'corretor';
                $base = Str::slug($nome) ?: 'corretor';
                $slug = $base;

                if ($this->slugExiste($slug, $perfil->id)) {
                    do {
                        $slug = $base.'-'.Str::lower(Str::random(3));
                    } while ($this->slugExiste($slug, $perfil->id));
                }

                DB::table('corretor_perfis')
                    ->where('id', $perfil->id)
                    ->update([
                        'slug' => $slug,
                        'public_hash' => $hashAtual,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('corretor_perfis', function (Blueprint $table) {
            if (Schema::hasColumn('corretor_perfis', 'public_hash')) {
                $table->dropUnique(['public_hash']);
                $table->dropColumn('public_hash');
            }
        });
    }

    private function slugExiste(string $slug, int $ignorarId): bool
    {
        return DB::table('corretor_perfis')
            ->where('slug', $slug)
            ->where('id', '!=', $ignorarId)
            ->exists();
    }
};
