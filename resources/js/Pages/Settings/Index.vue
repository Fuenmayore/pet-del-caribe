<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ 
    stats: Object 
});

const menus = [
    { 
        title: 'Máquinas', 
        desc: 'Gestionar inyectoras y estaciones', 
        icon: '🏭', 
        route: 'settings.maquinas.index', 
        count: props.stats?.totalMaquinas,
        color: 'text-blue-500',
        bgColor: 'bg-blue-50'
    },
    { 
        title: 'Referencias', 
        desc: 'Ciclos, cavidades y pesos estándar', 
        icon: '📦', 
        route: 'settings.productos.index', 
        count: props.stats?.totalProductos,
        color: 'text-emerald-500',
        bgColor: 'bg-emerald-50'
    },
    { 
        title: 'Defectos PNC', 
        desc: 'Catálogo de anomalías de calidad', 
        icon: '⚠️', 
        route: 'settings.pnc.index', 
        count: props.stats?.totalPnc,
        color: 'text-red-500',
        bgColor: 'bg-red-50'
    },
    { 
        title: 'Tipos de Paro', 
        desc: 'Codificación de tiempos perdidos', 
        icon: '🛑', 
        route: 'settings.paradas.index', 
        count: props.stats?.totalParadas,
        color: 'text-orange-500',
        bgColor: 'bg-orange-50'
    },
    { 
        title: 'Usuarios', 
        desc: 'Roles de operarios y supervisores', 
        icon: '👥', 
        route: 'settings.usuarios.index', 
        count: props.stats?.totalUsuarios,
        color: 'text-purple-500',
        bgColor: 'bg-purple-50'
    },
    { 
        title: 'Materia Prima', 
        desc: 'Catálogo de materiales de entrada', 
        icon: '🛢️', 
        route: 'settings.materiaprima.index', 
        count: props.stats?.totalMateriaPrima,
        color: 'text-green-500',
        bgColor: 'bg-green-50'
    },
    { 
        title: 'Colores', 
        desc: 'Catálogo de Pigmentos y Masterbatch', 
        icon: '🎨', 
        route: 'settings.colores.index', 
        count: props.stats?.totalColores,
        color: 'text-fuchsia-500',
        bgColor: 'bg-fuchsia-50'
    }
];
</script>

<template>
    <Head title="Ajustes de Sistema" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-slate-900 text-white rounded-xl shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-800 uppercase tracking-tighter italic leading-none">Panel Maestro de Configuración</h2>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Gestión de Catálogos del Sistema</p>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto p-4 md:p-8 space-y-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <Link v-for="menu in menus" :key="menu.title" :href="route(menu.route)"
                    class="relative bg-white p-6 rounded-[2.5rem] border border-slate-100 hover:border-pet-blue shadow-sm hover:shadow-xl transition-all duration-500 group flex flex-col items-center text-center overflow-hidden">
                    
                    <div v-if="menu.count != null" class="absolute top-5 right-5">
                        <span :class="[menu.bgColor, menu.color]" class="px-2.5 py-1 rounded-xl text-[9px] font-black uppercase tracking-widest border border-white">
                            {{ menu.count }} Registros
                        </span>
                    </div>

                    <div :class="menu.bgColor" class="w-20 h-20 rounded-3xl flex items-center justify-center text-4xl mb-4 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500 shadow-inner mt-4">
                        {{ menu.icon }}
                    </div>

                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tighter leading-tight">{{ menu.title }}</h3>
                    <p class="text-[10px] text-slate-400 font-bold mt-1.5 px-2 uppercase tracking-widest">{{ menu.desc }}</p>
                    
                    <div class="mt-6 flex items-center gap-2 px-6 py-2.5 rounded-xl bg-slate-50 text-[9px] font-black text-slate-500 group-hover:bg-pet-blue group-hover:text-white transition-all duration-300 uppercase tracking-[0.2em] border border-slate-100 group-hover:border-pet-blue">
                        Administrar
                        <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </div>
                </Link>
            </div>

            <div class="bg-slate-900 rounded-[2.5rem] p-10 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl relative overflow-hidden mt-8 border border-slate-800">
                <div class="absolute top-0 right-0 w-64 h-64 bg-pet-blue/10 blur-[80px] rounded-full -mr-20 -mt-20 pointer-events-none"></div>
                <div class="z-10">
                    <h4 class="text-xl font-black uppercase tracking-tighter italic">¿Necesitas ayuda técnica?</h4>
                    <p class="text-slate-400 text-[10px] font-bold mt-1.5 uppercase tracking-widest">Los cambios realizados aquí afectan los KPIs en tiempo real a nivel de planta.</p>
                </div>
                <button class="z-10 px-8 py-4 bg-pet-blue text-white rounded-2xl font-black uppercase text-[10px] tracking-[0.2em] hover:scale-105 hover:bg-blue-500 transition-all shadow-lg shadow-pet-blue/20">
                    Ver Documentación
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.group:hover {
    transform: translateY(-4px);
}
</style>