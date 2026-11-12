<div>
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Agenda Guru</h1>
            <p class="text-sm text-gray-600 mt-1">Daftar agenda dan topik pembelajaran yang telah disampaikan.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="w-full md:w-1/3">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari topik pembelajaran..." class="w-full rounded-lg px-3 py-2 text-sm shadow-sm" style="border: 1px solid #e4e4e7;">
            </div>
            <div class="flex flex-col md:flex-row w-full md:w-auto gap-4">
                <select wire:model.live="filterClassroom" class="rounded-lg px-3 py-2 text-sm shadow-sm" style="border: 1px solid #e4e4e7;">
                    <option value="">Semua Kelas</option>
                    @foreach($classrooms as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2">
                    <input type="date" wire:model.live="dateStart" class="rounded-lg px-3 py-2 text-sm shadow-sm" style="border: 1px solid #e4e4e7;">
                    <span class="text-gray-500 text-sm">s/d</span>
                    <input type="date" wire:model.live="dateEnd" class="rounded-lg px-3 py-2 text-sm shadow-sm" style="border: 1px solid #e4e4e7;">
                </div>
                @if($search || $filterClassroom || $dateStart || $dateEnd)
                    <button wire:click="resetFilters" class="px-3 py-2 text-sm text-red-600 font-medium hover:bg-red-50 rounded-lg transition-colors">
                        Reset
                    </button>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b border-gray-200">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal & Pertemuan</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Topik Kegiatan / Materi</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Rekap Absensi</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($agendas as $agenda)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $agenda->meeting_date->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">Pertemuan ke-{{ $agenda->meeting_no }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium" style="background-color: #E8EDFC; color: #2347C4;">
                                    {{ $agenda->classroom->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 line-clamp-2 max-w-md" title="{{ $agenda->topic }}">
                                    {{ $agenda->topic ?: '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $h = $agenda->records->where('status', 'hadir')->count();
                                    $i = $agenda->records->where('status', 'izin')->count();
                                    $s = $agenda->records->where('status', 'sakit')->count();
                                    $a = $agenda->records->where('status', 'alpa')->count();
                                @endphp
                                <div class="flex items-center justify-center gap-1.5 text-xs font-medium">
                                    <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded">H:{{ $h }}</span>
                                    <span class="text-yellow-600 bg-yellow-50 px-1.5 py-0.5 rounded">I:{{ $i }}</span>
                                    <span class="text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded">S:{{ $s }}</span>
                                    <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded">A:{{ $a }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('guru.absensi.form', ['classroom' => $agenda->classroom_id, 'sessionId' => $agenda->id]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition-colors" style="background-color: #2347C4; color: #FFFFFF;" onmouseover="this.style.backgroundColor='#1A3596'" onmouseout="this.style.backgroundColor='#2347C4'">
                                    Detail / Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500 text-sm">
                                Belum ada agenda yang tersimpan. Agenda akan otomatis dibuat saat Anda menyimpan absensi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($agendas->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $agendas->links() }}
            </div>
        @endif
    </div>
</div>
