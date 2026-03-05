<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ 
    productos: Object,
    filters: Object 
});

const showModal = ref(false);
const editMode = ref(false);
const search = ref(props.filters?.search || ''); 

// --- FILTRO GLOBAL (SERVIDOR) ---
watch(search, (value) => {
    router.get(route('settings.productos.index'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
});

const form = useForm({
    id: null,
    item: '',
    descripcion: '',
    ciclo: 0,
    cavidades: 1,
    area: 'INYECCIÓN'
});

const abrirModal = (producto = null) => {
    if (producto) {
        editMode.value = true;
        form.id = producto.id;
        form.item = producto.item;
        form.descripcion = producto.descripcion;
        form.ciclo = producto.ciclo;
        form.cavidades = producto.cavidades;
        form.area = producto.area;
    } else {
        editMode.value = false;
        form.reset();
    }
    showModal.value = true;
};

const guardar = () => {
    const action = editMode.value 
        ? route('settings.productos.update', form.id) 
        : route('settings.productos.store');
    
    form[editMode.value ? 'put' : 'post'](action, {
        onSuccess: () => cerrarModal(),
        preserveScroll: true
    });
};

const eliminar = (id) => {
    if (confirm('¿Eliminar esta referencia?')) {
        router.delete(route('settings.productos.destroy', id), { preserveScroll: true });
    }
};

const cerrarModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};
</script>

<template>
    <Head title="Productos" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-600 text-white rounded-lg shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                </div>
                <h2 class="font-black text-slate-800 uppercase tracking-tighter">Maestro de Productos</h2>
            </div>
        </template>

        <div class="max-w-6xl mx-auto p-4 md:p-6 pb-24">
            
            <div class="flex flex-col sm:flex-row gap-3 mb-6 items-stretch sm:items-center justify-between">
                <div class="relative w-full sm:w-80 group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input v-model="search" type="text" 
                        class="w-full h-11 bg-white border-slate-200 rounded-xl pl-10 pr-4 text-[10px] font-black uppercase tracking-widest focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 transition-all shadow-sm shadow-slate-100" 
                        placeholder="Buscar en el catálogo...">
                </div>

                <button @click="abrirModal()" class="h-11 px-6 bg-slate-900 hover:bg-emerald-600 text-white rounded-xl font-black text-[9px] uppercase tracking-widest transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                    Nuevo Producto
                </button>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
                
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50 border-b border-slate-50">
                                <th class="p-5 pl-8">Item</th>
                                <th class="p-5">Referencia</th>
                                <th class="p-5 text-center">Ciclo (s)</th>
                                <th class="p-5 text-center">Cav.</th>
                                <th class="p-5 text-right pr-8">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="p in productos.data" :key="p.id" class="group hover:bg-slate-50/50 transition-colors">
                                <td class="p-5 pl-8">
                                    <div class="font-mono font-black text-emerald-600 text-xs bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100 inline-block">
                                        {{ p.item }}
                                    </div>
                                </td>
                                <td class="p-5">
                                    <p class="font-black text-slate-700 uppercase text-xs leading-none mb-1">{{ p.descripcion }}</p>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ p.area }}</span>
                                </td>
                                <td class="p-5 text-center font-black text-slate-700 text-xs">{{ p.ciclo }}s</td>
                                <td class="p-5 text-center font-black text-slate-500 text-xs">{{ p.cavidades }}</td>
                                <td class="p-5 text-right pr-8 space-x-1">
                                    <button @click="abrirModal(p)" class="h-8 w-8 bg-white border border-slate-100 rounded-lg text-slate-400 hover:text-emerald-600 hover:shadow-md transition-all inline-flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <button @click="eliminar(p.id)" class="h-8 w-8 bg-white border border-slate-100 rounded-lg text-slate-300 hover:text-red-500 hover:shadow-md transition-all inline-flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="sm:hidden divide-y divide-slate-50">
                    <div v-for="p in productos.data" :key="p.id" class="p-5 flex items-center justify-between hover:bg-slate-50 transition-colors group">
                        <div class="flex items-center gap-3">
                            <div :class="['w-1 h-10 rounded-full', p.area.includes('INY') ? 'bg-emerald-500' : 'bg-blue-500']"></div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-mono font-black text-emerald-600 text-[9px] bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">{{ p.item }}</span>
                                    <span class="text-[7px] font-black text-slate-300 uppercase tracking-widest">{{ p.area }}</span>
                                </div>
                                <h4 class="font-black text-slate-700 uppercase text-xs leading-tight">{{ p.descripcion }}</h4>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="abrirModal(p)" class="h-10 w-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center border border-emerald-100/50 active:scale-90 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                            <button @click="eliminar(p.id)" class="h-10 w-10 bg-red-50 text-red-400 rounded-xl flex items-center justify-center border border-red-100/50 active:scale-90 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="productos.data.length === 0" class="p-20 text-center flex flex-col items-center gap-3">
                    <p class="text-slate-300 font-black uppercase text-[10px] tracking-[0.3em]">No se encontraron resultados</p>
                </div>
            </div>

            <div v-if="productos.links && productos.links.length > 3" class="mt-10 flex flex-wrap justify-center gap-2">
                <Link v-for="(link, k) in productos.links" :key="k" 
                    :href="link.url || '#'" 
                    v-html="link.label"
                    :class="[
                        'px-4 py-2 rounded-xl text-[10px] font-black transition-all border shrink-0', 
                        link.active ? 'bg-emerald-600 text-white border-emerald-600 shadow-lg shadow-emerald-600/20' : 'bg-white text-slate-400 border-slate-100 hover:bg-slate-50', 
                        !link.url ? 'opacity-30 cursor-not-allowed' : ''
                    ]" 
                />
            </div>
        </div>

        <transition name="fade">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-6">
                <div class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden p-8 animate-in zoom-in-95 duration-200">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter leading-none">
                                {{ editMode ? 'Editar' : 'Nueva' }} Referencia
                            </h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2 italic leading-none">Ficha Técnica</p>
                        </div>
                        <button @click="cerrarModal" class="h-10 w-10 bg-slate-50 text-slate-300 hover:text-slate-800 rounded-full flex items-center justify-center transition-all font-black">✕</button>
                    </div>
                    
                    <form @submit.prevent="guardar" class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[8px] font-black text-slate-400 uppercase mb-1.5 block tracking-widest px-1">Código Item</label>
                                <input v-model="form.item" type="text" required class="w-full h-11 bg-slate-50 border-none rounded-xl font-black px-4 focus:ring-4 focus:ring-emerald-500/5 text-xs uppercase shadow-inner" placeholder="P-100">
                                <p v-if="form.errors.item" class="text-red-500 text-[8px] font-bold mt-1.5 uppercase px-1">{{ form.errors.item }}</p>
                            </div>
                            <div>
                                <label class="text-[8px] font-black text-slate-400 uppercase mb-1.5 block tracking-widest px-1">Proceso</label>
                                <select v-model="form.area" class="w-full h-11 bg-slate-50 border-none rounded-xl font-black px-4 focus:ring-4 focus:ring-emerald-500/5 text-xs shadow-inner appearance-none cursor-pointer uppercase">
                                    <option value="INYECCIÓN">INYECCIÓN</option>
                                    <option value="SOPLADO">SOPLADO</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="text-[8px] font-black text-slate-400 uppercase mb-1.5 block tracking-widest px-1">Descripción Técnica</label>
                            <input v-model="form.descripcion" type="text" required class="w-full h-11 bg-slate-50 border-none rounded-xl font-black px-4 focus:ring-4 focus:ring-emerald-500/5 text-xs uppercase shadow-inner" placeholder="Nombre completo">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[8px] font-black text-slate-400 uppercase mb-1.5 block tracking-widest px-1">Ciclo (s)</label>
                                <input v-model="form.ciclo" type="number" step="0.01" required class="w-full h-11 bg-slate-50 border-none rounded-xl font-black px-4 focus:ring-4 focus:ring-emerald-500/5 text-xs shadow-inner text-emerald-600">
                            </div>
                            <div>
                                <label class="text-[8px] font-black text-slate-400 uppercase mb-1.5 block tracking-widest px-1">Cavidades</label>
                                <input v-model="form.cavidades" type="number" required class="w-full h-11 bg-slate-50 border-none rounded-xl font-black px-4 focus:ring-4 focus:ring-emerald-500/5 text-xs shadow-inner">
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col gap-3">
                            <button type="submit" :disabled="form.processing" 
                                class="w-full h-14 bg-slate-900 hover:bg-emerald-600 text-white border-b-4 border-slate-950 hover:border-emerald-800 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-xl active:scale-95 active:border-b-0 transition-all disabled:opacity-50 flex items-center justify-center">
                                {{ editMode ? 'Actualizar Referencia' : 'Guardar en Maestro 🚀' }}
                            </button>
                            <button type="button" @click="cerrarModal" 
                                class="w-full h-12 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-2xl font-black text-[9px] uppercase tracking-widest transition-all text-center border border-transparent hover:border-slate-300 active:scale-95">
                                Cancelar Registro
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