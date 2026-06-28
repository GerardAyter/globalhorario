import axios from 'axios'

const instance = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
    headers: {
        'Content-Type': 'application/json',
    },
})

// attach token automatically if present
instance.interceptors.request.use(cfg => {
    try {
        // prefer token in localStorage, fallback to sessionStorage
        let t = localStorage.getItem('token')
        if (!t) t = sessionStorage.getItem('token')
        if (t) cfg.headers.Authorization = `Bearer ${t}`
    } catch (e) {}
    return cfg
})

export default instance