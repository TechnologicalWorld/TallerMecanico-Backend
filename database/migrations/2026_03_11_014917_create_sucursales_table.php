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
        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            
            $table->string('nombre', 150);
            $table->string('codigo', 50)->unique();
            $table->boolean('activa')->default(true);
            $table->string('email', 150)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('descripcion', 255)->nullable();
            
            $table->time('horario_apertura')->nullable();
            $table->time('horario_cierre')->nullable();
            
            $table->string('direccion', 255)->nullable();
            
            $table->timestamps();

            $table->index('nombre');
            $table->index('activa');
            $table->index('codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};