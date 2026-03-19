<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ 
    maquina: Object,
    supervisores: Array, // <-- Nueva propiedad que recibe la BD
});

const user = usePage().props.auth.user;

const fechaObj = new Date();
const dia = String(fechaObj.getDate()).padStart(2, '0');
const mes = String(fechaObj.getMonth() + 1).padStart(2, '0');
const anio = fechaObj.getFullYear();

const form = useForm({
    maquina_id: props.maquina.id,
    fecha: new Date().toISOString().substr(0, 10),
    turno: '',
    duracion_turno: 8, 
    cod_operario: user.nombre, 
    supervisor: '', // Guardará el nombre del supervisor seleccionado
    area: props.maquina.sub_area, 
    
    producto_id: null, 
    ciclo_estandar: 0,
    cavidades_estandar: 0,
    mezcla: []
});

const submit = () => {
    form.post(route('produccion.store'));
};

// Se ajustó ligeramente la clase base para que los <select> luzcan idénticos a los <input>
const inputClass = (hasError) => {
    const baseClass = "w-full h-12 px-4 border-2 rounded-xl text-sm font-bold transition-all focus:ring-0 appearance-none bg-no-repeat bg-[position:right_1rem_center] bg-[length:1.2em_1.2em]";
    const iconNormal = `url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e")`;
    
    return hasError 
        ? `${baseClass} border-red-500 bg-red-50 focus:border-red-600 text-red-900` 
        : `${baseClass} border-gray-200 bg-gray-50 focus:border-pet-blue text-gray-700`;
};

const toggleJornada = (val) => {
    form.duracion_turno = val ? 12 : 8;
    form.turno = ''; 
};
</script>

<template>
    <Head title="Identificación de Turno" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center text-gray-800 font-bold">
                Apertura de Turno Operativo
            </div>
        </template>

        <div class="max-w-2xl mx-auto p-6">
            <form @submit.prevent="submit" class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                
                <div class="bg-pet-blue p-8 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em] opacity-80 mb-1">Máquina en Operación</p>
                            <h2 class="text-2xl font-black">{{ maquina.nombre }}</h2>
                            <span class="inline-block mt-1 px-3 py-1 bg-white/20 rounded-full text-[9px] font-bold uppercase tracking-widest">
                                Área: {{ maquina.sub_area }}
                            </span>
                        </div>
                        
                        <div class="flex space-x-2">
                            <div class="flex flex-col items-center">
                                <span class="text-[8px] font-black uppercase mb-1 opacity-60">Día</span>
                                <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center font-bold border border-white/20 text-sm">{{ dia }}</div>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="text-[8px] font-black uppercase mb-1 opacity-60">Mes</span>
                                <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center font-bold border border-white/20 text-sm">{{ mes }}</div>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="text-[8px] font-black uppercase mb-1 opacity-60">Año</span>
                                <div class="w-14 h-10 bg-white/10 rounded-lg flex items-center justify-center font-bold border border-white/20 text-sm">{{ anio }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    
                    <div class="flex items-center justify-between bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Jornada Laboral</span>
                            <span class="text-xs font-bold text-slate-600 mt-1">Configurar duración de turno</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-bold" :class="form.duracion_turno === 8 ? 'text-pet-blue' : 'text-slate-400'">8H</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" :checked="form.duracion_turno === 12" @change="toggleJornada($event.target.checked)" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-pet-blue shadow-inner"></div>
                            </label>
                            <span class="text-[10px] font-bold" :class="form.duracion_turno === 12 ? 'text-pet-blue' : 'text-slate-400'">12H</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1 relative">
                            <label class="text-xs font-bold text-gray-500 ml-1">Seleccionar Turno *</label>
                            <select v-model="form.turno" :class="inputClass(form.errors.turno)" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e');">
                                <option value="" disabled>Seleccione...</option>
                                <template v-if="form.duracion_turno === 8">
                                    <option value="1">Turno 1 (Mañana)</option>
                                    <option value="2">Turno 2 (Tarde)</option>
                                    <option value="3">Turno 3 (Noche)</option>
                                </template>
                                <template v-else>
                                    <option value="1">Turno 1 (Día)</option>
                                    <option value="2">Turno 2 (Noche)</option>
                                </template>
                            </select>
                            <p v-if="form.errors.turno" class="text-red-500 text-[10px] font-bold mt-1 ml-1 uppercase">{{ form.errors.turno }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-500 ml-1">Nombre Operario</label>
                            <input type="text" :value="user.nombre" disabled 
                                class="w-full h-12 px-4 bg-gray-100 border-none rounded-xl text-sm font-bold text-gray-400 uppercase">
                        </div>
                    </div>

                    <!-- CAMBIO PRINCIPAL: Select de Supervisor conectado a la BD -->
                    <div class="space-y-1 relative">
                        <label class="text-xs font-bold text-gray-500 ml-1">Nombre del Supervisor *</label>
                        <select v-model="form.supervisor" 
                            :class="inputClass(form.errors.supervisor)"
                            style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e');"
                        >
                            <option value="" disabled>Seleccione al supervisor en turno...</option>
                            <option v-for="sup in supervisores" :key="sup.id" :value="sup.nombre">
                                {{ sup.nombre }}
                            </option>
                        </select>
                        <p v-if="form.errors.supervisor" class="text-red-500 text-[10px] font-bold mt-1 ml-1 uppercase">{{ form.errors.supervisor }}</p>
                    </div>

                    <div class="pt-4">
                        <button :disabled="form.processing"
                            class="w-full bg-pet-blue text-white h-14 rounded-xl font-bold text-sm shadow-lg shadow-blue-900/10 hover:bg-pet-dark transition-all active:scale-95 disabled:opacity-40 uppercase">
                            <span v-if="!form.processing">Confirmar Identificación 🚀</span>
                            <span v-else class="flex items-center justify-center">
                                <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>