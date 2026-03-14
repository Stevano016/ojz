@extends('layouts.app')

@section('title', $ticket->ticket_id . ' - ' . config('app.name'))

@section('content')
<div class="space-y-4">
    <a href="{{ route('tickets.index') }}" class="inline-block text-[11px] text-slate-400 hover:text-slate-200">← Kembali ke daftar</a>

    @if(session('sheet_synced') === false)
        <div class="rounded-xl border border-amber-500/50 bg-amber-500/10 px-3 py-2 text-[11px] text-amber-200 space-y-1">
            <div>Status sudah tersimpan di database. Sinkron ke Google Sheets gagal.</div>
            @if(session('sheet_sync_error'))
                <div class="mt-1 font-mono text-[10px] text-amber-300/90 break-all">{{ session('sheet_sync_error') }}</div>
            @else
                <div class="text-amber-200/80">Cek .env (GOOGLE_SHEETS_SPREADSHEET_ID, credentials) dan share spreadsheet ke email service account.</div>
            @endif
        </div>
    @endif

    <div class="grid md:grid-cols-3 gap-4">
        <div class="md:col-span-2 space-y-3">
            <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30">
                <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                    <div>
                        <div class="text-xs font-mono text-slate-400">{{ $ticket->ticket_id }}</div>
                        <h1 class="text-lg font-semibold text-slate-50 mt-1">{{ $ticket->judul_sinyal }}</h1>
                        <div class="text-[11px] text-slate-400 mt-1">
                            @if($ticket->nama_lokasi)<span>{{ $ticket->nama_lokasi }} · </span>@endif
                            <span>{{ $ticket->level_sinyal ?? 'Level tidak tercatat' }}</span>
                        </div>
                    </div>
                    <div class="text-right text-[11px] text-slate-400">
                        <div>Skor urgensi hukum: <span class="text-amber-300 font-semibold">{{ $ticket->skor_urgensi_hukum ?? '-' }}</span></div>
                        <div>Status eskalasi: <span class="text-rose-300 font-semibold">{{ $ticket->status_eskalasi }}</span></div>
                        <div>Sumber: <span class="uppercase">{{ $ticket->jenis_sumber ?? '-' }}</span></div>
                    </div>
                </div>
                <div class="mt-3 space-y-2 text-sm text-slate-200">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase">Deskripsi sinyal</div>
                    <p class="text-[13px] leading-relaxed text-slate-100">{{ $ticket->deskripsi_sinyal }}</p>
                </div>
                @if($ticket->risiko_teridentifikasi)
                    <div class="mt-3">
                        <div class="text-[11px] font-semibold text-slate-400 uppercase">Risiko teridentifikasi</div>
                        <p class="text-[13px] leading-relaxed text-slate-100 mt-1">{{ $ticket->risiko_teridentifikasi }}</p>
                    </div>
                @endif
                @if($ticket->rekomendasi_ai)
                    <div class="mt-3">
                        <div class="text-[11px] font-semibold text-emerald-300 uppercase">Rekomendasi AI</div>
                        <p class="text-[13px] leading-relaxed text-slate-100 mt-1">{{ $ticket->rekomendasi_ai }}</p>
                    </div>
                @endif
                @if($ticket->dasar_hukum)
                    <div class="mt-3 text-[11px] text-slate-400">Dasar hukum: <span class="text-slate-200">{{ $ticket->dasar_hukum }}</span></div>
                @endif
            </div>

            <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30">
                <div class="text-xs font-medium text-slate-300 mb-3">Riwayat aksi & progres</div>
                @php $actions = $ticket->actions->sortByDesc('created_at'); @endphp
                @if($actions->count())
                    <ul class="space-y-2 text-xs">
                        @foreach($actions as $a)
                            <li class="rounded-xl border border-slate-800/80 bg-slate-900/70 px-3 py-2">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-medium text-slate-100">{{ $a->action_type }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $a->created_at?->format('d/m/Y H:i') }}</div>
                                </div>
                                <p class="text-[11px] text-slate-200 mt-1">{{ $a->description }}</p>
                                @if($a->old_status && $a->new_status)
                                    <div class="mt-1 text-[10px] text-slate-400">Status: {{ $a->old_status }} → <span class="text-emerald-300">{{ $a->new_status }}</span></div>
                                @endif
                                @if($a->user)
                                    <div class="mt-1 text-[10px] text-slate-500">Oleh: <span class="text-slate-300">{{ $a->user->name }}</span></div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-xs text-slate-500">Belum ada aksi tercatat untuk tiket ini.</div>
                @endif
            </div>
        </div>

        <div class="space-y-3">
            <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30">
                <div class="text-xs font-medium text-slate-300 mb-2">Ubah status tiket</div>
                <form method="POST" action="{{ route('tickets.update', $ticket->id) }}" class="space-y-2 text-xs">
                    @csrf
                    @method('PUT')
                    <select name="status_ticket" class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70">
                        @foreach(['Open', 'In Progress', 'Resolved', 'Closed'] as $s)
                            <option value="{{ $s }}" {{ $ticket->status_ticket === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg bg-emerald-500/90 px-3 py-1.5 text-[11px] font-semibold text-slate-950 shadow-md shadow-emerald-500/40 hover:brightness-110 transition">Simpan status</button>
                </form>
            </div>

            <div class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 shadow-xl shadow-black/30">
                <div class="text-xs font-medium text-slate-300 mb-2">Tambah aksi / progres</div>
                <form method="POST" action="{{ route('tickets.actions', $ticket->id) }}" class="space-y-2 text-xs">
                    @csrf
                    <input type="hidden" name="old_status" value="{{ $ticket->status_ticket }}" />
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Jenis aksi</label>
                        <input type="text" name="action_type" required placeholder="Contoh: kontak Veilig Thuis"
                            class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70" />
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Deskripsi singkat</label>
                        <textarea name="description" required rows="3" class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70"></textarea>
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Update status (opsional)</label>
                        <select name="new_status" class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70">
                            <option value="">Tidak mengubah status</option>
                            @foreach(['Open', 'In Progress', 'Resolved', 'Closed'] as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-3 py-1.5 text-[11px] font-semibold text-slate-950 shadow-md shadow-amber-500/40 hover:brightness-110 transition">Simpan aksi</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
