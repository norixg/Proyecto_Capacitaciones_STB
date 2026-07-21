<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capacitacion', function (Blueprint $table) {
            $table->dropForeign('FK_capacitacion_instructor');
        });
    }

    public function down(): void
    {
        Schema::table('capacitacion', function (Blueprint $table) {
            $table->foreign('id_instructor', 'FK_capacitacion_instructor')
                ->references('id_instructor')
                ->on('instructor');
        });
    }
};
