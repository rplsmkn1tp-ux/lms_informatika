<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6" x-data="{ role: '{{ old('role', 'siswa') }}' }">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Buat Akun Baru</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Pilih peran Anda dan lengkapi data diri di bawah ini</p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5" novalidate>
            @csrf

            <!-- Role Selection (Segmented Control) -->
            <div class="bg-gray-100/80 dark:bg-zinc-800/80 p-1 rounded-xl flex items-center relative overflow-hidden ring-1 ring-black/5 dark:ring-white/10">
                <div class="absolute inset-y-1 w-[calc(50%-4px)] bg-white dark:bg-zinc-700 shadow-sm rounded-lg transition-transform duration-300 ease-out border border-gray-200 dark:border-zinc-600"
                     :class="role === 'guru' ? 'translate-x-[calc(100%+8px)]' : 'translate-x-0'"></div>
                
                <label class="relative flex-1 text-center py-2 text-sm font-medium cursor-pointer transition-colors z-10"
                       :class="role === 'siswa' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900'">
                    <input type="radio" name="role" value="siswa" x-model="role" class="sr-only">
                    <div class="flex items-center justify-center gap-2">
                        <flux:icon.users class="size-4" />
                        Saya Siswa
                    </div>
                </label>
                
                <label class="relative flex-1 text-center py-2 text-sm font-medium cursor-pointer transition-colors z-10"
                       :class="role === 'guru' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900'">
                    <input type="radio" name="role" value="guru" x-model="role" class="sr-only">
                    <div class="flex items-center justify-center gap-2">
                        <flux:icon.academic-cap class="size-4" />
                        Saya Guru
                    </div>
                </label>
            </div>

            @error('role')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror

            <!-- Session Status (if any) -->
            <x-auth-session-status class="text-center" :status="session('status')" />

            <!-- Name -->
            <flux:input
                name="name"
                label="Nama Lengkap"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Masukkan nama lengkap Anda"
                icon="user"
                class="transition-colors duration-200 focus-within:ring-2 focus-within:ring-blue-500/50"
            />

            <!-- Siswa Only Fields -->
            <div x-show="role === 'siswa'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;" class="flex flex-col gap-5">
                <flux:input
                    name="nisn"
                    label="NISN (Opsional)"
                    :value="old('nisn')"
                    type="text"
                    placeholder="Masukkan Nomor Induk Siswa Nasional (Opsional)"
                    icon="identification"
                    class="transition-colors duration-200 focus-within:ring-2 focus-within:ring-blue-500/50"
                />

                @php
                    $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
                    $classrooms = $activeYear ? \App\Models\Classroom::where('academic_year_id', $activeYear->id)->orderBy('grade')->orderBy('major')->get() : [];
                @endphp
                
                <flux:select name="classroom_id" label="Kelas" x-bind:required="role === 'siswa'" class="transition-colors duration-200 focus-within:ring-2 focus-within:ring-blue-500/50">
                    <option value="" disabled {{ !old('classroom_id') ? 'selected' : '' }}>Pilih Kelas Anda</option>
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Email -->
            <flux:input
                name="email"
                label="Alamat Email"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@sekolah.com"
                icon="envelope"
                class="transition-colors duration-200 focus-within:ring-2 focus-within:ring-blue-500/50"
            />

            <!-- Password -->
            <flux:input
                name="password"
                label="Kata Sandi"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Buat kata sandi yang kuat"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
                icon="lock-closed"
                class="transition-colors duration-200 focus-within:ring-2 focus-within:ring-blue-500/50"
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                label="Konfirmasi Kata Sandi"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Ulangi kata sandi Anda"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
                icon="lock-closed"
                class="transition-colors duration-200 focus-within:ring-2 focus-within:ring-blue-500/50"
            />

            <div class="mt-2">
                <flux:button type="submit" variant="primary" class="w-full group transition-all duration-200" data-test="register-user-button">
                    <div class="flex items-center justify-center gap-2 w-full">
                        Daftar Sekarang
                        <flux:icon.arrow-right class="size-4 transition-transform duration-200 group-hover:translate-x-1" />
                    </div>
                </flux:button>
            </div>
        </form>

        <div class="mt-2 text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>Sudah punya akun?</span>
            <flux:link :href="route('login')" wire:navigate class="font-medium text-blue-600 dark:text-blue-400 hover:underline">Masuk di sini</flux:link>
        </div>
    </div>
</x-layouts::auth>
