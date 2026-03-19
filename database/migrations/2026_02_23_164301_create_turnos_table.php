<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('turnos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('fecha');
            $table->integer('numero_turno');
            $table->integer('duracion_turno');
            $table->foreignId('maquina_id')->constrained('maquinas')->onDelete('restrict');
            $table->foreignUuid('operario_id')->constrained('users')->onDelete('restrict');
            $table->string('supervisor');
            
            // Campo para identificar el área operativa (Inyección / Soplado)
            $table->string('area'); 
            
            $table->enum('estado', ['Abierto', 'Cerrado'])->default('Abierto');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }};