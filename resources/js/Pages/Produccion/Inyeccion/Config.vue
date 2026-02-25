<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ 
    maquina: Object,
    productos: Array 
});

const user = usePage().props.auth.user;

// Lógica de fecha para los cuadritos visuales
const fechaObj = new Date();
const dia = String(fechaObj.getDate()).padStart(2, '0');
const mes = String(fechaObj.getMonth() + 1).padStart(2, '0');
const anio = fechaObj.getFullYear();

const form = useForm({
    maquina_id: props.maquina.id,
    fecha: new Date().toISOString().substr(0, 10),
    turno: '',
    cod_operario: user.codigo_empleado, 
    supervisor: '',
    // La lógica de estos campos se mantiene en el objeto para el envío
    producto_id: null, 
    ciclo_estandar: 0,
    cavidades_estandar: 0,
    mezcla: [
        { nombre: 'PET VIRGEN', porcentaje: 100 }
    ]
});

const submit = () => {
    form.post(route('produccion.store'));
};

const errorClass = (field) => {
    return field ? 'border-red-500 bg-red-50' : 'border-gray-200 bg-gray-50';
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
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em] opacity-80 mb-1">Equipo Asignado</p>
                            <h2 class="text-3xl font-black">{{ maquina.nombre }}</h2>
                            <span class="inline-block mt-2 px-3 py-1 bg-white/20 rounded-full text-[10px] font-bold uppercase">
                                {{ maquina.sub_area }}
                            </span>
                        </div>
                        
                        <div class="flex space-x-2">
                            <div class="flex flex-col items-center">
                                <span class="text-[9px] font-bold uppercase mb-1 opacity-70">Día</span>
                                <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center font-black border border-white/20">{{ dia }}</div>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="text-[9px] font-bold uppercase mb-1 opacity-70">Mes</span>
                                <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center font-black border border-white/20">{{ mes }}</div>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="text-[9px] font-bold uppercase mb-1 opacity-70">Año</span>
                                <div class="w-14 h-10 bg-white/10 rounded-lg flex items-center justify-center font-black border border-white/20">{{ anio }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-500">Seleccionar Turno *</label>
                            <select v-model="form.turno" 
                                :class="errorClass(form.errors.turno)"
                                class="w-full h-12 border-2 rounded-xl text-sm font-bold focus:border-pet-blue focus:ring-0 transition-all">
                                <option value="" disabled>Seleccione...</option>
                                <option value="1">Turno 1 (Mañana)</option>
                                <option value="2">Turno 2 (Tarde)</option>
                                <option value="3">Turno 3 (Noche)</option>
                            </select>
                            <p v-if="form.errors.turno" class="text-red-500 text-[10px] font-bold mt-1">{{ form.errors.turno }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-500">Operario en Máquina</label>
                            <input type="text" :value="user.codigo_empleado" disabled 
                                class="w-full h-12 bg-gray-100 border-none rounded-xl text-sm font-bold text-gray-400">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-500">Nombre del Supervisor *</label>
                        <input type="text" v-model="form.supervisor" 
                            :class="errorClass(form.errors.supervisor)"
                            placeholder="Ingrese nombre del supervisor" 
                            class="w-full h-12 border-2 rounded-xl text-sm font-bold focus:border-pet-blue focus:ring-0 transition-all">
                        <p v-if="form.errors.supervisor" class="text-red-500 text-[10px] font-bold mt-1">{{ form.errors.supervisor }}</p>
                    </div>

                    <div class="pt-4 border-t border-gray-50">
                        <button :disabled="form.processing || !form.turno || !form.supervisor"
                            class="w-full bg-pet-blue text-white h-14 rounded-xl font-bold text-sm shadow-lg shadow-blue-900/10 hover:bg-pet-dark transition-all active:scale-95 disabled:opacity-40 disabled:grayscale">
                            <span v-if="!form.processing">CONFIRMAR IDENTIFICACIÓN 🚀</span>
                            <span v-else class="flex items-center justify-center">
                                <svg class="animate-spin h-5 w-5 mr-3 text-white" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                PROCESANDO...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>