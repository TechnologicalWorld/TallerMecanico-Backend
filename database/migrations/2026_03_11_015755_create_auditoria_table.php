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
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('usuario_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
            
            $table->string('accion', 100);
            
            $table->string('entidad_type', 50);
            $table->unsignedBigInteger('entidad_id');
            
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();
            
            $table->timestamps(); 

            $table->index('usuario_id');
            $table->index('accion');
            $table->index(['entidad_type', 'entidad_id']);
            $table->index('created_at');
            
            $table->index(['entidad_type', 'entidad_id', 'created_at']);
            $table->index(['usuario_id', 'created_at']);
            $table->index(['accion', 'created_at']);
            
            $table->index(['created_at', 'accion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};