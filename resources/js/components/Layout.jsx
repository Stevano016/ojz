import React from 'react';
import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from './AuthContext';

const navLinkClass =
    'px-3 py-2 rounded-md text-sm font-medium transition-colors hover:bg-white/10';

export default function Layout() {
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    const handleLogout = async () => {
        await logout();
        navigate('/login');
    };

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 flex">
            <aside className="w-60 bg-slate-900/80 border-r border-slate-800/60 backdrop-blur-xl hidden md:flex flex-col">
                <div className="px-6 py-5 border-b border-slate-800/60">
                    <Link to="/dashboard" className="flex items-center gap-2">
                        <div className="h-9 w-9 rounded-xl bg-gradient-to-br from-amber-400 via-orange-500 to-rose-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                            <span className="text-xs font-black tracking-tight">OZJ</span>
                        </div>
                        <div className="flex flex-col">
                            <span className="text-sm font-semibold leading-tight">
                                OZJ Reporting
                            </span>
                            <span className="text-[11px] text-slate-400 leading-tight">
                                AI Signal Intelligence
                            </span>
                        </div>
                    </Link>
                </div>
                <nav className="flex-1 px-3 py-4 space-y-1 text-sm">
                    <NavLink
                        to="/dashboard"
                        className={({ isActive }) =>
                            `${navLinkClass} ${
                                isActive
                                    ? 'bg-slate-800 text-amber-300'
                                    : 'text-slate-300'
                            } flex items-center gap-2`
                        }
                    >
                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-400" />
                        Dashboard
                    </NavLink>
                    <NavLink
                        to="/tickets"
                        className={({ isActive }) =>
                            `${navLinkClass} ${
                                isActive
                                    ? 'bg-slate-800 text-amber-300'
                                    : 'text-slate-300'
                            } flex items-center gap-2`
                        }
                    >
                        <span className="h-1.5 w-1.5 rounded-full bg-sky-400" />
                        Tickets
                    </NavLink>
                    <NavLink
                        to="/tickets/new"
                        className={({ isActive }) =>
                            `${navLinkClass} ${
                                isActive
                                    ? 'bg-slate-800 text-amber-300'
                                    : 'text-slate-300'
                            } flex items-center gap-2`
                        }
                    >
                        <span className="h-1.5 w-1.5 rounded-full bg-amber-400" />
                        New Ticket
                    </NavLink>
                </nav>
                <div className="px-4 py-4 border-t border-slate-800/60 text-xs text-slate-400 flex items-center justify-between gap-2">
                    <div className="flex items-center gap-2">
                        <div className="h-7 w-7 rounded-full bg-slate-800 flex items-center justify-center text-[10px] font-semibold">
                            {user?.name?.[0] ?? 'U'}
                        </div>
                        <div className="flex flex-col">
                            <span className="font-medium text-slate-200 truncate max-w-[7rem]">
                                {user?.name ?? 'User'}
                            </span>
                            <span className="text-[11px] truncate max-w-[7rem]">
                                {user?.email}
                            </span>
                        </div>
                    </div>
                    <button
                        onClick={handleLogout}
                        className="text-[11px] text-rose-300 hover:text-rose-200"
                    >
                        Logout
                    </button>
                </div>
            </aside>

            <div className="flex-1 flex flex-col">
                <header className="md:hidden px-4 py-3 border-b border-slate-800/60 bg-slate-950/80 backdrop-blur-xl flex items-center justify-between">
                    <Link to="/dashboard" className="flex items-center gap-2">
                        <div className="h-8 w-8 rounded-xl bg-gradient-to-br from-amber-400 via-orange-500 to-rose-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                            <span className="text-[11px] font-black tracking-tight">
                                OZJ
                            </span>
                        </div>
                        <span className="text-sm font-semibold">OZJ Reporting</span>
                    </Link>
                    {user && (
                        <button
                            onClick={handleLogout}
                            className="text-[11px] text-rose-300 hover:text-rose-200"
                        >
                            Logout
                        </button>
                    )}
                </header>

                {/* Bar atas area utama: Logout selalu terlihat di desktop (sidebar bisa panjang) */}
                <div className="hidden md:flex items-center justify-end gap-3 px-4 py-2 border-b border-slate-800/60 bg-slate-950/50">
                    <span className="text-[11px] text-slate-400 truncate max-w-[12rem]">
                        {user?.email}
                    </span>
                    <button
                        onClick={handleLogout}
                        className="text-xs text-rose-300 hover:text-rose-200 font-medium px-2.5 py-1 rounded-md hover:bg-rose-500/10 transition"
                    >
                        Logout
                    </button>
                </div>

                <main className="flex-1 bg-gradient-to-b from-slate-950 via-slate-950 to-slate-950/90">
                    <div className="max-w-6xl mx-auto px-4 py-6 md:py-8">
                        <Outlet />
                    </div>
                </main>
            </div>
        </div>
    );
}

