<div>
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('guru.dashboard') }}" wire:navigate class="inline-flex items-center justify-center p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors" title="Kembali ke Dashboard">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight" style="color: #181a20;">Jadwal Mengajar</h1>
            </div>
            <p class="text-sm mt-1" style="color: #71717a;">Kelola dan lihat jadwal mengajar Anda pekan ini</p>
        </div>
        
        <div class="flex bg-white rounded-lg p-1 shadow-sm" style="border: 1px solid #e4e4e7;">
            <button wire:click="$set('activeTab', 'jadwal')" class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors {{ $activeTab === 'jadwal' ? 'bg-[#2347C4] text-white shadow' : 'text-[#71717a] hover:text-[#181a20]' }}">
                Lihat Jadwal
            </button>
            <button wire:click="$set('activeTab', 'tambah')" class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors {{ $activeTab === 'tambah' ? 'bg-[#2347C4] text-white shadow' : 'text-[#71717a] hover:text-[#181a20]' }}">
                Tambah Manual
            </button>
            <button wire:click="$set('activeTab', 'import')" class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors {{ $activeTab === 'import' ? 'bg-[#2347C4] text-white shadow' : 'text-[#71717a] hover:text-[#181a20]' }}">
                Import / Export CSV
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 rounded-xl flex items-start gap-3" style="background-color: #dcfce7; border: 1px solid #bbf7d0; color: #166534;">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <p class="font-semibold text-sm">{{ session('message') }}</p>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 rounded-xl flex items-start gap-3" style="background-color: #fee2e2; border: 1px solid #fecaca; color: #991b1b;">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <p class="font-semibold text-sm">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($activeTab === 'jadwal')
        <style>
            .modern-jadwal { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; border-radius: 12px; overflow: hidden; border: 1px solid #e4e4e7; }
            .modern-jadwal th, .modern-jadwal td { padding: 8px 12px; border-right: 1px solid #e4e4e7; border-bottom: 1px solid #e4e4e7; vertical-align: top; }
            .modern-jadwal th { background-color: #f8fafc; font-weight: 600; text-align: center; color: #334155; text-transform: uppercase; font-size: 12px; letter-spacing: 0.05em; padding: 12px; }
            .modern-jadwal td:last-child, .modern-jadwal th:last-child { border-right: none; }
            .modern-jadwal tr:last-child td { border-bottom: none; }
            .modern-jadwal .waktu { background-color: #f1f5f9; font-weight: 600; white-space: nowrap; text-align: center; color: #475569; width: 100px; vertical-align: middle; }
            .modern-jadwal .istirahat { background-color: #f3f4f6; color: #9ca3af; font-weight: 700; letter-spacing: 4px; text-align: center; vertical-align: middle; font-size: 14px; }
            .modern-jadwal .skj { background-color: #ffedd5; color: #c2410c; font-weight: bold; text-align: center; vertical-align: middle; }
            .modern-jadwal .cell-card { background-color: #eff6ff; border-left: 3px solid #3b82f6; padding: 8px; border-radius: 6px; display: flex; flex-direction: column; gap: 4px; min-height: 60px; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
            .modern-jadwal .cell-card .class-name { font-weight: 700; color: #1e3a8a; font-size: 13px; }
            .modern-jadwal .cell-card .subject { font-weight: 500; color: #2563eb; font-size: 11px; }
            .modern-jadwal .cell-card .code { background-color: #bfdbfe; color: #1e3a8a; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; align-self: flex-start; }
            .modern-jadwal .empty-cell { background-color: #fafafa; }
        </style>

        <div class="bg-white p-6 rounded-2xl shadow-sm mb-6" style="border: 1px solid #e4e4e7;">
            <div class="text-center mb-6">
                <h2 class="font-bold text-xl text-[#181a20]">JADWAL PENGGUNAAN LAB TI (JADWAL MENGAJAR)</h2>
                <h3 class="font-semibold text-md text-[#71717a] mt-1">SMK NEGERI 1 TANJUNGPANDAN</h3>
            </div>
            
            @php
            $cell = function($day, $slot) use ($matrix) {
                if ($matrix[$day][$slot]) {
                    $m = $matrix[$day][$slot];
                    $entryId = $m['id'] ?? null;
                    
                    $actionBtns = "";
                    if ($entryId) {
                        $actionBtns = "
                        <div class=\"absolute top-1 right-1 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity\">
                            <button wire:click=\"editEntry({$entryId})\" class=\"text-blue-500 hover:text-blue-700 bg-white/80 rounded p-0.5\" title=\"Edit\">
                                <svg width=\"14\" height=\"14\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7\"></path><path d=\"M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z\"></path></svg>
                            </button>
                            <button wire:click=\"deleteEntry({$entryId})\" wire:confirm=\"Yakin ingin menghapus jadwal ini?\" class=\"text-red-500 hover:text-red-700 bg-white/80 rounded p-0.5\" title=\"Hapus\">
                                <svg width=\"14\" height=\"14\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><line x1=\"18\" y1=\"6\" x2=\"6\" y2=\"18\"></line><line x1=\"6\" y1=\"6\" x2=\"18\" y2=\"18\"></line></svg>
                            </button>
                        </div>";
                    }
                    
                    return "<td class='relative group'>
                        <div class='cell-card relative'>
                            {$actionBtns}
                            <span class='class-name'>{$m['class']}</span>
                            <span class='subject'>{$m['subject']}</span>
                            <span class='code'>Guru: {$m['code']}</span>
                        </div>
                    </td>";
                }
                return "<td class='empty-cell'></td>";
            };
            @endphp
            
            <div class="overflow-x-auto pb-4">
                <table class="modern-jadwal min-w-[800px]">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Senin</th>
                            <th>Selasa</th>
                            <th>Rabu</th>
                            <th>Kamis</th>
                            <th style="border-left: 2px solid #cbd5e1;">Waktu (Jumat)</th>
                            <th>Jumat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row 1 -->
                        <tr>
                            <td class="waktu">07.00 - 07.45</td>
                            <td class="skj"><div class="py-2">Upacara Bendera</div></td>
                            {!! $cell(2, 1) !!}
                            {!! $cell(3, 1) !!}
                            {!! $cell(4, 1) !!}
                            <td class="waktu" style="border-left: 2px solid #cbd5e1;">07.00 - 07.45</td>
                            <td class="skj"><div class="py-2">SKJ</div></td>
                        </tr>
                        
                        <!-- Row 2 -->
                        <tr>
                            <td class="waktu">07.45 - 08.30</td>
                            {!! $cell(1, 2) !!}
                            {!! $cell(2, 2) !!}
                            {!! $cell(3, 2) !!}
                            {!! $cell(4, 2) !!}
                            <td class="waktu" style="border-left: 2px solid #cbd5e1;">07.45 - 08.30</td>
                            {!! $cell(5, 2) !!}
                        </tr>

                        <!-- Row 3 -->
                        <tr>
                            <td class="waktu">08.30 - 09.15</td>
                            {!! $cell(1, 3) !!}
                            {!! $cell(2, 3) !!}
                            {!! $cell(3, 3) !!}
                            {!! $cell(4, 3) !!}
                            <td class="waktu" style="border-left: 2px solid #cbd5e1;">08.30 - 09.15</td>
                            {!! $cell(5, 3) !!}
                        </tr>

                        <!-- Row 4 -->
                        <tr>
                            <td class="waktu">09.15 - 10.00</td>
                            {!! $cell(1, 4) !!}
                            {!! $cell(2, 4) !!}
                            {!! $cell(3, 4) !!}
                            {!! $cell(4, 4) !!}
                            <td class="waktu" style="border-left: 2px solid #cbd5e1;">09.15 - 09.30</td>
                            <td class="istirahat" style="font-size: 11px; letter-spacing: 1px;">ISTIRAHAT</td>
                        </tr>

                        <!-- Row 5 -->
                        <tr>
                            <td class="waktu">10.00 - 10.15</td>
                            <td colspan="4" class="istirahat">ISTIRAHAT</td>
                            <td class="waktu" style="border-left: 2px solid #cbd5e1;">09.30 - 10.15</td>
                            {!! $cell(5, 4) !!}
                        </tr>

                        <!-- Row 6 -->
                        <tr>
                            <td class="waktu">10.15 - 11.00</td>
                            {!! $cell(1, 5) !!}
                            {!! $cell(2, 5) !!}
                            {!! $cell(3, 5) !!}
                            {!! $cell(4, 5) !!}
                            <td class="waktu" style="border-left: 2px solid #cbd5e1;">10.15 - 11.00</td>
                            {!! $cell(5, 5) !!}
                        </tr>

                        <!-- Row 7 -->
                        <tr>
                            <td class="waktu">11.00 - 11.45</td>
                            {!! $cell(1, 6) !!}
                            {!! $cell(2, 6) !!}
                            {!! $cell(3, 6) !!}
                            {!! $cell(4, 6) !!}
                            <td colspan="2" rowspan="7" style="border-left: 2px solid #cbd5e1; background-color: #f8fafc; text-align: center; vertical-align: middle;">
                                <div class="text-[#94a3b8] flex flex-col items-center justify-center gap-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    <span class="text-sm font-medium">Akhir Pekan</span>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 8 -->
                        <tr>
                            <td class="waktu">11.45 - 12.30</td>
                            <td colspan="4" class="istirahat">ISHOMA</td>
                        </tr>

                        <!-- Row 9 -->
                        <tr>
                            <td class="waktu">12.30 - 13.15</td>
                            {!! $cell(1, 7) !!}
                            {!! $cell(2, 7) !!}
                            {!! $cell(3, 7) !!}
                            {!! $cell(4, 7) !!}
                        </tr>

                        <!-- Row 10 -->
                        <tr>
                            <td class="waktu">13.15 - 14.00</td>
                            {!! $cell(1, 8) !!}
                            {!! $cell(2, 8) !!}
                            {!! $cell(3, 8) !!}
                            {!! $cell(4, 8) !!}
                        </tr>

                        <!-- Row 11 -->
                        <tr>
                            <td class="waktu">14.00 - 14.45</td>
                            {!! $cell(1, 9) !!}
                            {!! $cell(2, 9) !!}
                            {!! $cell(3, 9) !!}
                            {!! $cell(4, 9) !!}
                        </tr>

                        <!-- Row 12 -->
                        <tr>
                            <td class="waktu">14.45 - 15.30</td>
                            {!! $cell(1, 10) !!}
                            {!! $cell(2, 10) !!}
                            {!! $cell(3, 10) !!}
                            {!! $cell(4, 10) !!}
                        </tr>

                        <!-- Row 13 -->
                        <tr>
                            <td class="waktu">15.30 - 16.15</td>
                            {!! $cell(1, 11) !!}
                            {!! $cell(2, 11) !!}
                            {!! $cell(3, 11) !!}
                            {!! $cell(4, 11) !!}
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button wire:click="exportJadwal" class="px-4 py-2 bg-white rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors shadow-sm" style="border: 1px solid #e4e4e7; color: #181a20;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export Jadwal (CSV)
            </button>
        </div>

    @elseif($activeTab === 'import')
        <div class="bg-white rounded-2xl shadow-sm p-6" style="border: 1px solid #e4e4e7;">
            <h2 class="text-lg font-bold mb-4" style="color: #181a20;">Import Jadwal (Ms. Excel / CSV)</h2>
            <p class="text-sm text-[#71717a] mb-6">
                Anda dapat mengimpor jadwal mengajar massal menggunakan file CSV. Gunakan Ms. Excel untuk menyimpan tabel Anda dalam format <strong>CSV (Comma delimited) (*.csv)</strong>.
            </p>
            
            <div class="mb-6 p-4 rounded-xl" style="background-color: #f8fafc; border: 1px dashed #cbd5e1;">
                <h3 class="font-semibold text-sm mb-2" style="color: #334155;">Instruksi:</h3>
                <ol class="list-decimal pl-5 text-sm space-y-1" style="color: #475569;">
                    <li>Unduh template CSV yang disediakan.</li>
                    <li>Isi data jadwal. Gunakan <strong>ID Kelas</strong> yang tertera pada tabel di bawah.</li>
                    <li>Simpan kembali sebagai CSV.</li>
                    <li>Unggah file CSV Anda ke form ini.</li>
                </ol>
                <div class="mt-4">
                    <button wire:click="downloadTemplate" class="px-3 py-1.5 bg-white text-xs font-semibold rounded shadow-sm flex items-center gap-2 transition-colors" style="border: 1px solid #cbd5e1; color: #0f172a;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Unduh Template CSV
                    </button>
                </div>
            </div>

            <form wire:submit.prevent="import" class="space-y-4 max-w-lg">
                <div>
                    <label class="block text-sm font-medium mb-1" style="color: #181a20;">File CSV</label>
                    <input type="file" wire:model="file" accept=".csv,.txt" class="block w-full text-sm text-[#71717a] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#eef2ff] file:text-[#4f46e5] hover:file:bg-[#e0e7ff]"/>
                    @error('file') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 rounded-xl text-sm font-bold inline-flex items-center gap-2 transition-colors shadow-sm" style="background-color: #2347C4; color: #ffffff; border: none;">
                    <span wire:loading.remove wire:target="import">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    </span>
                    <span wire:loading wire:target="import" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                    Import Data
                </button>
            </form>
            
            <div class="mt-8 pt-6 border-t" style="border-color: #e4e4e7;">
                <h3 class="font-semibold text-sm mb-3" style="color: #181a20;">Daftar ID Kelas Anda (Untuk Referensi)</h3>
                <div class="overflow-x-auto rounded-xl border" style="border-color: #e4e4e7;">
                    <table class="w-full text-sm text-left">
                        <thead style="background-color: #f8fafc; color: #475569;">
                            <tr>
                                <th class="px-4 py-2 border-b">ID Kelas</th>
                                <th class="px-4 py-2 border-b">Nama Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr class="border-b last:border-0" style="border-color: #e4e4e7;">
                                    <td class="px-4 py-2 font-mono text-xs">{{ $assignment->classroom_id }}</td>
                                    <td class="px-4 py-2">{{ $assignment->classroom->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-4 text-center text-[#71717a]">Belum ada kelas yang ditugaskan kepada Anda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif($activeTab === 'tambah')
        <div class="bg-white p-6 rounded-2xl shadow-sm mb-6" style="border: 1px solid #e4e4e7;">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-lg" style="color: #181a20;">{{ $isEditMode ? 'Edit Jadwal' : 'Tambah Jadwal Manual' }}</h2>
                @if($isEditMode)
                    <button wire:click="cancelEdit" class="text-sm text-gray-500 hover:text-gray-700 underline">Batal Edit</button>
                @endif
            </div>
            
            <form wire:submit.prevent="tambahManual" class="space-y-4 max-w-lg">
                <div>
                    <label class="block text-sm font-medium mb-1" style="color: #181a20;">Kelas</label>
                    <select wire:model="formClassId" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #181a20;">
                        <option value="">Pilih Kelas</option>
                        @foreach($assignments as $assignment)
                            <option value="{{ $assignment->classroom_id }}">{{ $assignment->classroom->name }}</option>
                        @endforeach
                    </select>
                    @error('formClassId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1" style="color: #181a20;">Hari</label>
                    <select wire:model="formDayOfWeek" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #181a20;">
                        <option value="">Pilih Hari</option>
                        <option value="1">Senin</option>
                        <option value="2">Selasa</option>
                        <option value="3">Rabu</option>
                        <option value="4">Kamis</option>
                        <option value="5">Jumat</option>
                        <option value="6">Sabtu</option>
                        <option value="7">Minggu</option>
                    </select>
                    @error('formDayOfWeek') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: #181a20;">Jam Mulai (JP ke-)</label>
                        <input type="number" wire:model="formStartPeriod" min="1" max="12" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #181a20;" placeholder="Contoh: 1">
                        @error('formStartPeriod') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: #181a20;">Jam Selesai (JP ke-)</label>
                        <input type="number" wire:model="formEndPeriod" min="1" max="12" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #181a20;" placeholder="Contoh: 3">
                        @error('formEndPeriod') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1" style="color: #181a20;">Ruangan (Opsional)</label>
                    <input type="text" wire:model="formRoom" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #181a20;" placeholder="Contoh: Lab Komputer">
                    @error('formRoom') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="pt-2">
                    <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 rounded-xl text-sm font-bold inline-flex items-center gap-2 transition-colors shadow-sm" style="background-color: #2347C4; color: #ffffff; border: none;">
                        <span wire:loading.remove wire:target="tambahManual">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </span>
                        <span wire:loading wire:target="tambahManual" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                        {{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Jadwal' }}
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
