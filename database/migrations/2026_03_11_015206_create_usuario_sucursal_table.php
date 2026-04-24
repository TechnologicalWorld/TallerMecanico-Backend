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
        Schema::create('usuario_sucursal', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreignId('sucursal_id')
                ->constrained('sucursales')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->boolean('es_administrador')->default(false);
            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->index('usuario_id');
            $table->index('sucursal_id');
            $table->index('activo');

            $table->unique(['usuario_id', 'sucursal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_sucursal');
    }
};
