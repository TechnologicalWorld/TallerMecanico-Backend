<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('persona_id')
                ->unique()
                ->constrained('personas')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->string('email', 150)->unique();
            $table->string('username', 100)->unique();
            $table->string('password');

            $table->foreignId('current_branch_id')
                ->nullable()
                ->constrained('sucursales')
                ->nullOnDelete();

            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('username');
            $table->index('email');
            $table->index('activo');
            $table->index('persona_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');

    }
};
