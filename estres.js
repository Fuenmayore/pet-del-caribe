import http from 'k6/http';
import { sleep, check } from 'k6';

export const options = {
    vus: 50, // Usuarios simultáneos
    duration: '30s', // Duración de la ráfaga
};

export default function () {
    const url = 'http://localhost:8000/test/inyectar-datos';
    
    // 🕒 Simulación de reparto: Los operarios graban en diferentes horas del turno
    const horasDisponibles = [
        "06:00", "07:00", "08:00", "09:00", 
        "10:00", "11:00", "12:00", "13:00"
    ];
    const horaAleatoria = horasDisponibles[Math.floor(Math.random() * horasDisponibles.length)];

    const payload = JSON.stringify({
        config_id: "019cbf3c-f4f6-703f-bf4a-10133910ecfa", // Tu UUID real
        hora: horaAleatoria, // 👈 Ya no chocan todos en la misma fila
        num_vale: "LOTE-STRESS-" + Math.floor(Math.random() * 9999),
        unidades_buenas: Math.floor(Math.random() * (200 - 100 + 1)) + 100, // Entre 100 y 200
        pnc: {
            cavidades_reales: 48,
            ciclo_real: 12.5,
            defectos: [],
            paros: [],
            inspeccion: [
                { cav: Math.floor(Math.random() * 48) + 1, defecto: 1 }
            ],
            inspeccion_completada: true
        }
    });

    const params = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    };

    const res = http.post(url, payload, params);

    // LOG DE ERRORES: Solo verás esto si algo sale mal
    if (res.status !== 200 && res.status !== 302) {
        console.log(`❌ Error ${res.status} en hora ${horaAleatoria}: ${res.body}`);
    }

    check(res, {
        'status es 200 o 302': (r) => r.status === 200 || r.status === 302,
        'tiempo respuesta < 2s': (r) => r.timings.duration < 2000,
    });

    sleep(1); // Espera 1 segundo entre envíos por usuario
}