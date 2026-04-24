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
        Schema::create('sesiones', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('usuario_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            $table->string('token', 255)->unique();
            $table->unsignedBigInteger('token_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamp('login_at')->useCurrent();
            $table->timestamp('logout_at')->nullable();
            
            $table->boolean('activa')->default(true);
            
            $table->timestamps();

            $table->index('usuario_id');
            $table->index('token');
            $table->index('ip');
            $table->index('activa');
            $table->index('login_at');
            
            $table->index(['usuario_id', 'activa', 'login_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesiones');
    }
};