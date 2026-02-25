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
    Schema::create('produccion_config', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('turno_id')->constrained('turnos')->onDelete('restrict');
        $table->string('referencia');
        $table->integer('num_cavidades');
        $table->string('materia_prima');
        $table->decimal('porcentaje_molido', 5, 2);
        $table->softDeletes();
        $table->timestamps();
    });

    Schema::create('produccion_horaria', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('config_id')->constrained('produccion_config')->onDelete('restrict');
        $table->time('hora');
        $table->string('num_vale_inyectora');
        $table->integer('unidades_buenas');
        $table->softDeletes();
        $table->timestamps();
    });

    Schema::create('registros_pnc', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('produccion_hora_id')->constrained('produccion_horaria')->onDelete('restrict');
        $table->foreignId('anomalia_id')->constrained('anomalias_produccion')->onDelete('restrict');
        $table->integer('unid_malas')->default(0);
        $table->integer('unid_contaminadas')->default(0);
        $table->integer('unid_torta')->default(0);
        $table->text('causa')->nullable();
        $table->boolean('sello_reproceso')->default(false);
        $table->softDeletes();
        $table->timestamps();
    });

    Schema::create('tiempos_perdidos', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('turno_id')->constrained('turnos')->onDelete('restrict');
        $table->string('item_motivo');
        $table->time('hora_inicial');
        $table->integer('tiempo_perdido_min');
        $table->text('observaciones')->nullable();
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
