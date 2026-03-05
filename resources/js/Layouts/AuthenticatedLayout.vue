<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// Estados de Navegación
const isSidebarOpen = ref(false); // Mobile (Drawer)
const isCollapsed = ref(false);  // Desktop (Mini/Full)
const produccionOpen = ref(route().current('produccion.*'));

// Lógica de Fecha y Hora
const currentTime = ref(new Date());
let timer;

onMounted(() => {
    timer = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

onUnmounted(() => clearInterval(timer));

const formatDate = (date) => {
    return date.toLocaleDateString('es-ES', { 
        weekday: 'long', day: 'numeric', month: 'long' 
    });
};

const formatTime = (date) => {
    return date.toLocaleTimeString('es-ES', { 
        hour: '2-digit', minute: '2-digit', second: '2-digit' 
    });
};

const toggleSidebar = () => {
    if (window.innerWidth < 768) {
        isSidebarOpen.value = !isSidebarOpen.value;
        isCollapsed.value = false; 
    } else {
        isCollapsed.value = !isCollapsed.value;
    }
};
</script>

<template>
    <div class="min-h-screen bg-[#f8fafc] flex overflow-hidden font-sans text-slate-900">
        
        <transition name="fade">
            <div v-if="isSidebarOpen" 
                 @click="isSidebarOpen = false"
                 class="fixed inset-0 bg-slate-900/60 z-[60] md:hidden backdrop-blur-sm">
            </div>
        </transition>

        <aside :class="[
            'bg-gradient-to-b from-[#081b33] via-[#0d2a4a] to-[#061426] text-white fixed md:static inset-y-0 left-0 z-[70] transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] flex flex-col shadow-[10px_0_30px_-15px_rgba(0,0,0,0.5)]',
            isSidebarOpen ? 'translate-x-0 w-72' : '-translate-x-full md:translate-x-0',
            isCollapsed ? 'md:w-20' : 'md:w-72'
        ]">
            <div class="h-24 flex items-center px-4 relative overflow-hidden flex-shrink-0">
                <div class="absolute -top-10 -left-10 w-32 h-32 bg-pet-green/10 blur-[50px] rounded-full"></div>
                
                <Link :href="route('dashboard')" class="flex items-center gap-3 z-10 mx-auto md:ml-2">
                    <div class="min-w-[48px] h-12 bg-white rounded-xl flex items-center justify-center p-1.5 shadow-lg transform transition-transform hover:rotate-3">
                        <img src="/images/logo-pet.png" alt="Logo" class="h-full object-contain">
                    </div>
                    <div v-if="!isCollapsed || isSidebarOpen" class="flex flex-col animate-in fade-in slide-in-from-left-2 duration-500">
                        <span class="font-black tracking-tighter text-xl leading-none bg-clip-text text-transparent bg-gradient-to-r from-white to-blue-200 uppercase">
                            Pet Caribe
                        </span>
                        <span class="text-[9px] font-bold text-pet-green tracking-[0.3em] uppercase opacity-80">Industrial</span>
                    </div>
                </Link>
            </div>

            <div class="flex-grow overflow-y-auto px-3 py-4 space-y-2 custom-scrollbar overflow-x-hidden">
                <p v-if="!isCollapsed || isSidebarOpen" class="text-[10px] font-black text-blue-400/50 uppercase tracking-[0.2em] px-4 mb-4 mt-2">Menú Principal</p>
                
                <Link :href="route('dashboard')" 
                    :class="[
                        'relative flex items-center gap-4 p-3.5 rounded-xl transition-all duration-300 group',
                        route().current('dashboard') 
                            ? 'bg-white/10 text-white shadow-[inset_0_0_20px_rgba(255,255,255,0.05)]' 
                            : 'hover:bg-white/5 text-blue-100/60 hover:text-white'
                    ]">
                    <div v-if="route().current('dashboard')" class="absolute left-0 top-1/4 bottom-1/4 w-1 bg-pet-green rounded-r-full shadow-[0_0_15px_#10b981]"></div>
                    <svg :class="route().current('dashboard') ? 'text-pet-green' : 'group-hover:text-pet-green'" 
                         class="w-6 h-6 flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span v-if="!isCollapsed || isSidebarOpen" class="font-bold tracking-wide whitespace-nowrap uppercase text-xs">Dashboard</span>
                </Link>

                <div class="space-y-1">
                    <button @click="produccionOpen = !produccionOpen" 
                        :class="route().current('produccion.*') ? 'text-white' : 'text-blue-100/60 hover:text-white'"
                        class="w-full flex items-center justify-between p-3.5 rounded-xl hover:bg-white/5 transition-all group">
                        <div class="flex items-center gap-4">
                            <svg :class="route().current('produccion.*') ? 'text-pet-green' : 'group-hover:text-pet-green'"
                                 class="w-6 h-6 flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span v-if="!isCollapsed || isSidebarOpen" class="font-bold tracking-wide whitespace-nowrap uppercase text-xs">Producción</span>
                        </div>
                        <svg v-if="!isCollapsed || isSidebarOpen" :class="{'rotate-180 text-pet-green': produccionOpen}" class="w-4 h-4 transition-transform text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div v-show="produccionOpen && (!isCollapsed || isSidebarOpen)" class="pl-14 pr-4 space-y-1 relative">
                        <div class="absolute left-[30px] top-0 bottom-2 w-[1px] bg-white/10"></div>
                        <Link :href="route('produccion.index')" 
                            :class="route().current('produccion.index') ? 'text-pet-green font-bold' : 'text-blue-200/50 hover:text-white'"
                            class="block py-2 text-[10px] uppercase font-bold transition-colors">
                            Inyección (F1)
                        </Link>
                    </div>
                </div>

                <Link :href="route('settings.index')" 
                    :class="[
                        'relative flex items-center gap-4 p-3.5 rounded-xl transition-all duration-300 group',
                        route().current('produccion.settings.*') 
                            ? 'bg-white/10 text-white shadow-[inset_0_0_20px_rgba(255,255,255,0.05)]' 
                            : 'hover:bg-white/5 text-blue-100/60 hover:text-white'
                    ]">
                    <div v-if="route().current('produccion.settings.*')" class="absolute left-0 top-1/4 bottom-1/4 w-1 bg-pet-green rounded-r-full shadow-[0_0_15px_#10b981]"></div>
                    <svg :class="route().current('produccion.settings.*') ? 'text-pet-green' : 'group-hover:text-pet-green'" 
                         class="w-6 h-6 flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span v-if="!isCollapsed || isSidebarOpen" class="font-bold tracking-wide whitespace-nowrap uppercase text-xs">Configuraciones</span>
                </Link>
            </div>

            <div class="p-4 flex-shrink-0 border-t border-white/5 bg-black/10">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 min-w-[40px] bg-pet-green rounded-xl flex items-center justify-center font-black text-[#081b33] shadow-lg shadow-pet-green/20 uppercase">
                        {{ $page.props.auth.user.nombre.substring(0,1) }}
                    </div>
                    <div v-if="!isCollapsed || isSidebarOpen" class="flex-grow min-w-0 animate-in fade-in">
                        <p class="text-[11px] font-black text-white truncate uppercase tracking-tight">{{ $page.props.auth.user.nombre }}</p>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-pet-green animate-pulse"></span>
                            <span class="text-[8px] text-emerald-400 font-bold tracking-widest uppercase">Online</span>
                        </div>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" v-if="!isCollapsed || isSidebarOpen"
                          class="p-2 text-blue-300 hover:text-red-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    </Link>
                </div>
            </div>
        </aside>

        <div class="flex-grow flex flex-col h-screen overflow-hidden">
            <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 z-50 flex-shrink-0">
                <div class="flex items-center gap-4">
                    <button @click="toggleSidebar" 
                            class="p-2.5 rounded-xl bg-slate-50 text-slate-600 hover:bg-pet-blue hover:text-white transition-all shadow-sm group">
                        <svg class="w-6 h-6 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="isCollapsed && !isSidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                        </svg>
                    </button>
                    
                    <h1 class="font-black text-slate-800 text-lg md:text-xl tracking-tight uppercase border-l-2 border-slate-100 pl-4 hidden sm:block">
                        <slot name="header" />
                    </h1>
                </div>

                <div class="flex items-center gap-6">
                    <div class="flex flex-col items-end pr-6 border-r border-slate-100">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest hidden xs:block">{{ formatDate(currentTime) }}</span>
                        <span class="text-xl font-mono font-black text-pet-blue leading-none">{{ formatTime(currentTime) }}</span>
                    </div>

                    <div class="hidden md:flex items-center gap-3 bg-emerald-50 px-4 py-2 rounded-2xl border border-emerald-100">
                        <div class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-tighter">Sistema Activo</span>
                    </div>
                </div>
            </header>

            <main class="flex-grow overflow-y-auto p-4 md:p-8 bg-[#f8fafc] custom-scrollbar-main">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>