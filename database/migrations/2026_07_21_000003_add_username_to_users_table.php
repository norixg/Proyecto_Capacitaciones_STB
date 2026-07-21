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
        if (! Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username', 50)->nullable();
            });
        }

        $ocupados = [];

        DB::table('users')->orderBy('id')->get(['id', 'email'])->each(function ($usuario) use (&$ocupados) {
            $local = Str::before((string) $usuario->email, '@');
            $base = Str::lower(Str::ascii($local));
            $base = preg_replace('/[^a-z0-9._-]+/', '', $base) ?: 'usuario'.$usuario->id;
            $base = trim($base, '._-');
            $base = strlen($base) >= 3 ? $base : 'usuario'.$usuario->id;
            $base = substr($base, 0, 50);
            $username = $base;
            $sufijo = 2;

            while (isset($ocupados[$username])) {
                $textoSufijo = (string) $sufijo++;
                $username = substr($base, 0, 50 - strlen($textoSufijo)).$textoSufijo;
            }

            $ocupados[$username] = true;

            DB::table('users')->where('id', $usuario->id)->update(['username' => $username]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable(false)->change();
            $table->unique('username', 'UQ_users_username');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('UQ_users_username');
                $table->dropColumn('username');
            });
        }
    }
};
