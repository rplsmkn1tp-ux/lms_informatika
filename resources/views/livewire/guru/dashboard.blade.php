<div>
    <style>
        .guru-grid-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            align-items: flex-start;
        }
        @media (min-width: 1024px) {
            .guru-grid-container {
                flex-direction: row;
            }
        }
        .guru-grid-left {
            flex: 1 1 auto;
            width: 100%;
        }
        .guru-grid-right {
            width: 100%;
            flex-shrink: 0;
        }
        @media (min-width: 1024px) {
            .guru-grid-right {
                width: 18rem; /* lg:w-72 */
            }
        }
        @media (min-width: 1280px) {
            .guru-grid-right {
                width: 20rem; /* xl:w-80 */
            }
        }
        
        .guru-hero-container {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            justify-content: space-between;
        }
        @media (min-width: 768px) {
            .guru-hero-container {
                flex-direction: row;
            }
        }
        
        .guru-kelas-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .guru-kelas-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        
        .guru-jadwal-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
        @media (min-width: 768px) {
            .guru-jadwal-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }
        
        .guru-tindakan-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
    </style>

    <!-- Header Text -->
    <div class="mb-4">
        <p class="text-[10px] font-semibold tracking-widest uppercase mb-1" style="color: #71717a;">
            {{ $currentDate }}@if(!empty($academicYear)) &bull; {{ $academicYear }}@endif
        </p>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight" style="color: #181a20;">{{ $greetingTitle }}</h1>
    </div>

    <!-- Main Grid: Left Content (Hero, Kelas, Jadwal) & Right Content (Sidebar Widgets) -->
    <div class="guru-grid-container">
        <!-- Left Content -->
        <div class="guru-grid-left space-y-6">
            
            <!-- Hero Banner: Pelajaran Berikutnya -->
            <div class="guru-hero-container rounded-2xl p-6 sm:p-8 relative overflow-hidden" style="background-color: #181a20; color: #ffffff;">
                <div class="z-10 flex-1">
                    @php
                        $activeItem = $todaySchedule[$activeScheduleIndex] ?? null;
                    @endphp

                    @if($activeItem)
                        <p class="text-xs font-bold tracking-widest uppercase mb-3" style="color: #facc15;">{{ $activeItem['status'] === 'Selesai' ? 'Sesi Selesai' : 'Pelajaran ' . $activeItem['status'] }}</p>
                        <h2 class="text-2xl sm:text-3xl font-bold mb-2">{{ $activeItem['class'] }} &bull; Informatika</h2>
                        <p class="text-sm mb-4" style="color: #d4d4d8;">{{ $activeItem['time'] }} &bull; {{ $activeItem['jp'] }} JP &bull; {{ $activeItem['topic'] }}</p>
                        
                        @if($activeItem['has_attendance'])
                            <p class="text-sm mb-6" style="color: #a1a1aa;">Kehadiran: {{ $activeItem['attendance_summary'] }}</p>
                        @else
                            <p class="text-sm mb-6" style="color: #a1a1aa;">Pertemuan ini belum dicatat kehadirannya.</p>
                        @endif
                        
                        <div class="flex flex-wrap gap-3">
                            @if($activeItem['has_attendance'])
                                <a href="{{ $activeItem['topic_link'] }}" wire:navigate class="font-semibold py-2.5 px-5 rounded-lg text-sm flex items-center gap-2 transition-colors border-0 cursor-pointer no-underline" style="background-color: #facc15; color: #181a20;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    Lihat absensi
                                </a>
                            @else
                                <a href="{{ route('guru.absensi.form', [$activeItem['class_id'], 'new']) }}?topic={{ urlencode($activeItem['topic']) }}" wire:navigate class="font-semibold py-2.5 px-5 rounded-lg text-sm flex items-center gap-2 transition-colors border-0 cursor-pointer no-underline" style="background-color: #facc15; color: #181a20;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    Buat pertemuan absensi
                                </a>
                            @endif

                            @if($activeItem['topic'] !== 'Belum ada materi')
                                <a href="{{ $activeItem['topic_link'] }}" wire:navigate class="bg-transparent border font-medium py-2.5 px-5 rounded-lg text-sm flex items-center gap-2 transition-colors cursor-pointer no-underline" style="border: 1px solid #52525b; color: #ffffff;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                                    Buka materi
                                </a>
                            @else
                                <a href="{{ route('guru.lessons') }}" wire:navigate class="bg-transparent border font-medium py-2.5 px-5 rounded-lg text-sm flex items-center gap-2 transition-colors cursor-pointer no-underline" style="border: 1px solid #52525b; color: #ffffff;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                    Pilih materi
                                </a>
                            @endif
                        </div>
                    @else
                        <p class="text-xs font-bold tracking-widest uppercase mb-3" style="color: #a1a1aa;">Jadwal Mengajar</p>
                        <h2 class="text-2xl sm:text-3xl font-bold mb-2">Tidak ada jadwal hari ini</h2>
                        <p class="text-sm mb-6" style="color: #d4d4d8;">Selamat beristirahat atau persiapkan materi untuk hari esok.</p>
                        <a href="{{ route('guru.jadwal') }}" wire:navigate class="font-semibold py-2.5 px-5 rounded-lg text-sm flex items-center gap-2 transition-colors border-0 cursor-pointer no-underline w-max" style="background-color: #f4f4f5; color: #181a20;">
                            Lihat Jadwal Penuh
                        </a>
                    @endif
                </div>
                
                <!-- Schedule snippet on right of banner -->
                <div class="z-10 shrink-0" style="width: 100%; max-width: 16rem;">
                    <div class="flex justify-between items-center text-xs mb-3 uppercase tracking-wider font-semibold" style="color: #a1a1aa;">
                        <span>Jadwal hari ini &bull; {{ $todayName }}</span>
                        <span>{{ count($todaySchedule) }} sesi</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($todaySchedule as $idx => $entry)
                            @php
                                $isActive = ($idx === $activeScheduleIndex);
                            @endphp
                            <div wire:click="$set('activeScheduleIndex', {{ $idx }})" class="border rounded-lg p-3 flex justify-between items-center cursor-pointer transition-all {{ $isActive ? '' : 'opacity-70 hover:opacity-100' }}" style="border: 1px solid {{ $isActive ? 'rgba(250, 204, 21, 0.3)' : '#3f3f46' }}; background-color: {{ $isActive ? 'rgba(250, 204, 21, 0.1)' : 'transparent' }};">
                                <div>
                                    <div class="{{ $isActive ? 'font-semibold text-sm' : 'text-sm' }}" style="color: {{ $isActive ? '#facc15' : '#d4d4d8' }};">{{ $entry['time'] }}</div>
                                    <div class="text-white text-sm font-bold mt-0.5" style="color: #ffffff;">{{ $entry['class'] }}</div>
                                </div>
                                <span class="text-xs font-medium" style="color: {{ $isActive ? '#facc15' : '#71717a' }};">{{ $entry['status'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Kelas yang diampu -->
            <div>
                <h3 class="text-lg font-bold mb-1" style="color: #181a20;">Kelas yang diampu</h3>
                <p class="text-xs mb-4" style="color: #71717a;">Informatika &bull; {{ $stats['total_kelas'] }} kelas &bull; Semester Ganjil 2026/2027</p>
                
                <div class="guru-kelas-grid">
                    @forelse ($classrooms as $classroom)
                        <div class="rounded-2xl p-5 shadow-sm" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-lg font-bold" style="color: #181a20;">{{ $classroom->name }}</h4>
                                    <p class="text-xs mt-0.5" style="color: #71717a;">{{ $classroom->students_count }} siswa &bull; Senin</p>
                                </div>
                                <div class="text-xs px-2 py-1 rounded flex items-center gap-1 font-mono" style="background-color: #f4f4f5; border: 1px solid #e4e4e7; color: #3f3f46;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    {{ $classroom->join_code }}
                                </div>
                            </div>
                            
                            <!-- Dummy Progress -->
                            <div class="mb-4">
                                <div class="flex justify-between items-end mb-1.5">
                                    <p class="text-xs leading-tight w-2/3" style="color: #52525b;">BK-5 Sistem Komputer &bull; 3 dari 5 subbab terbit</p>
                                    <span class="text-sm font-bold" style="color: #181a20;">60%</span>
                                </div>
                                <div class="w-full rounded-full h-1.5" style="background-color: #f4f4f5;">
                                    <div class="h-1.5 rounded-full" style="background-color: #2563eb; width: 60%;"></div>
                                </div>
                            </div>
                            
                            <!-- Dummy Kehadiran -->
                            <div class="flex justify-between items-center mb-5 pb-4 border-b" style="border-color: #f4f4f5;">
                                <p class="text-xs" style="color: #71717a;">Kehadiran pekan ini</p>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold" style="color: #181a20;">94%</span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" style="background-color: #dcfce7; color: #15803d;">Baik</span>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 flex-wrap">
                                @php
                                    $openSession = \App\Models\AttendanceSession::where('classroom_id', $classroom->id)->where('status', 'open')->first();
                                    $absensiRoute = $openSession 
                                        ? route('guru.absensi.form', [$classroom->id, $openSession->id])
                                        : route('guru.absensi.form', [$classroom->id, 'new']);
                                @endphp
                                <a href="{{ $absensiRoute }}" wire:navigate class="flex-1 text-xs font-semibold py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 transition-colors" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #27272a; text-decoration: none;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    Absensi
                                </a>
                                <a href="{{ route('guru.buku-nilai.spreadsheet', $classroom->id) }}" wire:navigate class="flex-1 text-xs font-semibold py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 transition-colors" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #27272a; text-decoration: none;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                                    Buku Nilai
                                </a>
                                <a href="{{ route('guru.peta-ketercapaian') }}" wire:navigate class="flex-1 text-xs font-semibold py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 transition-colors" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #27272a; text-decoration: none;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                    Progres
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full rounded-2xl p-8 text-center shadow-sm" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
                            <svg class="w-10 h-10 mx-auto mb-3" style="color: #d4d4d8;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>
                            <h4 class="text-base font-bold" style="color: #181a20;">Belum ada kelas</h4>
                            <p class="text-sm" style="color: #71717a;">Admin belum menetapkan jadwal kelas untuk Anda.</p>
                        </div>
                    @endforelse
                </div>
                <div class="mt-4 flex justify-end">
                    <button class="text-sm font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition-colors shadow-sm cursor-pointer" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #27272a;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        Lihat semua kelas
                    </button>
                </div>
            </div>

        </div>
        
        <!-- Right Sidebar Widgets -->
        <div class="guru-grid-right space-y-6">
            
            <!-- Jadwal Hari Ini -->
            <div class="rounded-2xl p-5 shadow-sm" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
                @php
                    $todayNum = now()->dayOfWeekIso;
                    $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                    $todayName = $dayNames[$todayNum] ?? 'Hari Ini';
                    $todaySchedule = $schedule[$todayNum] ?? [];
                @endphp
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold" style="color: #181a20;">Jadwal Hari Ini</h3>
                    <span class="text-[10px] font-mono tracking-widest uppercase font-bold" style="color: #2563eb;">{{ $todayName }}</span>
                </div>
                
                <div class="space-y-2">
                    @forelse($todaySchedule as $entry)
                        <div class="rounded-xl p-3 flex flex-col gap-1" style="background-color: #fafafa; border: 1px solid #f4f4f5; border-left: 3px solid #2563eb;">
                            <div class="flex justify-between items-start">
                                <div class="font-semibold text-xs" style="color: #2563eb;">{{ $entry['time'] }}</div>
                                <div class="text-[10px] px-1.5 py-0.5 rounded font-medium" style="background-color: #e2e8f0; color: #475569;">{{ $entry['jp'] }} JP</div>
                            </div>
                            <div class="text-sm font-bold" style="color: #181a20;">{{ $entry['class'] }}</div>
                        </div>
                    @empty
                        <div class="text-center py-5 rounded-xl" style="background-color: #fafafa; border: 1px dashed #e4e4e7;">
                            <p class="text-xs" style="color: #71717a;">Tidak ada jadwal mengajar hari ini.</p>
                        </div>
                    @endforelse
                </div>
                
                <div class="mt-4 pt-4 border-t" style="border-color: #f4f4f5;">
                    <a href="{{ route('guru.jadwal') }}" wire:navigate class="block w-full text-xs font-semibold py-2 px-3 rounded-lg text-center transition-colors shadow-sm cursor-pointer" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #27272a; text-decoration: none;">
                        Lihat Jadwal Penuh
                    </a>
                </div>
            </div>

            <!-- Perlu Tindakan -->
            <div class="rounded-2xl p-5 shadow-sm" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold" style="color: #181a20;">Perlu tindakan</h3>
                    <span class="text-[10px] font-mono tracking-widest uppercase" style="color: #a1a1aa;">Hari ini</span>
                </div>
                
                <div class="guru-tindakan-grid">
                    <div class="rounded-xl p-3" style="background-color: #fff7ed;">
                        <svg class="w-4 h-4 mb-2" style="color: #ea580c;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        <div class="text-xl font-bold mb-1" style="color: #181a20;">{{ $actionItems['tugas_perlu_dinilai'] }}</div>
                        <div class="text-[10px] leading-tight" style="color: #52525b;">tugas perlu dinilai</div>
                    </div>
                    <div class="rounded-xl p-3" style="background-color: #fef2f2;">
                        <svg class="w-4 h-4 mb-2" style="color: #dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        <div class="text-xl font-bold mb-1" style="color: #181a20;">{{ $actionItems['siswa_perlu_perhatian'] }}</div>
                        <div class="text-[10px] leading-tight" style="color: #52525b;">siswa perlu perhatian</div>
                    </div>
                    <div class="rounded-xl p-3" style="background-color: #eff6ff;">
                        <svg class="w-4 h-4 mb-2" style="color: #2563eb;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                        <div class="text-xl font-bold mb-1" style="color: #181a20;">{{ $actionItems['subbab_draf'] }}</div>
                        <div class="text-[10px] leading-tight" style="color: #52525b;">subbab masih draf</div>
                    </div>
                    <div class="rounded-xl p-3" style="background-color: #f4f4f5;">
                        <svg class="w-4 h-4 mb-2" style="color: #71717a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <div class="text-xl font-bold mb-1" style="color: #181a20;">{{ $actionItems['sumatif_belum_buka'] }}</div>
                        <div class="text-[10px] leading-tight" style="color: #52525b;">sumatif belum dibuka</div>
                    </div>
                </div>
            </div>

            <!-- Perlu Perhatian (Tidak Aktif 7 Hari) - Modified to keep previous requirement -->
            <div class="rounded-2xl p-5 shadow-sm" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold" style="color: #181a20;">Perlu perhatian</h3>
                    <span class="text-[10px] font-mono tracking-widest uppercase" style="color: #a1a1aa;">Tidak Aktif > 7 Hari</span>
                </div>
                
                <div class="space-y-2">
                    @forelse($inactiveStudents as $student)
                        <div class="rounded-xl p-3 flex flex-col gap-1.5" style="background-color: #fafafa; border: 1px solid #f4f4f5;">
                            <h4 class="text-xs font-bold" style="color: #181a20;">{{ $student->name }}</h4>
                            <div class="flex justify-between items-end">
                                <p class="text-[10px]" style="color: #71717a;">
                                    @if($student->classrooms->first())
                                        {{ $student->classrooms->first()->name }} &bull; 
                                    @endif
                                    {{ $student->last_login_at ? $student->last_login_at->diffForHumans() : 'Belum login' }}
                                </p>
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded" style="background-color: #fef2f2; color: #b91c1c;">Perlu perhatian</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <p class="text-xs" style="color: #71717a;">Semua siswa aktif dalam 7 hari terakhir.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Peta Akses Stats Summary (Kept as requested) -->
            <div class="rounded-2xl p-5 shadow-sm" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
                <h3 class="text-sm font-bold mb-4" style="color: #181a20;">Statistik Kelas</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs" style="color: #52525b;">Total Kelas</span>
                        <span class="text-sm font-bold" style="color: #181a20;">{{ $stats['total_kelas'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs" style="color: #52525b;">Total Siswa</span>
                        <span class="text-sm font-bold" style="color: #181a20;">{{ $stats['total_siswa'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs" style="color: #52525b;">Aktif 7 Hari</span>
                        <span class="text-sm font-bold" style="color: #16a34a;">{{ $stats['aktif_7_hari'] }} <span class="text-xs font-normal" style="color: #71717a;">({{ $stats['persentase_aktif'] }}%)</span></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
