<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight" style="color: #181a20;">{{ $mode === 'create' ? 'Buat Sesi Absensi' : 'Kelola Absensi' }}</h1>
            <p class="text-sm mt-1" style="color: #71717a;">Kelas: {{ $classroom->name }}</p>
        </div>
        <a href="{{ route('guru.absensi.session', $classroom->id) }}" wire:navigate class="text-sm font-semibold py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-sm" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #18181b; text-decoration: none;">
            Batal
        </a>
    </div>

    @if (session('status'))
        <div class="px-4 py-3 rounded-xl text-sm font-semibold mb-4" style="background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="rounded-2xl p-5 shadow-sm" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1" style="color: #181a20;">Tanggal Pertemuan</label>
                    <input type="date" wire:model="meeting_date" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #181a20;">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" style="color: #181a20;">Pertemuan Ke-</label>
                    <input type="number" wire:model="meeting_no" min="1" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #181a20;">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1" style="color: #181a20;">Topik (Opsional)</label>
                    <input type="text" wire:model="topic" placeholder="Misal: Pengenalan Jaringan" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #181a20;">
                </div>
            </div>

            @if($mode === 'edit_closed')
                <div class="mb-4 p-4 rounded-lg" style="background-color: #fef2f2; border: 1px solid #fecaca;">
                    <h3 class="text-sm font-bold mb-2" style="color: #b91c1c;">Sesi telah ditutup</h3>
                    <label class="block text-sm font-medium mb-1" style="color: #991b1b;">Alasan Perubahan</label>
                    <textarea wire:model="edit_reason" placeholder="Wajib diisi mengapa mengubah data yang sudah ditutup" required class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #ffffff; border: 1px solid #fca5a5; color: #7f1d1d;"></textarea>
                </div>
            @endif
        </div>

        <div class="rounded-2xl p-5 shadow-sm" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold" style="color: #181a20;">Daftar Kehadiran Siswa</h2>
                <button type="button" wire:click="markAllPresent" class="text-xs font-semibold py-1.5 px-3 rounded-lg shadow-sm cursor-pointer flex items-center gap-1.5" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #27272a;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Tandai Semua Hadir
                </button>
            </div>
            
            <div class="space-y-4">
                @foreach($students as $student)
                    <div class="flex flex-col md:flex-row md:items-center gap-4 p-4 rounded-xl" style="background-color: #fafafa; border: 1px solid #f4f4f5;">
                        <div class="w-full md:w-1/3">
                            <div class="font-bold flex items-center gap-2" style="color: #181a20;">
                                {{ $student->name }}
                                @if(isset($percentages[$student->id]) && $percentages[$student->id] < 75)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold" style="background-color: #fef2f2; color: #b91c1c;">Perlu perhatian</span>
                                @endif
                            </div>
                            <div class="text-xs font-mono mt-0.5" style="color: #71717a;">
                                {{ $student->nisn }} &bull; {{ $percentages[$student->id] ?? 100 }}% Kehadiran
                            </div>
                        </div>
                        <div class="w-full md:w-1/3">
                            <div class="flex rounded-lg overflow-hidden border" style="border-color: #e4e4e7;" x-data="{ status: @entangle('attendance.'.$student->id) }">
                                @php
                                    $options = [
                                        'hadir' => ['label' => 'H', 'bg' => '#16a34a', 'text' => '#ffffff'], // green-600
                                        'izin' => ['label' => 'I', 'bg' => '#eab308', 'text' => '#ffffff'], // yellow-500
                                        'sakit' => ['label' => 'S', 'bg' => '#f97316', 'text' => '#ffffff'], // orange-500
                                        'alpa' => ['label' => 'A', 'bg' => '#ef4444', 'text' => '#ffffff'], // red-500
                                    ];
                                @endphp
                                @foreach($options as $val => $opt)
                                    <label class="flex-1 text-center py-2 cursor-pointer transition-colors text-sm font-semibold border-r last:border-r-0" 
                                           style="border-color: #e4e4e7;"
                                           x-bind:style="status === '{{ $val }}' ? 'background-color: {{ $opt['bg'] }}; color: {{ $opt['text'] }};' : 'background-color: #ffffff; color: #71717a;'">
                                        <input type="radio" x-model="status" value="{{ $val }}" class="sr-only">
                                        {{ $opt['label'] }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="w-full md:w-1/3">
                            <input type="text" wire:model="notes.{{ $student->id }}" placeholder="Catatan (opsional)..." class="w-full rounded-lg px-3 py-2 text-sm shadow-sm" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #181a20;">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex flex-wrap gap-3 justify-end mt-6">
            @if($mode !== 'edit_closed' && ($session?->status ?? 'open') === 'open')
                <button type="button" wire:click="closeSession" class="text-sm font-semibold py-2.5 px-5 rounded-xl shadow-sm cursor-pointer flex items-center gap-2" style="background-color: #ef4444; color: #ffffff; border: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Simpan & Tutup Pertemuan
                </button>
            @endif
            <button type="submit" class="text-sm font-semibold py-2.5 px-6 rounded-xl shadow-sm cursor-pointer" style="background-color: #2347C4; color: #ffffff; border: none;">
                Simpan
            </button>
        </div>
    </form>
</div>
