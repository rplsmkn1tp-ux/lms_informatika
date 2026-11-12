<div>
    <x-slot:title>Dashboard</x-slot>
    <x-slot:breadcrumbs>PANEL ADMIN · DASHBOARD</x-slot>
    <x-slot:description>Ringkasan statistik sekolah dan verifikasi guru yang menunggu persetujuan.</x-slot>
    


    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Siswa</h3>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['siswa'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Guru</h3>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['guru'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Kelas</h3>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['kelas'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Materi Dibuat</h3>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['materi'] }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-bold">Menunggu Verifikasi</h3>
            <a href="{{ route('admin.verifikasi') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Lihat Semua</a>
        </div>
        <div class="p-6">
            @if($pendingVerifications->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="border-b-2 p-3 text-sm font-semibold text-gray-600">Nama</th>
                                <th class="border-b-2 p-3 text-sm font-semibold text-gray-600">Email</th>
                                <th class="border-b-2 p-3 text-sm font-semibold text-gray-600">Peran</th>
                                <th class="border-b-2 p-3 text-sm font-semibold text-gray-600">Waktu Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingVerifications as $user)
                                <tr>
                                    <td class="border-b p-3">{{ $user->name }}</td>
                                    <td class="border-b p-3">{{ $user->email }}</td>
                                    <td class="border-b p-3 capitalize">{{ $user->role }}</td>
                                    <td class="border-b p-3">{{ $user->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Tidak ada pendaftaran yang menunggu verifikasi saat ini.</p>
            @endif
        </div>
    </div>
</div>
