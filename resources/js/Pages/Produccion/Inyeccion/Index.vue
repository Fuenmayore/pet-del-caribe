<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    maquinasLibres: Array,
    misTurnosActivos: Array,
    historialTurnos: Object 
});

const eliminarTurno = (id) => {
    if (confirm('¿Estás seguro de eliminar este registro?')) {
        router.delete(route('produccion.destroy', id), { preserveScroll: true });
    }
};

const obtenerTotalesTurno = (turno) => {
    let buenas = 0;
    let desperdicioKg = 0;
    
    turno.configuraciones.forEach(config => {
        const horas = config.horas_produccion || config.horasProduccion || [];
        horas.forEach(hora => {
            buenas += (parseInt(hora.unidades_buenas) || 0);
            if (hora.pnc_detalle?.defectos) {
                hora.pnc_detalle.defectos.forEach(d => {
                    desperdicioKg += (parseFloat(d.malas_kg) || 0) + (parseFloat(d.cont_kg) || 0) + (parseFloat(d.torta_kg) || 0);
                });
            }
        });
    });
    return { buenas, desperdicioKg: desperdicioKg.toFixed(2) };
};
</script>

<template>
    <Head title="Panel de Control - Inyección" />

    <AuthenticatedLayout>
        <template #header>
            <div class="font-black text-slate-800 uppercase tracking-tighter text-lg">Consola de Control</div>
        </template>

        <div class="max-w-7xl mx-auto space-y-12 p-4 pb-24">
            
            <div v-if="misTurnosActivos.length > 0">
                <div class="flex items-center gap-3 mb-6 px-2">
                    <div class="h-2 w-2 bg-pet-green rounded-full animate-ping"></div>
                    <h3 class="text-xs font-black text-slate-500 uppercase tracking-[0.2em]">En Operación</h3>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="turno in misTurnosActivos" :key="turno.id"
                        class="relative bg-white border-2 border-pet-green/20 rounded-[2.5rem] p-6 shadow-sm hover:shadow-xl transition-all duration-500"
                    >
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-center gap-4">
                                <div class="bg-slate-50 p-3 rounded-2xl text-2xl shadow-inner">⚙️</div>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Estación</p>
                                    <h4 class="text-lg font-black text-pet-blue leading-none truncate">{{ turno.maquina?.nombre }}</h4>
                                </div>
                            </div>
                            <button v-if="$can('turnos.cancelar')" @click.stop="eliminarTurno(turno.id)" class="p-2 text-slate-200 hover:text-red-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>

                        <Link :href="route('produccion.registro', turno.id)" class="block group">
                            <div class="mt-4 pt-4 border-t border-slate-50">
                                <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">Referencia Activa</p>
                                <p class="text-sm font-black text-slate-700 truncate uppercase leading-tight">
                                    {{ turno.config_activa?.producto?.descripcion || 'Pendiente' }}
                                </p>
                                <div class="mt-4 flex items-center justify-between">
                                    <span v-if="turno.configuraciones?.length > 1" class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[8px] font-black rounded uppercase">Fases: {{ turno.configuraciones.length }}</span>
                                    <span class="ml-auto bg-pet-blue text-white text-[9px] font-black px-5 py-2.5 rounded-2xl shadow-lg group-hover:bg-pet-dark transition-all">ABRIR PANEL</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <div v-if="$can('turnos.abrir')">
                <div class="flex items-center gap-3 mb-6 px-2">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Estaciones Disponibles</h3>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <Link v-for="maq in maquinasLibres" :key="maq.id"
                        :href="route('produccion.config', maq.id)"
                        class="bg-white border-2 border-dashed border-slate-100 rounded-[2rem] p-6 flex flex-col items-center justify-center text-center hover:border-pet-blue hover:bg-blue-50/30 transition-all duration-300 group"
                    >
                        <div class="text-3xl mb-3 grayscale group-hover:grayscale-0 transition-all">🏭</div>
                        <span class="font-bold text-slate-600 group-hover:text-pet-blue text-sm">{{ maq.nombre }}</span>
                        <div class="mt-2 px-3 py-0.5 bg-slate-50 rounded-full text-[8px] font-black text-slate-300 uppercase">Libre</div>
                    </Link>
                </div>
            </div>

            <div class="bg-white rounded-[3rem] p-4 md:p-10 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-8 px-2">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">📊</span>
                        <h3 class="text-sm font-black text-gray-600 uppercase tracking-widest">Historial de Turnos</h3>
                    </div>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                                <th class="pb-5 px-4">Fecha / Turno</th>
                                <th class="pb-5 px-4">Máquina</th>
                                <th class="pb-5 px-4">Referencias</th>
                                <th class="pb-5 px-4 text-center">Unidades</th>
                                <th class="pb-5 px-4 text-center">Desperdicio</th>
                                <th class="pb-5 px-4 text-center">Estado</th>
                                <th class="pb-5 px-4 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="h in historialTurnos.data" :key="h.id" class="hover:bg-slate-50/50 transition-colors group">
                                <td class="py-5 px-4">
                                    <p class="text-xs font-black text-slate-700">{{ h.fecha }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">Turno #{{ h.numero_turno }}</p>
                                </td>
                                <td class="py-5 px-4 font-black text-pet-blue text-xs uppercase">{{ h.maquina?.nombre }}</td>
                                <td class="py-5 px-4">
                                    <div class="flex flex-col gap-1">
                                        <div v-for="(config, idx) in h.configuraciones" :key="config.id" class="flex items-center gap-2">
                                            <span class="text-[7px] bg-slate-100 px-1 rounded font-black text-slate-400">F{{ idx + 1 }}</span>
                                            <p class="text-[9px] font-bold text-slate-600 truncate max-w-[180px] uppercase">{{ config.producto?.descripcion }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-5 px-4 text-center font-mono font-black text-slate-700 text-xs">{{ obtenerTotalesTurno(h).buenas.toLocaleString() }}</td>
                                <td class="py-5 px-4 text-center font-mono font-black text-red-500 text-xs">{{ obtenerTotalesTurno(h).desperdicioKg }} Kg</td>
                                <td class="py-5 px-4 text-center">
                                    <span :class="h.estado === 'Abierto' ? 'bg-amber-100 text-amber-600 border-amber-200' : 'bg-slate-100 text-slate-400 border-slate-200'" class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase border">
                                        {{ h.estado }}
                                    </span>
                                </td>
                                <td class="py-5 px-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link v-if="$can('analisis.ver')" :href="route('produccion.analisis', h.id)" 
                                            class="inline-flex h-9 w-9 items-center justify-center bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-amber-500 hover:border-amber-500 shadow-sm transition-all"
                                            title="Análisis de Eficiencia">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                        </Link>
                                        <Link :href="route('produccion.registro', h.id)" 
                                            class="inline-flex h-9 w-9 items-center justify-center bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-pet-blue hover:border-pet-blue shadow-sm transition-all"
                                            title="Ver Detalles">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden space-y-4">
                    <div v-for="h in historialTurnos.data" :key="h.id" class="bg-slate-50/50 rounded-3xl p-5 border border-slate-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0">
                            <span :class="h.estado === 'Abierto' ? 'bg-amber-400 text-white' : 'bg-slate-300 text-white'" class="px-4 py-1 rounded-bl-2xl text-[8px] font-black uppercase tracking-widest">
                                {{ h.estado }}
                            </span>
                        </div>

                        <div class="flex items-center gap-4 mb-4">
                            <div class="h-12 w-12 bg-white rounded-2xl flex items-center justify-center shadow-sm text-xl">🏗️</div>
                            <div>
                                <h4 class="font-black text-pet-blue uppercase text-sm leading-none">{{ h.maquina?.nombre }}</h4>
                                <p class="text-[10px] font-bold text-slate-400 mt-1">{{ h.fecha }} · Turno #{{ h.numero_turno }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100">
                                <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest mb-1">Buenas</p>
                                <p class="text-sm font-mono font-black text-slate-700">{{ obtenerTotalesTurno(h).buenas.toLocaleString() }}</p>
                            </div>
                            <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100">
                                <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest mb-1">Desperdicio</p>
                                <p class="text-sm font-mono font-black text-red-500">{{ obtenerTotalesTurno(h).desperdicioKg }} <span class="text-[9px]">Kg</span></p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <div class="flex -space-x-2 overflow-hidden">
                                <span v-for="idx in Math.min(h.configuraciones.length, 3)" :key="idx" class="inline-block h-6 w-6 rounded-full ring-2 ring-white bg-slate-200 flex items-center justify-center text-[7px] font-black text-slate-500 uppercase">F{{ idx }}</span>
                                <span v-if="h.configuraciones.length > 3" class="inline-block h-6 w-6 rounded-full ring-2 ring-white bg-slate-100 flex items-center justify-center text-[7px] font-black text-slate-400">+{{ h.configuraciones.length - 3 }}</span>
                            </div>
                            <div class="flex gap-2">
                                <Link v-if="$can('analisis.ver')" :href="route('produccion.analisis', h.id)" 
                                    class="p-2.5 bg-amber-100 text-amber-600 rounded-xl shadow-sm active:scale-95 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                </Link>
                                <Link :href="route('produccion.registro', h.id)" class="px-6 py-2 bg-pet-blue text-white rounded-xl text-[9px] font-black uppercase shadow-md active:scale-95 transition-all">Ver Detalles</Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="historialTurnos.links.length > 3" class="mt-10 flex flex-wrap justify-center gap-2">
                    <Link v-for="(link, k) in historialTurnos.links" :key="k" 
                        :href="link.url || '#'" 
                        v-html="link.label"
                        :class="[
                            'px-4 py-2 rounded-xl text-[10px] font-black transition-all border shrink-0', 
                            link.active ? 'bg-pet-blue text-white border-pet-blue shadow-lg' : 'bg-white text-slate-400 border-slate-100', 
                            !link.url ? 'opacity-30 cursor-not-allowed' : ''
                        ]" />
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }
.custom-scrollbar::-webkit-scrollbar { height: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>