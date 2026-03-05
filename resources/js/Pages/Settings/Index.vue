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
        route: 'settings.anomalias.index', 
        count: null,
        color: 'text-red-500',
        bgColor: 'bg-red-50'
    },
    { 
        title: 'Tipos de Paro', 
        desc: 'Codificación de tiempos perdidos', 
        icon: '🛑', 
        route: 'settings.fallas.index', 
        count: null,
        color: 'text-orange-500',
        bgColor: 'bg-orange-50'
    },
    { 
        title: 'Usuarios', 
        desc: 'Roles de operarios y supervisores', 
        icon: '👥', 
        route: 'settings.usuarios.index', // Cambiar cuando tengas el CRUD de usuarios
        count: props.stats?.totalUsuarios,
        color: 'text-purple-500',
        bgColor: 'bg-purple-50'
    },
];
</script>

<template>
    <Head title="Ajustes de Sistema" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-slate-800 text-white rounded-lg shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h2 class="font-black text-slate-800 uppercase tracking-tighter">Panel Maestro de Configuración</h2>
            </div>
        </template>

        <div class="max-w-7xl mx-auto p-4 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <Link v-for="menu in menus" :key="menu.title" :href="route(menu.route)"
                    class="relative bg-white p-8 rounded-[3rem] border-2 border-slate-50 hover:border-pet-blue shadow-sm hover:shadow-2xl transition-all duration-500 group flex flex-col items-center text-center overflow-hidden">
                    
                    <div v-if="menu.count !== null" class="absolute top-6 right-8">
                        <span :class="[menu.bgColor, menu.color]" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                            {{ menu.count }} Registros
                        </span>
                    </div>

                    <div :class="menu.bgColor" class="w-20 h-20 rounded-3xl flex items-center justify-center text-5xl mb-6 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 shadow-inner">
                        {{ menu.icon }}
                    </div>

                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter leading-tight">{{ menu.title }}</h3>
                    <p class="text-xs text-slate-400 font-bold mt-2 px-4">{{ menu.desc }}</p>
                    
                    <div class="mt-8 flex items-center gap-2 px-8 py-3 rounded-2xl bg-slate-50 text-[10px] font-black text-slate-500 group-hover:bg-pet-blue group-hover:text-white transition-all duration-300 uppercase tracking-[0.2em]">
                        Administrar
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </div>
                </Link>
            </div>

            <div class="bg-slate-900 rounded-[2.5rem] p-10 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-pet-blue/10 blur-[80px] rounded-full -mr-20 -mt-20"></div>
                <div class="z-10">
                    <h4 class="text-xl font-black uppercase tracking-tighter">¿Necesitas ayuda técnica?</h4>
                    <p class="text-slate-400 text-xs font-bold mt-1 uppercase tracking-widest">Los cambios realizados aquí afectan los KPIs en tiempo real.</p>
                </div>
                <button class="z-10 px-8 py-4 bg-pet-green text-slate-900 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:scale-105 transition-all shadow-lg shadow-pet-green/20">
                    Documentación Sistema
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Transición suave para los bordes y sombras */
.group:hover {
    transform: translateY(-5px);
}
</style>