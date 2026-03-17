<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Ingreso al Sistema" />

    <div class="min-h-screen bg-gradient-to-br from-[#081b33] via-[#0a203f] to-[#040e1a] flex flex-col justify-center items-center p-4 sm:p-8 relative overflow-hidden font-sans">
        
        <div class="absolute top-[-10%] left-[-10%] w-[50vw] h-[50vw] max-w-[500px] max-h-[500px] bg-pet-green/10 blur-[100px] rounded-full pointer-events-none animate-pulse-slow"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40vw] h-[40vw] max-w-[400px] max-h-[400px] bg-blue-500/10 blur-[80px] rounded-full pointer-events-none"></div>

        <div class="w-full max-w-md bg-[#081b33]/40 backdrop-blur-xl border border-white/10 rounded-[2rem] shadow-[0_30px_60px_-15px_rgba(0,0,0,0.8)] overflow-hidden z-10 transition-all duration-300 hover:border-white/20">
            
            <div class="pt-10 pb-6 px-6 sm:px-10 text-center flex flex-col items-center relative">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-1/2 h-px bg-gradient-to-r from-transparent via-pet-green/50 to-transparent"></div>

                <div class="w-20 h-20 bg-white/5 backdrop-blur-md rounded-[1.5rem] flex items-center justify-center p-3 border border-white/10 shadow-2xl transform transition-transform duration-500 hover:scale-105 hover:-rotate-3 mb-6 relative group">
                    <div class="absolute inset-0 bg-pet-green/20 rounded-[1.5rem] blur-md opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <img src="/images/logo-pet.png" alt="Logo" class="h-full object-contain relative z-10 drop-shadow-md">
                </div>

                <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white to-blue-100 tracking-tight uppercase leading-none">
                    Pet Caribe
                </h1>
                <div class="flex items-center gap-2 mt-3">
                    <span class="w-1.5 h-1.5 bg-pet-green rounded-full animate-pulse"></span>
                    <p class="text-[9px] font-black text-pet-green tracking-[0.4em] uppercase opacity-90">
                        Manufacturing System
                    </p>
                </div>
            </div>

            <div class="px-6 sm:px-10 pb-10">
                
                <transition name="fade">
                    <div v-if="status" class="mb-6 p-4 bg-pet-green/10 border border-pet-green/20 rounded-2xl flex items-center gap-3">
                        <svg class="w-5 h-5 text-pet-green shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-xs text-pet-green/90 font-bold tracking-wide">{{ status }}</p>
                    </div>
                </transition>

                <form @submit.prevent="submit" class="space-y-6">
                    
                    <div class="space-y-1.5">
                        <InputLabel for="login" value="ID de Operador o Correo" class="text-[10px] text-blue-100/60 font-black uppercase tracking-widest ml-1" />
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-white/30 group-focus-within:text-pet-green transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <TextInput
                                id="login"
                                type="text"
                                class="block w-full h-14 bg-black/20 border-white/10 text-white pl-11 pr-4 focus:ring-2 focus:ring-pet-green/50 focus:border-pet-green rounded-2xl transition-all placeholder:text-white/20 font-medium text-sm"
                                v-model="form.login"
                                placeholder="Ingrese su credencial..."
                                required
                                autofocus
                            />
                        </div>
                        <InputError class="mt-1 ml-1" :message="form.errors.login" />
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center ml-1 pr-1">
                            <InputLabel for="password" value="Código de Acceso" class="text-[10px] text-blue-100/60 font-black uppercase tracking-widest" />
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-[9px] font-bold text-pet-green hover:text-white transition-colors uppercase tracking-widest underline decoration-pet-green/30 underline-offset-4"
                            >
                                Recuperar Acceso
                            </Link>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-white/30 group-focus-within:text-pet-green transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            </div>
                            <TextInput
                                id="password"
                                type="password"
                                class="block w-full h-14 bg-black/20 border-white/10 text-white pl-11 pr-4 focus:ring-2 focus:ring-pet-green/50 focus:border-pet-green rounded-2xl transition-all placeholder:text-white/20 tracking-widest text-lg"
                                v-model="form.password"
                                placeholder="••••••••"
                                required
                            />
                        </div>
                        <InputError class="mt-1 ml-1" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center ml-1 mt-2">
                        <label class="flex items-center cursor-pointer group">
                            <Checkbox 
                                name="remember" 
                                v-model:checked="form.remember" 
                                class="rounded-[0.35rem] border-white/20 bg-black/20 text-pet-green focus:ring-pet-green/50 w-4 h-4 transition-all"
                            />
                            <span class="ms-3 text-[11px] font-bold text-blue-100/50 group-hover:text-blue-100 transition-colors">Mantener sesión activa</span>
                        </label>
                    </div>

                    <div class="pt-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="relative w-full group overflow-hidden rounded-2xl flex justify-center items-center h-14 bg-gradient-to-r from-pet-green to-emerald-500 text-[#040e1a] font-black text-xs uppercase tracking-[0.2em] transition-all transform active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed shadow-[0_0_20px_rgba(139,197,63,0.3)] hover:shadow-[0_0_30px_rgba(139,197,63,0.5)]"
                        >
                            <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/40 to-transparent group-hover:animate-shimmer z-0"></div>
                            
                            <span class="relative z-10 flex items-center gap-2">
                                <template v-if="!form.processing">
                                    Iniciar Sesión
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                </template>
                                <template v-else>
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-[#081b33]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Validando Accesos...
                                </template>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="p-5 bg-black/30 text-center border-t border-white/5 backdrop-blur-md">
                <p class="text-[8px] sm:text-[9px] text-blue-200/30 uppercase tracking-[0.3em] font-bold">
                    Control de Piso • Licencia Autorizada
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Elimina el background blanco feo del autocompletado en navegadores webkit */
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active{
    -webkit-box-shadow: 0 0 0 30px #0c203a inset !important;
    -webkit-text-fill-color: white !important;
    transition: background-color 5000s ease-in-out 0s;
}

@keyframes shimmer {
    100% {
        transform: translateX(100%);
    }
}
.animate-shimmer {
    animation: shimmer 1.5s infinite;
}

.animate-pulse-slow {
    animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Transición suave para notificaciones */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-10px); }
</style>