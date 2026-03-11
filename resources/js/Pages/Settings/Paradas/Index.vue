<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    paradas: Array
});

const showModal = ref(false);
const editing = ref(null);
const search = ref('');

// Formulario de Inertia
const form = useForm({
    codigo: '',
    falla: '',
    categoria: ''
});

// Filtrado simple para búsqueda rápida
const filteredParadas = computed(() => {
    return props.paradas.filter(p => 
        p.falla.toLowerCase().includes(search.value.toLowerCase()) || 
        p.codigo.toLowerCase().includes(search.value.toLowerCase()) ||
        p.categoria.toLowerCase().includes(search.value.toLowerCase())
    );
});

// Función centralizada para abrir el modal (Crear o Editar)
const openModal = (item = null) => {
    if (item) {
        editing.value = item;
        form.codigo = item.codigo;
        form.falla = item.falla;
        form.categoria = item.categoria;
    } else {
        editing.value = null;
        form.reset();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editing.value = null;
};

const submit = () => {
    if (editing.value) {
        // Ruta de actualización (PUT)
        form.put(route('settings.paradas.update', editing.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        // Ruta de creación (POST)
        form.post(route('settings.paradas.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteParada = (id) => {
    if (confirm('¿Estás seguro de eliminar este código de falla?')) {
        form.delete(route('settings.paradas.destroy', id));
    }
};

// Colores por categoría para el Motor OEE (Fase III)
const getCategoryColor = (cat) => {
    const colors = {
        'MECANICAS': 'bg-red-50 text-red-700 border-red-100',
        'ELECTRICAS': 'bg-yellow-50 text-yellow-700 border-yellow-100',
        'ELECTRONICAS': 'bg-orange-50 text-orange-700 border-orange-100',
        'ADMIN': 'bg-blue-50 text-blue-700 border-blue-100',
        'GENERAL': 'bg-slate-50 text-slate-700 border-slate-100',
        'MOLDE': 'bg-purple-50 text-purple-700 border-purple-100',
        'PERIFERICOS': 'bg-cyan-50 text-cyan-700 border-cyan-100',
        'HIDRAULICAS': 'bg-indigo-50 text-indigo-700 border-indigo-100',
        'NEUMATICAS': 'bg-teal-50 text-teal-700 border-teal-100',
    };
    return colors[cat] || 'bg-gray-50 text-gray-600 border-gray-100';
};
</script>

<template>
    <Head title="Catálogo de Fallas" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto p-4 sm:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h2 class="text-2xl font-black uppercase text-slate-800 tracking-tighter">
                        Catálogo de Fallas y Paradas
                    </h2>
                    <p class="text-xs font-bold text-slate-400 uppercase">Pet del Caribe - Gestión de Disponibilidad</p>
                </div>
                
                <div class="flex w-full md:w-auto gap-3">
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="BUSCAR CÓDIGO O FALLA..."
                        class="flex-1 md:w-64 h-10 bg-white border-slate-200 rounded-xl text-xs font-bold uppercase focus:ring-pet-blue"
                    />
                    <button 
                        @click="openModal()" 
                        class="bg-pet-blue text-white px-6 py-2 rounded-xl font-black text-xs uppercase shadow-lg shadow-blue-200 hover:scale-105 transition-transform"
                    >
                        + Nuevo Código
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 border-b border-slate-100">
                            <tr class="text-[10px] font-black uppercase text-slate-400">
                                <th class="p-5 w-24">Código</th>
                                <th class="p-5">Falla / Descripción</th>
                                <th class="p-5 text-center">Categoría</th>
                                <th class="p-5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="parada in filteredParadas" :key="parada.id" class="hover:bg-slate-50/80 transition-colors group">
                                <td class="p-5">
                                    <span class="font-black text-pet-blue text-base">{{ parada.codigo }}</span>
                                </td>
                                <td class="p-5">
                                    <p class="font-bold text-slate-700 uppercase leading-tight">{{ parada.falla }}</p>
                                </td>
                                <td class="p-5 text-center">
                                    <span 
                                        :class="getCategoryColor(parada.categoria)"
                                        class="px-3 py-1 rounded-lg text-[9px] font-black uppercase border"
                                    >
                                        {{ parada.categoria }}
                                    </span>
                                </td>
                                <td class="p-5 text-right">
                                    <div class="flex justify-end gap-3">
                                        <button 
                                            @click="openModal(parada)"
                                            class="text-[10px] font-black uppercase text-slate-400 hover:text-pet-blue transition-colors"
                                        >
                                            Editar
                                        </button>
                                        <button 
                                            @click="deleteParada(parada.id)"
                                            class="text-[10px] font-black uppercase text-slate-400 hover:text-red-500 transition-colors"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredParadas.length === 0">
                                <td colspan="4" class="p-20 text-center">
                                    <p class="text-xs font-black uppercase text-slate-300">No se encontraron registros</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-[3rem] p-10 w-full max-w-lg shadow-2xl scale-in-center">
                <div class="mb-8">
                    <h3 class="text-2xl font-black uppercase text-slate-800 tracking-tighter">
                        {{ editing ? 'Editar Falla' : 'Nuevo Código' }}
                    </h3>
                    <p class="text-[10px] font-black text-slate-400 uppercase">Información técnica de parada</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-1">
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-2">Código</label>
                            <input 
                                v-model="form.codigo" 
                                type="text" 
                                class="w-full h-14 bg-slate-50 border-none rounded-2xl font-black text-center text-lg focus:ring-2 focus:ring-pet-blue"
                                placeholder="Ej: 42"
                                required
                            />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-2">Descripción de la Falla</label>
                            <input 
                                v-model="form.falla" 
                                type="text" 
                                class="w-full h-14 bg-slate-50 border-none rounded-2xl font-bold uppercase px-6 focus:ring-2 focus:ring-pet-blue"
                                placeholder="EJ: FALLA HIDRAULICA"
                                required
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-2">Categoría Administrativa/Técnica</label>
                        <select 
                            v-model="form.categoria" 
                            class="w-full h-14 bg-slate-50 border-none rounded-2xl font-bold uppercase px-6 focus:ring-2 focus:ring-pet-blue"
                            required
                        >
                            <option value="">Seleccione Categoría</option>
                            <option value="ADMIN">ADMIN</option>
                            <option value="GENERAL">GENERAL</option>
                            <option value="MECANICAS">MECANICAS</option>
                            <option value="ELECTRICAS">ELECTRICAS</option>
                            <option value="ELECTRONICAS">ELECTRONICAS</option>
                            <option value="HIDRAULICAS">HIDRAULICAS</option>
                            <option value="NEUMATICAS">NEUMATICAS</option>
                            <option value="MOLDE">MOLDE</option>
                            <option value="PERIFERICOS">PERIFERICOS</option>
                        </select>
                    </div>

                    <div class="flex gap-4 mt-10">
                        <button 
                            type="button" 
                            @click="closeModal" 
                            class="flex-1 py-4 text-xs font-black uppercase text-slate-400 hover:bg-slate-50 rounded-2xl transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="flex-1 py-4 bg-pet-blue text-white rounded-2xl font-black text-xs uppercase shadow-xl shadow-blue-100 hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50"
                        >
                            {{ editing ? 'Actualizar Falla' : 'Guardar Código' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>