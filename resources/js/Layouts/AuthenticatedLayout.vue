<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
// Control para el sub-menú de Producción
const produccionOpen = ref(route().current('produccion.*')); 
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col md:flex-row">
        <nav class="bg-pet-blue text-white w-full md:w-64 md:min-h-screen shadow-2xl z-50 flex-shrink-0">
            <div class="flex flex-col h-full">
                <div class="flex items-center justify-between p-4 bg-pet-dark md:bg-transparent border-b border-white/10 md:border-none">
                    <Link :href="route('dashboard')" class="flex items-center">
                        <img src="/images/logo-pet.png" alt="Pet del Caribe" class="h-10 w-auto brightness-200">
                    </Link>

                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="md:hidden p-2 rounded-md hover:bg-pet-green transition-colors">
                        <svg class="h-6 w-6 text-white" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="md:block flex-grow px-3 py-6 space-y-2">
                    <p class="text-[10px] font-black text-blue-200 uppercase tracking-widest px-4 mb-2 opacity-70">Principal</p>
                    
                    <Link :href="route('dashboard')" 
                        :class="route().current('dashboard') ? 'bg-pet-green text-white shadow-lg' : 'hover:bg-white/10 text-blue-50'"
                        class="flex items-center space-x-3 p-3 rounded-xl transition-all duration-300 font-bold">
                        <span class="text-xl">📊</span>
                        <span>Dashboard</span>
                    </Link>

                    <div>
                        <button @click="produccionOpen = !produccionOpen" 
                            :class="route().current('produccion.*') ? 'text-white' : 'text-blue-50 hover:bg-white/10'"
                            class="w-full flex items-center justify-between p-3 rounded-xl transition-all duration-300 font-bold">
                            <div class="flex items-center space-x-3">
                                <span class="text-xl">🏭</span>
                                <span>Producción</span>
                            </div>
                            <svg :class="{'rotate-180': produccionOpen}" class="h-4 w-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div v-show="produccionOpen" class="mt-2 ml-4 space-y-1 overflow-hidden transition-all duration-300">
                            <Link :href="route('produccion.index')" 
                                :class="route().current('produccion.index') ? 'text-pet-green font-black' : 'text-blue-100 hover:text-white'"
                                class="flex items-center space-x-3 p-2 text-sm transition-colors border-l-2 border-white/10 hover:border-pet-green pl-4">
                                <span class="w-2 h-2 rounded-full bg-current"></span>
                                <span>Inyección (F1)</span>
                            </Link>
                            
                            <Link href="#" 
                                class="flex items-center space-x-3 p-2 text-sm text-blue-100/50 cursor-not-allowed border-l-2 border-white/10 pl-4">
                                <span class="w-2 h-2 rounded-full bg-current"></span>
                                <span>Soplado (Próximamente)</span>
                            </Link>
                        </div>
                    </div>

                    <div class="pt-6">
                        <p class="text-[10px] font-black text-blue-200 uppercase tracking-widest px-4 mb-2 opacity-70">Configuración</p>
                        <Link :href="route('profile.edit')" 
                            :class="route().current('profile.edit') ? 'bg-pet-green text-white' : 'hover:bg-white/10'"
                            class="flex items-center space-x-3 p-3 rounded-xl transition-all duration-300 font-bold">
                            <span class="text-xl">👤</span>
                            <span>Mi Perfil</span>
                        </Link>
                    </div>
                </div>

                <div class="p-4 border-t border-white/10 mt-auto bg-pet-dark/30">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="h-9 w-9 bg-pet-green rounded-lg flex items-center justify-center font-black text-white">
                                {{ $page.props.auth.user.nombre.substring(0,1).toUpperCase() }}
                            </div>
                            <div class="truncate">
                                <p class="text-sm font-bold text-white leading-none truncate">{{ $page.props.auth.user.nombre }}</p>
                                <p class="text-[10px] text-blue-200">En línea</p>
                            </div>
                        </div>
                        <Link :href="route('logout')" method="post" as="button" class="text-blue-200 hover:text-white p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <main class="flex-grow overflow-y-auto h-screen bg-gray-100">
            <header class="bg-white border-b border-gray-200 py-4 px-6 md:px-10 flex justify-between items-center sticky top-0 z-40" v-if="$slots.header">
                <div class="font-black text-gray-800 text-lg uppercase">
                    <slot name="header" />
                </div>

                <div class="flex items-center space-x-3 bg-green-50 px-3 py-1.5 rounded-full border border-green-100">
                    <div class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                    </div>
                    <span class="text-[10px] font-black text-green-700 uppercase tracking-widest">Sincronizado</span>
                </div>
            </header>

            <div class="p-4 md:p-10">
                <slot />
            </div>
        </main>
    </div>
</template>