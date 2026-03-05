<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue'; 

const props = defineProps({ 
    users: Array, 
    roles: Array, 
    permissions: Array 
});

const activeTab = ref('usuarios');
const search = ref('');

// --- FILTRO MEJORADO ---
const usuariosFiltrados = computed(() => {
    return props.users.filter(u => {
        const busqueda = search.value.toLowerCase();
        const tieneRol = u.roles && u.roles.length > 0 ? u.roles[0].name.toLowerCase().includes(busqueda) : false;
        
        return u.nombre.toLowerCase().includes(busqueda) ||
               u.codigo_empleado.toLowerCase().includes(busqueda) ||
               tieneRol;
    });
});

// --- LÓGICA USUARIOS ---
const userModal = ref(false);
const editModeUser = ref(false);

const userForm = useForm({ 
    id: null, 
    nombre: '', 
    codigo_empleado: '',
    email: '', 
    password: '', 
    password_confirmation: '', 
    rol: '',
    activo: true
});

// --- MEJORA: AUTO-GENERAR CORREO POR CÓDIGO DE EMPLEADO ---
watch(() => userForm.codigo_empleado, (newCodigo) => {
    // Solo generar automáticamente si estamos CREANDO un usuario nuevo
    if (!editModeUser.value) {
        if (newCodigo && newCodigo.length > 0) {
            // Limpiamos el código (minúsculas y sin espacios)
            const cleanCode = newCodigo
                .toLowerCase()
                .trim()
                .replace(/\s+/g, ''); 
            
            userForm.email = `${cleanCode}@petcaribe.com`;
        } else {
            userForm.email = '';
        }
    }
});

const openUserModal = (user = null) => {
    userForm.clearErrors();
    if (user) {
        editModeUser.value = true;
        userForm.id = user.id;
        userForm.nombre = user.nombre;
        userForm.codigo_empleado = user.codigo_empleado;
        userForm.email = user.email;
        userForm.rol = user.roles && user.roles.length > 0 ? user.roles[0].name : '';
        userForm.activo = Boolean(user.activo);
        userForm.password = '';
        userForm.password_confirmation = '';
    } else { 
        editModeUser.value = false;
        userForm.reset(); 
        userForm.id = null;
        userForm.activo = true;
    }
    userModal.value = true;
};

const submitUser = () => {
    const action = editModeUser.value ? 'put' : 'post';
    const url = editModeUser.value ? route('settings.usuarios.update', userForm.id) : route('settings.usuarios.store');
    
    userForm[action](url, { 
        onSuccess: () => {
            userModal.value = false;
            userForm.reset();
        }
    });
};

const deleteUser = (user) => {
    if (confirm(`¿Estás seguro de eliminar a ${user.nombre}?`)) {
        router.delete(route('settings.usuarios.destroy', user.id));
    }
};

// --- LÓGICA ROLES ---
const roleModal = ref(false);
const roleForm = useForm({ id: null, name: '', permissions: [] });

const openRoleModal = (role = null) => {
    roleForm.clearErrors();
    if (role) {
        roleForm.id = role.id;
        roleForm.name = role.name;
        roleForm.permissions = role.permissions.map(p => p.name);
    } else { 
        roleForm.reset(); 
        roleForm.id = null;
    }
    roleModal.value = true;
};

const submitRole = () => {
    roleForm.post(route('settings.roles.save'), { 
        onSuccess: () => roleModal.value = false 
    });
};

const deleteRole = (role) => {
    if (confirm(`¿Eliminar rol "${role.name}"? Los usuarios con este perfil perderán sus permisos.`)) {
        router.delete(route('settings.roles.destroy', role.id));
    }
};
</script>

<template>
    <Head title="Seguridad" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-slate-900 text-white rounded-2xl shadow-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 uppercase tracking-tighter italic">Centro de Control de Acceso</h2>
                </div>
                <div class="flex bg-slate-200/50 p-1 rounded-xl border border-slate-200 shadow-inner group">
                    <button @click="activeTab = 'usuarios'" :class="activeTab === 'usuarios' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500'" class="px-5 py-2 rounded-lg font-black text-[10px] uppercase transition-all duration-200">Personal</button>
                    <button @click="activeTab = 'roles'" :class="activeTab === 'roles' ? 'bg-white shadow-sm text-purple-600' : 'text-slate-500'" class="px-5 py-2 rounded-lg font-black text-[10px] uppercase transition-all duration-200">Perfiles</button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto p-4 md:p-8 space-y-6">
            
            <div class="flex flex-col md:flex-row gap-4 justify-between items-stretch md:items-center">
                <div v-if="activeTab === 'usuarios'" class="relative flex-grow max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input v-model="search" type="text" placeholder="BUSCAR POR NOMBRE, ID O ROL..." 
                        class="w-full pl-11 pr-4 py-3 bg-white border-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-widest focus:ring-4 focus:ring-blue-500/5 transition-all shadow-sm">
                </div>

                <button @click="activeTab === 'usuarios' ? openUserModal() : openRoleModal()" 
                    class="px-8 py-3.5 bg-slate-900 hover:bg-blue-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-slate-200 transition-all flex items-center justify-center gap-3 active:scale-95">
                    <span class="text-lg leading-none">+</span> NUEVO {{ activeTab === 'usuarios' ? 'COLABORADOR' : 'PERFIL' }}
                </button>
            </div>

            <div v-if="activeTab === 'usuarios'" class="animate-in fade-in duration-500">
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50 border-b border-slate-100">
                                <th class="p-6 pl-8">Colaborador</th>
                                <th class="p-6">Código ID</th>
                                <th class="p-6">Rol / Perfil</th>
                                <th class="p-6 text-center">Estado</th>
                                <th class="p-6 text-right pr-8">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="u in usuariosFiltrados" :key="u.id" class="group hover:bg-blue-50/20 transition-colors">
                                <td class="p-5 pl-8">
                                    <div class="flex items-center gap-4">
                                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center font-black text-slate-600 text-[11px] border border-slate-200 shadow-inner uppercase">
                                            {{ (u.nombre || '??').substring(0, 2) }}
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 uppercase text-xs leading-none mb-1.5">{{ u.nombre }}</p>
                                            <p class="text-[9px] text-slate-400 font-bold lowercase tracking-wider">{{ u.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-5">
                                    <span class="font-mono font-black text-slate-500 text-[10px] bg-slate-100 px-2.5 py-1.5 rounded-lg border border-slate-200 tracking-tighter">{{ u.codigo_empleado }}</span>
                                </td>
                                <td class="p-5">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-if="u.roles && u.roles.length > 0" v-for="r in u.roles" :key="r.id" 
                                            class="px-2.5 py-1 bg-purple-100 text-purple-700 rounded-lg text-[9px] font-black uppercase tracking-tighter border border-purple-200 shadow-sm">
                                            {{ r.name }}
                                        </span>
                                        <span v-else class="text-[9px] font-black text-slate-300 uppercase italic">Sin Rol</span>
                                    </div>
                                </td>
                                <td class="p-5 text-center">
                                    <span :class="u.activo ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-slate-400 bg-slate-50 border-slate-200'" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[9px] font-black uppercase tracking-widest">
                                        <span :class="u.activo ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'" class="w-1.5 h-1.5 rounded-full"></span>
                                        {{ u.activo ? 'Habilitado' : 'Suspendido' }}
                                    </span>
                                </td>
                                <td class="p-5 text-right pr-8 space-x-1">
                                    <div class="flex justify-end gap-2 sm:opacity-0 group-hover:opacity-100 transition-all transform group-hover:scale-100 scale-95">
                                        <button @click="openUserModal(u)" class="h-9 w-9 bg-white border border-slate-100 rounded-xl text-blue-600 hover:shadow-md transition-all flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></button>
                                        <button @click="deleteUser(u)" class="h-9 w-9 bg-white border border-slate-100 rounded-xl text-slate-300 hover:text-red-500 hover:shadow-md transition-all flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="animate-in fade-in duration-500">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="role in roles" :key="role.id" class="bg-white p-7 rounded-[2.5rem] border border-slate-200 shadow-sm hover:shadow-xl transition-all group flex flex-col justify-between relative overflow-hidden">
                        <div class="relative z-10">
                            <div class="flex justify-between items-start mb-6">
                                <span class="bg-purple-600 text-white px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md shadow-purple-100 border border-purple-500">{{ role.name }}</span>
                                <div class="flex gap-1.5">
                                    <button @click="openRoleModal(role)" class="h-9 w-9 bg-slate-50 text-slate-400 hover:text-purple-600 hover:bg-purple-50 rounded-xl transition-all flex items-center justify-center border border-slate-100">⚙️</button>
                                    <button @click="deleteRole(role)" class="h-9 w-9 bg-slate-50 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all flex items-center justify-center border border-slate-100">✕</button>
                                </div>
                            </div>
                            <p class="text-[9px] font-black text-slate-400 uppercase mb-4 tracking-[0.2em] px-1">Permisos habilitados:</p>
                            <div class="flex flex-wrap gap-1.5 max-h-40 overflow-y-auto custom-scrollbar pr-1">
                                <span v-for="p in role.permissions" :key="p.id" class="px-2 py-1 bg-slate-50 text-slate-500 rounded-lg text-[8px] font-black uppercase border border-slate-100 tracking-tighter">{{ p.name.replace('.', ' ') }}</span>
                            </div>
                        </div>
                    </div>
                    <button @click="openRoleModal()" class="border-2 border-dashed border-slate-200 rounded-[2.5rem] p-10 text-center hover:border-purple-400 hover:bg-purple-50 transition-all group flex flex-col items-center justify-center min-h-[220px]">
                        <div class="w-14 h-14 bg-slate-100 rounded-3xl flex items-center justify-center mb-4 group-hover:rotate-12 transition-transform shadow-inner">
                            <span class="text-3xl opacity-50 group-hover:opacity-100">🛡️</span>
                        </div>
                        <span class="font-black text-slate-400 group-hover:text-purple-600 text-[10px] uppercase tracking-[0.2em]">Nuevo Perfil</span>
                    </button>
                </div>
            </div>
        </div>

        <div v-if="userModal" class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/40 backdrop-blur-md p-4 overflow-y-auto">
            <div class="bg-white w-full max-w-lg rounded-[3rem] shadow-2xl animate-in zoom-in-95 duration-200">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 uppercase italic tracking-tight">{{ editModeUser ? 'Modificar' : 'Registrar' }} Ficha</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Control de acceso planta</p>
                    </div>
                    <button @click="userModal = false" class="h-10 w-10 bg-white border border-slate-100 text-slate-300 hover:text-red-500 rounded-full flex items-center justify-center transition-all shadow-sm">✕</button>
                </div>

                <form @submit.prevent="submitUser" class="p-8 grid grid-cols-2 gap-5">
                    <div class="col-span-2">
                        <label class="label-form">Nombre del Usuario</label>
                        <input v-model="userForm.nombre" type="text" class="input-form uppercase shadow-inner" placeholder="PEDRO PEREZ">
                        <div v-if="userForm.errors.nombre" class="error-msg">{{ userForm.errors.nombre }}</div>
                    </div>
                    
                    <div>
                        <label class="label-form">Cédula Empleado</label>
                        <input v-model="userForm.codigo_empleado" type="text" class="input-form font-mono shadow-inner uppercase" placeholder="ID-000">
                        <div v-if="userForm.errors.codigo_empleado" class="error-msg">{{ userForm.errors.codigo_empleado }}</div>
                    </div>

                    <div>
                        <label class="label-form">Perfil de Sistema</label>
                        <select v-model="userForm.rol" class="input-form appearance-none cursor-pointer shadow-inner">
                            <option value="" disabled>ELEGIR...</option>
                            <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                        </select>
                        <div v-if="userForm.errors.rol" class="error-msg">{{ userForm.errors.rol }}</div>
                    </div>

                    <div class="col-span-2">
                        <label class="label-form">Email Institucional</label>
                        <input v-model="userForm.email" type="email" class="input-form shadow-inner lowercase" placeholder="usuario@empresa.com">
                        <div v-if="userForm.errors.email" class="error-msg">{{ userForm.errors.email }}</div>
                    </div>

                    <div>
                        <label class="label-form">Contraseña</label>
                        <input v-model="userForm.password" type="password" class="input-form shadow-inner" placeholder="••••">
                        <div v-if="userForm.errors.password" class="error-msg">{{ userForm.errors.password }}</div>
                    </div>
                    <div>
                        <label class="label-form">Reconfirmar</label>
                        <input v-model="userForm.password_confirmation" type="password" class="input-form shadow-inner" placeholder="••••">
                    </div>

                    <div class="col-span-2 bg-slate-50 p-5 rounded-2xl border border-slate-100 flex items-center justify-between mt-2">
                        <div>
                            <p class="text-[10px] font-black text-slate-700 uppercase tracking-widest">Estatus de Acceso</p>
                            <p class="text-[8px] font-bold text-slate-400 uppercase">Habilitar o restringir entrada</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="userForm.activo" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-blue-600 transition-all after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 peer-checked:after:translate-x-full shadow-inner"></div>
                        </label>
                    </div>

                    <div class="col-span-2 flex gap-3 mt-6">
                        <button type="button" @click="userModal = false" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-2xl font-black text-xs uppercase hover:bg-slate-200">Descartar</button>
                        <button type="submit" :disabled="userForm.processing" class="flex-[2] py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase shadow-xl hover:bg-blue-600 transition-all flex items-center justify-center gap-3">
                            <span v-if="userForm.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            {{ editModeUser ? 'Actualizar Datos' : 'Confirmar Registro 🚀' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="roleModal" class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/40 backdrop-blur-md p-4 overflow-y-auto">
            <div class="bg-white w-full max-w-2xl rounded-[3rem] shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-in zoom-in-95 duration-300">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div><h3 class="text-xl font-black text-slate-900 uppercase">Matriz de Facultades</h3><p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5 tracking-widest italic">Nivel de autoridad</p></div>
                    <button @click="roleModal = false" class="h-10 w-10 bg-white border border-slate-100 text-slate-300 hover:text-red-500 rounded-full flex items-center justify-center transition-all shadow-sm">✕</button>
                </div>
                <div class="p-8 pb-0">
                    <label class="label-form">Nombre del Perfil</label>
                    <input v-model="roleForm.name" type="text" class="input-form uppercase shadow-inner" placeholder="Ej: SUPERVISOR">
                </div>
                <div class="flex-grow overflow-y-auto p-8 custom-scrollbar">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label v-for="p in permissions" :key="p.id" class="flex items-center gap-4 p-4 rounded-2xl cursor-pointer hover:bg-white border group">
                            <input type="checkbox" :value="p.name" v-model="roleForm.permissions" class="w-5 h-5 rounded border-slate-300 text-purple-600 focus:ring-purple-500 shadow-sm cursor-pointer">
                            <span class="text-[10px] font-black text-slate-600 uppercase group-hover:text-purple-700 tracking-tighter">{{ p.name.replace('.', ' ') }}</span>
                        </label>
                    </div>
                </div>
                <div class="p-8 border-t border-slate-50 flex gap-4 bg-slate-50/30">
                    <button @click="roleModal = false" class="flex-1 py-4 bg-slate-100 text-slate-400 rounded-2xl font-black text-xs uppercase hover:bg-slate-200">Cancelar</button>
                    <button @click="submitRole" :disabled="roleForm.processing" class="flex-[2] py-4 bg-purple-600 text-white rounded-2xl font-black text-xs uppercase shadow-xl shadow-purple-100 hover:bg-purple-700 transition-all">Guardar Configuración</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.label-form { @apply text-[9px] font-black text-slate-400 uppercase mb-2 block tracking-[0.2em]; }
.input-form { @apply w-full h-12 bg-slate-50 border-slate-100 border-2 rounded-2xl font-black px-5 text-xs focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all outline-none placeholder:text-slate-300; }
.error-msg { @apply text-red-500 text-[10px] font-black mt-1.5 uppercase italic; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.animate-in { animation: zoom-in 0.3s ease-out forwards; }
@keyframes zoom-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>