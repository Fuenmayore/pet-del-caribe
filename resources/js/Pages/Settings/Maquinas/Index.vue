<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ maquinas: Array });

const showModal = ref(false);
const editMode = ref(false);
const search = ref(''); 

// --- FILTRO REACTIVO ---
const maquinasFiltradas = computed(() => {
    return props.maquinas.filter(m => 
        m.nombre.toLowerCase().includes(search.value.toLowerCase()) ||
        m.sub_area.toLowerCase().includes(search.value.toLowerCase())
    );
});

const form = useForm({
    id: null,
    nombre: '',
    sub_area: 'Inyeccion'
});

const abrirModal = (maquina = null) => {
    if (maquina) {
        editMode.value = true;
        form.id = maquina.id;
        form.nombre = maquina.nombre;
        form.sub_area = maquina.sub_area;
    } else {
        editMode.value = false;
        form.reset();
    }
    showModal.value = true;
};

const cerrarModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

const guardar = () => {
    if (editMode.value) {
        form.put(route('settings.maquinas.update', form.id), {
            onSuccess: () => cerrarModal(),
            preserveScroll: true
        });
    } else {
        form.post(route('settings.maquinas.store'), {
            onSuccess: () => cerrarModal(),
            preserveScroll: true
        });
    }
};

const eliminar = (id) => {
    if (confirm('¿Mover esta estación a la papelera?')) {
        router.delete(route('settings.maquinas.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Estaciones" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-slate-800 text-white rounded-lg shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </div>
                <h2 class="font-black text-slate-800 uppercase tracking-tighter">Ajustes de Estaciones</h2>
            </div>
        </template>

        <div class="max-w-5xl mx-auto p-4 md:p-6 pb-24">
            
            <div class="flex flex-col sm:flex-row gap-3 mb-6 items-stretch sm:items-center justify-between">
                <div class="relative w-full sm:w-72 group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input v-model="search" type="text" 
                        class="w-full h-11 bg-white border-slate-200 rounded-xl pl-10 pr-4 text-[10px] font-black uppercase tracking-widest focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-sm shadow-slate-100" 
                        placeholder="Filtrar por nombre...">
                </div>

                <button @click="abrirModal()" class="h-11 px-6 bg-slate-900 hover:bg-blue-600 text-white rounded-xl font-black text-[9px] uppercase tracking-widest transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                    Nueva Estación
                </button>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50 border-b border-slate-50">
                                <th class="p-5 pl-8">Nombre de Estación</th>
                                <th class="p-5 text-center">Tipo de Proceso</th>
                                <th class="p-5 text-right pr-8">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="m in maquinasFiltradas" :key="m.id" class="group hover:bg-slate-50/50 transition-colors">
                                <td class="p-5 pl-8">
                                    <div class="flex items-center gap-3">
                                        <div class="h-1.5 w-1.5 rounded-full" :class="m.sub_area === 'Inyeccion' ? 'bg-blue-500 shadow-[0_0_8px_#3b82f6]' : 'bg-emerald-500 shadow-[0_0_8px_#10b981]'"></div>
                                        <span class="font-black text-slate-700 uppercase text-xs leading-none">{{ m.nombre }}</span>
                                    </div>
                                </td>
                                <td class="p-5 text-center">
                                    <span :class="[
                                        'px-3 py-1 rounded-lg text-[8px] font-black uppercase tracking-tighter border shadow-sm',
                                        m.sub_area === 'Inyeccion' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'
                                    ]">
                                        {{ m.sub_area }}
                                    </span>
                                </td>
                                <td class="p-5 text-right pr-8 space-x-1">
                                    <button @click="abrirModal(m)" class="h-8 w-8 bg-white border border-slate-100 rounded-lg text-slate-400 hover:text-blue-600 hover:shadow-md transition-all inline-flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <button @click="eliminar(m.id)" class="h-8 w-8 bg-white border border-slate-100 rounded-lg text-slate-300 hover:text-red-500 hover:shadow-md transition-all inline-flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="sm:hidden divide-y divide-slate-50">
                    <div v-for="m in maquinasFiltradas" :key="m.id" class="p-5 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div :class="['w-1 h-8 rounded-full', m.sub_area === 'Inyeccion' ? 'bg-blue-500 shadow-[0_0_8px_#3b82f6]' : 'bg-emerald-500 shadow-[0_0_8px_#10b981]']"></div>
                            <div>
                                <h4 class="font-black text-slate-700 uppercase text-xs leading-none mb-1">{{ m.nombre }}</h4>
                                <span class="text-[7px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100">{{ m.sub_area }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="abrirModal(m)" class="h-10 w-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center border border-blue-100/50 active:scale-90 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                            <button @click="eliminar(m.id)" class="h-10 w-10 bg-red-50 text-red-400 rounded-xl flex items-center justify-center border border-red-100/50 active:scale-90 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="maquinasFiltradas.length === 0" class="p-16 text-center flex flex-col items-center gap-3">
                    <p class="text-slate-300 font-black uppercase text-[9px] tracking-[0.2em]">Sin resultados</p>
                </div>
            </div>
        </div>

        <transition name="fade">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-6">
                <div class="bg-white w-full max-w-xs rounded-[2.5rem] shadow-2xl overflow-hidden p-8 animate-in zoom-in-95 duration-200">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-base font-black text-slate-800 uppercase tracking-tighter">
                            {{ editMode ? 'Editar' : 'Nueva' }} Estación
                        </h3>
                        <button @click="cerrarModal" class="h-8 w-8 bg-slate-50 text-slate-300 hover:text-slate-800 rounded-full flex items-center justify-center transition-all font-black">✕</button>
                    </div>
                    
                    <form @submit.prevent="guardar" class="space-y-4">
                        <div>
                            <label class="text-[8px] font-black text-slate-400 uppercase mb-1.5 block tracking-widest px-1">Nombre / ID</label>
                            <input v-model="form.nombre" type="text" required
                                class="w-full h-11 bg-slate-50 border-none rounded-xl font-black px-4 focus:ring-4 focus:ring-blue-500/5 transition-all text-xs uppercase shadow-inner" 
                                placeholder="INY-01">
                            <p v-if="form.errors.nombre" class="text-red-500 text-[8px] font-black mt-1.5 uppercase px-1">{{ form.errors.nombre }}</p>
                        </div>

                        <div>
                            <label class="text-[8px] font-black text-slate-400 uppercase mb-1.5 block tracking-widest px-1">Proceso</label>
                            <select v-model="form.sub_area" class="w-full h-11 bg-slate-50 border-none rounded-xl font-black px-4 focus:ring-4 focus:ring-blue-500/5 text-xs uppercase shadow-inner cursor-pointer appearance-none">
                                <option value="Inyeccion">Inyección</option>
                                <option value="Soplado">Soplado</option>
                            </select>
                        </div>

                        <div class="mt-8 flex flex-col gap-3">
                            <button type="submit" :disabled="form.processing" 
                                class="w-full h-14 bg-slate-900 hover:bg-blue-600 text-white border-b-4 border-slate-950 hover:border-blue-800 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-xl active:scale-95 active:border-b-0 transition-all disabled:opacity-50 flex items-center justify-center">
                                {{ editMode ? 'Actualizar Estación' : 'Guardar Registro 🚀' }}
                            </button>
                            <button type="button" @click="cerrarModal" 
                                class="w-full h-12 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-2xl font-black text-[9px] uppercase tracking-widest transition-all text-center border border-transparent hover:border-slate-300 active:scale-95">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }
.custom-scrollbar::-webkit-scrollbar { height: 2px; width: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@keyframes zoom-in {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.animate-in { animation: zoom-in 0.3s ease-out forwards; }
</style>