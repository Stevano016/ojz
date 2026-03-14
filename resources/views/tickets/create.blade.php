@extends('layouts.app')

@section('title', 'Tiket manual baru - ' . config('app.name'))

@section('content')
<div class="space-y-4">
    <h1 class="text-xl font-semibold text-slate-50">Input tiket manual baru</h1>
    <p class="text-sm text-slate-400 max-w-2xl">Gunakan form ini untuk mencatat sinyal yang datang dari kanal offline atau analisis manual yang belum terhubung ke n8n / Google Sheets.</p>

    @php $createSuccess = session('create_success'); @endphp
    @if($createSuccess)
        <div class="rounded-lg bg-emerald-950/50 border border-emerald-700/60 px-4 py-3 space-y-2">
            <p class="text-sm text-emerald-200">{{ $createSuccess['message'] ?? 'Laporan telah dikirim.' }}</p>
            @if(!empty($createSuccess['ticket_id']))
                <p class="text-xs text-emerald-300/90">Nomor tiket: <strong class="font-mono">{{ $createSuccess['ticket_id'] }}</strong></p>
            @endif
            <div class="flex flex-wrap gap-2 pt-1">
                @if(!empty($createSuccess['ticket_id']))
                    <a href="{{ route('track.show', $createSuccess['ticket_id']) }}" class="inline-flex items-center rounded-lg bg-emerald-600/80 hover:bg-emerald-500/80 px-3 py-1.5 text-xs font-medium text-white transition">Cek status laporan</a>
                @endif
                <a href="{{ route('tickets.index') }}" class="inline-flex items-center rounded-lg border border-slate-600 bg-slate-800/60 hover:bg-slate-700/60 px-3 py-1.5 text-xs text-slate-200 transition">Lihat daftar tiket</a>
                <a href="{{ route('tickets.create') }}" class="inline-flex items-center rounded-lg border border-slate-600 text-slate-400 hover:text-slate-200 px-3 py-1.5 text-xs transition">Laporkan lagi</a>
            </div>
        </div>
    @endif

    @if(!$createSuccess)
    <form method="POST" action="{{ route('tickets.store') }}" class="rounded-2xl bg-slate-900/70 border border-slate-800/80 p-4 md:p-5 shadow-xl shadow-black/30 space-y-4 text-xs">
        @csrf
        @if($errors->any())
            <div class="text-xs text-rose-300 bg-rose-950/40 border border-rose-900 rounded-md px-3 py-2">{{ $errors->first() }}</div>
        @endif
        <div class="grid md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <div>
                    <label class="block text-[11px] text-slate-300 mb-1">Judul sinyal *</label>
                    <input type="text" name="judul_sinyal" value="{{ old('judul_sinyal') }}" required
                        class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70" />
                </div>
                <div>
                    <label class="block text-[11px] text-slate-300 mb-1">Lokasi</label>
                    <input type="text" name="nama_lokasi" value="{{ old('nama_lokasi') }}"
                        class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70" />
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] text-slate-300 mb-1">Level sinyal *</label>
                        <select name="level_sinyal" required class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70">
                            <option value="Mikro" {{ old('level_sinyal', 'Mikro') === 'Mikro' ? 'selected' : '' }}>Mikro</option>
                            <option value="Meso" {{ old('level_sinyal') === 'Meso' ? 'selected' : '' }}>Meso</option>
                            <option value="Makro" {{ old('level_sinyal') === 'Makro' ? 'selected' : '' }}>Makro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-300 mb-1">Urgensi sinyal *</label>
                        <select name="urgensi_sinyal" required class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70">
                            <option value="Rendah" {{ old('urgensi_sinyal') === 'Rendah' ? 'selected' : '' }}>Rendah</option>
                            <option value="Sedang" {{ old('urgensi_sinyal', 'Sedang') === 'Sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="Tinggi" {{ old('urgensi_sinyal') === 'Tinggi' ? 'selected' : '' }}>Tinggi</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] text-slate-300 mb-1">Kategori sinyal</label>
                    <input type="text" name="kategori_sinyal" value="{{ old('kategori_sinyal') }}" placeholder="Contoh: Verwaarlozing, Akses Layanan"
                        class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70" />
                </div>
            </div>
            <div class="space-y-2">
                <div>
                    <label class="block text-[11px] text-slate-300 mb-1">Nama pelapor</label>
                    <input type="text" name="nama_pelapor" value="{{ old('nama_pelapor') }}"
                        class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70" />
                </div>
                <div>
                    <label class="block text-[11px] text-slate-300 mb-1">Skor urgensi hukum (0–100)</label>
                    <input type="number" name="skor_urgensi_hukum" min="0" max="100" value="{{ old('skor_urgensi_hukum', 50) }}"
                        class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70" />
                </div>
                <div>
                    <label class="block text-[11px] text-slate-300 mb-1">Deskripsi sinyal *</label>
                    <textarea name="deskripsi_sinyal" required rows="5" class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-400/70">{{ old('deskripsi_sinyal') }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
            <a href="{{ route('tickets.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-700 text-[11px] text-slate-300 hover:bg-slate-800/60">Batal</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-3.5 py-1.5 text-[11px] font-semibold text-slate-950 shadow-lg shadow-amber-500/40 hover:brightness-110 transition">Simpan tiket</button>
        </div>
    </form>
    @endif
</div>
@endsection
