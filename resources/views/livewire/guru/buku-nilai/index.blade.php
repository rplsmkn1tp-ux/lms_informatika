<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight" style="color: #181a20;">Buku Nilai</h1>
            <p class="text-sm mt-1" style="color: #71717a;">Pilih kelas untuk mengelola buku nilai (Tugas, UH, UAS/MID).</p>
        </div>
        <div class="flex gap-2">
            <select wire:model.live="selectedYear" class="w-48 rounded-xl shadow-sm px-4 py-2 focus:ring-0" style="background-color: #ffffff; color: #181a20; border: 1px solid #e4e4e7;">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->name }} - {{ ucfirst($year->semester) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if($classrooms->isEmpty())
        <div class="rounded-2xl shadow-sm border p-8" style="background-color: #ffffff; border-color: #e4e4e7;">
            <div class="text-center py-8" style="color: #71717a;">
                <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #a1a1aa;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                <p class="font-medium">Anda belum mengampu kelas pada tahun ajaran ini.</p>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($classrooms as $classroom)
                <div class="rounded-2xl shadow-sm border flex flex-col p-6" style="background-color: #ffffff; border-color: #e4e4e7;">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold mb-1" style="color: #181a20;">{{ $classroom->name }}</h3>
                        <p class="text-sm mb-4" style="color: #71717a;">{{ $classroom->major }} • Kelas {{ $classroom->grade }}</p>
                        
                        <div class="flex items-center gap-2 text-sm mb-2" style="color: #52525b;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span class="font-medium">{{ $classroom->students()->count() }} Siswa</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t" style="border-color: #e4e4e7;">
                        <a href="{{ route('guru.buku-nilai.spreadsheet', $classroom) }}" wire:navigate class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold flex items-center justify-center transition-colors shadow-sm cursor-pointer text-center" style="background-color: #2347C4; color: #ffffff; text-decoration: none;">
                            Buka Buku Nilai
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
