<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_xx_xx_update_produccion_tables.php

public function up(): void {
        // 1. Configuración del Turno (Flexible para materiales y parámetros)
    Schema::create('produccion_config', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('turno_id')->constrained('turnos')->onDelete('restrict');
        
        // Cambiamos 'referencia' por 'producto_id' para mayor claridad
        // Cambiar a nullable() para que el Paso 1 no falle
        $table->foreignId('producto_id')->nullable()->constrained('productos');
        // JSON: Aquí guardamos el color y la mezcla de materiales
        // Ejemplo: {"color": "ROJO", "materiales": [{"id":1, "cantidad": 50}, {"id":2, "cantidad": 20}]}
        $table->json('mezcla_materiales')->nullable(); 
        
        $table->softDeletes();
        $table->timestamps();
    });

    // 2. Producción Horaria (Consolidada con PNC para evitar millones de filas extras)
    Schema::create('produccion_horaria', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('config_id')->constrained('produccion_config')->onDelete('restrict');
        $table->time('hora');
        $table->string('num_vale_inyectora')->nullable();
        $table->integer('unidades_buenas');
        
        // JSON: Aquí guardamos todo el PNC (malas, contaminadas, torta, causa)
        // Ejemplo: {"malas": 5, "contaminadas": 2, "torta": 1, "causa": "Punto negro"}
        $table->json('pnc_detalle')->nullable(); 
        
        $table->softDeletes();
        $table->timestamps();
    });

    // 3. Tiempos Perdidos (Mantenemos por separado pero con observaciones flexibles)
    Schema::create('tiempos_perdidos', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('turno_id')->constrained('turnos')->onDelete('restrict');
        $table->string('item_motivo');
        $table->time('hora_inicial');
        $table->integer('tiempo_perdido_min');
        $table->json('detalle_paro')->nullable(); // JSON para futuras métricas de falla
        $table->softDeletes();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modulo_produccion');
    }
};
