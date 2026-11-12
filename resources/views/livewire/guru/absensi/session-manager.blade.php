<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight" style="color: #181a20;">Absensi: {{ $classroom->name }}</h1>
            <p class="text-sm mt-1" style="color: #71717a;">Kelola sesi pertemuan dan absensi siswa.</p>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <a href="{{ route('guru.absensi.form', [$classroom->id, 'new']) }}" wire:navigate class="flex-1 sm:flex-none text-sm font-semibold py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-sm" style="background-color: #2347C4; color: #ffffff; text-decoration: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Buat Pertemuan
            </a>
            <a href="{{ route('guru.absensi.index') }}" wire:navigate class="flex-1 sm:flex-none text-sm font-semibold py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-sm" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #18181b; text-decoration: none;">
                Kembali
            </a>
        </div>
    </div>

    <div class="rounded-2xl shadow-sm overflow-hidden" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr style="background-color: #fafafa; border-bottom: 1px solid #e4e4e7; color: #71717a;">
                        <th class="px-5 py-3 font-semibold">Pertemuan Ke-</th>
                        <th class="px-5 py-3 font-semibold">Tanggal</th>
                        <th class="px-5 py-3 font-semibold">Topik</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold text-center">Jml Siswa</th>
                        <th class="px-5 py-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="divide-color: #e4e4e7;">
                    @forelse($sessions as $session)
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-5 py-4 font-medium" style="color: #181a20;">{{ $session->meeting_no }}</td>
                            <td class="px-5 py-4" style="color: #3f3f46;">{{ $session->meeting_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-4" style="color: #3f3f46;">{{ $session->topic ?? '-' }}</td>
                            <td class="px-5 py-4">
                                @if($session->status === 'open')
                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold inline-block" style="background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;">Terbuka</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold inline-block" style="background-color: #f4f4f5; color: #52525b; border: 1px solid #e4e4e7;">Ditutup</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center" style="color: #3f3f46;">{{ $session->records_count }}</td>
                            <td class="px-5 py-4">
                                <a href="{{ route('guru.absensi.form', [$classroom->id, $session->id]) }}" wire:navigate class="text-xs font-semibold py-1.5 px-3 rounded-lg flex items-center justify-center gap-1.5 transition-colors shadow-sm border inline-flex" style="background-color: #ffffff; border-color: #e4e4e7; color: #27272a; text-decoration: none;">
                                    @if($session->status === 'open')
                                        Isi Absen
                                    @else
                                        Lihat/Ubah
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center" style="color: #71717a;">Belum ada sesi pertemuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
