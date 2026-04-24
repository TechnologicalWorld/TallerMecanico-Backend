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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            
            $table->enum('tipo_persona', ['FISICA', 'MORAL']);
            
            $table->string('nombre', 150)->nullable();
            $table->string('apellido', 150)->nullable();
            
            $table->string('razon_social', 255)->nullable();
            
            $table->string('identificacion_principal', 100);
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero', 20)->nullable();
            $table->string('foto_path', 255)->nullable();
            
            $table->enum('estado', ['ACTIVO', 'INACTIVO', 'BLOQUEADO'])
                  ->default('ACTIVO');
            
            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo_persona');
            $table->index('identificacion_principal');
            $table->index('estado');
            $table->index(['nombre', 'apellido']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};