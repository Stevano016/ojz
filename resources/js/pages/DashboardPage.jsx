import React, { useEffect, useState } from 'react';
import axios from 'axios';
import {
    Area,
    AreaChart,
    Bar,
    BarChart,
    CartesianGrid,
    Legend,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

function StatCard({ label, value, accent, large }) {
    return (
        <div
            className={`rounded-2xl bg-slate-900/70 border border-slate-800/80 px-4 py-3 flex items-center justify-between shadow-lg shadow-black/30 ${large ? 'md:px-5 md:py-4' : ''}`}
        >
            <div>
                <div className={`uppercase tracking-wide text-slate-400 ${large ? 'text-xs font-medium' : 'text-[11px]'}`}>
                    {label}
                </div>
                <div className={`mt-1 font-semibold text-slate-50 ${large ? 'text-2xl md:text-3xl' : 'text-xl'}`}>
                    {value}
                </div>
            </div>
            <div
                className={`rounded-xl bg-gradient-to-br ${accent} flex items-center justify-center ${large ? 'h-12 w-12' : 'h-9 w-9'}`}
            >
                <span className="h-2 w-2 rounded-full bg-slate-950/80" />
            </div>
        </div>
    );
}

export default function DashboardPage() {
    const [stats, setStats] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchStats = async () => {
            try {
            const res = await axios.get('/dashboard/stats');
                setStats(res.data);
            } finally {
                setLoading(false);
            }
        };
        fetchStats();
    }, []);

    if (loading || !stats) {
        return (
            <div className="text-sm text-slate-400">
                Memuat data dashboard dari tiket OZJ...
            </div>
        );
    }

    const urgencyData = Object.entries(stats.by_urgency || {}).map(([key, value]) => ({
        name: key,
        value,
    }));

    const statusData = [
        { name: 'Open', value: stats.summary.open },
        { name: 'In Progress', value: stats.summary.in_progress },
        { name: 'Resolved', value: stats.summary.resolved },
        { name: 'Closed', value: stats.summary.closed },
    ].filter((d) => d.value > 0);

    const levelData = Object.entries(stats.by_level || {}).map(([key, value]) => ({
        name: key,
        value,
    }));

    // Level sinyal dari spreadsheet: Mikro, Meso, Makro (kolom level_sinyal)
    const levelLaporanOrder = ['Mikro', 'Meso', 'Makro'];
    const levelLaporanDataAll = levelLaporanOrder.map((name) => ({
        name,
        value: stats.by_level_laporan?.[name] ?? 0,
    }));

    const ticketsByDate = (stats.tickets_by_date || [])
        .slice()
        .reverse()
        .map((item) => ({
            date: item.date,
            total: item.total,
        }));

    const mikro = stats.by_level_laporan?.Mikro ?? 0;
    const meso = stats.by_level_laporan?.Meso ?? 0;
    const makro = stats.by_level_laporan?.Makro ?? 0;

    return (
        <div className="space-y-5">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h1 className="text-xl md:text-2xl font-semibold text-slate-50">
                        Dashboard Sinyal & Tiket
                    </h1>
                    <p className="text-sm text-slate-400 mt-1 max-w-xl">
                        Ringkasan status sinyal AI, eskalasi, dan tiket lapangan untuk
                        mendukung keputusan cepat di Youth Care.
                    </p>
                </div>
            </div>

            {/* Level Sinyal — pokok dashboard (kolom level_sinyal dari spreadsheet) */}
            <div className="rounded-2xl border-2 border-amber-500/40 bg-gradient-to-br from-amber-950/20 via-slate-900/90 to-slate-900/90 p-4 md:p-5 shadow-xl shadow-amber-500/10">
                <div className="flex flex-wrap items-center gap-2 mb-3">
                    <span className="inline-flex items-center rounded-lg bg-amber-500/20 px-2 py-0.5 text-xs font-semibold text-amber-300 border border-amber-500/40">
                        Fokus utama
                    </span>
                    <h2 className="text-base md:text-lg font-semibold text-slate-100">
                        Level Sinyal
                    </h2>
                    <span className="text-[11px] text-slate-500">
                        Berdasarkan kolom level_sinyal di spreadsheet
                    </span>
                </div>
                <div className="grid grid-cols-3 gap-3 mb-4">
                    <StatCard
                        label="Mikro"
                        value={mikro}
                        accent="from-sky-400 to-sky-600"
                        large
                    />
                    <StatCard
                        label="Meso"
                        value={meso}
                        accent="from-amber-400 to-orange-500"
                        large
                    />
                    <StatCard
                        label="Makro"
                        value={makro}
                        accent="from-rose-400 to-rose-600"
                        large
                    />
                </div>
                <div className="h-44 md:h-52">
                    <ResponsiveContainer width="100%" height="100%">
                        <BarChart data={levelLaporanDataAll} margin={{ top: 8, right: 8, left: 8, bottom: 8 }}>
                            <CartesianGrid strokeDasharray="3 3" stroke="#334155" vertical={false} />
                            <XAxis
                                dataKey="name"
                                tick={{ fontSize: 12, fill: '#e2e8f0', fontWeight: 500 }}
                            />
                            <YAxis
                                tick={{ fontSize: 11, fill: '#94a3b8' }}
                                allowDecimals={false}
                            />
                            <Tooltip
                                contentStyle={{
                                    backgroundColor: '#0f172a',
                                    borderRadius: 12,
                                    border: '1px solid #f59e0b',
                                    fontSize: 12,
                                }}
                                formatter={(value) => [value, 'Tiket']}
                            />
                            <Bar dataKey="value" name="Tiket" fill="#f59e0b" radius={[8, 8, 0, 0]} />
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                <StatCard
                    label="Total Tickets"
                    value={stats.summary.total}
                    accent="from-emerald-400/90 to-emerald-500"
                />
                <StatCard
                    label="Open"
                    value={stats.summary.open}
                    accent="from-sky-400/90 to-sky-500"
                />
                <StatCard
                    label="KRITIS / Tinggi"
                    value={
                        (stats.by_urgency?.KRITIS || 0) + (stats.by_urgency?.Tinggi || 0)
                    }
                    accent="from-rose-400/90 to-rose-500"
                />
                <StatCard
                    label="Avg Skor Urgensi"
                    value={stats.summary.avg_score}
                    accent="from-amber-400/90 to-amber-500"
                />
            </div>

            <div className="grid md:grid-cols-3 gap-4">
                <div className="md:col-span-2 rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30">
                    <div className="flex items-center justify-between mb-3">
                        <div>
                            <div className="text-xs font-medium text-slate-300">
                                Volume tiket per hari
                            </div>
                            <div className="text-[11px] text-slate-500">
                                30 hari terakhir berdasarkan waktu catat
                            </div>
                        </div>
                    </div>
                    <div className="h-56">
                        <ResponsiveContainer width="100%" height="100%">
                            <AreaChart data={ticketsByDate}>
                                <defs>
                                    <linearGradient
                                        id="volGradient"
                                        x1="0"
                                        y1="0"
                                        x2="0"
                                        y2="1"
                                    >
                                        <stop
                                            offset="5%"
                                            stopColor="#38bdf8"
                                            stopOpacity={0.8}
                                        />
                                        <stop
                                            offset="95%"
                                            stopColor="#38bdf8"
                                            stopOpacity={0}
                                        />
                                    </linearGradient>
                                </defs>
                                <CartesianGrid
                                    strokeDasharray="3 3"
                                    stroke="#1f2937"
                                    vertical={false}
                                />
                                <XAxis
                                    dataKey="date"
                                    tick={{ fontSize: 10, fill: '#9ca3af' }}
                                />
                                <YAxis
                                    tick={{ fontSize: 10, fill: '#9ca3af' }}
                                    allowDecimals={false}
                                />
                                <Tooltip
                                    contentStyle={{
                                        backgroundColor: '#020617',
                                        borderRadius: 12,
                                        border: '1px solid #1f2937',
                                        fontSize: 11,
                                    }}
                                />
                                <Area
                                    type="monotone"
                                    dataKey="total"
                                    stroke="#38bdf8"
                                    fillOpacity={1}
                                    fill="url(#volGradient)"
                                />
                            </AreaChart>
                        </ResponsiveContainer>
                    </div>
                </div>

                <div className="space-y-4">
                    <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-3 shadow-xl shadow-black/30">
                        <div className="text-xs font-medium text-slate-300 mb-2">
                            Komposisi status tiket
                        </div>
                        <div className="h-40">
                            <ResponsiveContainer width="100%" height="100%">
                                <PieChart>
                                    <Pie
                                        data={statusData}
                                        dataKey="value"
                                        nameKey="name"
                                        outerRadius={60}
                                        innerRadius={28}
                                        paddingAngle={2}
                                    >
                                        {/* colors handled via Tooltip/Legend labels */}
                                    </Pie>
                                    <Tooltip
                                        contentStyle={{
                                            backgroundColor: '#020617',
                                            borderRadius: 12,
                                            border: '1px solid #1f2937',
                                            fontSize: 11,
                                        }}
                                    />
                                    <Legend
                                        verticalAlign="bottom"
                                        height={24}
                                        wrapperStyle={{ fontSize: 10, color: '#9ca3af' }}
                                    />
                                </PieChart>
                            </ResponsiveContainer>
                        </div>
                    </div>

                    <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-3 shadow-xl shadow-black/30">
                        <div className="text-xs font-medium text-slate-300 mb-2">
                            Ringkasan level sinyal
                        </div>
                        <div className="flex flex-col gap-2">
                            {levelLaporanDataAll.map((d) => (
                                <div key={d.name} className="flex items-center justify-between text-sm">
                                    <span className="text-slate-300">{d.name}</span>
                                    <span className="font-semibold text-slate-50">{d.value}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30">
                <div className="flex items-center justify-between mb-3">
                    <div>
                        <div className="text-xs font-medium text-slate-300">
                            Distribusi skor urgensi hukum
                        </div>
                        <div className="text-[11px] text-slate-500">
                            Bucket Normal, Sedang, Tinggi, KRITIS
                        </div>
                    </div>
                </div>
                <div className="h-48">
                    <ResponsiveContainer width="100%" height="100%">
                        <BarChart data={stats.score_distribution}>
                            <CartesianGrid
                                strokeDasharray="3 3"
                                stroke="#1f2937"
                                vertical={false}
                            />
                            <XAxis
                                dataKey="range_label"
                                tick={{ fontSize: 10, fill: '#9ca3af' }}
                            />
                            <YAxis
                                tick={{ fontSize: 10, fill: '#9ca3af' }}
                                allowDecimals={false}
                            />
                            <Tooltip
                                contentStyle={{
                                    backgroundColor: '#020617',
                                    borderRadius: 12,
                                    border: '1px solid #1f2937',
                                    fontSize: 11,
                                }}
                            />
                            <Legend
                                wrapperStyle={{ fontSize: 10, color: '#9ca3af' }}
                                formatter={(value) => (
                                    <span className="text-slate-300">{value}</span>
                                )}
                            />
                            <Bar dataKey="total" name="Tickets" fill="#f97316" />
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            </div>
        </div>
    );
}

