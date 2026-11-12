<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kode Kelas: ') }} {{ $classroom->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8 text-center border-t-4 border-indigo-500">
                <h3 class="text-2xl font-bold text-gray-700 mb-2">Kode Pendaftaran Kelas</h3>
                <p class="text-gray-500 mb-8">Berikan kode ini kepada siswa agar mereka dapat bergabung ke kelas {{ $classroom->name }}.</p>
                
                <div class="flex justify-center items-center space-x-4 mb-8">
                    <div class="bg-gray-100 border-2 border-gray-300 rounded-lg px-8 py-4 text-5xl font-mono tracking-widest text-indigo-600 font-bold select-all" id="joinCodeText">
                        {{ $classroom->join_code }}
                    </div>
                    
                    <button onclick="navigator.clipboard.writeText('{{ $classroom->join_code }}'); alert('Kode kelas berhasil disalin!');" 
                            class="p-4 bg-indigo-100 text-indigo-600 rounded-lg hover:bg-indigo-200 transition-colors"
                            title="Salin Kode">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                        </svg>
                    </button>
                </div>

                <div class="flex justify-center gap-4">
                    <button type="button" wire:click="regenerateJoinCode" 
                            onclick="confirm('Yakin ingin mereset kode? Kode lama tidak akan bisa dipakai lagi.') || event.stopImmediatePropagation()" 
                            class="px-4 py-2 text-sm font-semibold rounded-xl transition-colors shadow-sm cursor-pointer"
                            style="background-color: #ffffff; color: #18181b; border: 1px solid #e4e4e7;">
                        Buat Ulang Kode
                    </button>

                    <button type="button" wire:click="toggleJoinCodeActive" 
                            class="px-4 py-2 text-sm font-semibold rounded-xl transition-colors shadow-sm cursor-pointer"
                            style="background-color: {{ $classroom->join_code_active ? '#dc2626' : '#2347C4' }}; color: #ffffff; border: none;">
                        {{ $classroom->join_code_active ? 'Nonaktifkan Pendaftaran' : 'Aktifkan Pendaftaran' }}
                    </button>
                </div>
            </div>

            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border" style="border-color: #e4e4e7;">
                <h4 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Informasi Kelas</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 block">Tahun Ajaran</span>
                        <span class="font-medium">{{ $classroom->academicYear->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Tingkat / Jurusan</span>
                        <span class="font-medium">Kelas {{ $classroom->grade }} {{ $classroom->major }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Status Pendaftaran</span>
                        <span class="font-medium inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classroom->join_code_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $classroom->join_code_active ? 'Aktif' : 'Ditutup' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Jumlah Siswa</span>
                        <span class="font-medium">{{ $classroom->students_count }} Siswa</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
