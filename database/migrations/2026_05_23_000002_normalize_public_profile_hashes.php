<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('corretor_perfis')
            ->select('id', 'slug')
            ->orderBy('id')
            ->get()
            ->each(function ($perfil): void {
                if (is_string($perfil->slug) && preg_match('/^[A-Z0-9]{14}$/', $perfil->slug)) {
                    return;
                }

                do {
                    $hash = Str::upper(Str::random(14));
                } while (DB::table('corretor_perfis')->where('slug', $hash)->exists());

                DB::table('corretor_perfis')
                    ->where('id', $perfil->id)
                    ->update(['slug' => $hash]);
            });
    }

    public function down(): void
    {
        //
    }
};
