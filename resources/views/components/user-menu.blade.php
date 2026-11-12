<div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
    <button type="button" @click="open = !open" aria-label="Menu akun" style="width: 44px; height: 44px; flex: none; border-radius: 99px; border: 0; background: #F2A93B; color: #15191F; font: inherit; font-weight: 800; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center">
        {{ auth()->user()->initials() }}
    </button>

    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100" 
         x-transition:enter-start="transform opacity-0 scale-95" 
         x-transition:enter-end="transform opacity-100 scale-100" 
         x-transition:leave="transition ease-in duration-75" 
         x-transition:leave-start="transform opacity-100 scale-100" 
         x-transition:leave-end="transform opacity-0 scale-95" 
         class="absolute right-0 z-50 mt-2 w-64 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
         style="display: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); border: 1px solid #E2E4DF;">
         
        <div class="px-4 py-3">
            <p class="text-[15px] font-bold m-0 leading-tight" style="color: #15191F;">{{ auth()->user()->name }}</p>
            @if(auth()->user()->role === 'siswa' && auth()->user()->classroom)
                <p class="text-[13px] mt-1 m-0" style="color: #4A515C;">Siswa - Kelas {{ auth()->user()->classroom->name }}</p>
            @elseif(auth()->user()->role === 'guru')
                <p class="text-[13px] mt-1 m-0" style="color: #4A515C;">Guru Informatika</p>
            @else
                <p class="text-[13px] mt-1 m-0" style="color: #4A515C;">{{ ucfirst(auth()->user()->role) }}</p>
            @endif
            <p class="text-[12px] mt-1 m-0" style="color: #4A515C; font-family: 'JetBrains Mono', monospace;">{{ auth()->user()->identifier ?? 'NIP/NUPTK' }} &bull; hanya baca</p>
        </div>

        <div class="border-t" style="border-color: #E2E4DF;"></div>

        <div class="py-1">
            <a href="{{ route('profil') }}" wire:navigate class="group flex w-full items-center gap-3 px-4 py-2 text-[14px] font-semibold transition-colors hover:bg-gray-50" style="color: #15191F; text-decoration: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #4A515C;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                Profil saya
            </a>
            <a href="{{ route('profil') }}" wire:navigate class="group flex w-full items-center gap-3 px-4 py-2 text-[14px] font-semibold transition-colors hover:bg-gray-50" style="color: #15191F; text-decoration: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #4A515C;"><path d="M2 18v3c0 .6.4 1 1 1h4v-3h3v-3h2l1.4-1.4a6.5 6.5 0 1 0-4-4Z"></path><circle cx="16.5" cy="7.5" r=".5" fill="currentColor"></circle></svg>
                Ubah sandi
            </a>
        </div>

        <div class="border-t" style="border-color: #E2E4DF;"></div>

        <form method="POST" action="{{ route('logout') }}" class="py-1 m-0">
            @csrf
            <button type="submit" class="group flex w-full items-center gap-3 px-4 py-2 text-[14px] font-semibold transition-colors hover:bg-orange-50" style="color: #C2410C; text-align: left; background: none; border: none; cursor: pointer; font-family: inherit;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Keluar
            </button>
        </form>
    </div>
</div>
