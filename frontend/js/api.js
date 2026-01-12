const API_URL = 'http://127.0.0.1:8000/api';

function getToken() {
    return localStorage.getItem('token');
}

function setToken(token) {
    localStorage.setItem('token', token);
}

function removeToken() {
    localStorage.removeItem('token');
}

function showMessage(message, type = 'success') {
    const msgDiv = document.getElementById('message');
    if (msgDiv) {
        msgDiv.textContent = message;
        msgDiv.className = type;
        msgDiv.style.display = 'block';
        setTimeout(() => {
            msgDiv.style.display = 'none';
        }, 3000);
    }
}

async function apiRequest(endpoint, options = {}) {
    const token = getToken();
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers
    };

    if (token && !options.noAuth) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    try {
        const response = await fetch(`${API_URL}${endpoint}`, {
            ...options,
            headers
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Request failed');
        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}
