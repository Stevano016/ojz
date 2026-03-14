import React, { useState } from 'react';
import { Navigate, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../components/AuthContext';

export default function LoginPage() {
    const { login, isAuthenticated, loading } = useAuth();
    const [email, setEmail] = useState('admin@ozj.nl');
    const [password, setPassword] = useState('password');
    const [error, setError] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const navigate = useNavigate();
    const location = useLocation();

    const from = location.state?.from?.pathname || '/dashboard';

    if (isAuthenticated && !loading) {
        return <Navigate to={from} replace />;
    }

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setSubmitting(true);
        try {
            await login(email, password);
            navigate(from, { replace: true });
        } catch (e) {
            setError('Login gagal. Periksa email & password.');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="min-h-screen bg-slate-950 flex items-center justify-center px-4">
            <div className="max-w-md w-full">
                <div className="mb-6 flex flex-col items-center gap-2 text-center">
                    <img src="/images/ozj-logo.png" alt="OZJ - AI Signal Intelligence" className="h-16 w-auto object-contain" />
                    <div className="flex flex-col">
                        <span className="text-sm font-semibold text-slate-100">
                            OZJ Reporting System
                        </span>
                        <span className="text-xs text-slate-400">
                            AI Signal Intelligence Youth Care
                        </span>
                    </div>
                </div>

                <div className="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-2xl shadow-black/40 backdrop-blur-xl">
                    <h1 className="text-lg font-semibold text-slate-50 mb-1">
                        Masuk ke dashboard
                    </h1>
                    <p className="text-sm text-slate-400 mb-5">
                        Gunakan akun internal OZJ untuk mengakses sinyal dan tiket.
                    </p>

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label className="block text-xs font-medium text-slate-300 mb-1.5">
                                Email
                            </label>
                            <input
                                type="email"
                                className="w-full rounded-lg bg-slate-900/80 border border-slate-700 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-400/70 focus:border-amber-400/60"
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                autoComplete="email"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-slate-300 mb-1.5">
                                Password
                            </label>
                            <input
                                type="password"
                                className="w-full rounded-lg bg-slate-900/80 border border-slate-700 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-400/70 focus:border-amber-400/60"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                autoComplete="current-password"
                            />
                        </div>

                        {error && (
                            <div className="text-xs text-rose-300 bg-rose-950/40 border border-rose-900 rounded-md px-3 py-2">
                                {error}
                            </div>
                        )}

                        <button
                            type="submit"
                            disabled={submitting}
                            className="w-full mt-2 inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-lg shadow-amber-500/40 hover:brightness-110 transition disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            {submitting ? 'Masuk...' : 'Masuk'}
                        </button>
                    </form>

                    <div className="mt-4 text-[11px] text-slate-500">
                        Demo login: <span className="font-mono">admin@ozj.nl / password</span>
                    </div>
                </div>
            </div>
        </div>
    );
}

