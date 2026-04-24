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
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            
            $table->string('entidad_type', 50);
            $table->unsignedBigInteger('entidad_id');
            
            $table->string('nombre', 255);
            $table->string('ruta', 500);
            $table->string('tipo', 100)->nullable();
            $table->date('fecha_expiracion')->nullable();
            
            $table->timestamps(); 

            $table->index(['entidad_type', 'entidad_id']);

            $table->softDeletes();
            
            $table->index('tipo');
            $table->index('fecha_expiracion');
            $table->index('nombre');
            
            $table->index(['fecha_expiracion', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archivos');
    }
};