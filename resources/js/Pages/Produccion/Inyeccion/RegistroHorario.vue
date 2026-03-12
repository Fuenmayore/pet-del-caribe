<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useForm, router, Head, Link } from '@inertiajs/vue3';

const props = defineProps({ 
    turno: Object, 
    productos: Array, 
    anomalias: Array,
    materiales: Array,
    pncCatalogo: Array,
    paradasCatalogo: Array,
    perfilesGuardados: Array 
});

const finalizarTurno = () => {
    if (confirm('¿Estás seguro de finalizar el turno? Una vez cerrado pasará al historial y no podrás registrar más horas.')) {
        router.post(route('produccion.finalizar', props.turno.id));
    }
};

// --- KPIs AUDITADOS (UNIFICADOS CON VISTA ANÁLISIS) ---
const kpisCalculados = computed(() => {
    let totalBuenas = 0;
    let totalScrapKg = 0;
    let totalMinutosParo = 0;
    let metaTotalAcumulada = 0;

    registros.value.forEach(reg => {
        // Solo procesamos horas que tengan algún dato o ya estén guardadas en DB
        if (reg.guardado || reg.buenas > 0 || reg.pnc.length > 0 || reg.paros.length > 0) {
            
            totalBuenas += (parseInt(reg.buenas) || 0);

            // Sumar Scrap en Kilogramos
            if (reg.pnc && reg.pnc.length > 0) {
                reg.pnc.forEach(p => {
                    totalScrapKg += (parseFloat(p.malas_kg) || 0) + 
                                    (parseFloat(p.cont_kg) || 0) + 
                                    (parseFloat(p.torta_kg) || 0);
                });
            }

            // Sumar Minutos de Paro
            if (reg.paros && reg.paros.length > 0) {
                reg.paros.forEach(p => {
                    totalMinutosParo += (parseInt(p.minutos) || 0);
                });
            }

            // --- LÓGICA DE META ESTRICTA (IGUAL A ANÁLISIS) ---
            const config = props.turno.configuraciones?.find(c => c.id === reg.config_id) || props.turno.config_activa;
            
            if (config && config.producto) {
                const cicloStd = parseFloat(config.producto.ciclo || 0);
                const cavStd = parseInt(config.producto.cavidades || 0); // USA ESTÁNDAR, NO reg.cav

                if (cicloStd > 0) {
                    const metaHora = (3600 / cicloStd) * cavStd;
                    metaTotalAcumulada += metaHora;
                }
            }
        }
    });

    const eficienciaReal = metaTotalAcumulada > 0 ? ((totalBuenas * 100) / metaTotalAcumulada).toFixed(1) : "0.0";
    const hrsParo = Math.floor(totalMinutosParo / 60);
    const minsParo = totalMinutosParo % 60;
    const tiempoParoFormateado = hrsParo > 0 ? `${hrsParo}h ${minsParo}m` : `${minsParo} min`;

    return { 
        eficiencia: eficienciaReal, 
        paradas: tiempoParoFormateado, 
        scrap: totalScrapKg.toFixed(2),
        totalBuenas: totalBuenas.toLocaleString()
    };
});

const modalPnc = ref(false);
const modalParo = ref(false);
const indexActivo = ref(null);

const catalogoFallas = computed(() => props.paradasCatalogo);
const listaDefectosCalidad = computed(() => props.pncCatalogo);

const perfilesGuardadosCount = computed(() => {
    return props.perfilesGuardados ? props.perfilesGuardados.length : 0;
});

const modalInspeccion = ref(false);
const cavidadSeleccionada = ref(null);

const calcularDiferenciaMinutos = (inicio, fin) => {
    if (!inicio || !fin) return 0;
    const [h1, m1] = inicio.split(':').map(Number);
    const [h2, m2] = fin.split(':').map(Number);
    let totalInicio = h1 * 60 + m1;
    let totalFin = h2 * 60 + m2;
    if (totalFin < totalInicio) totalFin += 1440;
    return totalFin - totalInicio;
};

const faseSeleccionadaManual = ref(null);
const faseAMostrar = computed(() => faseSeleccionadaManual.value ?? props.turno.config_activa);
const esFaseActiva = computed(() => faseAMostrar.value?.id === props.turno.config_activa?.id);

watch(() => props.turno.config_activa, () => { faseSeleccionadaManual.value = null; }, { deep: true });

const seleccionarFase = (fase) => {
    faseSeleccionadaManual.value = fase;
    editandoConfig.value = false;
};

const generarLoteSugerido = () => {
    const f = new Date();
    const yy = String(f.getFullYear()).slice(-2);
    const mm = String(f.getMonth() + 1).padStart(2, '0');
    const dd = String(f.getDate()).padStart(2, '0');
    const t = props.turno.numero_turno;
    const m = props.turno.maquina.nombre;
    return `${yy}${mm}${dd}T${t}${m}`.replace(/\s+/g, '').toUpperCase();
};

const generarHorasPorTurno = (numeroTurno, duracion = 8) => {
    let horaInicio = 7;
    if (duracion == 12) {
        horaInicio = (numeroTurno == 1) ? 7 : 19;
    } else {
        horaInicio = (numeroTurno == 1) ? 7 : (numeroTurno == 2 ? 15 : 23);
    }
    return Array.from({ length: duracion }, (_, i) => ({
        id: null, // SOPORTE UUID
        hora: `${String((horaInicio + i) % 24).padStart(2, '0')}:00`,
        num_vale: '', ciclo: '', buenas: '', cav: '', 
        pnc: [], paros: [], 
        inspeccion: [], 
        inspeccion_completada: false, 
        procesando: false, guardado: false,
        modificado: false, 
        referencia_nombre: null, config_id: null
    }));
};

const registros = ref(generarHorasPorTurno(props.turno.numero_turno, props.turno.duracion_turno));

const calcularBuenasAutomatico = (index) => {
    const reg = registros.value[index];
    const ciclo = parseFloat(reg.ciclo);
    const cav = parseInt(reg.cav);
    if (ciclo > 0 && cav > 0) {
        reg.buenas = Math.floor((3600 / ciclo) * cav);
    }
};

const cargarDatosExistentes = () => {
    if (props.turno.configuraciones) {
        props.turno.configuraciones.forEach(config => {
            const horas = config.horas_produccion || config.horasProduccion;
            if (horas) {
                horas.forEach(dbHora => {
                    const fila = registros.value.find(r => dbHora.hora.startsWith(r.hora));
                    if (fila && !fila.modificado) {
                        fila.id = dbHora.id; // GUARDAMOS EL UUID
                        fila.num_vale = dbHora.num_vale_inyectora;
                        fila.buenas = dbHora.unidades_buenas;
                        if(dbHora.pnc_detalle?.ciclo_real) fila.ciclo = dbHora.pnc_detalle.ciclo_real;
                        if(dbHora.pnc_detalle?.cavidades_reales) fila.cav = dbHora.pnc_detalle.cavidades_reales;
                        fila.pnc = dbHora.pnc_detalle?.defectos || [];
                        fila.paros = dbHora.pnc_detalle?.paros || [];
                        fila.inspeccion = dbHora.pnc_detalle?.inspeccion || [];
                        fila.inspeccion_completada = dbHora.pnc_detalle?.inspeccion_completada || (dbHora.unidades_buenas > 0 || fila.inspeccion.length > 0);
                        fila.guardado = true;
                        fila.referencia_nombre = config.producto?.descripcion;
                        fila.config_id = config.id;
                    }
                });
            }
        });
    }
};

let pollInterval = null;
onMounted(() => {
    cargarDatosExistentes();
    if (registros.value.length > 0 && !registros.value[0].num_vale) {
        registros.value[0].num_vale = generarLoteSugerido();
    }
    pollInterval = setInterval(() => {
        const estaOcupado = registros.value.some(r => r.procesando) || editandoConfig.value || modalPnc.value || modalParo.value || modalInspeccion.value;
        if (!estaOcupado) {
            router.reload({ 
                only: ['turno', 'perfilesGuardados'], 
                preserveScroll: true, 
                preserveState: true,
                onSuccess: () => cargarDatosExistentes() 
            });
        }
    }, 5000);
});

onUnmounted(() => { if (pollInterval) clearInterval(pollInterval); });

// --- LÓGICA PARA EDITAR FASE ---
const editandoConfig = ref(!props.turno.config_activa);
const isEditingExisting = ref(false);

const configForm = useForm({
    config_id: null,
    producto_id: null,
    referencia_nombre: '',
    color: props.turno.config_activa?.mezcla_materiales?.color || '',
    mezcla: props.turno.config_activa?.mezcla_materiales?.materiales || [{ materia_prima_id: '', porcentaje: '' }],
});

const prepararEdicionFase = (fase) => {
    isEditingExisting.value = true;
    configForm.config_id = fase.id;
    configForm.producto_id = fase.producto_id;
    configForm.referencia_nombre = fase.producto?.descripcion;
    configForm.color = fase.mezcla_materiales?.color || '';
    configForm.mezcla = fase.mezcla_materiales?.materiales || [{ materia_prima_id: '', porcentaje: '' }];
    editandoConfig.value = true;
};

const prepararNuevoMolde = () => {
    isEditingExisting.value = false;
    configForm.reset();
    configForm.config_id = null;
    configForm.mezcla = [{ materia_prima_id: '', porcentaje: '' }];
    editandoConfig.value = true;
};

const agregarFilaMaterial = () => configForm.mezcla.push({ materia_prima_id: '', porcentaje: '' });
const quitarFilaMaterial = (index) => configForm.mezcla.length > 1 && configForm.mezcla.splice(index, 1);

const guardarConfig = () => {
    if (totalMezcla.value !== 100) return alert("La mezcla debe sumar exactamente 100%");
    configForm.post(route('produccion.configurar', props.turno.id), {
        onSuccess: () => { 
            editandoConfig.value = false; 
            cargarDatosExistentes();
        }
    });
};

const abrirPnc = (index) => { 
    indexActivo.value = index; 
    registros.value[index].modificado = true;
    modalPnc.value = true; 
};
const agregarDefecto = () => { 
    registros.value[indexActivo.value].pnc.push({ anomalia_id: '', malas_kg: '', cont_kg: '', torta_kg: '', causa: '' }); 
};

const abrirParo = (index) => { 
    indexActivo.value = index; 
    registros.value[index].modificado = true; 
    modalParo.value = true; 
};
const agregarParo = () => { 
    registros.value[indexActivo.value].paros.push({ falla_id: '', categoria: '', hora_inicio: '', hora_fin: '', minutos: 0, motivo: '' }); 
};

const abrirInspeccion = (index) => { 
    const fila = registros.value[index];
    if (!fila.cav || fila.cav <= 0) {
        alert("⚠️ POR FAVOR, INDIQUE LA CANTIDAD EN 'CAV. REALES' ANTES DE INICIAR LA INSPECCIÓN DE PREFORMAS.");
        return;
    }
    indexActivo.value = index; 
    fila.modificado = true; 
    modalInspeccion.value = true; 
};

const asignarDefectoACavidad = (defectoId) => {
    const fila = registros.value[indexActivo.value];
    const index = fila.inspeccion.findIndex(i => i.cav === cavidadSeleccionada.value);
    if (defectoId === null) {
        if (index !== -1) fila.inspeccion.splice(index, 1);
    } else {
        if (index !== -1) {
            fila.inspeccion[index].defecto = defectoId;
        } else {
            fila.inspeccion.push({ cav: cavidadSeleccionada.value, defecto: defectoId });
        }
    }
    cavidadSeleccionada.value = null;
};

const finalizarInspeccion = () => {
    const index = indexActivo.value;
    registros.value[index].inspeccion_completada = true;
    modalInspeccion.value = false;
    syncHora(index); 
};

const actualizarCategoriaParo = (p) => {
    const seleccion = catalogoFallas.value.find(f => f.id === p.falla_id || f.codigo === p.falla_id);
    if (seleccion) p.categoria = seleccion.categoria;
};

// --- LÓGICA SYNC BLINDADA ---
const syncHora = (index) => {
    const fila = registros.value[index];
    if (!fila.inspeccion_completada) {
        alert("⚠️ DEBE REALIZAR LA INSPECCIÓN DE PREFORMAS ANTES DE GUARDAR.");
        abrirInspeccion(index);
        return;
    }
    
    const configIdDestino = fila.config_id || props.turno.config_activa?.id;
    if (!configIdDestino) return alert("Guarde la configuración primero");

    fila.procesando = true;
    router.post(route('produccion.guardarHora'), {
        id: fila.id, // Se manda el UUID
        config_id: configIdDestino, // Se envía la fase original
        hora: fila.hora,
        num_vale: fila.num_vale,
        unidades_buenas: fila.buenas,
        pnc: { 
            cavidades_reales: fila.cav, ciclo_real: fila.ciclo, 
            defectos: fila.pnc, paros: fila.paros, 
            inspeccion: fila.inspeccion, inspeccion_completada: true 
        }
    }, {
        onSuccess: () => { 
            fila.procesando = false; 
            fila.guardado = true;
            fila.modificado = false; 
            fila.config_id = configIdDestino; // Confirmar anclaje de fase local
            
            // Refrescar nombre local en caso de que sea nuevo
            const faseAplicada = props.turno.configuraciones?.find(c => c.id === configIdDestino) || props.turno.config_activa;
            if (faseAplicada) fila.referencia_nombre = faseAplicada.producto?.descripcion;
            
            cargarDatosExistentes(); 
        },
        onError: () => fila.procesando = false
    });
};

const totalMezcla = computed(() => configForm.mezcla.reduce((acc, m) => acc + (parseFloat(m.porcentaje) || 0), 0));
const searchProd = ref('');
const productosFiltrados = computed(() => {
    if (searchProd.value.length < 2) return [];
    return props.productos.filter(p => p.descripcion.toLowerCase().includes(searchProd.value.toLowerCase())).slice(0, 5);
});
const seleccionarProducto = (p) => {
    configForm.producto_id = p.id;
    configForm.referencia_nombre = p.descripcion;
    searchProd.value = '';
};

const currentHourString = computed(() => `${String(new Date().getHours()).padStart(2, '0')}:00`);
const clonarVale = (i) => { 
    if (i < registros.value.length - 1) {
        registros.value[i+1].num_vale = registros.value[i].num_vale; 
        registros.value[i+1].modificado = true; 
    }
};
</script>

<template>
    <Head title="Consola Industrial" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-slate-900 text-white rounded-lg shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-800 uppercase leading-none">Monitor Inyección</h2>
                        <p class="text-[9px] font-bold text-indigo-600 uppercase mt-0.5">{{ turno.maquina.nombre }}</p>
                    </div>
                </div>

                <div class="bg-slate-900 text-white px-4 py-1.5 rounded-lg border border-slate-800 flex items-center gap-2 shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[9px] font-black uppercase tracking-widest">TURNO #{{ turno.numero_turno }} ({{ turno.duracion_turno }}H)</span>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto p-3 md:p-6 space-y-6 pb-40">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                
                <div class="bg-slate-900 p-4 md:p-5 rounded-2xl shadow-xl border border-slate-800 flex items-center justify-between group">
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">OEE Desempeño (Auditado)</p>
                        <p class="text-2xl md:text-3xl font-black font-mono text-white">{{ kpisCalculados.eficiencia }}%</p>
                    </div>
                    <div class="h-10 w-10 md:h-12 md:w-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    </div>
                </div>

                <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:border-emerald-500 transition-all">
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Unidades Buenas</p>
                        <p class="text-2xl md:text-3xl font-black font-mono text-emerald-500">{{ kpisCalculados.totalBuenas }}</p>
                    </div>
                    <div class="h-10 w-10 md:h-12 md:w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                </div>

                <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:border-orange-400 transition-all">
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Tiempo de Paro</p>
                        <p class="text-2xl md:text-3xl font-black font-mono text-orange-500">{{ kpisCalculados.paradas }}</p>
                    </div>
                    <div class="h-10 w-10 md:h-12 md:w-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>

                <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:border-red-400 transition-all">
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Desperdicio (Kg)</p>
                        <p class="text-2xl md:text-3xl font-black font-mono text-red-500">{{ kpisCalculados.scrap }}<span class="text-xs ml-1 font-sans font-bold">kg</span></p>
                    </div>
                    <div class="h-10 w-10 md:h-12 md:w-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </div>
                </div>

            </div>

            <div class="space-y-3">
                <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
                    <button v-for="(fase, idx) in props.turno.configuraciones" :key="fase.id" 
                        @click="seleccionarFase(fase)"
                        :class="['px-4 py-2 rounded-xl text-[10px] font-black uppercase border transition-all whitespace-nowrap', faseAMostrar?.id === fase.id ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-400 border-slate-100']">
                        Fase {{ idx + 1 }}
                    </button>
                </div>

                <div v-if="faseAMostrar && !editandoConfig" 
                    :class="['rounded-2xl px-4 md:px-6 py-4 flex flex-col md:flex-row items-center justify-between border transition-all duration-500 shadow-sm gap-4', esFaseActiva ? 'bg-slate-900 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-500']">
                    <div class="flex items-center gap-3 md:gap-5 truncate w-full md:w-auto">
                        <div :class="['p-2 rounded-xl shrink-0', esFaseActiva ? 'bg-white/5 text-emerald-400' : 'bg-white text-slate-300']"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg></div>
                        <div class="truncate">
                            <span class="text-[7px] md:text-[8px] font-black uppercase opacity-50 block mb-1">Referencia Actual</span>
                            <h4 class="text-[11px] md:text-sm font-black uppercase truncate">{{ faseAMostrar.producto?.descripcion }}</h4>
                            <p v-if="faseAMostrar.mezcla_materiales?.color" class="text-[9px] font-black text-indigo-400 uppercase">Color: {{ faseAMostrar.mezcla_materiales.color }}</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2 md:gap-3 w-full md:w-auto justify-end">
                        
                        <button @click="prepararEdicionFase(faseAMostrar)" 
                            :class="['text-[9px] font-bold underline underline-offset-2 transition-colors mr-2', esFaseActiva ? 'text-slate-400 hover:text-white' : 'text-slate-500 hover:text-slate-900']">
                            Corregir Ref.
                        </button>

                        <Link v-if="esFaseActiva" :href="route('produccion.crearPerfil', props.turno.id)" 
                            class="px-5 py-2.5 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white shadow-sm flex items-center gap-2 transition-all relative">
                            <span v-if="perfilesGuardadosCount > 0" 
                                  :class="['absolute -top-2 -right-2 flex h-5 w-5 rounded-full items-center justify-center text-[10px] text-white border-2 border-white shadow-sm', 
                                           perfilesGuardadosCount >= 3 ? 'bg-emerald-500' : 'bg-red-500']">
                                {{ perfilesGuardadosCount }}
                            </span>
                            Perfil Operación
                        </Link>

                        <button v-if="esFaseActiva" @click="prepararNuevoMolde" class="px-5 py-2.5 bg-white text-slate-900 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-emerald-400 shadow-sm flex items-center gap-2 transition-colors">
                            ⚙️ Cambiar Molde
                        </button>

                        <button v-if="esFaseActiva" @click="finalizarTurno" 
                            class="px-6 py-2.5 bg-red-600 text-white rounded-xl text-[9px] font-black uppercase shadow-lg shadow-red-500/30 hover:bg-red-700 hover:-translate-y-0.5 transition-all">
                            Finalizar Turno
                        </button>

                        <button v-else @click="faseSeleccionadaManual = null" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-[9px] font-black uppercase shadow-sm">Ver Actual</button>
                    </div>
                </div>
            </div>

            <transition name="expand">
                <div v-if="editandoConfig" class="bg-white rounded-[2rem] shadow-2xl border border-slate-100 overflow-hidden mb-6 relative">
                    <div class="flex justify-between items-center p-6 border-b border-slate-50">
                         <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                             {{ isEditingExisting ? 'Corregir Referencia de Fase' : 'Configuración de Producción' }}
                         </h3>
                         <button @click="editandoConfig = false" class="h-10 w-10 bg-slate-100 rounded-full flex items-center justify-center hover:bg-slate-200 text-slate-500 font-bold">✕</button>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-12">
                        <div class="lg:col-span-6 p-6 md:p-8 border-b lg:border-b-0 lg:border-r border-slate-50">
                            <h3 class="text-[10px] font-black text-slate-800 uppercase mb-4 tracking-widest">1. Identificar Producto</h3>
                            <div v-if="!configForm.producto_id" class="relative">
                                <input type="text" v-model="searchProd" class="w-full h-14 bg-slate-50 border-none rounded-xl font-black px-6 focus:ring-4 focus:ring-indigo-500/5 text-sm shadow-inner" placeholder="Buscar producto...">
                                <div v-if="productosFiltrados.length" class="absolute z-50 w-full bg-white shadow-2xl rounded-xl mt-2 border overflow-hidden">
                                    <div v-for="p in productosFiltrados" :key="p.id" @click="seleccionarProducto(p)" class="p-4 hover:bg-slate-50 cursor-pointer flex justify-between items-center group"><div class="font-black text-slate-800 text-xs uppercase">{{ p.descripcion }}</div><span class="text-[9px] font-black text-indigo-600">ELEGIR →</span></div>
                                </div>
                            </div>
                            <div v-else class="p-6 rounded-2xl bg-indigo-600 text-white flex justify-between items-center shadow-lg">
                                <div class="truncate mr-2"><span class="text-[8px] font-black uppercase opacity-60 block mb-1">Elegido</span><h4 class="text-sm font-black uppercase truncate">{{ configForm.referencia_nombre }}</h4></div>
                                <button @click="configForm.producto_id = null" class="h-8 w-8 bg-white/10 rounded-lg hover:bg-white/20 shrink-0">✕</button>
                            </div>

                            <div class="mt-6">
                                <h3 class="text-[10px] font-black text-slate-800 uppercase mb-3 tracking-widest">2. Especificar Color</h3>
                                <input v-model="configForm.color" type="text" class="w-full h-12 bg-slate-50 border-none rounded-xl font-black px-6 focus:ring-4 focus:ring-indigo-500/5 text-sm shadow-inner uppercase" placeholder="Ej: AZUL REY, CRISTAL...">
                            </div>
                        </div>
                        <div class="lg:col-span-6 p-6 md:p-8 bg-slate-50/50 pr-16"> 
                            <div class="flex justify-between items-center mb-4"><h3 class="text-[10px] font-black text-slate-800 uppercase tracking-widest">3. Mezcla (%)</h3><button @click="agregarFilaMaterial" class="text-[9px] font-black text-indigo-600 uppercase px-3 py-1.5 bg-white rounded-lg shadow-sm">+ Añadir</button></div>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                                <div v-for="(m, i) in configForm.mezcla" :key="i" class="flex gap-2 mb-2 animate-in slide-in-from-right-4">
                                    <select v-model="m.materia_prima_id" class="flex-1 h-10 bg-white border-none rounded-xl text-[10px] font-bold shadow-sm text-slate-700">
                                        <option value="" disabled>Seleccionar Material...</option>
                                        <option v-for="mat in materiales" :key="mat.id" :value="mat.id">{{ mat.nombre }}</option>
                                    </select>
                                    <div class="w-20 flex items-center bg-white rounded-xl px-2 shadow-sm"><input type="number" v-model="m.porcentaje" class="w-full border-none text-right font-black text-indigo-600 text-xs focus:ring-0"><span class="text-[8px] font-bold text-slate-300 ml-1">%</span></div><button v-if="configForm.mezcla.length > 1" @click="quitarFilaMaterial(i)" class="h-10 w-10 flex items-center justify-center text-slate-300 hover:text-red-500 transition-colors">✕</button>
                                </div>
                            </div>
                            <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-between">
                                <p class="text-xl font-black font-mono" :class="totalMezcla === 100 ? 'text-emerald-500' : 'text-red-500'">{{ totalMezcla }}%</p>
                                <button @click="guardarConfig" :disabled="!configForm.producto_id || totalMezcla !== 100" class="px-6 h-12 bg-emerald-500 text-white rounded-xl font-black text-[10px] uppercase shadow-lg disabled:opacity-20 transition-all">
                                    {{ isEditingExisting ? 'Actualizar Fase' : 'Guardar 🚀' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <div class="space-y-4">
                <div class="hidden lg:grid lg:grid-cols-12 px-10 mb-2 text-slate-400 font-black uppercase text-[9px] tracking-widest">
                    <div class="lg:col-span-2">Hora / Ref</div>
                    <div class="lg:col-span-3 text-center">Vale / Lote</div> <div class="lg:col-span-1 text-center font-mono">Ciclo</div>
                    <div class="lg:col-span-1 text-center">Buenas</div> <div class="lg:col-span-1 text-center">Cav</div>
                    <div class="lg:col-span-4 text-center">Acciones</div>
                </div>

                <div v-for="(reg, i) in registros" :key="i" 
                    :class="['relative group bg-white rounded-2xl md:rounded-3xl p-4 md:p-6 transition-all duration-300 border shadow-sm', reg.hora === currentHourString ? 'border-indigo-600 ring-4 ring-indigo-500/5 z-20' : 'border-slate-50 opacity-95']">
                    
                    <div class="flex flex-col lg:grid lg:grid-cols-12 items-stretch lg:items-center gap-4 md:gap-6">
                        <div class="lg:col-span-2 flex items-center justify-between lg:flex-col lg:justify-center gap-2 lg:border-r border-slate-100 lg:pr-6">
                            <span :class="reg.hora === currentHourString ? 'text-indigo-600' : 'text-slate-400'" class="text-2xl font-black font-mono tracking-tighter">{{ reg.hora }}</span>
                            <span :class="['text-[8px] font-black px-2 py-1 rounded-lg border truncate lg:w-full text-center max-w-[150px]', reg.guardado ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : 'bg-slate-50 border-transparent text-slate-300']">
                                {{ reg.referencia_nombre || 'Pendiente' }}
                            </span>
                        </div>

                        <div class="lg:col-span-3 flex flex-col">
                            <span class="lg:hidden text-[9px] font-black text-slate-400 uppercase mb-1 ml-1 tracking-widest">Vale / Lote</span>
                            <div class="flex items-center gap-2"><input type="text" v-model="reg.num_vale" @input="reg.modificado = true" class="w-full h-11 bg-slate-50 border-none rounded-xl text-center font-black text-slate-700 text-sm shadow-inner uppercase" placeholder="0000"><button @click="clonarVale(i)" class="p-2 text-slate-200 hover:text-indigo-600 shrink-0 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 13l-7 7-7-7" /></svg></button></div>
                        </div>

                        <div class="grid grid-cols-3 lg:contents gap-3">
                             <div class="lg:col-span-1 flex flex-col">
                                <span class="lg:hidden text-[9px] font-black text-slate-400 uppercase mb-1 text-center tracking-widest">Ciclo</span>
                                <input type="number" step="0.1" v-model="reg.ciclo" @input="calcularBuenasAutomatico(i); reg.modificado = true" class="w-full h-11 bg-amber-50/40 border-none rounded-xl text-center font-black text-amber-600 text-sm shadow-inner" placeholder="0.0">
                             </div>
                             <div class="lg:col-span-1 flex flex-col">
                                <span class="lg:hidden text-[9px] font-black text-slate-400 uppercase mb-1 text-center tracking-widest">Buenas</span>
                                <input type="number" v-model="reg.buenas" @input="reg.modificado = true" class="w-full h-11 bg-blue-50/50 border-none rounded-xl text-center font-black text-lg text-indigo-600 shadow-inner" placeholder="0">
                             </div>
                             <div class="lg:col-span-1 flex flex-col">
                                <span class="lg:hidden text-[9px] font-black text-slate-400 uppercase mb-1 text-center tracking-widest">Cav</span>
                                <input type="number" v-model="reg.cav" @input="calcularBuenasAutomatico(i); reg.modificado = true" class="w-full h-11 bg-slate-50 border-none rounded-xl text-center font-black text-lg text-slate-400 shadow-inner" placeholder="0">
                             </div>
                        </div>
                        
                        <div class="lg:col-span-4 flex gap-1 pt-2 lg:pt-0">
                            <button @click="abrirInspeccion(i)" 
                                :class="['flex-1 h-11 rounded-xl text-[9px] font-black uppercase transition-all flex items-center justify-center gap-1 border', 
                                reg.inspeccion_completada ? 'bg-emerald-100 text-emerald-600 border-emerald-200' : 'bg-red-50 text-red-500 border-red-100 animate-pulse']">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4" /></svg>
                                PREFORMAS
                            </button>

                            <button @click="abrirPnc(i)" :class="['flex-1 h-11 rounded-xl text-[9px] font-black uppercase transition-all flex items-center justify-center gap-1', reg.pnc.length > 0 ? 'bg-red-500 text-white shadow-md' : 'bg-slate-50 text-slate-400 border border-slate-100']">
                                PNC
                            </button>
                            <button @click="abrirParo(i)" :class="['flex-1 h-11 rounded-xl text-[9px] font-black uppercase transition-all flex items-center justify-center gap-1', reg.paros.length > 0 ? 'bg-orange-500 text-white shadow-md' : 'bg-slate-50 text-slate-400 border border-slate-100']">
                                PARO
                            </button>
                            <button @click="syncHora(i)" :disabled="reg.procesando" :class="['flex-1 h-11 rounded-xl font-black text-[9px] uppercase transition-all shadow-sm flex items-center justify-center gap-2', reg.guardado ? 'bg-emerald-500 text-white shadow-emerald-500/20' : 'bg-indigo-600 text-white']">
                                <span v-if="reg.procesando" class="animate-spin border-2 border-white/30 border-t-white h-4 w-4 rounded-full"></span>
                                <template v-else>{{ reg.guardado ? 'SYNC ✓' : 'SYNC' }}</template>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <transition name="fade">
            <div v-if="modalPnc" class="fixed inset-0 z-[100] flex items-center justify-center p-2 sm:p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white w-full max-w-xl rounded-2xl md:rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95">
                    <div class="p-5 md:p-6">
                        <div class="flex justify-between items-start mb-4 border-b border-slate-50 pb-3">
                            <div><h3 class="text-lg font-black text-slate-800 uppercase leading-none">Reporte PNC</h3><p class="text-[9px] font-bold text-red-500 uppercase mt-1">Hora: {{ registros[indexActivo].hora }}</p></div>
                            <button @click="modalPnc = false" class="h-8 w-8 bg-slate-100 rounded-full flex items-center justify-center hover:bg-slate-200">✕</button>
                        </div>
                        <div class="space-y-3 max-h-[55vh] overflow-y-auto pr-1 custom-scrollbar">
                            <div v-for="(item, idx) in registros[indexActivo].pnc" :key="idx" class="p-3 md:p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                    <div class="sm:col-span-2"><span class="text-[7px] font-black text-slate-400 uppercase">Defecto</span><select v-model="item.anomalia_id" class="w-full h-9 bg-white border-none rounded-lg text-[10px] font-bold shadow-sm text-slate-700"><option value="" disabled>Seleccionar...</option><option v-for="a in pncCatalogo" :key="a.id" :value="a.id">{{ a.nombre }}</option></select></div>
                                    <div class="grid grid-cols-3 sm:col-span-2 gap-2">
                                        <div><span class="text-[7px] font-black text-slate-400 uppercase">MALAS (K)</span><input type="number" v-model="item.malas_kg" class="w-full h-9 bg-white border-none rounded-lg text-center font-black text-red-600 text-[10px] shadow-inner"></div>
                                        <div><span class="text-[7px] font-black text-slate-400 uppercase">CONT (K)</span><input type="number" v-model="item.cont_kg" class="w-full h-9 bg-white border-none rounded-lg text-center font-black text-amber-600 text-[10px] shadow-inner"></div>
                                        <div><span class="text-[7px] font-black text-slate-400 uppercase">TORTA (K)</span><input type="number" v-model="item.torta_kg" class="w-full h-9 bg-white border-none rounded-lg text-center font-black text-slate-700 text-[10px] shadow-inner"></div>
                                    </div>
                                    <div class="sm:col-span-2"><span class="text-[7px] font-black text-slate-400 uppercase">Causa</span><input type="text" v-model="item.causa" class="w-full h-9 bg-white border-none rounded-lg text-[10px] font-bold" placeholder="..."></div>
                                </div>
                                <button @click="registros[indexActivo].pnc.splice(idx, 1)" class="w-full text-[8px] font-black text-red-400 uppercase text-right hover:text-red-600 transition-colors">✕ Eliminar</button>
                            </div>
                        </div>
                        <button @click="agregarDefecto" class="w-full mt-4 py-3 border-2 border-dashed border-slate-200 rounded-xl text-[9px] font-black text-slate-400 uppercase hover:bg-slate-50 transition-all">+ Añadir Registro</button>
                    </div>
                    <div class="bg-slate-50 p-4 flex justify-between items-center"><div class="text-[10px] font-black text-red-600 uppercase">Total: {{ (registros[indexActivo].pnc.reduce((acc, i) => acc + (parseFloat(i.malas_kg) || 0) + (parseFloat(i.cont_kg) || 0) + (parseFloat(i.torta_kg) || 0), 0)).toFixed(2) }} Kg</div><button @click="modalPnc = false" class="px-8 py-2 bg-red-600 text-white rounded-xl text-[9px] font-black uppercase shadow-lg">Finalizar</button></div>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div v-if="modalParo" class="fixed inset-0 z-[100] flex items-center justify-center p-2 sm:p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white w-full max-w-lg rounded-2xl md:rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95">
                    <div class="p-5 md:p-6">
                        <div class="flex justify-between items-center mb-4 border-b border-slate-50 pb-3">
                            <div><h3 class="text-lg font-black text-slate-800 uppercase leading-none">Tiempo Perdido</h3><p class="text-[9px] font-bold text-orange-500 uppercase mt-1">Hora: {{ registros[indexActivo].hora }}</p></div>
                            <button @click="modalParo = false" class="h-8 w-8 bg-slate-100 rounded-full flex items-center justify-center hover:bg-slate-200 transition-colors">✕</button>
                        </div>
                        <div class="space-y-3 max-h-[50vh] overflow-y-auto pr-1 custom-scrollbar">
                            <div v-for="(p, idx) in registros[indexActivo].paros" :key="idx" class="p-3 md:p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 mb-2">
                                    <div class="sm:col-span-12"><span class="text-[7px] font-black text-slate-400 uppercase tracking-widest">FALLA</span><select v-model="p.falla_id" @change="actualizarCategoriaParo(p)" class="w-full h-9 bg-white border-none rounded-lg text-[10px] font-black shadow-sm text-slate-700"><option value="" disabled>Seleccionar falla...</option><option v-for="f in paradasCatalogo" :key="f.id" :value="f.id">{{ f.codigo }} - {{ f.falla }}</option></select></div>
                                    <div class="grid grid-cols-3 sm:col-span-12 gap-2">
                                        <div><span class="text-[7px] font-black text-slate-400 uppercase">INICIO</span><input type="time" v-model="p.hora_inicio" class="w-full h-9 bg-white border-none rounded-lg text-center font-black text-[10px]"></div>
                                        <div><span class="text-[7px] font-black text-slate-400 uppercase">FIN</span><input type="time" v-model="p.hora_fin" class="w-full h-10 bg-white border-none rounded-lg text-center font-black text-[10px]"></div>
                                        <div><span class="text-[7px] font-black text-slate-400 uppercase">MIN</span><div class="w-full h-9 bg-orange-100 rounded-lg flex items-center justify-center font-black text-orange-600 text-xs">{{ p.minutos = calcularDiferenciaMinutos(p.hora_inicio, p.hora_fin) }}'</div></div>
                                    </div>
                                    <div class="sm:col-span-12"><span class="text-[7px] font-black text-slate-400 uppercase">DETALLE</span><input type="text" v-model="p.motivo" class="w-full h-9 bg-white border-none rounded-lg text-[10px] font-bold" placeholder="..."></div>
                                </div>
                                <button @click="registros[indexActivo].paros.splice(idx, 1)" class="w-full text-[8px] font-black text-red-400 uppercase text-right">✕ Eliminar</button>
                            </div>
                        </div>
                        <button @click="agregarParo" class="w-full mt-4 py-3 border-2 border-dashed border-slate-200 rounded-xl text-[9px] font-black text-slate-400 uppercase hover:bg-slate-50 transition-all">+ Registrar Nuevo Paro</button>
                    </div>
                    <div class="bg-slate-50 p-4 flex justify-between items-center"><div class="text-[10px] font-black text-orange-600 uppercase">Total: {{ registros[indexActivo].paros.reduce((acc, p) => acc + (parseInt(p.minutos) || 0), 0) }} min</div><button @click="modalParo = false" class="px-8 py-2 bg-orange-500 text-white rounded-xl text-[9px] font-black uppercase shadow-lg">Finalizar</button></div>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div v-if="modalInspeccion" class="fixed inset-0 z-[130] flex items-center justify-center p-2 bg-slate-900/80 backdrop-blur-md">
                <div class="bg-white w-full max-w-lg rounded-[2.5rem] overflow-hidden shadow-2xl flex flex-col max-h-[95vh] animate-in zoom-in-95">
                    <div class="p-6 border-b flex justify-between items-center bg-slate-50">
                        <div><h3 class="text-sm font-black text-slate-800 uppercase leading-none">Inspección de Preformas</h3><p class="text-[9px] font-bold text-indigo-600 uppercase mt-1">Hora: {{ registros[indexActivo].hora }} | Cavidades Reportadas: {{ registros[indexActivo].cav }}</p></div>
                        <button @click="modalInspeccion = false" class="h-8 w-8 bg-white rounded-full shadow-sm text-slate-400 flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition-all">✕</button>
                    </div>
                    <div class="p-4 flex-grow overflow-y-auto custom-scrollbar">
                        <div class="grid grid-cols-6 gap-2 mb-6">
                            <button v-for="n in parseInt(registros[indexActivo].cav)" :key="n" @click="cavidadSeleccionada = n"
                                :class="['h-12 rounded-xl text-[10px] font-black border-2 transition-all flex flex-col items-center justify-center', cavidadSeleccionada === n ? 'border-indigo-600 bg-blue-50 text-indigo-600 scale-110 z-10' : (registros[indexActivo].inspeccion.find(i => i.cav === n) ? 'border-red-500 bg-red-50 text-red-600' : 'border-emerald-500 bg-emerald-50 text-emerald-600')]">
                                <span class="opacity-40 text-[7px]">CAV</span>{{ n }}
                                <span v-if="registros[indexActivo].inspeccion.find(i => i.cav === n)" class="text-[8px] mt-0.5">Def: {{ pncCatalogo.find(p => p.id === registros[indexActivo].inspeccion.find(i => i.cav === n).defecto)?.nombre.substring(0,5) || '...' }}</span>
                                <span v-else class="text-[8px] mt-0.5 opacity-40">OK</span>
                            </button>
                        </div>
                        <div v-if="cavidadSeleccionada" class="bg-slate-900 p-6 rounded-[2rem] shadow-2xl sticky bottom-0">
                            <div class="flex justify-between items-center mb-4"><span class="text-white font-black text-[10px] uppercase">Defecto en Cavidad {{ cavidadSeleccionada }}</span><button @click="cavidadSeleccionada = null" class="text-white/50 text-[10px] font-bold uppercase underline">Cancelar</button></div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto custom-scrollbar pr-2">
                                <button v-for="d in pncCatalogo" :key="d.id" @click="asignarDefectoACavidad(d.id)" class="bg-white/10 hover:bg-white/20 text-white p-2 rounded-lg text-[9px] font-bold border border-white/5 transition-all text-center leading-tight">
                                    {{ d.nombre }}
                                </button>
                                <button @click="asignarDefectoACavidad(null)" class="col-span-full mt-2 py-2.5 bg-red-600/30 text-red-300 rounded-xl text-[9px] font-black uppercase border border-red-500/20">Limpiar Defecto</button>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 bg-slate-50 border-t flex gap-3">
                        <button v-if="registros[indexActivo].inspeccion.length === 0" @click="finalizarInspeccion" class="flex-1 py-4 bg-emerald-500 text-white rounded-2xl font-black text-xs uppercase shadow-xl hover:bg-emerald-600 transition-all">TODAS LAS CAVIDADES OK √</button>
                        <button v-else @click="finalizarInspeccion" class="flex-1 py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase shadow-xl">CONFIRMAR DEFECTOS ({{ registros[indexActivo].inspeccion.length }})</button>
                    </div>
                </div>
            </div>
        </transition>

    </AuthenticatedLayout>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }
.custom-scrollbar::-webkit-scrollbar { height: 2px; width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.expand-enter-active, .expand-leave-active { transition: all 0.4s ease-in-out; max-height: 1000px; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.animate-in { animation: zoom-in 0.3s ease-out forwards; }
@keyframes zoom-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>