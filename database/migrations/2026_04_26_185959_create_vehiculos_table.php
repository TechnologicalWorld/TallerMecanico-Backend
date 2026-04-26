<?php
// database/migrations/2026_04_26_185959_create_vehiculos_table.php
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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')
                  ->constrained('clientes')
                  ->cascadeOnDelete();

            $table->foreignId('sucursal_id')
                  ->constrained('sucursales')
                  ->cascadeOnDelete();

            $table->string('placa', 20)->unique();
            $table->string('vin', 50)->nullable();
            $table->string('marca', 100);
            $table->string('modelo', 100);
            $table->year('anio');
            $table->string('color', 50)->nullable();
            $table->enum('tipo', ['AUTO', 'CAMIONETA', 'MOTO', 'CAMION'])->default('AUTO');
            $table->integer('kilometraje')->default(0);

            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');

            $table->timestamps();
            $table->softDeletes(); 

            $table->index('placa');
            $table->index('marca');
            $table->index('cliente_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};