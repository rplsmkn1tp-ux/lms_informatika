<div class="space-y-6">
    <div class="mb-4">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight" style="color: #181a20;">Absensi Kelas</h1>
        <p class="text-sm mt-1" style="color: #71717a;">Pilih kelas untuk mengelola absensi pertemuan.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($classrooms as $classroom)
        <div class="rounded-2xl p-5 shadow-sm flex flex-col space-y-4" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
            <div>
                <h2 class="text-lg font-bold" style="color: #181a20;">{{ $classroom->name }}</h2>
                <p class="text-sm" style="color: #71717a;">{{ $classroom->academicYear->name }}</p>
            </div>
            <div class="mt-auto pt-4 flex gap-2">
                <a href="{{ route('guru.absensi.session', $classroom->id) }}" wire:navigate class="w-full text-sm font-semibold py-2 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-sm" style="background-color: #2347C4; color: #ffffff; text-decoration: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    Kelola Absensi
                </a>
            </div>
        </div>
        @endforeach
        
        @if($classrooms->isEmpty())
        <div class="col-span-full rounded-2xl p-8 text-center shadow-sm" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3" style="color: #d4d4d8;"><path d="M4 22h14a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M2 15h10"></path><path d="M9 18l3-3-3-3"></path></svg>
            <h4 class="text-base font-bold" style="color: #181a20;">Belum ada kelas</h4>
            <p class="text-sm" style="color: #71717a;">Anda belum diplot untuk mengajar kelas manapun.</p>
        </div>
        @endif
    </div>
</div>
