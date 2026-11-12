<div>
    <x-slot:title>Kelas</x-slot>
    <x-slot:breadcrumbs>PANEL ADMIN · KELAS</x-slot>
    <x-slot:description>Kelas di semester aktif. Bagikan kode gabung ke siswa agar mereka terdaftar di kelas yang benar saat membuat akun.</x-slot>
    
    <x-slot:headerActions>
        <label class="flex items-center gap-2 text-sm font-semibold">
            Semester
            <select onchange="Livewire.dispatch('updateAcademicYear', { id: this.value })" class="h-11 px-3 border border-zinc-300 rounded-xl bg-white font-inherit text-sm text-zinc-900 focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none transition-shadow">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ $selectedAcademicYear == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                @endforeach
            </select>
        </label>
        <x-admin.button variant="primary" onclick="Livewire.dispatch('createClassroom')">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
            Tambah kelas
        </x-admin.button>
    </x-slot:headerActions>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-center gap-3">
            <svg class="w-5 h-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="M22 4L12 14.01l-3-3"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-3">
            <svg class="w-5 h-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-start">
        @forelse($classrooms as $classroom)
            <x-admin.card class="p-5 flex flex-col gap-4">
                <div class="flex justify-between items-start gap-3">
                    <div class="flex flex-col gap-0.5">
                        <h2 class="text-[22px] font-extrabold leading-tight">{{ $classroom->grade }} {{ $classroom->major ?: $classroom->name }}</h2>
                        <span class="text-[13px] text-zinc-600">Kelas {{ $classroom->grade }} &middot; {{ $classroom->name }}</span>
                    </div>
                    <span class="flex-none inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-zinc-100 text-[13px] font-bold whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3"></circle><path d="M3 20a6 6 0 0 1 12 0"></path><circle cx="17" cy="9" r="2.5"></circle><path d="M16 14.5a5 5 0 0 1 5 5.5"></path></svg>
                        {{ $classroom->students_count }} siswa
                    </span>
                </div>

                <dl class="flex flex-col gap-2 text-sm m-0">
                    <div class="flex gap-2.5 items-center">
                        <dt class="w-[120px] flex-none text-zinc-600">Guru Informatika</dt>
                        <dd class="m-0 font-semibold">
                            @php
                                $teacherAssignment = App\Models\TeachingAssignment::with('teacher')->where('classroom_id', $classroom->id)->first();
                            @endphp
                            @if($teacherAssignment && $teacherAssignment->teacher)
                                {{ $teacherAssignment->teacher->name }}
                            @else
                                <a href="{{ route('admin.teaching-assignments') }}" class="inline-flex items-center gap-1.5 text-amber-700 font-bold hover:text-amber-900 transition-colors">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l9 16H3z"></path><path d="M12 10v4M12 17h.01"></path></svg>
                                    Belum ada guru &middot; Atur plot
                                </a>
                            @endif
                        </dd>
                    </div>
                    <div class="flex gap-2.5 items-center">
                        <dt class="w-[120px] flex-none text-zinc-600">Target mingguan</dt>
                        <dd class="m-0 font-semibold">{{ $classroom->weekly_lesson_target }} subbab</dd>
                    </div>
                </dl>

                <div class="p-3.5 rounded-xl bg-zinc-50 flex flex-col gap-2.5">
                    <div class="flex justify-between items-center gap-2">
                        <span class="font-mono text-[11px] font-semibold tracking-widest text-zinc-500">KODE GABUNG</span>
                        @if($classroom->join_code_active)
                            <x-admin.badge variant="success">Aktif</x-admin.badge>
                        @else
                            <x-admin.badge variant="neutral">Kode nonaktif</x-admin.badge>
                        @endif
                    </div>
                    
                    <span class="font-mono text-2xl font-semibold tracking-wide {{ $classroom->join_code_active ? 'text-zinc-900' : 'text-zinc-400' }}">
                        {{ $classroom->join_code }}
                    </span>

                    <div class="flex flex-wrap gap-1.5">
                        <x-admin.button variant="secondary" onclick="navigator.clipboard.writeText('{{ $classroom->join_code }}'); alert('Kode disalin!')">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="12" height="12" rx="2"></rect><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"></path></svg>
                            Salin
                        </x-admin.button>
                        
                        <x-admin.button variant="secondary" wire:click="regenerateJoinCode({{ $classroom->id }})" wire:confirm="Yakin ingin membuat ulang kode gabung? Kode lama tidak akan berlaku lagi.">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11a8 8 0 0 0-14.9-4M4 4v4h4"></path><path d="M4 13a8 8 0 0 0 14.9 4M20 20v-4h-4"></path></svg>
                            Buat ulang
                        </x-admin.button>

                        @if($classroom->join_code_active)
                            <x-admin.button variant="secondary" wire:click="toggleJoinCodeActive({{ $classroom->id }})">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M6 6l12 12"></path></svg>
                                Nonaktifkan kode
                            </x-admin.button>
                        @else
                            <x-admin.button variant="primary" wire:click="toggleJoinCodeActive({{ $classroom->id }})">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v8"></path><path d="M6.5 7a8 8 0 1 0 11 0"></path></svg>
                                Aktifkan kode
                            </x-admin.button>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-1.5 pt-1 border-t border-zinc-100">
                    <div class="flex flex-wrap gap-1.5 pt-2.5">
                        <x-admin.button variant="secondary" wire:click="viewMembers({{ $classroom->id }})">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3"></circle><path d="M3 20a6 6 0 0 1 12 0"></path><circle cx="17" cy="9" r="2.5"></circle><path d="M16 14.5a5 5 0 0 1 5 5.5"></path></svg>
                            Anggota
                        </x-admin.button>
                        <x-admin.button variant="secondary" wire:click="edit({{ $classroom->id }})">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9l-4-4L4 16z"></path></svg>
                            Ubah
                        </x-admin.button>
                        
                        @if($classroom->students_count > 0)
                            <x-admin.button variant="secondary" disabled title="Tidak bisa dihapus selama masih ada siswa" aria-label="Tidak bisa dihapus">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"></path></svg>
                                Hapus
                            </x-admin.button>
                        @else
                            <x-admin.button variant="danger" wire:click="deleteClass({{ $classroom->id }})" wire:confirm="Yakin ingin menghapus kelas ini?">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"></path></svg>
                                Hapus
                            </x-admin.button>
                        @endif
                    </div>
                    @if($classroom->students_count > 0)
                        <span class="text-xs text-zinc-500">Tidak bisa dihapus selama masih ada siswa.</span>
                    @endif
                </div>
            </x-admin.card>
        @empty
            <div class="col-span-full">
                <button type="button" wire:click.prevent="create" class="w-full min-h-[220px] rounded-2xl border-2 border-dashed border-zinc-300 bg-transparent text-zinc-700 font-inherit text-[15px] font-bold flex flex-col items-center justify-center gap-2.5 cursor-pointer hover:border-[#2347C4] hover:text-[#2347C4] transition-colors">
                    <span class="w-12 h-12 rounded-full bg-[#E8EDFC] text-[#2347C4] flex items-center justify-center">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                    </span>
                    Tambah kelas baru
                </button>
            </div>
        @endforelse
    </div>

    <!-- Modal Form Kelas -->
    <div x-data="{ show: @entangle('showModal').live }" 
         x-show="show" 
         x-cloak 
         style="display: none;"
         class="relative z-50">
        
        <div x-show="show" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm transition-opacity"></div>
        
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="show" 
                 @click.outside="$wire.set('showModal', false)"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col relative z-50">
                <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-zinc-900">{{ $isEdit ? 'Ubah Kelas' : 'Tambah Kelas' }}</h2>
                    <button wire:click="$set('showModal', false)" class="text-zinc-500 hover:text-zinc-900 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form wire:submit.prevent="save" class="p-5 flex flex-col gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-900">Tahun Ajaran</label>
                        <select wire:model="academic_year_id" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                            <option value="">Pilih Tahun Ajaran</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                        @error('academic_year_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-900">Nama Kelas / Rombel</label>
                        <input type="text" wire:model="name" placeholder="Misal: X RPL 1" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                        @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex gap-4">
                        <div class="flex flex-col gap-1.5 flex-1">
                            <label class="text-sm font-semibold text-zinc-900">Tingkat (Grade)</label>
                            <select wire:model="grade" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                                <option value="">Pilih</option>
                                <option value="10">10 (X)</option>
                                <option value="11">11 (XI)</option>
                                <option value="12">12 (XII)</option>
                            </select>
                            @error('grade') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="flex flex-col gap-1.5 flex-1">
                            <label class="text-sm font-semibold text-zinc-900">Jurusan</label>
                            <input type="text" wire:model="major" placeholder="Opsional" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                            @error('major') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-900">Target Belajar Mingguan (Subbab)</label>
                        <input type="number" min="1" wire:model="weekly_lesson_target" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                        @error('weekly_lesson_target') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-zinc-100">
                        <x-admin.button type="button" variant="secondary" wire:click="$set('showModal', false)">Batal</x-admin.button>
                        <x-admin.button type="submit" variant="primary">Simpan</x-admin.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Anggota Kelas -->
    <div x-data="{ show: @entangle('showMembersModal').live }" 
         x-show="show" 
         x-cloak 
         style="display: none;"
         class="relative z-50">
         
        <div x-show="show" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm transition-opacity"></div>
        
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="show" 
                 @click.outside="$wire.set('showMembersModal', false)"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="bg-white rounded-2xl w-full max-w-2xl max-h-[80vh] shadow-2xl flex flex-col relative z-50">
                <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between shrink-0">
                    <h2 class="text-lg font-bold text-zinc-900">Anggota Kelas {{ $selectedClassroom ? $selectedClassroom->name : '' }}</h2>
                    <button wire:click="$set('showMembersModal', false)" class="text-zinc-500 hover:text-zinc-900 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="overflow-y-auto p-5">
                    @if($selectedClassroom && $selectedClassroom->students->isEmpty())
                        <div class="text-center text-zinc-500 py-8">
                            Belum ada siswa yang bergabung di kelas ini.
                        </div>
                    @elseif($selectedClassroom)
                        <div class="border border-zinc-200 rounded-xl overflow-hidden">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-zinc-50 text-zinc-600 text-[13px] uppercase tracking-wider font-semibold border-b border-zinc-200">
                                    <tr>
                                        <th class="px-4 py-3">Nama</th>
                                        <th class="px-4 py-3">NISN</th>
                                        <th class="px-4 py-3">Email</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200">
                                    @foreach($selectedClassroom->students as $student)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-zinc-900">{{ $student->name }}</td>
                                            <td class="px-4 py-3 font-mono text-[13px] text-zinc-600">{{ $student->nisn ?: '-' }}</td>
                                            <td class="px-4 py-3 text-zinc-600">{{ $student->email }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                
                <div class="px-5 py-4 border-t border-zinc-200 flex justify-end shrink-0 bg-zinc-50 rounded-b-2xl">
                    <x-admin.button type="button" variant="primary" wire:click="$set('showMembersModal', false)">Tutup</x-admin.button>
                </div>
            </div>
        </div>
    </div>
</div>
