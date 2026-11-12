<div>
    <x-slot:title>Profil Saya</x-slot:title>

    <div class="max-w-3xl mx-auto py-8">
        <flux:heading size="xl" level="1" class="mb-6">Profil Saya</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Informasi Akun (Read Only) -->
            <div class="md:col-span-1">
                <flux:card>
                    <flux:heading size="lg" class="mb-4">Informasi Akun</flux:heading>
                    <div class="space-y-4">
                        <div>
                            <flux:subheading class="text-xs font-semibold tracking-wider uppercase">NISN/NIP</flux:subheading>
                            <p class="font-medium">{{ auth()->user()->identifier }}</p>
                        </div>
                        <div>
                            <flux:subheading class="text-xs font-semibold tracking-wider uppercase">Peran</flux:subheading>
                            <p class="font-medium capitalize">{{ auth()->user()->role }}</p>
                        </div>
                        <div>
                            <flux:subheading class="text-xs font-semibold tracking-wider uppercase">Status</flux:subheading>
                            <p class="font-medium capitalize">{{ auth()->user()->status }}</p>
                        </div>
                        @if(auth()->user()->role === 'siswa')
                            @php
                                $cr = auth()->user()->classroom;
                            @endphp
                            <div>
                                <flux:subheading class="text-xs font-semibold tracking-wider uppercase">Kelas</flux:subheading>
                                <p class="font-medium">
                                    @if($cr)
                                        <span>{{ $cr->name }}</span>
                                        @if($cr->major && !str_contains($cr->name, $cr->major))
                                            <span class="text-xs text-zinc-500 font-normal">({{ $cr->grade }} {{ $cr->major }})</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        @elseif(auth()->user()->role === 'guru' && auth()->user()->subject)
                            <div>
                                <flux:subheading class="text-xs font-semibold tracking-wider uppercase">Mata Pelajaran</flux:subheading>
                                <p class="font-medium">{{ auth()->user()->subject }}</p>
                            </div>
                        @endif

                        <div class="pt-4 border-t border-zinc-200">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition cursor-pointer border-0">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                    Keluar dari Akun
                                </button>
                            </form>
                        </div>
                    </div>
                </flux:card>
            </div>

            <!-- Edit Profil & Password -->
            <div class="md:col-span-2 space-y-6">
                <!-- Form Edit Profil -->
                <flux:card>
                    <form wire:submit="updateProfile" class="space-y-6">
                        <flux:heading size="lg">Perbarui Profil</flux:heading>
                        <flux:subheading>Perbarui nama dan alamat email Anda.</flux:subheading>

                        <flux:input wire:model="name" label="Nama Lengkap" required />
                        <flux:input wire:model="email" type="email" label="Alamat Email" required />

                        <div class="flex justify-end">
                            <flux:button variant="primary" type="submit">Simpan Profil</flux:button>
                        </div>
                    </form>
                </flux:card>

                <!-- Form Ubah Sandi -->
                <flux:card>
                    <form wire:submit="updatePassword" class="space-y-6">
                        <flux:heading size="lg">Ubah Kata Sandi</flux:heading>
                        <flux:subheading>Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman.</flux:subheading>

                        <flux:input wire:model="current_password" type="password" label="Kata Sandi Saat Ini" required />
                        <flux:input wire:model="password" type="password" label="Kata Sandi Baru" required />
                        <flux:input wire:model="password_confirmation" type="password" label="Konfirmasi Kata Sandi" required />

                        <div class="flex justify-end">
                            <flux:button variant="primary" type="submit">Ubah Sandi</flux:button>
                        </div>
                    </form>
                </flux:card>
            </div>
        </div>
    </div>
</div>
