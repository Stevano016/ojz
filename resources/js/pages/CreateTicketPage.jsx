import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from 'axios';

export default function CreateTicketPage() {
    const navigate = useNavigate();
    const [form, setForm] = useState({
        judul_sinyal: '',
        deskripsi_sinyal: '',
        level_sinyal: 'Mikro',
        urgensi_sinyal: 'Sedang',
        kategori_sinyal: '',
        nama_pelapor: '',
        nama_lokasi: '',
        skor_urgensi_hukum: 50,
    });
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(null);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm((f) => ({ ...f, [name]: value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        setError('');
        try {
            const res = await axios.post('/tickets', form);
            // 202 Accepted: laporan dikirim ke n8n, data akan muncul setelah tersimpan di spreadsheet
            if (res.status === 202) {
                const ticketId = res.data.ticket_id;
                const msg = res.data.message || 'Laporan telah dikirim. Data akan muncul di daftar setelah diproses.';
                setSuccess({ message: msg, ticket_id: ticketId });
                return;
            }
            // Fallback 201 (jika backend berubah)
            if (res.data?.id) navigate(`/tickets/${res.data.id}`);
            else navigate('/tickets');
        } catch (err) {
            const msg = err.response?.data?.message || 'Gagal mengirim laporan. Pastikan field wajib terisi.';
            setError(typeof msg === 'string' ? msg : 'Gagal mengirim laporan.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <div className="space-y-4">
            <h1 className="text-xl font-semibold text-slate-50">
                Input tiket manual baru
            </h1>
            <p className="text-sm text-slate-400 max-w-2xl">
                Gunakan form ini untuk mencatat sinyal yang datang dari kanal offline atau
                analisis manual yang belum terhubung ke n8n / Google Sheets.
            </p>

            <form
                onSubmit={handleSubmit}
                className="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 md:p-5 shadow-xl shadow-black/30 space-y-4 text-xs"
            >
                <div className="grid md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <div>
                            <label className="block text-[11px] text-slate-300 mb-1">
                                Judul sinyal *
                            </label>
                            <input
                                name="judul_sinyal"
                                value={form.judul_sinyal}
                                onChange={handleChange}
                                className="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-[11px] text-slate-300 mb-1">
                                Lokasi
                            </label>
                            <input
                                name="nama_lokasi"
                                value={form.nama_lokasi}
                                onChange={handleChange}
                                className="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <label className="block text-[11px] text-slate-300 mb-1">
                                    Level sinyal *
                                </label>
                                <select
                                    name="level_sinyal"
                                    value={form.level_sinyal}
                                    onChange={handleChange}
                                    className="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                                    required
                                >
                                    <option value="Mikro">Mikro</option>
                                    <option value="Meso">Meso</option>
                                    <option value="Makro">Makro</option>
                                </select>
                            </div>
                            <div>
                                <label className="block text-[11px] text-slate-300 mb-1">
                                    Urgensi sinyal *
                                </label>
                                <select
                                    name="urgensi_sinyal"
                                    value={form.urgensi_sinyal}
                                    onChange={handleChange}
                                    className="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                                    required
                                >
                                    <option value="Rendah">Rendah</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Tinggi">Tinggi</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label className="block text-[11px] text-slate-300 mb-1">
                                Kategori sinyal
                            </label>
                            <input
                                name="kategori_sinyal"
                                value={form.kategori_sinyal}
                                onChange={handleChange}
                                className="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                                placeholder="Contoh: Verwaarlozing, Akses Layanan, Bahaya Akut"
                            />
                        </div>
                    </div>
                    <div className="space-y-2">
                        <div>
                            <label className="block text-[11px] text-slate-300 mb-1">
                                Nama pelapor
                            </label>
                            <input
                                name="nama_pelapor"
                                value={form.nama_pelapor}
                                onChange={handleChange}
                                className="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                            />
                        </div>
                        <div>
                            <label className="block text-[11px] text-slate-300 mb-1">
                                Skor urgensi hukum (0–100)
                            </label>
                            <input
                                type="number"
                                min="0"
                                max="100"
                                name="skor_urgensi_hukum"
                                value={form.skor_urgensi_hukum}
                                onChange={handleChange}
                                className="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70"
                            />
                        </div>
                        <div>
                            <label className="block text-[11px] text-slate-300 mb-1">
                                Deskripsi sinyal *
                            </label>
                            <textarea
                                name="deskripsi_sinyal"
                                value={form.deskripsi_sinyal}
                                onChange={handleChange}
                                className="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70 min-h-[110px]"
                                required
                            />
                        </div>
                    </div>
                </div>

                {error && (
                    <div className="text-xs text-rose-300 bg-rose-950/40 border border-rose-900 rounded-md px-3 py-2">
                        {error}
                    </div>
                )}

                {success && (
                    <div className="rounded-lg bg-emerald-950/50 border border-emerald-700/60 px-4 py-3 space-y-2">
                        <p className="text-sm text-emerald-200">{success.message}</p>
                        {success.ticket_id && (
                            <p className="text-xs text-emerald-300/90">
                                Nomor tiket: <strong className="font-mono">{success.ticket_id}</strong>
                            </p>
                        )}
                        <div className="flex flex-wrap gap-2 pt-1">
                            {success.ticket_id && (
                                <Link
                                    to={`/track?ticket_id=${encodeURIComponent(success.ticket_id)}`}
                                    className="inline-flex items-center rounded-lg bg-emerald-600/80 hover:bg-emerald-500/80 px-3 py-1.5 text-xs font-medium text-white transition"
                                >
                                    Cek status laporan
                                </Link>
                            )}
                            <button
                                type="button"
                                onClick={() => navigate('/tickets')}
                                className="inline-flex items-center rounded-lg border border-slate-600 bg-slate-800/60 hover:bg-slate-700/60 px-3 py-1.5 text-xs text-slate-200 transition"
                            >
                                Lihat daftar tiket
                            </button>
                            <button
                                type="button"
                                onClick={() => { setSuccess(null); setForm({ judul_sinyal: '', deskripsi_sinyal: '', level_sinyal: 'Mikro', urgensi_sinyal: 'Sedang', kategori_sinyal: '', nama_pelapor: '', nama_lokasi: '', skor_urgensi_hukum: 50 }); }}
                                className="inline-flex items-center rounded-lg border border-slate-600 text-slate-400 hover:text-slate-200 px-3 py-1.5 text-xs transition"
                            >
                                Laporkan lagi
                            </button>
                        </div>
                    </div>
                )}

                {!success && (
                <div className="flex items-center justify-end gap-2 pt-2">
                    <button
                        type="button"
                        onClick={() => navigate('/tickets')}
                        className="px-3 py-1.5 rounded-lg border border-slate-700 text-[11px] text-slate-300 hover:bg-slate-800/60"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={saving}
                        className="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-3.5 py-1.5 text-[11px] font-semibold text-slate-950 shadow-lg shadow-amber-500/40 hover:brightness-110 transition disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        {saving ? 'Menyimpan...' : 'Simpan tiket'}
                    </button>
                </div>
                )}
            </form>
        </div>
    );
}

