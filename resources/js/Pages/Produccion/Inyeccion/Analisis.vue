<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    turno: Object
});

// Catálogo de defectos
const listaDefectosCalidad = [
    { id: 1, nombre: 'Quemada' }, { id: 2, nombre: 'Rayas' }, { id: 3, nombre: 'Burbujas Fondo' },
    { id: 4, nombre: 'Burbujas Cuerpo' }, { id: 6, nombre: 'Punto Negro' }, { id: 7, nombre: 'Opacidad' },
    { id: 10, nombre: 'Fractura' }, { id: 13, nombre: 'Rebaba Cierre' }, { id: 15, nombre: 'Cuello Jirafa' },
];

const cavidadActiva = ref(null);

// --- LÓGICA SENIOR PARA SOLTAR/TOGGLE ---
const gestionarCavidad = (n, datos) => {
    // Si la cavidad que toco ya es la activa, la pongo en null (la suelto)
    if (cavidadActiva.value?.n === n) {
        cavidadActiva.value = null;
    } else {
        // Si no, cargo la nueva
        cavidadActiva.value = { n, ...datos };
    }
};

const reporte = computed(() => {
    let r = {
        buenas: 0, esperadas: 0, 
        malasKg: 0, contKg: 0, tortaKg: 0,
        minutosParo: 0,
        parosPorMotivo: {},
        defectosPorTipo: {},
        cavidadesDetalle: {},
        resumenFases: [],
        trazabilidad: []
    };

    props.turno.configuraciones.forEach(config => {
        let buenasFase = 0;
        let esperadasFase = 0;
        const horas = config.horas_produccion || config.horasProduccion || [];

        horas.forEach(h => {
            const det = h.pnc_detalle || {};
            r.buenas += parseInt(h.unidades_buenas || 0);
            buenasFase += parseInt(h.unidades_buenas || 0);

            const ciclo = parseFloat(det.ciclo_real || config.producto?.ciclo || 1);
            const cavs = parseInt(det.cavidades_reales || config.producto?.cavidades || 1);
            const metaHora = (3600 / ciclo) * cavs;
            r.esperadas += metaHora;
            esperadasFase += metaHora;

            r.trazabilidad.push({
                hora: h.hora,
                referencia: config.producto?.descripcion,
                lote: h.num_vale_inyectora || 'S/N',
                buenas: h.unidades_buenas,
                eficiencia: metaHora > 0 ? ((h.unidades_buenas / metaHora) * 100).toFixed(1) : 0,
                eventos: (det.paros?.length || 0) + (det.defectos?.length || 0)
            });

            if (det.paros) {
                det.paros.forEach(p => {
                    r.minutosParo += parseInt(p.minutos || 0);
                    r.parosPorMotivo[p.motivo] = (r.parosPorMotivo[p.motivo] || 0) + parseInt(p.minutos || 0);
                });
            }

            if (det.defectos) {
                det.defectos.forEach(d => {
                    r.malasKg += parseFloat(d.malas_kg || 0);
                    r.contKg += parseFloat(d.cont_kg || 0);
                    r.tortaKg += parseFloat(d.torta_kg || 0);
                });
            }

            if (det.inspeccion) {
                det.inspeccion.forEach(ins => {
                    if (!r.cavidadesDetalle[ins.cav]) {
                        r.cavidadesDetalle[ins.cav] = { count: 0, fallas: [] };
                    }
                    const nombreFalla = listaDefectosCalidad.find(df => df.id === ins.defecto)?.nombre || 'Otro';
                    r.cavidadesDetalle[ins.cav].count++;
                    r.cavidadesDetalle[ins.cav].fallas.push({
                        hora: h.hora,
                        defecto: nombreFalla,
                        lote: h.num_vale_inyectora
                    });
                });
            }
        });

        r.resumenFases.push({
            producto: config.producto?.descripcion,
            color: config.mezcla_materiales?.color || 'N/A',
            mezcla: config.mezcla_materiales?.materiales || [],
            eficiencia: esperadasFase > 0 ? ((buenasFase / esperadasFase) * 100).toFixed(1) : 0
        });
    });

    r.eficienciaTotal = r.esperadas > 0 ? ((r.buenas / r.esperadas) * 100).toFixed(1) : 0;
    return r;
});
</script>

<template>
    <Head title="Análisis de Producción" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto p-4 md:p-8 pb-32 space-y-8 animate-in fade-in duration-700">
            
            <div class="bg-white p-6 md:p-10 rounded-[2.5rem] shadow-sm border border-slate-100 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-6">
                    <div class="h-16 w-16 md:h-20 md:w-20 bg-slate-900 text-emerald-400 rounded-3xl flex items-center justify-center text-3xl shadow-2xl rotate-3">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-4xl font-black text-slate-800 tracking-tighter uppercase leading-none">Analítica de Turno</h1>
                        <p class="text-slate-400 font-bold mt-2 uppercase text-[10px] tracking-[0.3em] flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            {{ turno.maquina?.nombre }} <span class="opacity-30">|</span> {{ turno.fecha }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 md:gap-8 px-6 py-4 bg-slate-50 rounded-3xl border border-slate-100 w-full md:w-auto overflow-x-auto">
                    <div class="text-center min-w-fit">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Operador</p>
                        <p class="text-[11px] font-black text-slate-700 uppercase">{{ turno.operario?.nombre }}</p>
                    </div>
                    <div class="h-8 w-px bg-slate-200"></div>
                    <div class="text-center min-w-fit">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Supervisor</p>
                        <p class="text-[11px] font-black text-blue-600 uppercase">{{ turno.supervisor }}</p>
                    </div>
                    <Link :href="route('produccion.index')" class="ml-auto p-2.5 bg-white text-slate-400 rounded-xl hover:text-red-500 transition-colors shadow-sm">✕</Link>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <div class="col-span-2 lg:col-span-1 bg-blue-600 p-8 rounded-[2.5rem] text-white shadow-xl shadow-blue-100 relative overflow-hidden group">
                    <p class="text-[10px] font-black uppercase opacity-70 tracking-widest">OEE Real</p>
                    <h2 class="text-6xl md:text-7xl font-black mt-2 tracking-tighter">{{ reporte.eficienciaTotal }}%</h2>
                    <div class="mt-4 h-2 w-full bg-white/20 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-400 transition-all duration-1000" :style="{ width: reporte.eficienciaTotal + '%' }"></div>
                    </div>
                    <div class="absolute -right-6 -bottom-6 text-9xl opacity-10 rotate-12 group-hover:rotate-0 transition-transform duration-500">📊</div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-md transition-all">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Logrado</p>
                    <h2 class="text-4xl md:text-5xl font-black text-slate-800 mt-2 tracking-tighter">{{ reporte.buenas.toLocaleString() }}</h2>
                    <p class="text-[9px] font-bold text-slate-300 mt-2 uppercase">Meta: {{ Math.floor(reporte.esperadas).toLocaleString() }} un</p>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-md transition-all">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">T. Paro</p>
                    <h2 class="text-4xl md:text-5xl font-black text-orange-500 mt-2 tracking-tighter">{{ reporte.minutosParo }}<span class="text-xl ml-1">min</span></h2>
                    <p class="text-[9px] font-bold text-slate-300 mt-2 uppercase tracking-tighter">Tiempo muerto total</p>
                </div>

                <div class="bg-slate-900 p-8 rounded-[2.5rem] text-white shadow-xl">
                    <p class="text-[10px] font-black uppercase opacity-40 tracking-widest">Scrap Kg</p>
                    <h2 class="text-4xl md:text-5xl font-black text-red-500 mt-2 tracking-tighter">{{ (reporte.malasKg + reporte.contKg + reporte.tortaKg).toFixed(1) }}</h2>
                    <p class="text-[9px] font-bold text-slate-500 mt-2 uppercase tracking-tighter">PNC Procesada</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-8 md:p-10 rounded-[3rem] border border-slate-100 shadow-sm">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-8 flex items-center gap-3">
                        <div class="p-2 bg-orange-100 text-orange-600 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        Pareto de Tiempos Muertos
                    </h3>
                    <div class="space-y-6">
                        <div v-for="(min, motivo) in reporte.parosPorMotivo" :key="motivo" class="group">
                            <div class="flex justify-between text-[10px] font-black uppercase mb-2">
                                <span class="text-slate-500 group-hover:text-slate-800 transition-colors">{{ motivo }}</span>
                                <span class="text-orange-600 font-mono">{{ min }} min</span>
                            </div>
                            <div class="h-3 w-full bg-slate-50 rounded-full p-0.5 border border-slate-100">
                                <div class="h-full bg-orange-400 rounded-full transition-all duration-1000 shadow-sm shadow-orange-100" 
                                    :style="{ width: (min/reporte.minutosParo)*100 + '%' }"></div>
                            </div>
                        </div>
                        <div v-if="Object.keys(reporte.parosPorMotivo).length === 0" class="text-center py-12">
                            <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest italic">Eficiencia 100% en disponibilidad</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-8 md:p-10 rounded-[3rem] border border-slate-100 shadow-sm flex flex-col">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-8 flex items-center gap-3">
                        <div class="p-2 bg-red-100 text-red-600 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
                        </div>
                        Distribución de Scrap (PNC)
                    </h3>
                    <div class="flex-grow flex flex-col justify-center">
                        <div class="grid grid-cols-3 gap-4 mb-8">
                            <div v-for="(val, label) in { Malas: reporte.malasKg, Contam: reporte.contKg, Torta: reporte.tortaKg }" :key="label" 
                                class="p-4 bg-slate-50 rounded-3xl border border-slate-100 text-center">
                                <p class="text-[8px] font-black text-slate-400 uppercase mb-1">{{ label }}</p>
                                <p class="text-xl font-black text-slate-700">{{ val.toFixed(1) }} <span class="text-[9px]">kg</span></p>
                            </div>
                        </div>
                        <div class="p-6 bg-red-600 rounded-[2rem] text-white shadow-xl shadow-red-100 flex items-center justify-between">
                            <div>
                                <p class="text-[9px] font-black uppercase opacity-60">Pérdida de Material</p>
                                <p class="text-2xl font-black tracking-tighter">Total Acumulado</p>
                            </div>
                            <span class="text-4xl font-black">{{ (reporte.malasKg + reporte.contKg + reporte.tortaKg).toFixed(1) }}<span class="text-lg ml-1 uppercase">kg</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 md:p-10 rounded-[3rem] border border-slate-100 shadow-sm relative">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                    <div>
                        <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] flex items-center gap-3">
                            <div class="p-2 bg-blue-100 text-blue-600 rounded-xl">🔬</div>
                            Mapa de Cavidades Críticas
                        </h3>
                        <p class="text-[9px] font-bold text-slate-400 mt-2 uppercase tracking-widest">Toque una cavidad para ver el historial (Vuelva a tocar para soltar)</p>
                    </div>
                </div>

                <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-12 gap-3">
                    <button v-for="(datos, cav) in reporte.cavidadesDetalle" :key="cav" 
                        @click="gestionarCavidad(cav, datos)"
                        :class="['p-4 border rounded-2xl text-center transition-all duration-300 transform active:scale-90', 
                                cavidadActiva?.n === cav ? 'bg-slate-900 border-slate-900 shadow-xl scale-105' : 'bg-white border-slate-100 hover:border-red-300 hover:bg-red-50/30']">
                        <p :class="['text-[8px] font-black mb-1', cavidadActiva?.n === cav ? 'text-blue-400' : 'text-slate-400']">CAV {{ cav }}</p>
                        <p :class="['text-xl font-black', cavidadActiva?.n === cav ? 'text-white' : 'text-red-600']">{{ datos.count }}</p>
                    </button>
                    <div v-if="Object.keys(reporte.cavidadesDetalle).length === 0" class="col-span-full py-12 text-center bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-200">
                        <p class="text-slate-300 font-black uppercase text-xs tracking-widest">Molde Operando al 100% Calidad</p>
                    </div>
                </div>

                <div v-if="cavidadActiva" class="mt-8 p-6 bg-slate-900 rounded-[2.5rem] text-white animate-in slide-in-from-bottom-4 duration-500 relative">
                    <div class="flex items-center justify-between mb-8 gap-4">
                        <h4 class="text-blue-400 font-black text-xs uppercase tracking-widest flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-ping"></span>
                            Diagnóstico: Cavidad {{ cavidadActiva.n }}
                        </h4>
                        <button @click="cavidadActiva = null" class="h-10 px-5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shrink-0">
                            Cerrar ✕
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div v-for="(f, i) in cavidadActiva.fallas" :key="i" class="bg-white/5 border border-white/10 p-4 rounded-2xl flex items-center justify-between group hover:bg-white/10 transition-colors">
                            <div>
                                <p class="text-[10px] font-black text-white uppercase">{{ f.defecto }}</p>
                                <p class="text-[8px] font-bold text-white/40 uppercase mt-1">Lote: {{ f.lote }}</p>
                            </div>
                            <span class="text-[10px] font-black text-emerald-400 bg-emerald-400/10 px-2 py-1 rounded-lg font-mono">{{ f.hora }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-8 md:p-10 border-b border-slate-50 flex justify-between items-center">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] flex items-center gap-3">
                        <div class="p-2 bg-slate-100 rounded-xl">📦</div>
                        Bitácora Cronológica de Turno
                    </h3>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                                <th class="p-6">Referencia</th>
                                <th class="p-6">Vale / Lote</th>
                                <th class="p-6 text-center">Hora</th>
                                <th class="p-6 text-center">Buenas</th>
                                <th class="p-6 text-center">Eficiencia</th>
                                <th class="p-6 text-right pr-10">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="h in reporte.trazabilidad" :key="h.hora" class="group hover:bg-slate-50 transition-colors">
                                <td class="p-6">
                                    <p class="text-[11px] font-black text-slate-700 uppercase leading-none">{{ h.referencia }}</p>
                                </td>
                                <td class="p-6">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-500 text-[10px] font-black rounded-lg border border-slate-200 uppercase">{{ h.lote }}</span>
                                </td>
                                <td class="p-6 text-center font-mono font-black text-slate-400 text-xs">{{ h.hora }}</td>
                                <td class="p-6 text-center font-mono font-black text-slate-700">{{ h.buenas.toLocaleString() }}</td>
                                <td class="p-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-12 bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                            <div :class="['h-full rounded-full transition-all duration-700', h.eficiencia < 85 ? 'bg-red-500' : 'bg-emerald-500']" :style="{width: h.eficiencia + '%'}"></div>
                                        </div>
                                        <span :class="h.eficiencia < 85 ? 'text-red-500' : 'text-emerald-600'" class="font-black text-[10px] font-mono">{{ h.eficiencia }}%</span>
                                    </div>
                                </td>
                                <td class="p-6 text-right pr-10">
                                    <span v-if="h.eventos > 0" class="px-3 py-1 bg-amber-50 text-amber-600 text-[8px] font-black rounded-full border border-amber-100 uppercase">Fallas ({{ h.eventos }})</span>
                                    <span v-else class="text-slate-200 text-[8px] font-black uppercase">Óptimo</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="(f, i) in reporte.resumenFases" :key="i" class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm group hover:border-blue-200 transition-all">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <span class="text-[8px] font-black text-blue-500 uppercase tracking-widest px-3 py-1 bg-blue-50 rounded-full">Fase {{ i + 1 }}</span>
                            <h4 class="text-lg font-black text-slate-800 uppercase mt-3 tracking-tighter leading-none">{{ f.producto }}</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mt-2">Pigmentación: <span class="text-slate-700">{{ f.color }}</span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[8px] font-black text-slate-300 uppercase">Efic. Fase</p>
                            <p class="text-3xl font-black text-slate-800 font-mono tracking-tighter">{{ f.eficiencia }}%</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <div v-for="m in f.mezcla" :key="m.nombre" class="flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-2xl border border-slate-100 group-hover:bg-white transition-colors">
                            <span class="text-[9px] font-black text-slate-500 uppercase">{{ m.nombre }}</span>
                            <span class="px-2 py-0.5 bg-slate-900 text-white text-[9px] font-black rounded-lg">{{ m.porcentaje }}%</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', 'Fira Code', monospace; }
input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

.animate-in {
    animation-fill-mode: both;
}
</style>