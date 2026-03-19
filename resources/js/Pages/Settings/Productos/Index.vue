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
    ref_maq: '',
    item: '',
    descripcion: '',
    tipo_inventario: 'PT',
    area: 'INYECCIÓN',
    centro_trabajo: '',
    preforma: '',
    maquina: '',
    ciclo: 0,
    cavidades: 0,
    bot_hora: 0,
    unidad_empaque: ''
});

const abrirModal = (producto = null) => {
    if (producto) {
        editMode.value = true;
        form.id = producto.id;
        form.ref_maq = producto.ref_maq;
        form.item = producto.item;
        form.descripcion = producto.descripcion;
        form.tipo_inventario = producto.tipo_inventario;
        form.area = producto.area;
        form.centro_trabajo = producto.centro_trabajo;
        form.preforma = producto.preforma;
        form.maquina = producto.maquina;
        form.ciclo = producto.ciclo;
        form.cavidades = producto.cavidades;
        form.bot_hora = producto.bot_hora;
        form.unidad_empaque = producto.unidad_empaque;
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
    if (confirm('¿Eliminar esta referencia del catálogo maestro?')) {
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
    <Head title="Maestro de Productos" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-slate-900 text-emerald-400 rounded-lg shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                </div>
                <div>
                    <h2 class="font-black text-slate-800 uppercase tracking-tighter leading-none">Maestro de Productos</h2>
                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 tracking-widest">Catálogo Técnico de Referencias</p>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto p-4 md:p-6 pb-24">
            
            <div class="flex flex-col sm:flex-row gap-3 mb-6 items-stretch sm:items-center justify-between">
                <div class="relative w-full sm:w-96 group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input v-model="search" type="text" 
                        class="w-full h-12 bg-white border-slate-200 rounded-2xl pl-11 pr-4 text-xs font-bold uppercase focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all shadow-sm shadow-slate-100" 
                        placeholder="Buscar por ITEM, DESCRIPCIÓN o REF+MAQ...">
                </div>

                <button @click="abrirModal()" class="h-12 px-8 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all shadow-xl shadow-indigo-100 active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                    Registrar Producto
                </button>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50 border-b border-slate-100">
                                <th class="p-6">Item / Ref+Maq</th>
                                <th class="p-6">Descripción Técnica</th>
                                <th class="p-6 text-center">Ciclo</th>
                                <th class="p-6 text-center">Cav.</th>
                                <th class="p-6 text-center">Bot/Hora</th>
                                <th class="p-6 text-right pr-10">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="p in productos.data" :key="p.id" class="group hover:bg-slate-50 transition-colors">
                                <td class="p-6">
                                    <div class="font-mono font-black text-indigo-600 text-xs mb-1">{{ p.item }}</div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ p.ref_maq || 'S/D' }}</div>
                                </td>
                                <td class="p-6">
                                    <p class="font-black text-slate-700 uppercase text-[11px] leading-tight mb-1">{{ p.descripcion }}</p>
                                    <div class="flex gap-2">
                                        <span class="text-[7px] font-black px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 uppercase">{{ p.area }}</span>
                                        <span v-if="p.maquina" class="text-[7px] font-black px-1.5 py-0.5 rounded bg-blue-50 text-blue-500 uppercase">{{ p.maquina }}</span>
                                    </div>
                                </td>
                                <td class="p-6 text-center font-mono font-black text-slate-700 text-xs">{{ p.ciclo }}<span class="text-slate-300 ml-0.5 text-[10px]">s</span></td>
                                <td class="p-6 text-center font-black text-slate-500 text-xs">{{ p.cavidades }}</td>
                                <td class="p-6 text-center">
                                    <span class="font-mono font-black text-emerald-600 text-xs">{{ p.bot_hora?.toLocaleString() || 0 }}</span>
                                </td>
                                <td class="p-6 text-right pr-10 space-x-2">
                                    <button @click="abrirModal(p)" class="h-9 w-9 bg-white border border-slate-100 rounded-xl text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-lg transition-all inline-flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button @click="eliminar(p.id)" class="h-9 w-9 bg-white border border-slate-100 rounded-xl text-slate-300 hover:text-red-500 hover:border-red-100 hover:shadow-lg transition-all inline-flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div v-if="productos.data.length === 0" class="p-20 text-center flex flex-col items-center gap-3">
                    <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center text-slate-200">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                    <p class="text-slate-300 font-black uppercase text-[10px] tracking-[0.3em]">No hay coincidencias en el maestro</p>
                </div>
            </div>

            <div v-if="productos.links && productos.links.length > 3" class="mt-8 flex flex-wrap justify-center gap-2">
                <Link v-for="(link, k) in productos.links" :key="k" 
                    :href="link.url || '#'" 
                    v-html="link.label"
                    :class="[
                        'px-4 py-2 rounded-xl text-[10px] font-black transition-all border shrink-0', 
                        link.active ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-200' : 'bg-white text-slate-400 border-slate-100 hover:bg-slate-50', 
                        !link.url ? 'opacity-30 cursor-not-allowed' : ''
                    ]" 
                />
            </div>
        </div>

        <transition name="fade">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4 md:p-6">
                <div class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col max-h-[95vh] animate-in zoom-in-95 duration-200">
                    
                    <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tighter leading-none">
                                {{ editMode ? 'Editar' : 'Nueva' }} Ficha Técnica
                            </h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2 flex items-center gap-2">
                                <span class="w-2 h-px bg-slate-300"></span> Parámetros Estándar de Producción
                            </p>
                        </div>
                        <button @click="cerrarModal" class="h-12 w-12 bg-white text-slate-300 hover:text-red-500 rounded-2xl flex items-center justify-center transition-all shadow-sm border border-slate-100 font-black">✕</button>
                    </div>
                    
                    <form @submit.prevent="guardar" class="flex-grow overflow-y-auto p-8 custom-scrollbar space-y-8">
                        
                        <div class="space-y-4">
                            <h4 class="text-[10px] font-black text-indigo-500 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-lg inline-block">1. Identificación del Item</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black text-slate-400 uppercase px-1">REF + MAQ</label>
                                    <input v-model="form.ref_maq" type="text" class="w-full h-12 bg-slate-50 border-none rounded-2xl font-black px-5 focus:ring-4 focus:ring-indigo-500/5 text-xs uppercase shadow-inner" placeholder="Ej: P2591-H1">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black text-slate-400 uppercase px-1">Código Item (Único)</label>
                                    <input v-model="form.item" type="text" required class="w-full h-12 bg-slate-50 border-none rounded-2xl font-black px-5 focus:ring-4 focus:ring-indigo-500/5 text-xs uppercase shadow-inner" placeholder="Ej: 2591.0">
                                    <p v-if="form.errors.item" class="text-red-500 text-[8px] font-bold mt-1 uppercase">{{ form.errors.item }}</p>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black text-slate-400 uppercase px-1">Descripción del Item</label>
                                <input v-model="form.descripcion" type="text" required class="w-full h-12 bg-slate-50 border-none rounded-2xl font-black px-5 focus:ring-4 focus:ring-indigo-500/5 text-xs uppercase shadow-inner" placeholder="Ej: PREFORMA 28MM 13.5G CRISTAL">
                            </div>
                        </div>

                        <div class="space-y-4 pt-4 border-t border-slate-50">
                            <h4 class="text-[10px] font-black text-emerald-500 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-lg inline-block">2. Parámetros de Ingeniería</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black text-slate-400 uppercase px-1">Ciclo (s)</label>
                                    <input v-model="form.ciclo" type="number" step="0.01" required class="w-full h-12 bg-slate-50 border-none rounded-2xl font-black px-5 focus:ring-4 focus:ring-indigo-500/5 text-xs shadow-inner text-indigo-600">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black text-slate-400 uppercase px-1">Cavidades</label>
                                    <input v-model="form.cavidades" type="number" required class="w-full h-12 bg-slate-50 border-none rounded-2xl font-black px-5 focus:ring-4 focus:ring-indigo-500/5 text-xs shadow-inner">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black text-slate-400 uppercase px-1">BOT / HORA</label>
                                    <input v-model="form.bot_hora" type="number" class="w-full h-12 bg-slate-50 border-none rounded-2xl font-black px-5 focus:ring-4 focus:ring-indigo-500/5 text-xs shadow-inner text-emerald-600">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black text-slate-400 uppercase px-1">Área</label>
                                    <select v-model="form.area" class="w-full h-12 bg-slate-50 border-none rounded-2xl font-black px-5 focus:ring-4 focus:ring-indigo-500/5 text-xs shadow-inner appearance-none cursor-pointer uppercase">
                                        <option value="INYECCIÓN">INYECCIÓN</option>
                                        <option value="SOPLADO">SOPLADO</option>
                                        <option value="TAPA Y ASA">TAPA Y ASA</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black text-slate-400 uppercase px-1">Preforma</label>
                                    <input v-model="form.preforma" type="text" class="w-full h-12 bg-slate-50 border-none rounded-2xl font-black px-5 focus:ring-4 focus:ring-indigo-500/5 text-xs uppercase shadow-inner">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black text-slate-400 uppercase px-1">Máquina Ideal</label>
                                    <input v-model="form.maquina" type="text" class="w-full h-12 bg-slate-50 border-none rounded-2xl font-black px-5 focus:ring-4 focus:ring-indigo-500/5 text-xs uppercase shadow-inner" placeholder="Ej: H-01">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black text-slate-400 uppercase px-1">Centro Trabajo</label>
                                    <input v-model="form.centro_trabajo" type="text" class="w-full h-12 bg-slate-50 border-none rounded-2xl font-black px-5 focus:ring-4 focus:ring-indigo-500/5 text-xs uppercase shadow-inner" placeholder="Ej: INY-1">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 pt-4 border-t border-slate-50">
                            <h4 class="text-[10px] font-black text-amber-500 uppercase tracking-widest bg-amber-50 px-3 py-1 rounded-lg inline-block">3. Almacén y Empaque</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black text-slate-400 uppercase px-1">Tipo Inventario</label>
                                    <input v-model="form.tipo_inventario" type="text" class="w-full h-12 bg-slate-50 border-none rounded-2xl font-black px-5 focus:ring-4 focus:ring-indigo-500/5 text-xs uppercase shadow-inner">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black text-slate-400 uppercase px-1">Unidad de Empaque</label>
                                    <input v-model="form.unidad_empaque" type="text" class="w-full h-12 bg-slate-50 border-none rounded-2xl font-black px-5 focus:ring-4 focus:ring-indigo-500/5 text-xs uppercase shadow-inner" placeholder="Ej: CAJA X 15.000">
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="p-8 bg-slate-900 border-t border-slate-800 flex flex-col md:flex-row gap-3">
                        <button type="button" @click="cerrarModal" 
                            class="flex-1 h-14 bg-white/5 hover:bg-white/10 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                            Cerrar sin guardar
                        </button>
                        <button @click="guardar" :disabled="form.processing" 
                            class="flex-[2] h-14 bg-indigo-500 hover:bg-indigo-400 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-2xl shadow-indigo-500/20 transition-all disabled:opacity-50 flex items-center justify-center gap-3">
                            <span v-if="form.processing" class="animate-spin border-2 border-white/20 border-t-white h-4 w-4 rounded-full"></span>
                            {{ editMode ? 'Actualizar Ficha Técnica' : 'Sincronizar con Maestro 🚀' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }
.custom-scrollbar::-webkit-scrollbar { height: 4px; width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@keyframes zoom-in {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.animate-in { animation: zoom-in 0.3s ease-out forwards; }
input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
</style>