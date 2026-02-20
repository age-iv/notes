import axios from 'axios';

// Для разработки используем прокси (см. package.json proxy)
// Для продакшена можно использовать переменные окружения
const API_BASE_URL = 'http://localhost:8080';

const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    timeout: 10000, // 10 секунд таймаут
});

// Перехватчик запросов
api.interceptors.request.use(
    config => {
        console.log('Request:', config.method.toUpperCase(), config.url);
        return config;
    },
    error => {
        console.error('Request Error:', error);
        return Promise.reject(error);
    }
);

// Перехватчик ответов
api.interceptors.response.use(
    response => {
        console.log('Response:', response.status, response.config.url);
        console.log('Response data:', response.data);
        return response;
    },
    error => {
        if (error.response) {
            // Сервер ответил с кодом ошибки
            console.error('API Error Response:', {
                status: error.response.status,
                data: error.response.data,
                url: error.response.config?.url
            });
        } else if (error.request) {
            // Запрос был сделан, но ответ не получен
            console.error('API Network Error:', error.request);
        } else {
            // Что-то пошло не так при настройке запроса
            console.error('API Setup Error:', error.message);
        }
        return Promise.reject(error);
    }
);

export const notesApi = {
    // Получить все заметки
    getAll: async () => {
        const response = await api.get(API_BASE_URL + '/api/notes');
        // Извлекаем данные из response.data.data (новый формат)
        return response.data.data || response.data;
    },

    // Получить заметку по ID
    getById: async (id) => {
        const response = await api.get(API_BASE_URL + `/api/notes/${id}`);
        return response.data.data || response.data;
    },

    // Создать заметку
    create: async (noteData) => {
        const response = await api.post(API_BASE_URL + '/api/notes', noteData);
        return response.data.data || response.data;
    },

    // Обновить заметку
    update: async (id, noteData) => {
        const response = await api.put(API_BASE_URL + `/api/notes/${id}`, noteData);
        return response.data.data || response.data;
    },

    // Удалить заметку
    delete: async (id) => {
        await api.delete(API_BASE_URL + `/api/notes/${id}`);
    },
};

export default api;