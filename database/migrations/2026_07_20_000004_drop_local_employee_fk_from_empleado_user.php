<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleado_user', function (Blueprint $table) {
            $table->dropForeign('FK_empleado_user_empleado');
        });
    }

    public function down(): void
    {
        Schema::table('empleado_user', function (Blueprint $table) {
            $table->foreign('id_empleado', 'FK_empleado_user_empleado')
                ->references('id_empleado')
                ->on('empleado')
                ->cascadeOnDelete();
        });
    }
};
