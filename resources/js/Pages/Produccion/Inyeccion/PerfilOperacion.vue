<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { useForm, Link, router, Head } from '@inertiajs/vue3';

const props = defineProps({
    turno: Object,
    perfilesGuardados: Array // Historial de lecturas enviado desde el controlador
});

const tabActivo = ref(1);
const now = new Date();

// Identificamos el progreso de lecturas
const perfilesSeguros = computed(() => props.perfilesGuardados || []);
const numeroLecturaActual = computed(() => Math.min(perfilesSeguros.value.length + 1, 3));
const yaLlenoLasTres = computed(() => perfilesSeguros.value.length >= 3);

// Configuración de filas exactas del formato IY-FO-02
const clampRows = ['Cls Hi Spd', 'Cierre 2', 'Mold Protec', 'Cls Hi Press', 'Star Slow', 'Apertura 2', 'Open Hi Spd', 'Ending slow'];
const ejectRows = ['Forward/Ejector', 'Salida 2/Mould Closing', 'Backward/Ejector', 'Entrada lenta', 'Nozzle(Tobera)', 'Adjust(Molde)'];
const injectRows = ['Inj # 1', 'Inj # 2', 'Inj # 3', 'Inj # 4', 'Hld # 1', 'Hld # 2', 'Hld # 3'];
const chargeRows = ['Charge 1', 'Charge 2', 'Suck Back', 'Cool Bf. Chg', 'Cooling (Enf.)', 'Auto pur', 'Auto pur Time'];
const zonasTemp = ['Zona 1', 'Zona 2', 'Zona 3', 'Zona 4', 'Zona 5', 'Zona 6', 'Zona 7', 'Oil T°', 'T° Agua', 'T° Tolva'];
const coladaTemp = ['1', '2', '3', '4', '5', '6', '7', '8'];

const buildGrid = (rows, cols) => rows.reduce((acc, row) => ({ ...acc, [row]: cols.reduce((cAcc, col) => ({ ...cAcc, [col]: '' }), {}) }), {});

const form = useForm({
    config_id: props.turno.config_activa?.id || '',
    hora_medicion: `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`,
    clamp_expulsor: {
        clamp: buildGrid(clampRows, ['press', 'spd', 'end']),
        eject: buildGrid(ejectRows, ['press', 'spd', 'end']),
        eject_md: '', eject_cnt: ''
    },
    inyeccion_carga: {
        inject: buildGrid(injectRows, ['press', 'spd', 'times', 'end', 'umbral']),
        carga: buildGrid(chargeRows, ['press', 'spd', 'end', 'colchon']),
        enfriamiento: ''
    },
    temperaturas: {
        zonas: buildGrid(zonasTemp, ['val']),
        colada: buildGrid(coladaTemp, ['val']),
        reguladores: Array(49).fill('') // Del 0 al 48 según formato
    },
    perifericos_presion: {
        manometros: { inyeccion: '', carga: '', contrapresion: '' },
        dosificador: { rpm: '', shot: '', tiempo: '', porcentaje: '' },
        salida_tapas: Array(32).fill('') // 32 cavidades
    },
    observaciones: ''
});

// FUNCIÓN PARA CLONAR DATOS
const clonarLecturaAnterior = () => {
    if (perfilesSeguros.value.length === 0) return;
    
    const ultimo = perfilesSeguros.value[perfilesSeguros.value.length - 1];
    
    form.clamp_expulsor = ultimo.clamp_expulsor || form.clamp_expulsor;
    form.inyeccion_carga = ultimo.inyeccion_carga || form.inyeccion_carga;
    form.temperaturas = ultimo.temperaturas || form.temperaturas;
    form.perifericos_presion = ultimo.perifericos_presion || form.perifericos_presion;
    form.observaciones = ultimo.observaciones || '';

    alert("✨ ¡Datos de la lectura anterior cargados! Verifica y modifica solo lo necesario.");
};

const guardarPerfil = () => {
    form.post(route('produccion.guardarPerfil', props.turno.id), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('produccion.registro', props.turno.id));
        }
    });
};
</script>

<template>
    <Head title="Perfil de Operación" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('produccion.registro', turno.id)" class="h-10 w-10 bg-white shadow-sm border border-slate-200 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-pet-blue transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <div>
                        <h2 class="text-sm font-black text-slate-800 uppercase leading-none">Formato IY-FO-02</h2>
                        <p class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">Seguimiento a Perfiles de Operación</p>
                    </div>
                </div>
                <div class="bg-indigo-50 text-indigo-700 px-4 py-1.5 rounded-lg border border-indigo-100 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                    <span class="text-[9px] font-black uppercase tracking-widest">{{ turno.maquina.nombre }} - {{ turno.config_activa?.producto?.descripcion || 'Sin Ref' }}</span>
                </div>
            </div>
        </template>

        <div class="max-w-6xl mx-auto p-4 md:p-8 pb-32">
            
            <div v-if="!turno.config_activa" class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center">
                <p class="text-red-600 font-black uppercase text-sm">⚠️ Error: No hay un producto activo.</p>
                <p class="text-red-500 text-xs mt-2">Debe configurar un molde en la consola antes de llenar el perfil.</p>
                <Link :href="route('produccion.registro', turno.id)" class="mt-4 inline-block bg-red-600 text-white px-6 py-2 rounded-xl text-xs font-bold shadow-lg">Volver</Link>
            </div>

            <div v-else class="space-y-6">

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex-1 w-full">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Progreso del Turno</h3>
                        <div class="flex gap-2 w-full">
                            <div v-for="n in 3" :key="n" 
                                :class="['flex-1 p-3 rounded-2xl border-2 flex flex-col justify-center transition-all', 
                                    perfilesSeguros[n-1] ? 'bg-emerald-50 border-emerald-500 text-emerald-700' : 
                                    (n === numeroLecturaActual && !yaLlenoLasTres ? 'bg-indigo-50 border-indigo-500 text-indigo-700 ring-4 ring-indigo-500/10' : 'bg-slate-50 border-slate-100 text-slate-300 opacity-60')]">
                                
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest">Lectura {{ n }}</span>
                                    <svg v-if="perfilesSeguros[n-1]" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    <svg v-else-if="n === numeroLecturaActual" class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                </div>
                                <span v-if="perfilesSeguros[n-1]" class="text-xs font-black">{{ perfilesSeguros[n-1].hora_medicion }}</span>
                                <span v-else-if="n === numeroLecturaActual" class="text-xs font-black">En progreso...</span>
                                <span v-else class="text-[9px] font-bold">Pendiente</span>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="numeroLecturaActual > 1 && !yaLlenoLasTres" class="shrink-0">
                        <button @click="clonarLecturaAnterior" class="px-6 py-4 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase shadow-xl hover:bg-indigo-600 hover:scale-105 transition-all flex items-center gap-2 group">
                            <svg class="w-5 h-5 group-hover:-rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Cargar datos anteriores
                        </button>
                    </div>
                </div>

                <div v-if="yaLlenoLasTres" class="bg-emerald-50 border border-emerald-200 rounded-3xl p-10 text-center shadow-lg">
                    <div class="h-24 w-24 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h3 class="text-2xl font-black text-emerald-800 uppercase tracking-tighter mb-2">¡Excelente Trabajo!</h3>
                    <p class="text-emerald-600 font-bold max-w-lg mx-auto">Ya has completado las 3 lecturas requeridas por el formato de calidad para este turno. La máquina está operando bajo estándares trazables.</p>
                    <Link :href="route('produccion.registro', turno.id)" class="mt-8 inline-block bg-emerald-600 hover:bg-emerald-700 text-white px-10 py-4 rounded-2xl text-sm font-black uppercase shadow-xl transition-all hover:scale-105">Regresar a la Consola</Link>
                </div>

                <div v-else class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                    <div class="p-6 md:p-8 border-b border-slate-50 bg-slate-50/50 flex flex-col md:flex-row items-center justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tighter uppercase">Captura de Parámetros</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">Llenado en piso de producción</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-black text-slate-400 uppercase">Hora de Lectura:</span>
                            <input type="time" v-model="form.hora_medicion" class="h-12 bg-white border-slate-200 rounded-xl text-center font-black text-indigo-600 shadow-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="flex border-b border-slate-100 overflow-x-auto custom-scrollbar">
                        <button @click="tabActivo = 1" :class="['flex-1 py-4 px-6 text-xs font-black uppercase transition-all whitespace-nowrap border-b-2', tabActivo === 1 ? 'border-indigo-500 text-indigo-600 bg-indigo-50/30' : 'border-transparent text-slate-400 hover:bg-slate-50']">1. 🗜️ Cierre / Expulsor</button>
                        <button @click="tabActivo = 2" :class="['flex-1 py-4 px-6 text-xs font-black uppercase transition-all whitespace-nowrap border-b-2', tabActivo === 2 ? 'border-indigo-500 text-indigo-600 bg-indigo-50/30' : 'border-transparent text-slate-400 hover:bg-slate-50']">2. 💉 Inyección / Carga</button>
                        <button @click="tabActivo = 3" :class="['flex-1 py-4 px-6 text-xs font-black uppercase transition-all whitespace-nowrap border-b-2', tabActivo === 3 ? 'border-indigo-500 text-indigo-600 bg-indigo-50/30' : 'border-transparent text-slate-400 hover:bg-slate-50']">3. 🌡️ Temperaturas</button>
                        <button @click="tabActivo = 4" :class="['flex-1 py-4 px-6 text-xs font-black uppercase transition-all whitespace-nowrap border-b-2', tabActivo === 4 ? 'border-indigo-500 text-indigo-600 bg-indigo-50/30' : 'border-transparent text-slate-400 hover:bg-slate-50']">4. ⚙️ Periféricos y Obs.</button>
                    </div>

                    <div class="p-6 md:p-8 bg-white min-h-[50vh]">
                        
                        <div v-show="tabActivo === 1" class="space-y-8 animate-in slide-in-from-right-4">
                            <div>
                                <h4 class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-3 bg-slate-50 px-4 py-2 rounded-lg">M.PLT (Ajuste / Clamp)</h4>
                                <div class="overflow-x-auto border rounded-2xl border-slate-100">
                                    <table class="w-full text-left min-w-[500px]">
                                        <thead class="bg-slate-50/80 text-[9px] font-black text-slate-400 uppercase"><tr><th class="p-3">Parámetro</th><th class="p-3 text-center">Press</th><th class="p-3 text-center">Spd/Times</th><th class="p-3 text-center">End-Posn</th></tr></thead>
                                        <tbody class="divide-y divide-slate-50">
                                            <tr v-for="row in clampRows" :key="row" class="hover:bg-slate-50/50 transition-colors">
                                                <td class="p-3 text-[10px] font-bold text-slate-600">{{ row }}</td>
                                                <td class="p-2"><input v-model="form.clamp_expulsor.clamp[row].press" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                                <td class="p-2"><input v-model="form.clamp_expulsor.clamp[row].spd" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                                <td class="p-2"><input v-model="form.clamp_expulsor.clamp[row].end" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div>
                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-3 bg-slate-50 px-4 py-2 rounded-lg gap-4">
                                    <h4 class="text-[11px] font-black text-slate-500 uppercase tracking-widest">EJECT (Expulsor)</h4>
                                    <div class="flex gap-4">
                                        <div class="flex items-center gap-2"><span class="text-[9px] font-black text-slate-400 uppercase">Eject Md</span><input v-model="form.clamp_expulsor.eject_md" type="text" class="h-9 w-20 bg-white border-slate-200 rounded-lg text-center text-xs font-bold focus:ring-2 focus:ring-indigo-500"></div>
                                        <div class="flex items-center gap-2"><span class="text-[9px] font-black text-slate-400 uppercase">Eject cnt</span><input v-model="form.clamp_expulsor.eject_cnt" type="text" class="h-9 w-20 bg-white border-slate-200 rounded-lg text-center text-xs font-bold focus:ring-2 focus:ring-indigo-500"></div>
                                    </div>
                                </div>
                                <div class="overflow-x-auto border rounded-2xl border-slate-100">
                                    <table class="w-full text-left min-w-[500px]">
                                        <thead class="bg-slate-50/80 text-[9px] font-black text-slate-400 uppercase"><tr><th class="p-3">Parámetro</th><th class="p-3 text-center">Press</th><th class="p-3 text-center">Spd/Times</th><th class="p-3 text-center">De La</th></tr></thead>
                                        <tbody class="divide-y divide-slate-50">
                                            <tr v-for="row in ejectRows" :key="row" class="hover:bg-slate-50/50 transition-colors">
                                                <td class="p-3 text-[10px] font-bold text-slate-600">{{ row }}</td>
                                                <td class="p-2"><input v-model="form.clamp_expulsor.eject[row].press" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                                <td class="p-2"><input v-model="form.clamp_expulsor.eject[row].spd" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                                <td class="p-2"><input v-model="form.clamp_expulsor.eject[row].end" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div v-show="tabActivo === 2" class="space-y-8 animate-in slide-in-from-right-4">
                            <div>
                                <h4 class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-3 bg-slate-50 px-4 py-2 rounded-lg">INJECT (Inyección)</h4>
                                <div class="overflow-x-auto border rounded-2xl border-slate-100">
                                    <table class="w-full text-left min-w-[700px]">
                                        <thead class="bg-slate-50/80 text-[9px] font-black text-slate-400 uppercase"><tr><th class="p-3">Parámetro</th><th class="p-3 text-center">Press</th><th class="p-3 text-center">Spd</th><th class="p-3 text-center">Times</th><th class="p-3 text-center">End Posn</th><th class="p-3 text-center">Umbral</th></tr></thead>
                                        <tbody class="divide-y divide-slate-50">
                                            <tr v-for="row in injectRows" :key="row" class="hover:bg-slate-50/50 transition-colors">
                                                <td class="p-3 text-[10px] font-bold text-slate-600">{{ row }}</td>
                                                <td class="p-2"><input v-model="form.inyeccion_carga.inject[row].press" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                                <td class="p-2"><input v-model="form.inyeccion_carga.inject[row].spd" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                                <td class="p-2"><input v-model="form.inyeccion_carga.inject[row].times" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                                <td class="p-2"><input v-model="form.inyeccion_carga.inject[row].end" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                                <td class="p-2"><input v-model="form.inyeccion_carga.inject[row].umbral" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div>
                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-3 bg-slate-50 px-4 py-2 rounded-lg gap-4">
                                    <h4 class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Chg./Su.Bk. (Carga/Rechupe)</h4>
                                    <div class="flex items-center gap-2"><span class="text-[9px] font-black text-slate-400 uppercase">Enf. Enfriamiento</span><input v-model="form.inyeccion_carga.enfriamiento" type="number" step="0.1" class="h-9 w-24 bg-white border-slate-200 rounded-lg text-center text-xs font-bold focus:ring-2 focus:ring-indigo-500"></div>
                                </div>
                                <div class="overflow-x-auto border rounded-2xl border-slate-100">
                                    <table class="w-full text-left min-w-[600px]">
                                        <thead class="bg-slate-50/80 text-[9px] font-black text-slate-400 uppercase"><tr><th class="p-3">Parámetro</th><th class="p-3 text-center">Press</th><th class="p-3 text-center">Spd/Times</th><th class="p-3 text-center">End Posn</th><th class="p-3 text-center">Colchón</th></tr></thead>
                                        <tbody class="divide-y divide-slate-50">
                                            <tr v-for="row in chargeRows" :key="row" class="hover:bg-slate-50/50 transition-colors">
                                                <td class="p-3 text-[10px] font-bold text-slate-600">{{ row }}</td>
                                                <td class="p-2"><input v-model="form.inyeccion_carga.carga[row].press" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                                <td class="p-2"><input v-model="form.inyeccion_carga.carga[row].spd" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                                <td class="p-2"><input v-model="form.inyeccion_carga.carga[row].end" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                                <td class="p-2"><input v-model="form.inyeccion_carga.carga[row].colchon" type="number" step="0.1" class="w-full h-10 bg-slate-50/50 border-slate-100 rounded-xl text-center text-xs font-black focus:ring-2 focus:ring-indigo-500"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div v-show="tabActivo === 3" class="space-y-8 animate-in slide-in-from-right-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="border border-slate-100 rounded-2xl p-5">
                                    <h4 class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-4 border-b pb-2">T°C (Tobera-aceite-agua-tolva)</h4>
                                    <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                                        <div v-for="zona in zonasTemp" :key="zona" class="flex items-center justify-between border-b border-slate-50 pb-1">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase">{{ zona }}</span>
                                            <input v-model="form.temperaturas.zonas[zona].val" type="number" class="w-16 h-8 bg-slate-50 border-slate-200 rounded-lg text-center text-[11px] font-black text-red-500 focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="border border-slate-100 rounded-2xl p-5">
                                    <h4 class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-4 border-b pb-2">T°C de Colada</h4>
                                    <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                                        <div v-for="colada in coladaTemp" :key="colada" class="flex items-center justify-between border-b border-slate-50 pb-1">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase">Colada {{ colada }}</span>
                                            <input v-model="form.temperaturas.colada[colada].val" type="number" class="w-16 h-8 bg-slate-50 border-slate-200 rounded-lg text-center text-[11px] font-black text-orange-500 focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-slate-100 rounded-2xl p-5 bg-slate-50/50">
                                <div class="flex justify-between items-center mb-4 border-b border-slate-200 pb-2">
                                    <h4 class="text-[11px] font-black text-slate-500 uppercase tracking-widest">T° Reguladores Puntos de Inyección</h4>
                                    <button type="button" @click="form.temperaturas.reguladores = Array(49).fill('')" class="text-[9px] font-black text-slate-400 hover:text-red-500 uppercase">Limpiar Cavidades</button>
                                </div>
                                <div class="grid grid-cols-5 sm:grid-cols-7 md:grid-cols-10 lg:grid-cols-12 gap-2">
                                    <div v-for="n in 49" :key="n-1" class="flex flex-col bg-white p-1 rounded-xl shadow-sm border border-slate-100">
                                        <span class="text-[8px] font-black text-slate-300 text-center bg-slate-50 rounded-md mb-1 pb-0.5">CAV {{ n-1 }}</span>
                                        <input v-model="form.temperaturas.reguladores[n-1]" type="number" class="w-full h-8 border-none text-center text-[10px] font-black text-indigo-600 focus:ring-0 p-0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-show="tabActivo === 4" class="space-y-8 animate-in slide-in-from-right-4">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="border border-slate-100 rounded-2xl p-5">
                                    <h4 class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-4 border-b pb-2">Dosificador de Colorante</h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between"><span class="text-[10px] font-bold text-slate-500 uppercase">RPM</span><input v-model="form.perifericos_presion.dosificador.rpm" type="number" step="0.1" class="w-24 h-10 bg-slate-50 border-slate-200 rounded-xl text-center text-xs font-black"></div>
                                        <div class="flex items-center justify-between"><span class="text-[10px] font-bold text-slate-500 uppercase">SHOT</span><input v-model="form.perifericos_presion.dosificador.shot" type="number" step="0.1" class="w-24 h-10 bg-slate-50 border-slate-200 rounded-xl text-center text-xs font-black"></div>
                                        <div class="flex items-center justify-between"><span class="text-[10px] font-bold text-slate-500 uppercase">TIEMPO</span><input v-model="form.perifericos_presion.dosificador.tiempo" type="number" step="0.1" class="w-24 h-10 bg-slate-50 border-slate-200 rounded-xl text-center text-xs font-black"></div>
                                        <div class="flex items-center justify-between"><span class="text-[10px] font-bold text-slate-500 uppercase text-indigo-600">% Dosificación</span><input v-model="form.perifericos_presion.dosificador.porcentaje" type="number" step="0.1" class="w-24 h-10 bg-indigo-50 border-indigo-100 rounded-xl text-center text-xs font-black text-indigo-600 focus:ring-2 focus:ring-indigo-500"></div>
                                    </div>
                                </div>
                                
                                <div class="border border-slate-100 rounded-2xl p-5">
                                    <h4 class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-4 border-b pb-2">Presión (Bar) Manómetros</h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between"><span class="text-[10px] font-bold text-slate-500 uppercase">Inyección</span><input v-model="form.perifericos_presion.manometros.inyeccion" type="number" step="0.1" class="w-24 h-10 bg-slate-50 border-slate-200 rounded-xl text-center text-xs font-black"></div>
                                        <div class="flex items-center justify-between"><span class="text-[10px] font-bold text-slate-500 uppercase">Carga</span><input v-model="form.perifericos_presion.manometros.carga" type="number" step="0.1" class="w-24 h-10 bg-slate-50 border-slate-200 rounded-xl text-center text-xs font-black"></div>
                                        <div class="flex items-center justify-between"><span class="text-[10px] font-bold text-slate-500 uppercase">Contrapresión</span><input v-model="form.perifericos_presion.manometros.contrapresion" type="number" step="0.1" class="w-24 h-10 bg-slate-50 border-slate-200 rounded-xl text-center text-xs font-black"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-slate-100 rounded-2xl p-5 bg-slate-50/50">
                                <div class="flex justify-between items-center mb-4 border-b border-slate-200 pb-2">
                                    <h4 class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Temperatura de Salida Tapas (32 Cav)</h4>
                                    <button type="button" @click="form.perifericos_presion.salida_tapas = Array(32).fill('')" class="text-[9px] font-black text-slate-400 hover:text-red-500 uppercase">Limpiar</button>
                                </div>
                                <div class="grid grid-cols-8 md:grid-cols-16 gap-1">
                                    <div v-for="n in 32" :key="n" class="flex flex-col bg-white p-0.5 rounded shadow-sm border border-slate-100">
                                        <span class="text-[7px] font-black text-slate-400 text-center">{{ n }}</span>
                                        <input v-model="form.perifericos_presion.salida_tapas[n-1]" type="number" class="w-full h-6 border-none text-center text-[9px] font-black text-slate-700 p-0 focus:ring-0">
                                    </div>
                                </div>
                            </div>

                            <div class="border border-slate-100 rounded-2xl p-5">
                                <h4 class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Observaciones</h4>
                                <textarea v-model="form.observaciones" rows="3" class="w-full bg-slate-50 border-slate-200 rounded-xl p-4 text-xs font-bold shadow-inner resize-none focus:ring-2 focus:ring-indigo-500" placeholder="Anotaciones sobre el ajuste, desviaciones de calidad encontradas..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 bg-slate-50/80 border-t border-slate-100 flex gap-4">
                        <Link :href="route('produccion.registro', turno.id)" class="py-4 px-8 rounded-2xl text-xs font-black uppercase text-slate-500 bg-white border border-slate-200 hover:bg-slate-100 transition-colors text-center">
                            Cancelar
                        </Link>
                        <button @click="guardarPerfil" :disabled="form.processing" class="flex-1 py-4 bg-indigo-600 text-white rounded-2xl font-black text-sm uppercase shadow-xl shadow-indigo-600/20 hover:bg-indigo-700 transition-all active:scale-95 disabled:opacity-50">
                            {{ form.processing ? 'Registrando en bitácora...' : 'Firmar y Guardar Lectura ' + numeroLecturaActual }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { height: 4px; width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.animate-in { animation: fade-slide-in 0.3s ease-out forwards; }
@keyframes fade-slide-in { 0% { opacity: 0; transform: translateX(10px); } 100% { opacity: 1; transform: translateX(0); } }
</style>