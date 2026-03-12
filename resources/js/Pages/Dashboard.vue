<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    auth: Object,
    stats: Object 
});

const currentTime = ref('');
const currentHour = ref(new Date().getHours());

onMounted(() => {
    const updateTime = () => {
        const now = new Date();
        currentHour.value = now.getHours();
        currentTime.value = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    };
    updateTime();
    setInterval(updateTime, 60000);
});

// Saludo dinámico según la hora
const greeting = computed(() => {
    if (currentHour.value < 12) return 'Buenos días';
    if (currentHour.value < 18) return 'Buenas tardes';
    return 'Buenas noches';
});

// Definición de Módulos (Estética Senior)
const modules = [
    { 
        title: 'Producción', 
        desc: 'Gestión de turnos y eficiencia de planta', 
        icon: 'M13 10V3L4 14h7v7l9-11h-7z', 
        route: 'produccion.index', 
        color: 'text-indigo-600',
        bg: 'bg-indigo-50',
        border: 'group-hover:border-indigo-200'
    },
    { 
        title: 'Fichas Técnicas', 
        desc: 'Catálogo maestro y parámetros', 
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 
        route: 'settings.productos.index', 
        color: 'text-emerald-600',
        bg: 'bg-emerald-50',
        border: 'group-hover:border-emerald-200'
    },
    { 
        title: 'Materia Prima', 
        desc: 'Inventario y control de materiales', 
        icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 
        route: 'settings.materiaprima.index', 
        color: 'text-amber-600',
        bg: 'bg-amber-50',
        border: 'group-hover:border-amber-200'
    }
];

// Estadísticas para Grid
const kpis = computed(() => [
    { label: 'Referencias Activas', value: props.stats?.totalProductos || 0, icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
    { label: 'Stock Materiales', value: props.stats?.totalMateriaPrima || 0, icon: 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4' },
    { label: 'OEE Global Promedio', value: '88.5%', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { label: 'Alertas / Paros', value: '0', icon: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', isAlert: true }
]);
</script>

<template>
    <Head title="Panel de Control" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-50 pb-20 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-[500px] h-[500px] bg-blue-400/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
            <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-emerald-400/10 rounded-full blur-3xl translate-x-1/3 -translate-y-1/4 pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 md:pt-12 relative z-10 animate-in fade-in slide-in-from-bottom-4 duration-700">
                
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white border border-slate-200 shadow-sm mb-4">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest font-mono">{{ currentTime }} • En Línea</span>
                        </div>
                        <h1 class="text-3xl md:text-5xl font-black text-slate-900 tracking-tighter leading-none">
                            {{ greeting }}, <span class="text-blue-600">{{ auth.user.nombre.split(' ')[0] }}</span>
                        </h1>
                        <p class="text-sm md:text-base text-slate-500 font-medium mt-3 max-w-xl leading-relaxed">
                            Resumen operativo del sistema integrado <strong class="text-slate-700">Pet del Caribe</strong>. ¿Qué deseas gestionar hoy?
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-12">
                    <div v-for="kpi in kpis" :key="kpi.label" 
                        class="bg-white p-5 md:p-6 rounded-[2rem] border border-slate-200/60 shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col justify-between group">
                        <div class="flex justify-between items-start mb-4">
                            <p class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ kpi.label }}</p>
                            <div :class="['p-1.5 rounded-lg transition-colors', kpi.isAlert ? 'bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100' : 'bg-slate-50 text-slate-400 group-hover:text-blue-500']">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="kpi.icon" />
                                </svg>
                            </div>
                        </div>
                        <p :class="['text-3xl md:text-4xl font-black tracking-tighter', kpi.isAlert && kpi.value == '0' ? 'text-emerald-500' : 'text-slate-800']">
                            {{ kpi.value }}
                        </p>
                    </div>
                </div>

                <div>
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-6 pl-2">Módulos de Gestión</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                        
                        <Link v-for="mod in modules" :key="mod.title" :href="route(mod.route)"
                            class="group bg-white p-6 md:p-8 rounded-[2.5rem] border-2 border-transparent hover:border-slate-200 shadow-sm hover:shadow-2xl transition-all duration-500 flex flex-col justify-between min-h-[240px] md:min-h-[280px] relative overflow-hidden">
                            
                            <div class="absolute inset-0 bg-gradient-to-br from-transparent to-slate-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                            <div class="relative z-10">
                                <div :class="[mod.bg, mod.color, 'w-14 h-14 md:w-16 md:h-16 rounded-[1.25rem] flex items-center justify-center mb-6 transition-transform duration-500 group-hover:scale-110 group-hover:-rotate-3 shadow-inner']">
                                    <svg class="w-7 h-7 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="mod.icon" />
                                    </svg>
                                </div>

                                <h3 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight leading-none mb-3">{{ mod.title }}</h3>
                                <p class="text-slate-500 text-xs md:text-sm font-medium leading-relaxed max-w-[90%]">
                                    {{ mod.desc }}
                                </p>
                            </div>

                            <div :class="[mod.color, 'relative z-10 mt-8 flex items-center text-[10px] md:text-xs font-black uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-4 group-hover:translate-y-0']">
                                Acceder al Módulo
                                <svg class="w-4 h-4 ml-2 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </div>
                        </Link>

                    </div>
                </div>

               <div class="mt-20 flex justify-center">
                    <div class="px-8 py-4 bg-slate-200/50 rounded-full border border-slate-200/60 backdrop-blur-sm flex items-center gap-4">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        <p class="text-slate-500 text-[10px] font-black uppercase tracking-[0.2em]">
                            Manufacturing Execution System
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', 'Fira Code', monospace; }
.animate-in { animation-fill-mode: both; }
</style>