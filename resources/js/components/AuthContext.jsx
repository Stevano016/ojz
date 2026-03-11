import React, { createContext, useContext, useEffect, useState } from 'react';
import axios from 'axios';

const AuthContext = createContext(null);

// Base URL API: sama dengan origin (Laravel) atau proxy Vite di dev
axios.defaults.baseURL = '/api';

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [token, setToken] = useState(() => localStorage.getItem('ozj_token'));
    const [loading, setLoading] = useState(!!localStorage.getItem('ozj_token'));

    useEffect(() => {

        if (token) {
            axios.defaults.headers.common.Authorization = `Bearer ${token}`;
            localStorage.setItem('ozj_token', token);

            axios
                .get('/user')
                .then((res) => setUser(res.data))
                .catch(() => {
                    setUser(null);
                    setToken(null);
                    localStorage.removeItem('ozj_token');
                })
                .finally(() => setLoading(false));
        } else {
            delete axios.defaults.headers.common.Authorization;
            localStorage.removeItem('ozj_token');
            setLoading(false);
        }
    }, [token]);

    const login = async (email, password) => {
        const res = await axios.post('/login', { email, password });
        setToken(res.data.token);
        setUser(res.data.user);
    };

    const logout = async () => {
        try {
            await axios.post('/logout');
        } catch (_e) {
            // ignore
        }
        setUser(null);
        setToken(null);
        localStorage.removeItem('ozj_token');
    };

    const value = {
        user,
        token,
        loading,
        login,
        logout,
        isAuthenticated: !!user,
    };

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
    return useContext(AuthContext);
}

