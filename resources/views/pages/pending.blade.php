<x-layouts::auth title="Menunggu Verifikasi">
    <div class="flex flex-col items-center justify-center gap-6 text-center">
        <div class="p-4 bg-zinc-100 dark:bg-zinc-800 rounded-full">
            <flux:icon.clock class="w-12 h-12 text-accent" />
        </div>
        
        <x-auth-header title="Menunggu Verifikasi" description="Akun Anda sedang ditinjau admin." />
        
        <p class="text-sm text-zinc-600 dark:text-zinc-400 max-w-sm">
            Terima kasih telah mendaftar sebagai Guru. Admin akan segera meninjau dan memverifikasi akun Anda. Anda akan mendapatkan akses penuh setelah disetujui.
        </p>

        <form method="POST" action="{{ route('logout') }}" class="w-full mt-4">
            @csrf
            <flux:button type="submit" variant="primary" class="w-full">
                Logout
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
