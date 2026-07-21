<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $agregarCambioObligatorio = !Schema::hasColumn('users', 'debe_cambiar_password');
        $agregarExpiracion = !Schema::hasColumn('users', 'password_temporal_expira_en');

        Schema::table('users', function (Blueprint $table) use ($agregarCambioObligatorio, $agregarExpiracion) {
            if ($agregarCambioObligatorio) {
                $table->boolean('debe_cambiar_password')->default(false);
            }

            if ($agregarExpiracion) {
                $table->dateTime('password_temporal_expira_en')->nullable();
            }
        });

        if (Schema::hasColumn('users', 'password_temporal_notificacion')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('password_temporal_notificacion');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('password_temporal_notificacion')->nullable();
            $table->dropColumn(['debe_cambiar_password', 'password_temporal_expira_en']);
        });
    }
};
