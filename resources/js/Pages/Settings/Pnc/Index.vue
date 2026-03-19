<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ catalogo: Array });
const showModal = ref(false);
const editing = ref(null);

const form = useForm({ nombre: '', area: 'Inyección' });

const submit = () => {
    if (editing.value) {
        form.put(route('settings.pnc.update', editing.value.id), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('settings.pnc.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const openEdit = (item) => {
    editing.value = item;
    form.nombre = item.nombre;
    form.area = item.area;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editing.value = null;
    form.reset();
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-black text-slate-800 uppercase tracking-tighter">Catálogo de Defectos (PNC)</h2>
                <button @click="showModal = true" class="bg-pet-blue text-white px-6 py-2 rounded-xl font-black text-xs uppercase shadow-lg">+ Nuevo Concepto</button>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b">
                        <tr class="text-[10px] font-black text-slate-400 uppercase">
                            <th class="p-4">Concepto</th>
                            <th class="p-4 text-center">Área Aplicable</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="item in catalogo" :key="item.id" class="text-sm hover:bg-slate-50/50 transition-colors">
                            <td class="p-4 font-bold text-slate-700 uppercase">{{ item.nombre }}</td>
                            <td class="p-4 text-center">
                                <span :class="{'bg-blue-50 text-blue-600': item.area === 'Inyección', 'bg-emerald-50 text-emerald-600': item.area === 'Soplado', 'bg-purple-50 text-purple-600': item.area === 'Ambos'}" 
                                      class="px-3 py-1 rounded-full text-[9px] font-black uppercase border">
                                    {{ item.area }}
                                </span>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <button @click="openEdit(item)" class="text-pet-blue font-black text-[10px] uppercase hover:underline">Editar</button>
                                <button @click="$inertia.delete(route('settings.pnc.destroy', item.id))" class="text-red-400 font-black text-[10px] uppercase hover:text-red-600">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-[2rem] p-8 w-full max-w-md shadow-2xl">
                <h3 class="text-lg font-black uppercase text-slate-800 mb-6">{{ editing ? 'Actualizar' : 'Nuevo' }} Concepto PNC</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-400 ml-2">Nombre del Defecto</label>
                        <input v-model="form.nombre" type="text" class="w-full h-12 bg-slate-50 border-none rounded-xl font-bold uppercase" placeholder="Ej: TORTA COLOR">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-400 ml-2">Área Responsable</label>
                        <select v-model="form.area" class="w-full h-12 bg-slate-50 border-none rounded-xl font-bold">
                            <option value="Inyección">Inyección</option>
                            <option value="Soplado">Soplado</option>
                            <option value="Ambos">Ambos (Inyección - Soplado)</option>
                        </select>
                    </div>
                    <div class="flex gap-3 mt-8">
                        <button type="button" @click="closeModal" class="flex-1 py-3 text-[10px] font-black uppercase text-slate-400">Cancelar</button>
                        <button type="submit" class="flex-1 py-3 bg-pet-blue text-white rounded-xl font-black text-[10px] uppercase shadow-lg">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>