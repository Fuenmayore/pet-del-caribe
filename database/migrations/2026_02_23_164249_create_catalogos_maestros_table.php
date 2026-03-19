<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maquinas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('abreviacion')->nullable(); // <-- NUEVO CAMPO
            $table->enum('sub_area', ['Inyeccion', 'Soplado'])->default('Inyeccion');
            $table->softDeletes();
            $table->timestamps();
        });

        // 2. Catálogo Maestro de Referencias (Productos)
        // database/migrations/xxxx_xx_xx_create_productos_table.php
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('ref_maq')->nullable();           // REF + MAQ
            $table->string('item')->unique();               // Item
            $table->string('descripcion');                  // Desc. item
            $table->string('tipo_inventario')->nullable();  // Tipo inventari
            $table->string('area')->nullable();             // AREA
            $table->string('preforma')->nullable();         // Preforma (Cambiado de preforma_base)
            $table->decimal('ciclo', 8, 2)->nullable();     // Ciclo
            $table->string('maquina')->nullable();          // Maquina (Cambiado de maquina_ideal)
            $table->integer('cavidades')->nullable();       // Cavidades
            $table->integer('bot_hora')->nullable();        // BOT/HORA
            $table->string('unidad_empaque')->nullable();   // UNIDAD DE EMPAQUE
            $table->string('centro_trabajo')->nullable();   // Centro de trab

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

        // database/migrations/xxxx_xx_xx_create_pnc_catalogo_table.php
        Schema::create('pnc_catalogo', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre');
            $table->enum('area', ['Inyección', 'Soplado', 'Ambos']);
            $table->timestamps();
            $table->softDeletes(); // 
        });

        // database/migrations/xxxx_xx_xx_create_paradas_catalogo_table.php
        Schema::create('paradas_catalogo', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Estándar Fase I [cite: 22]
            $table->string('codigo')->unique();
            $table->string('falla');
            $table->string('categoria')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('materia_prima', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Estándar UUID definido en Fase I
            $table->string('nombre')->unique();
            $table->enum('tipo', ['Resina', 'Preforma', 'Pigmento', 'Aditivo', 'Otro']);
            $table->string('unidad_medida')->default('KG');
            $table->string('proveedor')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Integridad de datos industriales
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomalias_produccion');
        Schema::dropIfExists('moldes');
        Schema::dropIfExists('productos'); // Eliminar la nueva tabla
        Schema::dropIfExists('maquinas');
    }
};
