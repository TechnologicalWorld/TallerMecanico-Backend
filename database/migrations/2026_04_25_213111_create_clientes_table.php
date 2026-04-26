<?php
// app/database/migrations/create_cliente_table.php
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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')
                  ->constrained('personas')
                  ->onDelete('restrict');

            $table->string('codigo_cliente', 50)->unique();
            $table->boolean('activo')->default(true);

            $table->timestamps();
            
            $table->softDeletes(); 

            $table->index('codigo_cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};