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
                            <div>
                                <flux:subheading class="text-xs font-semibold tracking-wider uppercase">Kelas</flux:subheading>
                                <p class="font-medium">{{ auth()->user()->classroom?->name ?? '-' }}</p>
                            </div>
                        @endif
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
