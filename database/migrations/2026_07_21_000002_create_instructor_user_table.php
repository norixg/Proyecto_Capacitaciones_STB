<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('instructor_user')) {
            return;
        }

        Schema::create('instructor_user', function (Blueprint $table) {
            $table->id('id_instructor_user');
            $table->foreignId('id_user')->unique()->constrained('users', 'id')->cascadeOnDelete();
            $table->unsignedInteger('id_instructor')->unique();
            $table->dateTime('fecha_asignacion')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_user');
    }
};
