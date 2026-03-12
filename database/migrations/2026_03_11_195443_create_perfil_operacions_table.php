<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfiles_operacion', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // CORRECCIÓN: Usamos foreignUuid() para que coincida con tu arquitectura
            $table->foreignUuid('turno_id')->constrained('turnos')->cascadeOnDelete();
            $table->foreignUuid('config_id')->constrained('produccion_config')->cascadeOnDelete();
            $table->foreignUuid('registrado_por')->constrained('users'); 
            
            $table->string('hora_medicion'); // Ej: "07:00", "13:00", "17:00"
            
            // Bloques técnicos del formato IY-FO-02 en JSON
            $table->json('clamp_expulsor')->nullable();    
            $table->json('inyeccion_carga')->nullable();   
            $table->json('temperaturas')->nullable();      
            $table->json('perifericos_presion')->nullable(); 
            
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfiles_operacion');
    }
};