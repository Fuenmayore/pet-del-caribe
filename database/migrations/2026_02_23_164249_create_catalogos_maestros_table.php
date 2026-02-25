<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. Catálogo de Máquinas
        Schema::create('maquinas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('sub_area', ['Inyeccion', 'Soplado'])->default('Inyeccion');
            $table->softDeletes();
            $table->timestamps();
        });

        // 2. Catálogo Maestro de Referencias (Productos)
        // Basado en tu archivo "BASE REFERENCIAS.csv"
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('item')->unique();           // El código numérico (ej: 2591.0)
            $table->string('descripcion');              // Nombre del producto
            $table->string('area')->nullable();         // Area (ej: TAPA Y ASA)
            $table->decimal('ciclo', 8, 2)->nullable(); // Ciclo estándar
            $table->integer('cavidades')->nullable();   // Cavidades estándar
            $table->string('materia_prima')->default('PET'); 
            $table->softDeletes();
            $table->timestamps();
        });

        // 3. Catálogo de Moldes
        Schema::create('moldes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('cavidades_max');
            $table->softDeletes();
            $table->timestamps();
        });

        // 4. Catálogo de Anomalías (Motivos de desperdicio)
        Schema::create('anomalias_produccion', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('descripcion');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('materias_primas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // Ej: RES-PET-01
            $table->string('nombre');          // Ej: Resina PET Virgen
            $table->string('tipo');            // Ej: Virgen, Molido, Masterbatch
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('anomalias_produccion');
        Schema::dropIfExists('moldes');
        Schema::dropIfExists('productos'); // Eliminar la nueva tabla
        Schema::dropIfExists('maquinas');
    }
};