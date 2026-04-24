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
        Schema::create('contactos', function (Blueprint $table) {
            $table->id();
            
            $table->string('entidad_type', 50);
            $table->unsignedBigInteger('entidad_id');
            
            $table->enum('tipo', ['EMAIL', 'TELEFONO', 'OTRO'])->default('OTRO');
            $table->string('valor', 255);
            
            $table->timestamps();

            $table->index(['entidad_type', 'entidad_id']);
            
            $table->index('tipo');
            $table->index('valor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contactos');
    }
};