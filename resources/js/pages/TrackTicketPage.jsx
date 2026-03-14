import React, { useState, useEffect } from 'react';
import { useSearchParams, useParams, Link } from 'react-router-dom';
import axios from 'axios';

// baseURL sudah /api di AuthContext, jadi path tanpa prefix /api
const API_TRACK = '/tickets/track';

const statusLabels = {
    Open: 'Terbuka',
    'In Progress': 'Sedang diproses',
    Resolved: 'Selesai',
    Closed: 'Ditutup',
};

export default function TrackTicketPage() {
    const { ticketId: ticketIdFromPath } = useParams();
    const [searchParams] = useSearchParams();
    const ticketIdFromQuery = searchParams.get('ticket_id') || '';
    const ticketIdFromUrl = ticketIdFromPath || ticketIdFromQuery;
    const [ticketId, setTicketId] = useState(ticketIdFromUrl || '');
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [searched, setSearched] = useState(false);

    const doFetch = (id) => {
        const tid = (id || ticketId || '').trim();
        if (!tid) {
            setError('Masukkan nomor tiket.');
            return;
        }
        setLoading(true);
        setError('');
        setData(null);
        setSearched(true);
        axios
            .get(`${API_TRACK}/${encodeURIComponent(tid)}`)
            .then((res) => setData(res.data))
            .catch((err) => {
                setData(null);
                setError(err.response?.status === 404 ? 'Tiket tidak ditemukan.' : (err.response?.data?.message || 'Gagal memuat data.'));
            })
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        if (!ticketIdFromUrl) return;
        setTicketId(ticketIdFromUrl);
        setLoading(true);
        setError('');
        setData(null);
        setSearched(true);
        axios
            .get(`${API_TRACK}/${encodeURIComponent(ticketIdFromUrl)}`)
            .then((res) => setData(res.data))
            .catch((err) => {
                setData(null);
                setError(err.response?.status === 404 ? 'Tiket tidak ditemukan.' : (err.response?.data?.message || 'Gagal memuat data.'));
            })
            .finally(() => setLoading(false));
    }, [ticketIdFromUrl]);

    return (
        <div className="min-h-screen bg-slate-950 flex flex-col items-center justify-center px-4 py-8">
            <div className="w-full max-w-md space-y-6">
                <div className="text-center">
                    <img src="/images/ozj-logo.png" alt="OZJ" className="h-14 w-auto object-contain mx-auto mb-3" />
                    <h1 className="text-xl font-semibold text-slate-100">Cek status laporan</h1>
                    <p className="text-sm text-slate-400 mt-1">
                        Masukkan nomor tiket Anda untuk melihat status pemrosesan.
                    </p>
                </div>

                <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30 space-y-3">
                    <label className="block text-xs text-slate-300">Nomor tiket</label>
                    <div className="flex gap-2">
                        <input
                            type="text"
                            value={ticketId}
                            onChange={(e) => setTicketId(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && doFetch()}
                            placeholder="Contoh: OZJ-20260310-1234"
                            className="flex-1 bg-slate-950/80 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                        />
                        <button
                            type="button"
                            onClick={() => doFetch()}
                            disabled={loading}
                            className="rounded-lg bg-gradient-to-r from-amber-400 to-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 shadow-lg hover:brightness-110 transition disabled:opacity-60"
                        >
                            {loading ? 'Memuat...' : 'Cek'}
                        </button>
                    </div>
                </div>

                {error && searched && (
                    <div className="rounded-lg bg-rose-950/40 border border-rose-800/60 px-4 py-3 text-sm text-rose-200">
                        {error}
                    </div>
                )}

                {data && (
                    <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30 space-y-3">
                        <div className="flex items-center justify-between border-b border-slate-700/80 pb-2 gap-2">
                            <span className="text-xs text-slate-400">Nomor tiket</span>
                            <span className="font-mono text-sm font-medium text-amber-300 truncate">{data.ticket_id}</span>
                            <button
                                type="button"
                                onClick={() => {
                                    const url = `${window.location.origin}/track/${encodeURIComponent(data.ticket_id)}`;
                                    navigator.clipboard.writeText(url).then(() => alert('Link disalin. Bagikan via WA untuk cek status kapan saja.'));
                                }}
                                className="shrink-0 text-xs text-amber-400 hover:text-amber-300"
                            >
                                Salin link
                            </button>
                        </div>
                        {data.judul_sinyal && (
                            <div>
                                <span className="text-xs text-slate-400">Judul</span>
                                <p className="text-sm text-slate-200 mt-0.5">{data.judul_sinyal}</p>
                            </div>
                        )}
                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <span className="text-xs text-slate-400">Status</span>
                                <p className="text-sm text-slate-200">{statusLabels[data.status_ticket] || data.status_ticket}</p>
                            </div>
                            <div>
                                <span className="text-xs text-slate-400">Urgensi</span>
                                <p className="text-sm text-slate-200">{data.status_eskalasi || data.urgensi_sinyal || '—'}</p>
                            </div>
                        </div>
                        {data.waktu_catat && (
                            <div>
                                <span className="text-xs text-slate-400">Waktu laporan</span>
                                <p className="text-sm text-slate-300">{new Date(data.waktu_catat).toLocaleString('id-ID')}</p>
                            </div>
                        )}
                        {data.nama_pelapor && (
                            <div>
                                <span className="text-xs text-slate-400">Pelapor</span>
                                <p className="text-sm text-slate-300">{data.nama_pelapor}</p>
                            </div>
                        )}
                    </div>
                )}
            </div>

            <p className="mt-6 text-xs text-slate-500">
                Halaman ini untuk pelapor. <Link to="/login" className="text-amber-400/80 hover:text-amber-300">Login admin</Link>
            </p>
        </div>
    );
}
