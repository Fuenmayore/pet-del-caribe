<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    colores: Array,
});

const search = ref('');
const modalColor = ref(false);
const editMode = ref(false);

const form = useForm({
    id: null,
    nombre: '',
    activo: true
});

const coloresFiltrados = computed(() => {
    if (!search.value) return props.colores;
    return props.colores.filter(c => c.nombre.toLowerCase().includes(search.value.toLowerCase()));
});

const openModal = (color = null) => {
    form.clearErrors();
    if (color) {
        editMode.value = true;
        form.id = color.id;
        form.nombre = color.nombre;
        form.activo = Boolean(color.activo);
    } else {
        editMode.value = false;
        form.reset();
        form.id = null;
        form.activo = true;
    }
    modalColor.value = true;
};

const submitForm = () => {
    const action = editMode.value ? 'put' : 'post';
    const url = editMode.value ? route('settings.colores.update', form.id) : route('settings.colores.store');
    
    form[action](url, {
        onSuccess: () => {
            modalColor.value = false;
            form.reset();
        },
        preserveScroll: true
    });
};

const deleteColor = (color) => {
    if (confirm(`¿Estás seguro de eliminar el color "${color.nombre}"?`)) {
        router.delete(route('settings.colores.destroy', color.id));
    }
};
</script>

<template>
    <Head title="Catálogo de Colores" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-slate-900 text-white rounded-2xl shadow-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-800 uppercase tracking-tighter italic leading-none">Catálogo de Colores</h2>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Gestión de Pigmentos y Masterbatch</p>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto p-4 md:p-8 space-y-6">
            
            <div class="flex flex-col md:flex-row gap-4 justify-between items-stretch md:items-center">
                <div class="relative flex-grow max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input v-model="search" type="text" placeholder="BUSCAR COLOR O REFERENCIA..." 
                        class="w-full pl-11 pr-4 py-3 bg-white border-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-widest focus:ring-4 focus:ring-blue-500/5 transition-all shadow-sm">
                </div>

                <button @click="openModal()" 
                    class="px-8 py-3.5 bg-slate-900 hover:bg-blue-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-slate-200 transition-all flex items-center justify-center gap-3 active:scale-95">
                    <span class="text-lg leading-none">+</span> NUEVO COLOR
                </button>
            </div>

            <div class="animate-in fade-in duration-500">
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50 border-b border-slate-100">
                                <th class="p-6 pl-8 w-20">#</th>
                                <th class="p-6">Nombre de Referencia / Color</th>
                                <th class="p-6 text-center">Estado</th>
                                <th class="p-6 text-right pr-8">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="(color, index) in coloresFiltrados" :key="color.id" class="group hover:bg-blue-50/20 transition-colors">
                                <td class="p-5 pl-8 font-mono text-xs font-bold text-slate-400">{{ index + 1 }}</td>
                                <td class="p-5 font-black text-slate-800 uppercase text-xs tracking-tight">{{ color.nombre }}</td>
                                <td class="p-5 text-center">
                                    <span :class="color.activo ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-slate-400 bg-slate-50 border-slate-200'" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[9px] font-black uppercase tracking-widest">
                                        <span :class="color.activo ? 'bg-emerald-500' : 'bg-slate-400'" class="w-1.5 h-1.5 rounded-full"></span>
                                        {{ color.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="p-5 text-right pr-8">
                                    <div class="flex justify-end gap-2 sm:opacity-0 group-hover:opacity-100 transition-all transform group-hover:scale-100 scale-95">
                                        <button @click="openModal(color)" class="h-9 w-9 bg-white border border-slate-100 rounded-xl text-blue-600 hover:shadow-md transition-all flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></button>
                                        <button @click="deleteColor(color)" class="h-9 w-9 bg-white border border-slate-100 rounded-xl text-slate-300 hover:text-red-500 hover:shadow-md transition-all flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="coloresFiltrados.length === 0">
                                <td colspan="4" class="p-8 text-center text-slate-400 font-bold text-xs uppercase tracking-widest">No se encontraron resultados</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL CRUD -->
        <transition name="fade">
            <div v-if="modalColor" class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/40 backdrop-blur-md p-4">
                <div class="bg-white w-full max-w-lg rounded-[3rem] shadow-2xl animate-in zoom-in-95 duration-200">
                    <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 uppercase italic tracking-tight">{{ editMode ? 'Modificar' : 'Registrar' }} Color</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Catálogo de producción</p>
                        </div>
                        <button @click="modalColor = false" class="h-10 w-10 bg-white border border-slate-100 text-slate-300 hover:text-red-500 rounded-full flex items-center justify-center transition-all shadow-sm">✕</button>
                    </div>

                    <form @submit.prevent="submitForm">
                        <div class="p-8 space-y-6">
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase mb-2 block tracking-[0.2em]">Referencia / Nombre Técnico</label>
                                <input v-model="form.nombre" type="text" class="w-full h-12 bg-slate-50 border-slate-100 border-2 rounded-2xl font-black px-5 text-xs focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all outline-none uppercase placeholder:text-slate-300 shadow-inner" placeholder="EJ: MAGENTA SUMIMASTER PE-46580">
                                <div v-if="form.errors.nombre" class="text-red-500 text-[10px] font-black mt-1.5 uppercase italic">{{ form.errors.nombre }}</div>
                            </div>

                            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-black text-slate-700 uppercase tracking-widest">Disponibilidad</p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase">Aparecerá en los listados</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="form.activo" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-blue-600 transition-all after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 peer-checked:after:translate-x-full shadow-inner"></div>
                                </label>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="modalColor = false" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-2xl font-black text-xs uppercase hover:bg-slate-200 transition-colors">Descartar</button>
                                <button type="submit" :disabled="form.processing" class="flex-[2] py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase shadow-xl hover:bg-blue-600 transition-all flex items-center justify-center gap-3">
                                    <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                    {{ editMode ? 'Actualizar Referencia' : 'Guardar Color 🎨' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.animate-in { animation: zoom-in 0.3s ease-out forwards; }
@keyframes zoom-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>