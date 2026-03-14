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

function getFilenameFromResponse(res, defaultName) {
    const disp = res.headers['content-disposition'];
    let name = defaultName;
    if (disp) {
        let m = disp.match(/filename\*=(?:UTF-8'')?([^;\n]+)/i);
        if (m) name = decodeURIComponent(m[1].trim().replace(/^["']|["']$/g, ''));
        else { m = disp.match(/filename="?([^";\n]+)"?/i); if (m) name = m[1].trim().replace(/^["']|["']$/g, ''); }
    }
    return name || defaultName;
}

async function downloadExcel(path, defaultName, method = 'GET', body = null) {
    try {
        const opts = { responseType: 'blob' };
        const res = method === 'POST' && body
            ? await axios.post(path, body, opts)
            : await axios.get(path, opts);
        const blob = res.data;
        const name = getFilenameFromResponse(res, defaultName);
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = name;
        a.click();
        URL.revokeObjectURL(url);
    } catch (e) {
        console.error(e);
        alert('Gagal mengunduh. Coba lagi.');
    }
}

export default function TicketListPage() {
    const [tickets, setTickets] = useState([]);
    const [loading, setLoading] = useState(true);
    const [exporting, setExporting] = useState(null);
    const [selectedIds, setSelectedIds] = useState(new Set());
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

    const handleExportRekap = () => {
        setExporting('rekap');
        downloadExcel('/export/tickets/rekap', 'ozj-rekap.xlsx').finally(() => setExporting(null));
    };
    const handleExportPerTicket = (onlySelected = false) => {
        setExporting(onlySelected ? 'per-ticket-selected' : 'per-ticket');
        if (onlySelected && selectedIds.size > 0) {
            downloadExcel(
                '/export/tickets/per-ticket',
                'ozj-per-ticket.xlsx',
                'POST',
                { ids: Array.from(selectedIds) }
            ).finally(() => setExporting(null));
        } else {
            downloadExcel('/export/tickets/per-ticket', 'ozj-per-ticket.xlsx').finally(() => setExporting(null));
        }
    };

    const handleDownloadOneTicket = (ticketId) => {
        setExporting(`download-${ticketId}`);
        const q = `?ticket_id=${encodeURIComponent(ticketId)}`;
        downloadExcel(
            `/export/tickets/per-ticket${q}`,
            `ozj-ticket-${ticketId}.xlsx`
        ).finally(() => setExporting(null));
    };

    const toggleSelect = (id) => {
        setSelectedIds((prev) => {
            const next = new Set(prev);
            if (next.has(id)) next.delete(id);
            else next.add(id);
            return next;
        });
    };
    const toggleSelectAll = () => {
        if (selectedIds.size >= tickets.length) {
            setSelectedIds(new Set());
        } else {
            setSelectedIds(new Set(tickets.map((t) => t.id)));
        }
    };
    const clearSelection = () => setSelectedIds(new Set());

    return (
        <div className="space-y-6 max-w-[1600px]">
            {/* Header */}
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-slate-50 tracking-tight">
                        Daftar Tiket Sinyal
                    </h1>
                    <p className="text-sm text-slate-400 mt-1 max-w-xl">
                        Lihat, filter, dan unduh tiket. Pilih tiket lalu unduh sebagai Excel.
                    </p>
                </div>
                <div className="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        onClick={handleExportRekap}
                        disabled={!!exporting}
                        className="inline-flex items-center gap-2 rounded-xl border border-emerald-500/50 bg-emerald-500/10 px-4 py-2.5 text-sm font-medium text-emerald-300 hover:bg-emerald-500/20 disabled:opacity-50 transition"
                    >
                        {exporting === 'rekap' ? '...' : '📥'} Excel Rekap
                    </button>
                    <button
                        type="button"
                        onClick={() => handleExportPerTicket(false)}
                        disabled={!!exporting}
                        className="inline-flex items-center gap-2 rounded-xl border border-sky-500/50 bg-sky-500/10 px-4 py-2.5 text-sm font-medium text-sky-300 hover:bg-sky-500/20 disabled:opacity-50 transition"
                    >
                        {exporting === 'per-ticket' ? '...' : '📥'} Excel Semua
                    </button>
                    <button
                        type="button"
                        onClick={() => handleExportPerTicket(true)}
                        disabled={!!exporting || selectedIds.size === 0}
                        title={selectedIds.size === 0 ? 'Centang tiket di kolom Pilih, lalu klik tombol ini' : `Unduh ${selectedIds.size} tiket yang dipilih`}
                        className="inline-flex items-center gap-2 rounded-xl border-2 border-violet-500 bg-violet-500/20 px-4 py-2.5 text-sm font-semibold text-violet-200 hover:bg-violet-500/30 disabled:opacity-50 disabled:border-violet-500/50 disabled:bg-violet-500/10 transition"
                    >
                        {exporting === 'per-ticket-selected' ? 'Mengunduh...' : '📥 Unduh Terpilih'}
                        {selectedIds.size > 0 && (
                            <span className="rounded-full bg-violet-500/60 px-2 py-0.5 text-xs font-bold">
                                {selectedIds.size}
                            </span>
                        )}
                    </button>
                    <Link
                        to="/tickets/new"
                        className="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-lg shadow-amber-500/30 hover:brightness-110 transition"
                    >
                        + Tiket Baru
                    </Link>
                </div>
            </div>

            {/* Petunjuk singkat */}
            <div className="rounded-xl border border-slate-700/60 bg-slate-800/50 px-4 py-3 text-sm text-slate-300">
                <span className="font-medium text-slate-200">Cara download: </span>
                Centang tiket di kolom <strong className="text-amber-200/90">Pilih</strong> → klik <strong className="text-violet-200">Unduh Terpilih</strong>. Atau klik <strong className="text-sky-200">📥 Unduh</strong> per baris untuk satu tiket.
            </div>

            {/* Tabel & filter */}
            <div className="rounded-2xl bg-slate-900/60 border border-slate-700/80 shadow-xl shadow-black/20 overflow-hidden">
                <div className="px-4 py-3 border-b border-slate-700/80 bg-slate-800/30">
                    <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <span className="text-xs font-medium uppercase tracking-wider text-slate-400">Filter</span>
                        <div className="flex flex-wrap items-center gap-2">
                        <select
                            className="bg-slate-900/80 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-400/50"
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
                            className="bg-slate-900/80 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-400/50"
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
                            className="bg-slate-900/80 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-400/50"
                            value={filters.level}
                            onChange={(e) => onFilterChange('level', e.target.value)}
                        >
                            <option value="all">Level: Semua</option>
                            <option value="Mikro">Mikro</option>
                            <option value="Meso">Meso</option>
                            <option value="Makro">Makro</option>
                        </select>
                            <input
                                type="text"
                                placeholder="Cari (ID, judul, lokasi)..."
                                className="min-w-[180px] bg-slate-900/80 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-400/50"
                                value={filters.search}
                                onChange={(e) => onFilterChange('search', e.target.value)}
                            />
                        </div>
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full text-sm text-left">
                        <thead>
                            <tr className="bg-slate-800/80 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700/80">
                                <th className="px-4 py-3 w-12 text-center">
                                    <input
                                        type="checkbox"
                                        checked={tickets.length > 0 && selectedIds.size === tickets.length}
                                        onChange={toggleSelectAll}
                                        className="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-400/70"
                                        title="Pilih semua"
                                    />
                                </th>
                                <th className="px-4 py-3 font-medium">Ticket</th>
                                <th className="px-4 py-3 font-medium">Status</th>
                                <th className="px-4 py-3 font-medium">Level</th>
                                <th className="px-4 py-3 font-medium">Kategori</th>
                                <th className="px-4 py-3 font-medium">Skor</th>
                                <th className="px-4 py-3 font-medium">Sumber</th>
                                <th className="px-4 py-3 font-medium">Waktu</th>
                                <th className="px-4 py-3 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-700/60">
                            {loading ? (
                                <tr>
                                    <td colSpan={9} className="px-4 py-12 text-center text-slate-400">
                                        Memuat tiket...
                                    </td>
                                </tr>
                            ) : tickets.length === 0 ? (
                                <tr>
                                    <td colSpan={9} className="px-4 py-12 text-center text-slate-500">
                                        Tidak ada tiket dengan filter saat ini.
                                    </td>
                                </tr>
                            ) : (
                                tickets.map((t) => (
                                    <tr
                                        key={t.id}
                                        className="bg-slate-900/40 hover:bg-slate-800/60 transition-colors"
                                    >
                                        <td className="px-4 py-3 align-middle text-center">
                                            <input
                                                type="checkbox"
                                                checked={selectedIds.has(t.id)}
                                                onChange={() => toggleSelect(t.id)}
                                                className="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-400/70"
                                                title="Pilih untuk download"
                                            />
                                        </td>
                                        <td className="px-4 py-3 align-middle">
                                            <Link
                                                to={`/tickets/${t.id}`}
                                                className="text-xs font-medium text-sky-300 hover:text-sky-200"
                                            >
                                                {t.ticket_id}
                                            </Link>
                                            <div className="text-xs text-slate-300 mt-0.5 line-clamp-2">
                                                {t.judul_sinyal}
                                            </div>
                                            <div className="text-[11px] text-slate-500 mt-0.5">
                                                {t.nama_lokasi || '—'}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 align-middle">
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
                                        <td className="px-4 py-3 align-middle text-slate-200">
                                            {t.level_sinyal || '—'}
                                        </td>
                                        <td className="px-4 py-3 align-middle text-slate-200">
                                            {t.kategori_sinyal || '—'}
                                        </td>
                                        <td className="px-4 py-3 align-middle">
                                            <span className="font-semibold text-slate-100">{t.skor_urgensi_hukum ?? '—'}</span>
                                            <div className="text-[11px] text-slate-500">max {t.skor_urgensi_tertinggi ?? '—'}</div>
                                        </td>
                                        <td className="px-4 py-3 align-middle text-slate-200">
                                            <span className="uppercase text-[10px] tracking-wide">
                                                {t.jenis_sumber || '-'}
                                            </span>
                                            {t.nama_pelapor && (
                                                <div className="text-[10px] text-slate-500">
                                                    {t.nama_pelapor}
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 align-middle text-slate-300 text-xs whitespace-nowrap">
                                            {t.waktu_catat ? new Date(t.waktu_catat).toLocaleString() : '—'}
                                        </td>
                                        <td className="px-4 py-3 align-middle text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => handleDownloadOneTicket(t.ticket_id)}
                                                    disabled={!!exporting}
                                                    title={`Unduh ${t.ticket_id}`}
                                                    className="inline-flex items-center gap-1 rounded-lg border border-slate-600 bg-slate-800/80 px-2.5 py-1.5 text-xs font-medium text-sky-300 hover:bg-sky-500/20 hover:border-sky-500/40 transition disabled:opacity-50"
                                                >
                                                    {exporting === `download-${t.ticket_id}` ? '...' : '📥 Unduh'}
                                                </button>
                                                <Link
                                                    to={`/tickets/${t.id}`}
                                                    className="inline-flex items-center rounded-lg border border-slate-600 bg-slate-800/80 px-2.5 py-1.5 text-xs font-medium text-slate-200 hover:bg-amber-500/20 hover:border-amber-500/40 transition"
                                                >
                                                    Detail
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {selectedIds.size > 0 && (
                    <div className="mx-4 mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-violet-500/40 bg-violet-500/10 px-4 py-3">
                        <span className="text-sm font-medium text-violet-200">
                            {selectedIds.size} tiket dipilih
                        </span>
                        <button
                            type="button"
                            onClick={() => handleExportPerTicket(true)}
                            disabled={!!exporting}
                            className="inline-flex items-center gap-2 rounded-lg bg-violet-500 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-600 disabled:opacity-60 transition"
                        >
                            {exporting === 'per-ticket-selected' ? 'Mengunduh...' : '📥 Unduh Terpilih'}
                        </button>
                        <button
                            type="button"
                            onClick={clearSelection}
                            className="text-xs text-slate-400 hover:text-slate-200 underline"
                        >
                            Batalkan pilihan
                        </button>
                    </div>
                )}

                {!loading && pagination.last_page > 1 && (
                    <div className="px-4 py-3 border-t border-slate-700/80 flex items-center justify-between gap-4 text-sm text-slate-400 bg-slate-800/30">
                        <span>
                            Halaman <span className="font-medium text-slate-200">{pagination.current_page}</span> dari {pagination.last_page}
                            {pagination.total > 0 && (
                                <span className="ml-2 text-slate-500">({pagination.total} tiket)</span>
                            )}
                        </span>
                        <div className="flex items-center gap-2">
                            <button
                                type="button"
                                onClick={() => goToPage(pagination.current_page - 1)}
                                disabled={pagination.current_page <= 1}
                                className="px-3 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-200 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-700 transition"
                            >
                                Sebelumnya
                            </button>
                            <button
                                type="button"
                                onClick={() => goToPage(pagination.current_page + 1)}
                                disabled={pagination.current_page >= pagination.last_page}
                                className="px-3 py-2 rounded-lg border border-slate-600 bg-slate-800 text-slate-200 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-700 transition"
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

