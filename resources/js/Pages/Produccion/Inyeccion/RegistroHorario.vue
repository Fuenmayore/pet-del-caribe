<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({ turno: Object, anomalias: Array });

// Paso 2 y 3: Configuración de Producción (Reactiva)
const config = ref({
    referencia: '', num_cavidades: 0, unid_x_hora_meta: 0, ciclo_estandar: 0
});

// Paso 5: Materia Prima Dinámica (Permite múltiples mezclas)
const materiales = ref([{ nombre: 'PET', porcentaje: 100 }]);
const agregarMaterial = () => materiales.value.push({ nombre: '', porcentaje: 0 });

// Paso 4: Reporte Horario
const registros = ref([{ hora_reloj: '06:00', num_vale: '', unid_buenas: 0, pnc_total: 0 }]);

// Cálculo de % PNC (Tiempo Real)
const calcularPncPorcentaje = (buenas, pnc) => {
    const total = Number(buenas) + Number(pnc);
    return total > 0 ? ((pnc / total) * 100).toFixed(2) : "0.00";
};

// Modales
const modalPnc = ref(false);
const modalParo = ref(false);
</script>

<template>
    <AuthenticatedLayout>
        <template #header>Consola de Producción: {{ turno.maquina.nombre }}</template>
        
        <div class="max-w-7xl mx-auto p-4 space-y-6 pb-24">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="col-span-1 md:col-span-2">
                    <label class="text-[10px] font-black text-pet-blue uppercase mb-1 block tracking-widest">Referencia del Producto</label>
                    <input type="text" v-model="config.referencia" class="w-full h-12 border-gray-200 rounded-lg font-bold uppercase" placeholder="Ej: PREFORMA 23.7G">
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block tracking-widest">Cavidades</label>
                    <input type="number" v-model="config.num_cavidades" class="w-full h-12 border-gray-200 rounded-lg font-black text-center text-pet-blue">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Meta/H</label>
                        <input type="number" v-model="config.unid_x_hora_meta" class="w-full h-12 border-gray-200 rounded-lg text-center font-bold">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Ciclo (s)</label>
                        <input type="number" step="0.1" v-model="config.ciclo_estandar" class="w-full h-12 border-gray-200 rounded-lg text-center font-bold">
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                <div class="flex justify-between items-center mb-4">
                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Control de Materia Prima / % Molido</label>
                    <button @click="agregarMaterial" class="text-[9px] font-black text-pet-blue uppercase">+ Agregar Mezcla</button>
                </div>
                <div class="flex flex-wrap gap-4">
                    <div v-for="(mat, i) in materiales" :key="i" class="flex items-center space-x-2 bg-white p-2 rounded-lg border">
                        <input type="text" v-model="mat.nombre" class="h-8 text-xs border-none font-bold" placeholder="Material">
                        <input type="number" v-model="mat.porcentaje" class="h-8 w-16 text-xs border-none font-black text-pet-blue" placeholder="%">
                        <span class="text-[10px] font-bold text-gray-400">%</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                <div class="hidden md:grid grid-cols-12 bg-pet-blue text-white p-4 text-[10px] font-black uppercase tracking-widest">
                    <div class="col-span-1">Hora</div>
                    <div class="col-span-2 text-center">Nº Vale (Lote)</div>
                    <div class="col-span-3 text-center">Buenas (Unid)</div>
                    <div class="col-span-1 text-center">% PNC</div>
                    <div class="col-span-3 text-center">Novedades</div>
                    <div class="col-span-2 text-center">Sync</div>
                </div>
                <div v-for="(reg, i) in registros" :key="i" class="flex flex-col md:grid md:grid-cols-12 p-4 items-center gap-4 border-b group hover:bg-gray-50">
                    <div class="col-span-1 font-black text-pet-blue text-lg">{{ reg.hora_reloj }}</div>
                    <div class="col-span-2 w-full"><label class="md:hidden text-[9px] font-bold text-gray-400 uppercase mb-1 block">Nº Vale</label><input type="text" v-model="reg.num_vale" class="w-full h-12 border-gray-200 rounded-lg text-center font-bold text-sm"></div>
                    <div class="col-span-3 w-full"><label class="md:hidden text-[9px] font-bold text-gray-400 uppercase mb-1 block">Buenas</label><input type="number" v-model="reg.unid_buenas" class="w-full h-12 border-pet-blue/20 rounded-lg text-center font-black text-xl text-pet-blue"></div>
                    <div class="col-span-1 text-center"><label class="md:hidden text-[9px] font-bold text-gray-400 uppercase mb-1 block">% PNC</label><span class="font-black text-red-500">{{ calcularPncPorcentaje(reg.unid_buenas, reg.pnc_total) }}%</span></div>
                    <div class="col-span-3 w-full flex gap-2">
                        <button @click="modalPnc = true" class="flex-1 h-12 border-2 border-red-100 rounded-lg text-[9px] font-black text-red-600 uppercase hover:bg-red-600 hover:text-white transition-all">Reportar PNC</button>
                        <button @click="modalParo = true" class="flex-1 h-12 border-2 border-orange-100 rounded-lg text-[9px] font-black text-orange-600 uppercase hover:bg-orange-600 hover:text-white transition-all">Tiempos Perdidos</button>
                    </div>
                    <div class="col-span-2 w-full"><button class="w-full h-12 bg-pet-blue text-white rounded-lg font-black text-[10px] uppercase shadow-sm">Sincronizar</button></div>
                </div>
            </div>
        </div>

        <div v-if="modalPnc" class="fixed inset-0 z-[100] bg-pet-blue/20 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-xl rounded-[2rem] shadow-2xl overflow-hidden">
                <div class="bg-red-600 p-6 text-white text-center"><h3 class="font-black uppercase tracking-widest">Detalle de Producto No Conforme</h3></div>
                <div class="p-8 grid grid-cols-3 gap-4">
                    <div class="col-span-3"><label class="text-[10px] font-black text-gray-400 uppercase">Item (Anomalía Cliente)</label><select class="w-full h-12 border-gray-200 rounded-lg font-bold"><option v-for="a in anomalias" :key="a.id" :value="a.id">{{ a.descripcion }}</option></select></div>
                    <div><label class="text-[10px] font-black text-gray-400 uppercase">Malas</label><input type="number" class="w-full h-12 border-gray-200 rounded-lg text-center font-bold"></div>
                    <div><label class="text-[10px] font-black text-gray-400 uppercase">Cont</label><input type="number" class="w-full h-12 border-gray-200 rounded-lg text-center font-bold"></div>
                    <div><label class="text-[10px] font-black text-gray-400 uppercase">Torta</label><input type="number" class="w-full h-12 border-gray-200 rounded-lg text-center font-bold"></div>
                    <div class="col-span-3"><label class="text-[10px] font-black text-gray-400 uppercase">Causa / Observaciones</label><textarea class="w-full border-gray-200 rounded-lg text-sm" rows="3"></textarea></div>
                    <div class="col-span-3 flex gap-4 mt-4"><button @click="modalPnc = false" class="flex-1 py-4 font-black text-gray-400 uppercase text-xs">Cancelar</button><button class="flex-[2] py-4 bg-red-600 text-white rounded-xl font-black uppercase text-xs shadow-lg">Registrar PNC</button></div>
                </div>
            </div>
        </div>

        <div v-if="modalParo" class="fixed inset-0 z-[100] bg-pet-blue/20 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden">
                <div class="bg-orange-500 p-6 text-white text-center"><h3 class="font-black uppercase tracking-widest">Reporte de Tiempo Perdido</h3></div>
                <div class="p-8 space-y-4">
                    <div><label class="text-[10px] font-black text-gray-400 uppercase">Item (Motivo de Paro)</label><select class="w-full h-12 border-gray-200 rounded-lg font-bold"><option v-for="a in anomalias" :key="a.id" :value="a.id">{{ a.descripcion }}</option></select></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="text-[10px] font-black text-gray-400 uppercase">Hora Inicio</label><input type="time" class="w-full h-12 border-gray-200 rounded-lg text-center font-bold"></div>
                        <div><label class="text-[10px] font-black text-gray-400 uppercase">Minutos Perdidos</label><input type="number" class="w-full h-12 border-gray-200 rounded-lg text-center font-bold"></div>
                    </div>
                    <div><label class="text-[10px] font-black text-gray-400 uppercase">Observaciones Técnicas</label><textarea class="w-full border-gray-200 rounded-lg text-sm" rows="3"></textarea></div>
                    <div class="flex gap-4 mt-4"><button @click="modalParo = false" class="flex-1 py-4 font-black text-gray-400 uppercase text-xs">Cerrar</button><button class="flex-[2] py-4 bg-orange-500 text-white rounded-xl font-black uppercase text-xs shadow-lg">Registrar Paro</button></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>