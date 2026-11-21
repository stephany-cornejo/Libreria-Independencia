import axios from 'axios';

// Obtener el token CSRF que Laravel inserta en el HTML
const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');

// Cliente HTTP para comunicarse con Laravel
export const api = axios.create({
    baseURL: '/',
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
    },
});
