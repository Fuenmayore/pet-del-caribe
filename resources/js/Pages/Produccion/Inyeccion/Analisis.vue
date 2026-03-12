<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    turno: Object,
    pncCatalogo: Array,
    paradasCatalogo: Array,
    materiales: Array
});

const cavidadActiva = ref(null);

const gestionarCavidad = (n, datos) => {
    if (cavidadActiva.value?.n === n) {
        cavidadActiva.value = null;
    } else {
        cavidadActiva.value = { n, ...datos };
    }
};

// --- LÓGICA PARA EXTRAER PERFILES DE OPERACIÓN ---
const perfilesDelTurno = computed(() => {
    let perfiles = [];
    props.turno.configuraciones.forEach(c => {
        const guardados = c.perfiles_operacion || c.perfilesOperacion || [];
        // Le agregamos la referencia del producto para saber a qué molde pertenece
        guardados.forEach(p => perfiles.push({ ...p, nombre_producto: c.producto?.descripcion }));
    });
    // Ordenamos por fecha de creación (Lectura 1, Lectura 2...)
    return perfiles.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
});

const modalPerfiles = ref(false);
const lecturaActiva = ref(0); // Índice del perfil que estamos viendo en el modal
const tabPerfilActivo = ref(1); // 1:Cierre, 2:Iny, 3:Temp, 4:Perif

// Arrays de configuración para iterar las tablas en solo lectura
const clampRows = ['Cls Hi Spd', 'Cierre 2', 'Mold Protec', 'Cls Hi Press', 'Star Slow', 'Apertura 2', 'Open Hi Spd', 'Ending slow'];
const ejectRows = ['Forward/Ejector', 'Salida 2/Mould Closing', 'Backward/Ejector', 'Entrada lenta', 'Nozzle(Tobera)', 'Adjust(Molde)'];
const injectRows = ['Inj # 1', 'Inj # 2', 'Inj # 3', 'Inj # 4', 'Hld # 1', 'Hld # 2', 'Hld # 3'];
const chargeRows = ['Charge 1', 'Charge 2', 'Suck Back', 'Cool Bf. Chg', 'Cooling (Enf.)', 'Auto pur', 'Auto pur Time'];
const zonasTemp = ['Zona 1', 'Zona 2', 'Zona 3', 'Zona 4', 'Zona 5', 'Zona 6', 'Zona 7', 'Oil T°', 'T° Agua', 'T° Tolva'];
const coladaTemp = ['1', '2', '3', '4', '5', '6', '7', '8'];

const reporte = computed(() => {
    let r = {
        buenas: 0, esperadas: 0, 
        malasKg: 0, contKg: 0, tortaKg: 0,
        minutosParo: 0,
        parosPorMotivo: {},
        defectosPorTipo: {},
        cavidadesDetalle: {},
        resumenFases: [],
        trazabilidad: [],
        perfilesOperacionCount: perfilesDelTurno.value.length,
        totalCavidadesAfectadas: 0,
        maxFallasEnUnaCavidad: 0
    };

    props.turno.configuraciones.forEach(config => {
        let buenasFase = 0;
        let esperadasFase = 0;
        const horas = config.horas_produccion || config.horasProduccion || [];
        
        const mezclaReal = (config.mezcla_materiales?.materiales || []).map(m => {
            const materialDb = props.materiales.find(mat => mat.id === m.materia_prima_id);
            return { nombre: materialDb ? materialDb.nombre : (m.nombre || 'Desconocido'), porcentaje: m.porcentaje };
        });

        // --- EL CORAZÓN DEL OEE REAL ---
        // Extraemos el estándar de ingeniería desde la base de datos (Incorruptible)
        const cicloEstandar = parseFloat(config.producto?.ciclo) || 0;
        const cavidadesEstandar = parseInt(config.producto?.cavidades) || 0;
        
        // Fórmula Universal: (3600 seg / ciclo estandar) * cavidades estándar
        const metaTeoricaPorHora = cicloEstandar > 0 ? (3600 / cicloEstandar) * cavidadesEstandar : 0;

        horas.forEach(h => {
            const det = h.pnc_detalle || {};
            const unidadesReales = parseInt(h.unidades_buenas || 0);

            r.buenas += unidadesReales;
            buenasFase += unidadesReales;
            
            // Sumamos la meta teórica de esa hora al total esperado
            r.esperadas += metaTeoricaPorHora;
            esperadasFase += metaTeoricaPorHora;

            // OEE Estricto de la hora: (Producción Real / Meta Teórica) * 100
            const oeeHora = metaTeoricaPorHora > 0 ? (unidadesReales / metaTeoricaPorHora) * 100 : 0;

            r.trazabilidad.push({
                hora: h.hora, 
                referencia: config.producto?.descripcion, 
                lote: h.num_vale_inyectora || 'S/N',
                buenas: unidadesReales, 
                eficiencia: oeeHora.toFixed(1), // Muestra el OEE Real auditado
                eventos: (det.paros?.length || 0) + (det.defectos?.length || 0)
            });

            if (det.paros) {
                det.paros.forEach(p => {
                    const nombreFalla = props.paradasCatalogo.find(f => f.id === p.falla_id)?.falla || p.motivo || 'Falla Indefinida';
                    r.minutosParo += parseInt(p.minutos || 0);
                    r.parosPorMotivo[nombreFalla] = (r.parosPorMotivo[nombreFalla] || 0) + parseInt(p.minutos || 0);
                });
            }

            if (det.defectos) {
                det.defectos.forEach(d => {
                    const nombrePnc = props.pncCatalogo.find(p => p.id === d.anomalia_id)?.nombre || 'Defecto Indefinido';
                    const totalScrap = parseFloat(d.malas_kg || 0) + parseFloat(d.cont_kg || 0) + parseFloat(d.torta_kg || 0);
                    
                    r.malasKg += parseFloat(d.malas_kg || 0);
                    r.contKg += parseFloat(d.cont_kg || 0);
                    r.tortaKg += parseFloat(d.torta_kg || 0);

                    if(totalScrap > 0) r.defectosPorTipo[nombrePnc] = (r.defectosPorTipo[nombrePnc] || 0) + totalScrap;
                });
            }

            if (det.inspeccion) {
                det.inspeccion.forEach(ins => {
                    if (!r.cavidadesDetalle[ins.cav]) {
                        r.cavidadesDetalle[ins.cav] = { count: 0, fallas: [] };
                        r.totalCavidadesAfectadas++;
                    }
                    const nombreFalla = props.pncCatalogo.find(df => df.id === ins.defecto)?.nombre || 'Otro';
                    r.cavidadesDetalle[ins.cav].count++;
                    if (r.cavidadesDetalle[ins.cav].count > r.maxFallasEnUnaCavidad) r.maxFallasEnUnaCavidad = r.cavidadesDetalle[ins.cav].count;
                    r.cavidadesDetalle[ins.cav].fallas.push({ hora: h.hora, defecto: nombreFalla });
                });
            }
        });

        r.resumenFases.push({
            producto: config.producto?.descripcion, 
            color: config.mezcla_materiales?.color || 'N/A',
            mezcla: mezclaReal, 
            // OEE Real de toda la fase
            eficiencia: esperadasFase > 0 ? ((buenasFase / esperadasFase) * 100).toFixed(1) : 0
        });
    });

    // OEE Real Global del Turno
    r.eficienciaTotal = r.esperadas > 0 ? ((r.buenas / r.esperadas) * 100).toFixed(1) : 0;
    r.scrapTotal = (r.malasKg + r.contKg + r.tortaKg).toFixed(1);
    
    r.parosPorMotivo = Object.fromEntries(Object.entries(r.parosPorMotivo).sort(([,a],[,b]) => b - a));
    r.defectosPorTipo = Object.fromEntries(Object.entries(r.defectosPorTipo).sort(([,a],[,b]) => b - a));

    return r;
});

const getCavidadColor = (count, max) => {
    if (!count || max === 0) return 'bg-slate-50 border-slate-200 text-slate-400';
    const ratio = count / max;
    if (ratio > 0.7) return 'bg-red-600 border-red-700 text-white shadow-red-200 shadow-md';
    if (ratio > 0.4) return 'bg-orange-500 border-orange-600 text-white shadow-orange-200 shadow-md';
    return 'bg-amber-400 border-amber-500 text-white shadow-amber-200 shadow-md';
};
</script>

<template>
    <Head title="Resumen de Turno" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-50 pb-20">
            
            <div class="bg-white border-b border-slate-200 px-4 md:px-6 py-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 sticky top-0 z-40">
                <div class="flex items-center gap-4">
                    <Link :href="route('produccion.index')" class="p-2 bg-slate-100 rounded-lg hover:bg-slate-200 text-slate-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <div>
                        <h1 class="text-lg md:text-xl font-black text-slate-800 tracking-tight uppercase leading-none">Resumen de Turno</h1>
                        <p class="text-[9px] md:text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">
                            {{ turno.maquina?.nombre }} • {{ turno.fecha }} • T{{ turno.numero_turno }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-between md:justify-end w-full md:w-auto gap-4 md:gap-6 bg-slate-50 px-4 py-2 rounded-xl border border-slate-200 text-[9px] md:text-[10px] uppercase font-black tracking-widest">
                    <div class="flex flex-col text-left md:text-right"><span class="text-slate-400">Operador</span><span class="text-slate-700 truncate max-w-[120px]">{{ turno.operario?.nombre || turno.operario?.name }}</span></div>
                    <div class="w-px h-6 bg-slate-300"></div>
                    <div class="flex flex-col text-right md:text-left"><span class="text-slate-400">Supervisor</span><span class="text-blue-600">{{ turno.supervisor }}</span></div>
                </div>
            </div>

            <div class="max-w-screen-2xl mx-auto px-4 md:px-6 pt-6 space-y-6 animate-in fade-in duration-500">
                
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4">
                    
                    <div class="col-span-2 lg:col-span-1 bg-slate-900 border border-slate-800 rounded-[1.5rem] md:rounded-[2rem] p-5 relative overflow-hidden flex flex-col justify-between group hover:border-blue-500 transition-colors">
                        <div class="flex justify-between items-start">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Eficiencia OEE</p>
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                        </div>
                        <div class="mt-4">
                            <h2 class="text-4xl md:text-5xl font-black text-white tracking-tighter">{{ reporte.eficienciaTotal }}<span class="text-xl md:text-2xl text-slate-500">%</span></h2>
                            <div class="mt-3 h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                                <div :class="['h-full transition-all duration-1000', reporte.eficienciaTotal >= 85 ? 'bg-emerald-500' : 'bg-red-500']" :style="{ width: Math.min(reporte.eficienciaTotal, 100) + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-1 bg-white border border-slate-200 rounded-[1.5rem] md:rounded-[2rem] p-4 md:p-5 flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-2 gap-2">
                            <p class="text-[8px] md:text-[10px] font-black text-slate-400 uppercase tracking-widest leading-tight">Logrado</p>
                            <div class="p-1 md:p-1.5 bg-emerald-50 text-emerald-600 rounded-md shrink-0"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg></div>
                        </div>
                        <div>
                            <h2 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tighter truncate">{{ reporte.buenas.toLocaleString() }}</h2>
                            <p class="text-[8px] md:text-[9px] font-bold text-slate-400 uppercase mt-0.5 truncate">Meta Estandar: {{ Math.floor(reporte.esperadas).toLocaleString() }}</p>
                        </div>
                    </div>

                    <div class="col-span-1 bg-white border border-slate-200 rounded-[1.5rem] md:rounded-[2rem] p-4 md:p-5 flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-2 gap-2">
                            <p class="text-[8px] md:text-[10px] font-black text-slate-400 uppercase tracking-widest leading-tight">Scrap Kg</p>
                            <div class="p-1 md:p-1.5 bg-red-50 text-red-600 rounded-md shrink-0"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></div>
                        </div>
                        <div>
                            <h2 class="text-2xl md:text-3xl font-black text-red-600 tracking-tighter truncate">{{ reporte.scrapTotal }}<span class="text-xs text-red-300 ml-1">kg</span></h2>
                            <p class="text-[8px] md:text-[9px] font-bold text-slate-400 uppercase mt-0.5 truncate">Pérdida Total</p>
                        </div>
                    </div>

                    <div class="col-span-1 bg-white border border-slate-200 rounded-[1.5rem] md:rounded-[2rem] p-4 md:p-5 flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-2 gap-2">
                            <p class="text-[8px] md:text-[10px] font-black text-slate-400 uppercase tracking-widest leading-tight">Paro min</p>
                            <div class="p-1 md:p-1.5 bg-orange-50 text-orange-600 rounded-md shrink-0"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                        </div>
                        <div>
                            <h2 class="text-2xl md:text-3xl font-black text-orange-500 tracking-tighter truncate">{{ reporte.minutosParo }}<span class="text-xs text-orange-300 ml-1">min</span></h2>
                            <p class="text-[8px] md:text-[9px] font-bold text-slate-400 uppercase mt-0.5 truncate">Tiempo muerto</p>
                        </div>
                    </div>

                    <button @click="reporte.perfilesOperacionCount > 0 ? modalPerfiles = true : null" 
                        :class="['col-span-1 border rounded-[1.5rem] md:rounded-[2rem] p-4 md:p-5 flex flex-col justify-between transition-colors relative group text-left outline-none focus:ring-4 focus:ring-blue-500/20', reporte.perfilesOperacionCount >= 3 ? 'bg-emerald-50 border-emerald-200 hover:border-emerald-400' : (reporte.perfilesOperacionCount > 0 ? 'bg-amber-50 border-amber-200 hover:border-amber-400' : 'bg-slate-50 border-slate-200 cursor-not-allowed opacity-70')]">
                        <div class="flex justify-between items-start mb-2 gap-2">
                            <p class="text-[8px] md:text-[10px] font-black text-slate-500 uppercase tracking-widest leading-tight">Perfiles</p>
                            <div :class="['p-1 md:p-1.5 rounded-md shrink-0', reporte.perfilesOperacionCount >= 3 ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-200 text-slate-400']"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4L19 7" /></svg></div>
                        </div>
                        <div>
                            <h2 :class="['text-2xl md:text-3xl font-black tracking-tighter truncate', reporte.perfilesOperacionCount >= 3 ? 'text-emerald-700' : 'text-slate-800']">{{ reporte.perfilesOperacionCount }}<span class="text-xs text-slate-400 ml-1">/ 3</span></h2>
                            <p :class="['text-[8px] md:text-[9px] font-bold uppercase mt-0.5 truncate', reporte.perfilesOperacionCount >= 3 ? 'text-emerald-600' : 'text-red-500']">{{ reporte.perfilesOperacionCount >= 3 ? 'Cumplimiento OK' : 'Faltan Lecturas' }}</p>
                        </div>
                        <div v-if="reporte.perfilesOperacionCount > 0" class="absolute bottom-3 md:bottom-4 right-3 md:right-4 text-blue-500 opacity-50 group-hover:opacity-100 transition-opacity group-hover:translate-x-1">
                            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </div>
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 flex flex-col shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-[11px] font-black text-slate-800 uppercase tracking-widest border-b border-slate-100 pb-4 mb-4 flex items-center justify-between">
                            <span>Mantenimiento & Operación</span>
                            <span class="text-[9px] text-slate-400 bg-slate-50 px-2 py-1 rounded">Minutos de Paro</span>
                        </h3>
                        <div class="flex-grow space-y-4">
                            <div v-for="(min, motivo) in reporte.parosPorMotivo" :key="motivo" class="group">
                                <div class="flex justify-between text-[10px] font-bold uppercase mb-1">
                                    <span class="text-slate-600 truncate pr-4" :title="motivo">{{ motivo }}</span>
                                    <span class="text-orange-600 font-black shrink-0">{{ min }} m</span>
                                </div>
                                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-orange-400 rounded-full" :style="{ width: (min/reporte.minutosParo)*100 + '%' }"></div>
                                </div>
                            </div>
                            <div v-if="Object.keys(reporte.parosPorMotivo).length === 0" class="h-full flex items-center justify-center py-10">
                                <p class="text-xs font-bold text-slate-300 uppercase tracking-widest border-2 border-dashed border-slate-200 p-4 rounded-xl">Sin incidencias reportadas</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-3xl p-6 flex flex-col shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-[11px] font-black text-slate-800 uppercase tracking-widest border-b border-slate-100 pb-4 mb-4 flex items-center justify-between">
                            <span>Defectos de Calidad</span>
                            <span class="text-[9px] text-slate-400 bg-slate-50 px-2 py-1 rounded">Kilogramos de Scrap</span>
                        </h3>
                        <div class="flex-grow space-y-4">
                            <div v-for="(kg, defecto) in reporte.defectosPorTipo" :key="defecto">
                                <div class="flex justify-between text-[10px] font-bold uppercase mb-1">
                                    <span class="text-slate-600 truncate pr-4">{{ defecto }}</span>
                                    <span class="text-red-600 font-black shrink-0">{{ kg.toFixed(1) }} kg</span>
                                </div>
                                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-red-500 rounded-full" :style="{ width: (kg/reporte.scrapTotal)*100 + '%' }"></div>
                                </div>
                            </div>
                            <div v-if="Object.keys(reporte.defectosPorTipo).length === 0" class="h-full flex items-center justify-center py-10">
                                <p class="text-xs font-bold text-slate-300 uppercase tracking-widest border-2 border-dashed border-slate-200 p-4 rounded-xl">Cero Scrap Generado</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 mt-6 pt-4 border-t border-slate-100">
                            <div v-for="(val, label) in { Malas: reporte.malasKg, Contaminada: reporte.contKg, Torta: reporte.tortaKg }" :key="label" class="bg-slate-50 rounded-lg p-2 text-center">
                                <p class="text-[8px] font-black text-slate-400 uppercase">{{ label }}</p>
                                <p class="text-sm font-black text-slate-700">{{ val.toFixed(1) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-[11px] font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div> Inspección de Preformas (Heatmap)
                            </h3>
                            <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase">Cavidades con mayor incidencia de bloqueos o defectos</p>
                        </div>
                        <div class="flex gap-2 text-[8px] font-black uppercase tracking-widest items-center">
                            <span class="text-slate-400 hidden sm:inline">Severidad:</span>
                            <div class="flex items-center gap-1"><div class="w-3 h-3 bg-amber-400 rounded"></div> Baja</div>
                            <div class="flex items-center gap-1"><div class="w-3 h-3 bg-orange-500 rounded"></div> Media</div>
                            <div class="flex items-center gap-1"><div class="w-3 h-3 bg-red-600 rounded"></div> Alta</div>
                        </div>
                    </div>

                    <div v-if="Object.keys(reporte.cavidadesDetalle).length === 0" class="py-8 text-center bg-slate-50 rounded-xl border border-slate-100">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Todas las cavidades operando óptimamente</p>
                    </div>

                    <div v-else class="grid grid-cols-6 sm:grid-cols-10 md:grid-cols-12 lg:grid-cols-16 gap-1.5">
                        <div v-for="(datos, cav) in reporte.cavidadesDetalle" :key="cav" class="relative group cursor-help">
                            <div :class="['h-10 w-full rounded-md flex flex-col items-center justify-center transition-transform hover:scale-110 border', getCavidadColor(datos.count, reporte.maxFallasEnUnaCavidad)]">
                                <span class="text-[8px] opacity-80 leading-none">C{{ cav }}</span>
                                <span class="text-xs font-black leading-none mt-0.5">{{ datos.count }}</span>
                            </div>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 bg-slate-900 text-white rounded-xl p-3 shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-10 pointer-events-none scale-95 group-hover:scale-100">
                                <p class="text-[9px] font-black uppercase text-blue-400 border-b border-slate-700 pb-1 mb-2">Historial Cav {{ cav }}</p>
                                <ul class="space-y-1 max-h-32 overflow-y-auto custom-scrollbar pr-1">
                                    <li v-for="(f, i) in datos.fallas" :key="i" class="text-[9px] flex justify-between font-bold">
                                        <span class="text-slate-300 truncate pr-2">{{ f.defecto }}</span>
                                        <span class="text-emerald-400 font-mono">{{ f.hora }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="text-[11px] font-black text-slate-800 uppercase tracking-widest">Trazabilidad de Lotes y Referencias</h3>
                    </div>
                    
                    <div class="overflow-x-auto custom-scrollbar w-full">
                        <table class="w-full text-left min-w-[600px]">
                            <thead class="bg-white">
                                <tr class="text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200">
                                    <th class="px-6 py-4">Hora</th>
                                    <th class="px-6 py-4">Referencia / Mezcla</th>
                                    <th class="px-6 py-4">Lote Inyectora</th>
                                    <th class="px-6 py-4 text-center">Unidades</th>
                                    <th class="px-6 py-4">Status OEE (Real)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="h in reporte.trazabilidad" :key="h.hora" class="hover:bg-slate-50 transition-colors whitespace-nowrap">
                                    <td class="px-6 py-4 font-mono font-black text-slate-500 text-xs">{{ h.hora }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-[10px] font-black text-slate-800 uppercase leading-none mb-1 max-w-[200px] truncate" :title="h.referencia">{{ h.referencia || 'N/A' }}</p>
                                        <p v-if="h.eventos > 0" class="text-[8px] font-bold text-red-500 uppercase">Se reportaron {{ h.eventos }} incidencias en esta hora</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[9px] font-black rounded border border-slate-200 uppercase">{{ h.lote }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono font-black text-slate-700 text-sm">{{ h.buenas.toLocaleString() }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2 max-w-[120px]">
                                            <span :class="['w-8 text-right font-black text-[10px] font-mono', h.eficiencia < 85 ? 'text-red-500' : 'text-emerald-600']">{{ h.eficiencia }}%</span>
                                            <div class="flex-1 bg-slate-100 h-1.5 rounded-full overflow-hidden hidden sm:block">
                                                <div :class="['h-full rounded-full', h.eficiencia < 85 ? 'bg-red-500' : 'bg-emerald-500']" :style="{width: Math.min(h.eficiencia, 100) + '%'}"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <transition name="fade">
            <div v-if="modalPerfiles && perfilesDelTurno.length > 0" class="fixed inset-0 z-[200] flex items-center justify-center p-2 md:p-6 bg-slate-900/80 backdrop-blur-sm">
                <div class="bg-white w-full max-w-6xl rounded-[1.5rem] md:rounded-[2rem] shadow-2xl flex flex-col h-[95vh] md:h-auto md:max-h-[90vh] overflow-hidden animate-in zoom-in-95">
                    
                    <div class="bg-slate-900 p-4 md:p-6 flex items-start md:items-center justify-between shrink-0 gap-4">
                        <div>
                            <h3 class="text-white font-black text-sm md:text-lg uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-4 h-4 md:w-5 md:h-5 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <span class="leading-tight">Seguimiento de Perfiles</span>
                            </h3>
                            <p class="text-slate-400 text-[8px] md:text-[10px] font-bold uppercase tracking-widest mt-1">CÓDIGO IY-FO-02 | Versión 02 (01-04-2018)</p>
                        </div>
                        <button @click="modalPerfiles = false" class="w-8 h-8 md:w-10 md:h-10 bg-white/10 hover:bg-red-500 text-white rounded-full flex items-center justify-center transition-colors shrink-0">✕</button>
                    </div>

                    <div class="bg-slate-100 border-b border-slate-200 p-3 md:p-4 flex items-center gap-2 overflow-x-auto shrink-0 custom-scrollbar whitespace-nowrap">
                        <button v-for="(perfil, index) in perfilesDelTurno" :key="perfil.id" 
                            @click="lecturaActiva = index"
                            :class="['px-4 md:px-6 py-2 md:py-3 rounded-xl text-[10px] md:text-xs font-black uppercase transition-all flex flex-col items-start shrink-0', lecturaActiva === index ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-indigo-50 border border-slate-200']">
                            <span>Lectura {{ index + 1 }}</span>
                            <span :class="['text-[8px] md:text-[9px] font-bold mt-0.5', lecturaActiva === index ? 'text-indigo-200' : 'text-slate-400']">{{ perfil.hora_medicion }}</span>
                        </button>
                    </div>

                    <div class="flex-grow flex flex-col overflow-hidden bg-slate-50">
                        
                        <div class="flex bg-white border-b border-slate-200 p-2 gap-2 overflow-x-auto shrink-0 custom-scrollbar">
                            <button @click="tabPerfilActivo = 1" :class="['shrink-0 py-2 px-3 md:px-4 rounded-lg text-[9px] md:text-[10px] font-black uppercase transition-all', tabPerfilActivo === 1 ? 'bg-slate-900 text-white' : 'text-slate-500 bg-slate-50 hover:bg-slate-100 border border-slate-100']">1. Clamp/Eject</button>
                            <button @click="tabPerfilActivo = 2" :class="['shrink-0 py-2 px-3 md:px-4 rounded-lg text-[9px] md:text-[10px] font-black uppercase transition-all', tabPerfilActivo === 2 ? 'bg-slate-900 text-white' : 'text-slate-500 bg-slate-50 hover:bg-slate-100 border border-slate-100']">2. Inject/Charge</button>
                            <button @click="tabPerfilActivo = 3" :class="['shrink-0 py-2 px-3 md:px-4 rounded-lg text-[9px] md:text-[10px] font-black uppercase transition-all', tabPerfilActivo === 3 ? 'bg-slate-900 text-white' : 'text-slate-500 bg-slate-50 hover:bg-slate-100 border border-slate-100']">3. Temperaturas</button>
                            <button @click="tabPerfilActivo = 4" :class="['shrink-0 py-2 px-3 md:px-4 rounded-lg text-[9px] md:text-[10px] font-black uppercase transition-all', tabPerfilActivo === 4 ? 'bg-slate-900 text-white' : 'text-slate-500 bg-slate-50 hover:bg-slate-100 border border-slate-100']">4. Extras</button>
                        </div>

                        <div class="flex-grow overflow-y-auto p-4 md:p-6 custom-scrollbar" v-if="perfilesDelTurno[lecturaActiva]">
                            
                            <div class="mb-4 md:mb-6 flex flex-col sm:flex-row gap-2 sm:gap-4 bg-blue-50 border border-blue-100 p-3 md:p-4 rounded-xl">
                                <div class="flex-1">
                                    <p class="text-[8px] md:text-[9px] text-blue-400 font-black uppercase">Producto</p>
                                    <p class="text-[10px] md:text-xs font-black text-blue-900 uppercase truncate">{{ perfilesDelTurno[lecturaActiva].nombre_producto || 'N/A' }}</p>
                                </div>
                                <div class="hidden sm:block border-l border-blue-200"></div>
                                <div class="flex-1">
                                    <p class="text-[8px] md:text-[9px] text-blue-400 font-black uppercase">Auditor</p>
                                    <p class="text-[10px] md:text-xs font-black text-blue-900 uppercase truncate">{{ perfilesDelTurno[lecturaActiva].registrador?.nombre || 'Usuario Desconocido' }}</p>
                                </div>
                            </div>

                            <div v-show="tabPerfilActivo === 1" class="space-y-4 md:space-y-6">
                                <div>
                                    <h4 class="text-[9px] md:text-[10px] font-black text-slate-500 uppercase bg-slate-200 px-3 py-1.5 rounded-t-lg">M.PLT (Ajuste / Clamp)</h4>
                                    <div class="overflow-x-auto w-full custom-scrollbar border-x border-b border-slate-200 rounded-b-lg bg-white">
                                        <table class="w-full text-left min-w-[300px]">
                                            <thead class="bg-slate-50 text-[8px] md:text-[9px] font-black text-slate-400 uppercase"><tr><th class="p-2 border-b whitespace-nowrap">Parámetro</th><th class="p-2 border-b text-center">Press</th><th class="p-2 border-b text-center">Spd</th><th class="p-2 border-b text-center">End-Posn</th></tr></thead>
                                            <tbody class="divide-y divide-slate-100">
                                                <tr v-for="row in clampRows" :key="row">
                                                    <td class="p-2 text-[9px] md:text-[10px] font-bold text-slate-600 pl-3 md:pl-4 whitespace-nowrap">{{ row }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].clamp_expulsor?.clamp?.[row]?.press || '-' }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].clamp_expulsor?.clamp?.[row]?.spd || '-' }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].clamp_expulsor?.clamp?.[row]?.end || '-' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between items-center bg-slate-200 px-3 py-1.5 rounded-t-lg">
                                        <h4 class="text-[9px] md:text-[10px] font-black text-slate-500 uppercase">EJECT (Expulsor)</h4>
                                        <div class="flex gap-2 md:gap-4 text-[8px] md:text-[9px] font-black text-slate-600 uppercase">
                                            <span>Md: <span class="bg-white px-1.5 py-0.5 rounded">{{ perfilesDelTurno[lecturaActiva].clamp_expulsor?.eject_md || '-' }}</span></span>
                                            <span>Cnt: <span class="bg-white px-1.5 py-0.5 rounded">{{ perfilesDelTurno[lecturaActiva].clamp_expulsor?.eject_cnt || '-' }}</span></span>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto w-full custom-scrollbar border-x border-b border-slate-200 rounded-b-lg bg-white">
                                        <table class="w-full text-left min-w-[300px]">
                                            <thead class="bg-slate-50 text-[8px] md:text-[9px] font-black text-slate-400 uppercase"><tr><th class="p-2 border-b whitespace-nowrap">Parámetro</th><th class="p-2 border-b text-center">Press</th><th class="p-2 border-b text-center">Spd</th><th class="p-2 border-b text-center">De La</th></tr></thead>
                                            <tbody class="divide-y divide-slate-100">
                                                <tr v-for="row in ejectRows" :key="row">
                                                    <td class="p-2 text-[9px] md:text-[10px] font-bold text-slate-600 pl-3 md:pl-4 whitespace-nowrap">{{ row }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].clamp_expulsor?.eject?.[row]?.press || '-' }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].clamp_expulsor?.eject?.[row]?.spd || '-' }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].clamp_expulsor?.eject?.[row]?.end || '-' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div v-show="tabPerfilActivo === 2" class="space-y-4 md:space-y-6">
                                <div>
                                    <h4 class="text-[9px] md:text-[10px] font-black text-slate-500 uppercase bg-slate-200 px-3 py-1.5 rounded-t-lg">INJECT (Inyección)</h4>
                                    <div class="overflow-x-auto w-full custom-scrollbar border-x border-b border-slate-200 rounded-b-lg bg-white">
                                        <table class="w-full text-left min-w-[350px]">
                                            <thead class="bg-slate-50 text-[8px] md:text-[9px] font-black text-slate-400 uppercase"><tr><th class="p-2 border-b whitespace-nowrap">Parámetro</th><th class="p-2 border-b text-center">Press</th><th class="p-2 border-b text-center">Spd</th><th class="p-2 border-b text-center">Times</th><th class="p-2 border-b text-center">End Posn</th><th class="p-2 border-b text-center">Umbral</th></tr></thead>
                                            <tbody class="divide-y divide-slate-100">
                                                <tr v-for="row in injectRows" :key="row">
                                                    <td class="p-2 text-[9px] md:text-[10px] font-bold text-slate-600 pl-3 md:pl-4 whitespace-nowrap">{{ row }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].inyeccion_carga?.inject?.[row]?.press || '-' }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].inyeccion_carga?.inject?.[row]?.spd || '-' }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].inyeccion_carga?.inject?.[row]?.times || '-' }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].inyeccion_carga?.inject?.[row]?.end || '-' }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].inyeccion_carga?.inject?.[row]?.umbral || '-' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between items-center bg-slate-200 px-3 py-1.5 rounded-t-lg">
                                        <h4 class="text-[9px] md:text-[10px] font-black text-slate-500 uppercase">Carga/Rechupe</h4>
                                        <span class="text-[8px] md:text-[9px] font-black text-slate-600 uppercase">Enfriamiento: <span class="bg-white px-1.5 py-0.5 rounded">{{ perfilesDelTurno[lecturaActiva].inyeccion_carga?.enfriamiento || '-' }} s</span></span>
                                    </div>
                                    <div class="overflow-x-auto w-full custom-scrollbar border-x border-b border-slate-200 rounded-b-lg bg-white">
                                        <table class="w-full text-left min-w-[300px]">
                                            <thead class="bg-slate-50 text-[8px] md:text-[9px] font-black text-slate-400 uppercase"><tr><th class="p-2 border-b whitespace-nowrap">Parámetro</th><th class="p-2 border-b text-center">Press</th><th class="p-2 border-b text-center">Spd</th><th class="p-2 border-b text-center">End Posn</th><th class="p-2 border-b text-center">Colchón</th></tr></thead>
                                            <tbody class="divide-y divide-slate-100">
                                                <tr v-for="row in chargeRows" :key="row">
                                                    <td class="p-2 text-[9px] md:text-[10px] font-bold text-slate-600 pl-3 md:pl-4 whitespace-nowrap">{{ row }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].inyeccion_carga?.carga?.[row]?.press || '-' }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].inyeccion_carga?.carga?.[row]?.spd || '-' }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].inyeccion_carga?.carga?.[row]?.end || '-' }}</td>
                                                    <td class="p-2 text-center text-[9px] md:text-[10px] font-mono text-slate-800">{{ perfilesDelTurno[lecturaActiva].inyeccion_carga?.carga?.[row]?.colchon || '-' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div v-show="tabPerfilActivo === 3" class="space-y-4 md:space-y-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="bg-white border border-slate-200 rounded-xl p-3 md:p-4">
                                        <h4 class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase border-b pb-2 mb-2">Tobera / Aceite / Agua</h4>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div v-for="zona in zonasTemp" :key="zona" class="flex justify-between border-b border-slate-50 pb-1">
                                                <span class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase">{{ zona }}</span>
                                                <span class="text-[9px] md:text-[10px] font-mono font-black text-red-600">{{ perfilesDelTurno[lecturaActiva].temperaturas?.zonas?.[zona]?.val || '-' }}°</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-white border border-slate-200 rounded-xl p-3 md:p-4">
                                        <h4 class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase border-b pb-2 mb-2">Colada</h4>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div v-for="colada in coladaTemp" :key="colada" class="flex justify-between border-b border-slate-50 pb-1">
                                                <span class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase">Colada {{ colada }}</span>
                                                <span class="text-[9px] md:text-[10px] font-mono font-black text-orange-500">{{ perfilesDelTurno[lecturaActiva].temperaturas?.colada?.[colada]?.val || '-' }}°</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white border border-slate-200 rounded-xl p-3 md:p-4">
                                    <h4 class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase border-b pb-2 mb-3">Puntos de Inyección (48 Cavidades)</h4>
                                    <div class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-12 lg:grid-cols-16 gap-1">
                                        <div v-for="n in 49" :key="n-1" class="flex flex-col bg-slate-50 py-1 px-0.5 rounded border border-slate-100 text-center">
                                            <span class="text-[6px] md:text-[7px] font-black text-slate-400">C{{ n-1 }}</span>
                                            <span class="text-[8px] md:text-[9px] font-mono font-black text-slate-800 leading-tight mt-0.5">{{ perfilesDelTurno[lecturaActiva].temperaturas?.reguladores?.[n-1] || '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-show="tabPerfilActivo === 4" class="space-y-4 md:space-y-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="bg-white border border-slate-200 rounded-xl p-4">
                                        <h4 class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase border-b pb-2 mb-2">Dosificador Colorante</h4>
                                        <ul class="space-y-2 text-[9px] md:text-[10px]">
                                            <li class="flex justify-between border-b border-slate-50 pb-1"><span class="font-bold text-slate-500">RPM</span><span class="font-mono font-black">{{ perfilesDelTurno[lecturaActiva].perifericos_presion?.dosificador?.rpm || '-' }}</span></li>
                                            <li class="flex justify-between border-b border-slate-50 pb-1"><span class="font-bold text-slate-500">SHOT</span><span class="font-mono font-black">{{ perfilesDelTurno[lecturaActiva].perifericos_presion?.dosificador?.shot || '-' }}</span></li>
                                            <li class="flex justify-between border-b border-slate-50 pb-1"><span class="font-bold text-slate-500">TIEMPO</span><span class="font-mono font-black">{{ perfilesDelTurno[lecturaActiva].perifericos_presion?.dosificador?.tiempo || '-' }}</span></li>
                                            <li class="flex justify-between"><span class="font-bold text-slate-500">% DOSIFICACIÓN</span><span class="font-mono font-black text-blue-600">{{ perfilesDelTurno[lecturaActiva].perifericos_presion?.dosificador?.porcentaje || '-' }}%</span></li>
                                        </ul>
                                    </div>
                                    <div class="bg-white border border-slate-200 rounded-xl p-4">
                                        <h4 class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase border-b pb-2 mb-2">Presión Manómetros</h4>
                                        <ul class="space-y-2 text-[9px] md:text-[10px]">
                                            <li class="flex justify-between border-b border-slate-50 pb-1"><span class="font-bold text-slate-500">INYECCIÓN</span><span class="font-mono font-black">{{ perfilesDelTurno[lecturaActiva].perifericos_presion?.manometros?.inyeccion || '-' }} Bar</span></li>
                                            <li class="flex justify-between border-b border-slate-50 pb-1"><span class="font-bold text-slate-500">CARGA</span><span class="font-mono font-black">{{ perfilesDelTurno[lecturaActiva].perifericos_presion?.manometros?.carga || '-' }} Bar</span></li>
                                            <li class="flex justify-between"><span class="font-bold text-slate-500">CONTRAPRESIÓN</span><span class="font-mono font-black">{{ perfilesDelTurno[lecturaActiva].perifericos_presion?.manometros?.contrapresion || '-' }} Bar</span></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="bg-white border border-slate-200 rounded-xl p-4">
                                    <h4 class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase border-b pb-2 mb-2">Observaciones</h4>
                                    <p class="text-[10px] md:text-xs font-bold text-slate-700 whitespace-pre-wrap p-3 bg-slate-50 rounded-lg italic border border-slate-100">{{ perfilesDelTurno[lecturaActiva].observaciones || 'Ninguna observación registrada en esta lectura.' }}</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </transition>

    </AuthenticatedLayout>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', 'Fira Code', monospace; }
input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.custom-scrollbar::-webkit-scrollbar { height: 5px; width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
.animate-in { animation-fill-mode: both; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>