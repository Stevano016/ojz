import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';

const statusOptions = ['Open', 'In Progress', 'Resolved', 'Closed'];

export default function TicketDetailPage() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [ticket, setTicket] = useState(null);
    const [loading, setLoading] = useState(true);
    const [updatingStatus, setUpdatingStatus] = useState(false);
    const [newStatus, setNewStatus] = useState('');
    const [actionForm, setActionForm] = useState({
        action_type: '',
        description: '',
        new_status: '',
    });
    const [sheetSyncWarning, setSheetSyncWarning] = useState(false);
    const [sheetSyncError, setSheetSyncError] = useState(null);

    const fetchTicket = async () => {
        setLoading(true);
        setSheetSyncWarning(false);
        setSheetSyncError(null);
        try {
            const res = await axios.get(`/tickets/${id}`);
            setTicket(res.data);
            setNewStatus(res.data.status_ticket);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchTicket();
    }, [id]);

    const handleStatusUpdate = async () => {
        if (!newStatus || newStatus === ticket.status_ticket) return;
        setUpdatingStatus(true);
        try {
            const res = await axios.put(`/tickets/${id}`, {
                status_ticket: newStatus,
            });
            setTicket(res.data);
            setNewStatus(res.data.status_ticket);
            setSheetSyncWarning(res.data.sheet_synced === false);
            setSheetSyncError(res.data.sheet_sync_error ?? null);
        } catch (err) {
            console.error('Gagal update status:', err);
        } finally {
            setUpdatingStatus(false);
        }
    };

    const handleAddAction = async (e) => {
        e.preventDefault();
        if (!actionForm.action_type || !actionForm.description) return;
        const res = await axios.post(`/tickets/${id}/actions`, {
            ...actionForm,
            old_status: ticket.status_ticket,
        });
        setActionForm({
            action_type: '',
            description: '',
            new_status: '',
        });
        await fetchTicket();
        if (res.data?.sheet_synced === false) {
            setSheetSyncWarning(true);
            setSheetSyncError(res.data?.sheet_sync_error ?? null);
        }
    };

    if (loading || !ticket) {
        return (
            <div className="text-sm text-slate-400">
                Memuat detail tiket dan riwayat aksi...
            </div>
        );
    }

    return (
        <div className="space-y-4">
            <button
                onClick={() => navigate(-1)}
                className="text-[11px] text-slate-400 hover:text-slate-200"
            >
                ← Kembali ke daftar
            </button>

            <div className="grid md:grid-cols-3 gap-4">
                <div className="md:col-span-2 space-y-3">
                    <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30">
                        <div className="flex flex-wrap items-start justify-between gap-2 mb-2">
                            <div>
                                <div className="text-xs font-mono text-slate-400">
                                    {ticket.ticket_id}
                                </div>
                                <h1 className="text-lg font-semibold text-slate-50 mt-1">
                                    {ticket.judul_sinyal}
                                </h1>
                                <div className="text-[11px] text-slate-400 mt-1">
                                    {ticket.nama_lokasi && (
                                        <span>{ticket.nama_lokasi} · </span>
                                    )}
                                    <span>{ticket.level_sinyal || 'Level tidak tercatat'}</span>
                                </div>
                            </div>
                            <div className="text-right text-[11px] text-slate-400">
                                <div>
                                    Skor urgensi hukum:{' '}
                                    <span className="text-amber-300 font-semibold">
                                        {ticket.skor_urgensi_hukum ?? '-'}
                                    </span>
                                </div>
                                <div>
                                    Status eskalasi:{' '}
                                    <span className="text-rose-300 font-semibold">
                                        {ticket.status_eskalasi}
                                    </span>
                                </div>
                                <div>
                                    Sumber:{' '}
                                    <span className="uppercase">
                                        {ticket.jenis_sumber || '-'}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="mt-3 space-y-2 text-sm text-slate-200">
                            <div className="text-[11px] font-semibold text-slate-400 uppercase">
                                Deskripsi sinyal
                            </div>
                            <p className="text-[13px] leading-relaxed text-slate-100">
                                {ticket.deskripsi_sinyal}
                            </p>
                        </div>

                        {ticket.risiko_teridentifikasi && (
                            <div className="mt-3 space-y-1 text-sm">
                                <div className="text-[11px] font-semibold text-slate-400 uppercase">
                                    Risiko teridentifikasi
                                </div>
                                <p className="text-[13px] leading-relaxed text-slate-100">
                                    {ticket.risiko_teridentifikasi}
                                </p>
                            </div>
                        )}

                        {ticket.rekomendasi_ai && (
                            <div className="mt-3 space-y-1 text-sm">
                                <div className="text-[11px] font-semibold text-emerald-300 uppercase">
                                    Rekomendasi AI
                                </div>
                                <p className="text-[13px] leading-relaxed text-slate-100">
                                    {ticket.rekomendasi_ai}
                                </p>
                            </div>
                        )}

                        {ticket.dasar_hukum && (
                            <div className="mt-3 text-[11px] text-slate-400">
                                Dasar hukum:{' '}
                                <span className="text-slate-200">
                                    {ticket.dasar_hukum}
                                </span>
                            </div>
                        )}
                    </div>

                    <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30">
                        <div className="flex items-center justify-between mb-3">
                            <div>
                                <div className="text-xs font-medium text-slate-300">
                                    Riwayat aksi & progres
                                </div>
                                <div className="text-[11px] text-slate-500">
                                    Log perubahan status dan intervensi lapangan
                                </div>
                            </div>
                        </div>

                        {ticket.actions?.length ? (
                            <ul className="space-y-2 text-xs">
                                {ticket.actions
                                    .slice()
                                    .sort(
                                        (a, b) =>
                                            new Date(b.created_at) - new Date(a.created_at),
                                    )
                                    .map((a) => (
                                        <li
                                            key={a.id}
                                            className="rounded-xl border border-slate-800/80 bg-slate-900/70 px-3 py-2"
                                        >
                                            <div className="flex items-center justify-between gap-3">
                                                <div className="font-medium text-slate-100">
                                                    {a.action_type}
                                                </div>
                                                <div className="text-[10px] text-slate-500">
                                                    {a.created_at &&
                                                        new Date(
                                                            a.created_at,
                                                        ).toLocaleString()}
                                                </div>
                                            </div>
                                            <p className="text-[11px] text-slate-200 mt-1">
                                                {a.description}
                                            </p>
                                            {a.old_status && a.new_status && (
                                                <div className="mt-1 text-[10px] text-slate-400">
                                                    Status: {a.old_status} →{' '}
                                                    <span className="text-emerald-300">
                                                        {a.new_status}
                                                    </span>
                                                </div>
                                            )}
                                            {a.user && (
                                                <div className="mt-1 text-[10px] text-slate-500">
                                                    Oleh:{' '}
                                                    <span className="text-slate-300">
                                                        {a.user.name}
                                                    </span>
                                                </div>
                                            )}
                                        </li>
                                    ))}
                            </ul>
                        ) : (
                            <div className="text-xs text-slate-500">
                                Belum ada aksi tercatat untuk tiket ini.
                            </div>
                        )}
                    </div>
                </div>

                <div className="space-y-3">
                    {sheetSyncWarning && (
                        <div className="rounded-xl border border-amber-500/50 bg-amber-500/10 px-3 py-2 text-[11px] text-amber-200 space-y-1">
                            <div>Status sudah tersimpan di database. Sinkron ke Google Sheets gagal.</div>
                            {sheetSyncError ? (
                                <div className="mt-1 font-mono text-[10px] text-amber-300/90 break-all">
                                    {sheetSyncError}
                                </div>
                            ) : (
                                <div className="text-amber-200/80">
                                    Cek .env (GOOGLE_SHEETS_SPREADSHEET_ID, credentials) dan share spreadsheet ke email service account.
                                </div>
                            )}
                        </div>
                    )}
                    <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30">
                        <div className="text-xs font-medium text-slate-300 mb-2">
                            Ubah status tiket
                        </div>
                        <div className="space-y-2 text-xs">
                            <select
                                className="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                                value={newStatus}
                                onChange={(e) => setNewStatus(e.target.value)}
                            >
                                {statusOptions.map((s) => (
                                    <option key={s} value={s}>
                                        {s}
                                    </option>
                                ))}
                            </select>
                            <button
                                onClick={handleStatusUpdate}
                                disabled={
                                    updatingStatus || newStatus === ticket.status_ticket
                                }
                                className="w-full inline-flex items-center justify-center rounded-lg bg-emerald-500/90 px-3 py-1.5 text-[11px] font-semibold text-slate-950 shadow-md shadow-emerald-500/40 hover:brightness-110 transition disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                {updatingStatus ? 'Menyimpan...' : 'Simpan status'}
                            </button>
                        </div>
                    </div>

                    <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30">
                        <div className="text-xs font-medium text-slate-300 mb-2">
                            Tambah aksi / progres
                        </div>
                        <form onSubmit={handleAddAction} className="space-y-2 text-xs">
                            <div>
                                <label className="block text-[11px] text-slate-400 mb-1">
                                    Jenis aksi
                                </label>
                                <input
                                    type="text"
                                    className="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                                    value={actionForm.action_type}
                                    onChange={(e) =>
                                        setActionForm((f) => ({
                                            ...f,
                                            action_type: e.target.value,
                                        }))
                                    }
                                    placeholder="Contoh: kontak Veilig Thuis, koordinasi wijkteam"
                                />
                            </div>
                            <div>
                                <label className="block text-[11px] text-slate-400 mb-1">
                                    Deskripsi singkat
                                </label>
                                <textarea
                                    className="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70 min-h-[80px]"
                                    value={actionForm.description}
                                    onChange={(e) =>
                                        setActionForm((f) => ({
                                            ...f,
                                            description: e.target.value,
                                        }))
                                    }
                                />
                            </div>
                            <div>
                                <label className="block text-[11px] text-slate-400 mb-1">
                                    Update status (opsional)
                                </label>
                                <select
                                    className="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                                    value={actionForm.new_status}
                                    onChange={(e) =>
                                        setActionForm((f) => ({
                                            ...f,
                                            new_status: e.target.value,
                                        }))
                                    }
                                >
                                    <option value="">Tidak mengubah status</option>
                                    {statusOptions.map((s) => (
                                        <option key={s} value={s}>
                                            {s}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <button
                                type="submit"
                                className="w-full inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-3 py-1.5 text-[11px] font-semibold text-slate-950 shadow-md shadow-amber-500/40 hover:brightness-110 transition"
                            >
                                Simpan aksi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}

