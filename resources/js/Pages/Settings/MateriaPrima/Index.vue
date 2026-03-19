<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ materiales: Array });
const showModal = ref(false);
const editing = ref(null);

const form = useForm({
    nombre: '',
    tipo: 'Resina',
    unidad_medida: 'KG',
    proveedor: ''
});

const openModal = (item = null) => {
    if (item) {
        editing.value = item;
        form.nombre = item.nombre;
        form.tipo = item.tipo;
        form.unidad_medida = item.unidad_medida;
        form.proveedor = item.proveedor;
    } else {
        editing.value = null;
        form.reset();
    }
    showModal.value = true;
};

const submit = () => {
    if (editing.value) {
        // AJUSTE: Se agregó el prefijo 'settings.' a la ruta
        form.put(route('settings.materiaprima.update', editing.value.id), {
            onSuccess: () => { showModal.value = false; form.reset(); }
        });
    } else {
        // AJUSTE: Se agregó el prefijo 'settings.' a la ruta
        form.post(route('settings.materiaprima.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); }
        });
    }
};

const deleteItem = (id) => {
    if (confirm('¿Eliminar este material? Se mantendrá en registros históricos.')) {
        // AJUSTE: Se agregó el prefijo 'settings.' a la ruta
        form.delete(route('settings.materiaprima.destroy', id));
    }
};
</script>

<template>
    <Head title="Materia Prima" />
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-black uppercase text-slate-800 tracking-tighter">Insumos y Materia Prima</h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Control de Trazabilidad - Fase IV</p>
                </div>
                <button @click="openModal()" class="bg-pet-blue text-white px-6 py-3 rounded-2xl font-black text-xs uppercase shadow-xl transition-transform hover:scale-105">
                    + Nuevo Registro
                </button>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/50 border-b border-slate-100">
                        <tr class="text-[10px] font-black uppercase text-slate-400">
                            <th class="p-6">Material</th>
                            <th class="p-6">Categoría</th>
                            <th class="p-6 text-center">U. Medida</th>
                            <th class="p-6 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm">
                        <tr v-for="item in materiales" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-6">
                                <p class="font-bold text-slate-700 uppercase leading-tight">{{ item.nombre }}</p>
                            </td>
                            <td class="p-6">
                                <span class="px-3 py-1 rounded-lg bg-slate-100 text-[9px] font-black uppercase border border-slate-200">
                                    {{ item.tipo }}
                                </span>
                            </td>
                            <td class="p-6 text-center font-black text-pet-blue">{{ item.unidad_medida }}</td>
                            <td class="p-6 text-right flex justify-end gap-4">
                                <button @click="openModal(item)" class="text-[10px] font-black uppercase text-slate-400 hover:text-pet-blue">Editar</button>
                                <button @click="deleteItem(item.id)" class="text-[10px] font-black uppercase text-slate-400 hover:text-red-500">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-[3rem] p-10 w-full max-w-lg shadow-2xl">
                <h3 class="text-2xl font-black uppercase text-slate-800 tracking-tighter mb-8">{{ editing ? 'Editar' : 'Nuevo' }} Material</h3>
                <form @submit.prevent="submit" class="space-y-5">
                    <input v-model="form.nombre" type="text" class="w-full h-14 bg-slate-50 border-none rounded-2xl font-bold uppercase px-6 focus:ring-2 focus:ring-pet-blue" placeholder="NOMBRE DEL MATERIAL" required>
                    <div class="grid grid-cols-2 gap-4">
                        <select v-model="form.tipo" class="h-14 bg-slate-50 border-none rounded-2xl font-bold uppercase px-6">
                            <option value="Resina">Resina</option>
                            <option value="Preforma">Preforma</option>
                            <option value="Pigmento">Pigmento</option>
                            <option value="Aditivo">Aditivo</option>
                            <option value="Otro">Otro</option>
                        </select>
                        <input v-model="form.unidad_medida" type="text" class="h-14 bg-slate-50 border-none rounded-2xl font-black text-center" placeholder="KG" required>
                    </div>
                    <div class="flex gap-4 pt-6">
                        <button type="button" @click="showModal = false" class="flex-1 py-4 text-xs font-black uppercase text-slate-400">Cancelar</button>
                        <button type="submit" class="flex-1 py-4 bg-pet-blue text-white rounded-2xl font-black text-xs uppercase shadow-lg">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>