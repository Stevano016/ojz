import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';

const statusColors = {
    Open: 'bg-sky-500/20 text-sky-300 border-sky-500/40',
    'In Progress': 'bg-amber-500/20 text-amber-300 border-amber-500/40',
    Resolved: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
    Closed: 'bg-slate-500/20 text-slate-300 border-slate-500/40',
};

const escalationColors = {
    Normal: 'bg-emerald-500/15 text-emerald-300 border-emerald-400/40',
    Sedang: 'bg-yellow-500/15 text-yellow-200 border-yellow-400/40',
    Tinggi: 'bg-orange-500/20 text-orange-200 border-orange-400/40',
    KRITIS: 'bg-rose-600/25 text-rose-200 border-rose-500/50',
};

const PER_PAGE = 10;

export default function TicketListPage() {
    const [tickets, setTickets] = useState([]);
    const [loading, setLoading] = useState(true);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        per_page: PER_PAGE,
        total: 0,
    });
    const [filters, setFilters] = useState({
        status: 'all',
        urgency: 'all',
        level: 'all',
        search: '',
    });

    useEffect(() => {
        const fetchTickets = async () => {
            setLoading(true);
            try {
                const res = await axios.get('/tickets', {
                    params: {
                        status: filters.status,
                        urgency: filters.urgency,
                        level: filters.level,
                        search: filters.search || undefined,
                        per_page: PER_PAGE,
                        page: pagination.current_page,
                    },
                });
                const data = res.data;
                const list = data?.data ?? data;
                setTickets(Array.isArray(list) ? list : []);
                if (data?.current_page != null) {
                    setPagination((p) => ({
                        ...p,
                        current_page: data.current_page,
                        last_page: data.last_page ?? 1,
                        per_page: data.per_page ?? PER_PAGE,
                        total: data.total ?? 0,
                    }));
                }
            } finally {
                setLoading(false);
            }
        };
        fetchTickets();
    }, [filters, pagination.current_page]);

    const goToPage = (page) => {
        setPagination((p) => ({ ...p, current_page: Math.max(1, Math.min(page, pagination.last_page)) }));
    };

    // Reset ke halaman 1 saat filter berubah
    const onFilterChange = (key, value) => {
        setFilters((f) => ({ ...f, [key]: value }));
        setPagination((p) => ({ ...p, current_page: 1 }));
    };

    return (
        <div className="space-y-4">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h1 className="text-xl font-semibold text-slate-50">
                        Daftar tiket sinyal
                    </h1>
                    <p className="text-sm text-slate-400 mt-1 max-w-2xl">
                        Lihat, filter, dan telusuri semua sinyal AI dan laporan lapangan yang
                        menjadi tiket.
                    </p>
                </div>
                <Link
                    to="/tickets/new"
                    className="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-3.5 py-2 text-xs font-semibold text-slate-950 shadow-lg shadow-amber-500/40 hover:brightness-110 transition"
                >
                    + Tiket manual baru
                </Link>
            </div>

            <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-3 md:p-4 shadow-xl shadow-black/30">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-3">
                    <div className="flex flex-wrap gap-2 text-[11px]">
                        <select
                            className="bg-slate-900/80 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                            value={filters.status}
                            onChange={(e) => onFilterChange('status', e.target.value)}
                        >
                            <option value="all">Status: Semua</option>
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Closed">Closed</option>
                        </select>
                        <select
                            className="bg-slate-900/80 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                            value={filters.urgency}
                            onChange={(e) => onFilterChange('urgency', e.target.value)}
                        >
                            <option value="all">Eskalasi: Semua</option>
                            <option value="Normal">Normal</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Tinggi">Tinggi</option>
                            <option value="KRITIS">KRITIS</option>
                        </select>
                        <select
                            className="bg-slate-900/80 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                            value={filters.level}
                            onChange={(e) => onFilterChange('level', e.target.value)}
                        >
                            <option value="all">Level: Semua</option>
                            <option value="Mikro">Mikro</option>
                            <option value="Meso">Meso</option>
                            <option value="Makro">Makro</option>
                        </select>
                    </div>
                    <input
                        type="text"
                        placeholder="Cari tiket (ID, judul, lokasi, pelapor)..."
                        className="w-full md:w-72 bg-slate-900/80 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                        value={filters.search}
                        onChange={(e) => onFilterChange('search', e.target.value)}
                    />
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full text-xs text-left border-separate border-spacing-y-1">
                        <thead>
                            <tr className="text-[11px] uppercase tracking-wide text-slate-400">
                                <th className="px-3 py-1.5">Ticket</th>
                                <th className="px-3 py-1.5">Status</th>
                                <th className="px-3 py-1.5">Level</th>
                                <th className="px-3 py-1.5">Kategori</th>
                                <th className="px-3 py-1.5">Skor</th>
                                <th className="px-3 py-1.5">Sumber</th>
                                <th className="px-3 py-1.5">Waktu catat</th>
                                <th className="px-3 py-1.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr>
                                    <td
                                        colSpan={8}
                                        className="px-3 py-4 text-center text-slate-400"
                                    >
                                        Memuat tiket...
                                    </td>
                                </tr>
                            ) : tickets.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={8}
                                        className="px-3 py-4 text-center text-slate-500"
                                    >
                                        Tidak ada tiket dengan filter saat ini.
                                    </td>
                                </tr>
                            ) : (
                                tickets.map((t) => (
                                    <tr
                                        key={t.id}
                                        className="bg-slate-900/60 border border-slate-800/80 rounded-xl shadow-sm shadow-black/40 hover:bg-slate-900/80 transition"
                                    >
                                        <td className="px-3 py-2.5 align-top">
                                            <Link
                                                to={`/tickets/${t.id}`}
                                                className="text-xs font-medium text-sky-300 hover:text-sky-200"
                                            >
                                                {t.ticket_id}
                                            </Link>
                                            <div className="text-[11px] text-slate-200 mt-0.5 line-clamp-2">
                                                {t.judul_sinyal}
                                            </div>
                                            <div className="text-[10px] text-slate-500 mt-0.5">
                                                {t.nama_lokasi || 'Lokasi tidak tercatat'}
                                            </div>
                                        </td>
                                        <td className="px-3 py-2.5 align-top">
                                            <span
                                                className={`inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] ${
                                                    statusColors[t.status_ticket] ||
                                                    'bg-slate-700/30 text-slate-200 border-slate-500/40'
                                                }`}
                                            >
                                                <span className="h-1.5 w-1.5 rounded-full bg-current" />
                                                {t.status_ticket}
                                            </span>
                                            <div className="mt-1">
                                                <span
                                                    className={`inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] ${
                                                        escalationColors[t.status_eskalasi] ||
                                                        'bg-slate-700/30 text-slate-200 border-slate-500/40'
                                                    }`}
                                                >
                                                    {t.status_eskalasi || 'Normal'}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-3 py-2.5 align-top text-[11px] text-slate-200">
                                            {t.level_sinyal || '-'}
                                        </td>
                                        <td className="px-3 py-2.5 align-top text-[11px] text-slate-200">
                                            {t.kategori_sinyal || '-'}
                                        </td>
                                        <td className="px-3 py-2.5 align-top text-[11px] text-slate-100">
                                            <div className="font-semibold">
                                                {t.skor_urgensi_hukum ?? '-'}
                                            </div>
                                            <div className="text-[10px] text-slate-500">
                                                max {t.skor_urgensi_tertinggi ?? '-'}
                                            </div>
                                        </td>
                                        <td className="px-3 py-2.5 align-top text-[11px] text-slate-200">
                                            <span className="uppercase text-[10px] tracking-wide">
                                                {t.jenis_sumber || '-'}
                                            </span>
                                            {t.nama_pelapor && (
                                                <div className="text-[10px] text-slate-500">
                                                    {t.nama_pelapor}
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-3 py-2.5 align-top text-[11px] text-slate-200">
                                            {t.waktu_catat
                                                ? new Date(t.waktu_catat).toLocaleString()
                                                : '-'}
                                        </td>
                                        <td className="px-3 py-2.5 align-top text-right">
                                            <Link
                                                to={`/tickets/${t.id}`}
                                                className="inline-flex items-center rounded-lg border border-slate-700/80 bg-slate-900/70 px-2.5 py-1 text-[11px] font-medium text-slate-100 hover:bg-slate-800 hover:border-amber-400/60 hover:text-amber-200 transition"
                                            >
                                                Edit
                                            </Link>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {!loading && pagination.last_page > 1 && (
                    <div className="mt-4 flex items-center justify-between gap-2 text-xs text-slate-400">
                        <span>
                            Halaman {pagination.current_page} dari {pagination.last_page}
                            {pagination.total > 0 && (
                                <span className="ml-1">
                                    ({pagination.total} tiket)
                                </span>
                            )}
                        </span>
                        <div className="flex items-center gap-1">
                            <button
                                type="button"
                                onClick={() => goToPage(pagination.current_page - 1)}
                                disabled={pagination.current_page <= 1}
                                className="px-2.5 py-1.5 rounded-md border border-slate-700 bg-slate-900/80 text-slate-200 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-800"
                            >
                                Sebelumnya
                            </button>
                            <button
                                type="button"
                                onClick={() => goToPage(pagination.current_page + 1)}
                                disabled={pagination.current_page >= pagination.last_page}
                                className="px-2.5 py-1.5 rounded-md border border-slate-700 bg-slate-900/80 text-slate-200 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-800"
                            >
                                Selanjutnya
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}

