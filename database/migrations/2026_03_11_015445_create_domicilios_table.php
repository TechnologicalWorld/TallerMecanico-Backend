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
        Schema::create('domicilios', function (Blueprint $table) {
            $table->id();

            $table->string('entidad_type', 50);
            $table->unsignedBigInteger('entidad_id');

            $table->string('tipo', 50)->nullable();
            $table->string('pais', 100)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('direccion', 255);
            $table->string('codigo_postal', 20)->nullable();

            $table->boolean('principal')->default(false);

            $table->softDeletes();

            $table->timestamps();

            $table->index(['entidad_type', 'entidad_id']);

            $table->index('tipo');
            $table->index('pais');
            $table->index('ciudad');
            $table->index('codigo_postal');
            $table->index('principal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domicilios');
    }
};
