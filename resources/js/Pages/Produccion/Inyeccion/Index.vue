<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    maquinasLibres: Array,
    misTurnosActivos: Array
});
</script>

<template>
    <Head title="Panel de Inyección" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center font-bold">
                Control de Producción - Inyección
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-8 p-4">
            
            <div v-if="misTurnosActivos.length > 0">
                <div class="flex items-center space-x-2 mb-4">
                    <span class="text-pet-blue text-xl">⚡</span>
                    <h3 class="text-sm font-black text-gray-600 uppercase tracking-widest">Mis Máquinas Activas</h3>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link v-for="turno in misTurnosActivos" :key="turno.id"
                        :href="route('produccion.registro', turno.id)"
                        class="relative group bg-white border-2 border-pet-green/30 rounded-3xl p-6 shadow-sm hover:shadow-xl hover:border-pet-green transition-all duration-300"
                    >
                        <div class="absolute top-4 right-4 flex items-center space-x-1">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            <span class="text-[10px] font-black text-green-600 uppercase">En Proceso</span>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <div class="bg-gray-50 p-3 rounded-2xl text-2xl group-hover:scale-110 transition-transform">⚙️</div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Máquina</p>
                                <h4 class="text-xl font-black text-pet-blue leading-none">{{ turno.maquina?.nombre }}</h4>
                            </div>
                        </div>

                        <div class="mt-8 pt-4 border-t border-gray-100 flex justify-between items-end">
                            <div class="flex-1 mr-4">
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Referencia actual</p>
                                <p class="text-sm font-bold text-gray-700 truncate">
                                    {{ turno.produccion_config?.producto?.descripcion || 'PENDIENTE POR ASIGNAR' }}
                                </p>
                            </div>
                            
                            <span class="shrink-0 bg-pet-blue text-white text-[10px] font-bold px-4 py-2 rounded-xl group-hover:bg-pet-dark transition-colors shadow-lg shadow-blue-900/10">
                                REGISTRAR HORA
                            </span>
                        </div>
                    </Link>
                </div>
            </div>

            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <span class="text-gray-400 text-xl">➕</span>
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest">Máquinas Disponibles</h3>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <Link v-for="maq in maquinasLibres" :key="maq.id"
                        :href="route('produccion.config', maq.id)"
                        class="bg-white border-2 border-dashed border-gray-100 rounded-3xl p-6 flex flex-col items-center justify-center text-center hover:border-pet-blue hover:bg-blue-50/50 transition-all duration-200 group"
                    >
                        <div class="text-3xl mb-3 grayscale group-hover:grayscale-0 transition-all group-hover:scale-110">🏭</div>
                        <span class="font-bold text-gray-600 group-hover:text-pet-blue tracking-tight">{{ maq.nombre }}</span>
                        <span class="text-[9px] font-bold text-gray-300 uppercase mt-1">Libre</span>
                    </Link>
                </div>
            </div>

            <div v-if="maquinasLibres.length === 0 && misTurnosActivos.length === 0" 
                 class="bg-white rounded-[2rem] p-12 text-center border-2 border-dashed border-gray-100">
                <p class="text-gray-400 font-bold italic text-sm">No hay estaciones de trabajo disponibles en este momento.</p>
            </div>

        </div>
    </AuthenticatedLayout>
</template>