<div 
    x-data="{ 
        open: @entangle('isOpen'),
        focusSearch() {
            this.open = true;
            this.$nextTick(() => { 
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus(); 
                }
            });
        }
    }"
    @keydown.window.cmd.k.prevent="focusSearch()"
    @keydown.window.ctrl.k.prevent="focusSearch()"
    @keydown.escape.window="open = false; if ($refs.searchInput) $refs.searchInput.blur()"
    class="relative w-full"
    style="max-width: 420px;"
>
    <!-- Search Input Bar -->
    <div class="relative flex items-center w-full">
        <label for="global-search-input-{{ $this->getId() }}" class="sr-only">Cari</label>
        
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#717680]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="11" cy="11" r="7"></circle>
                <path d="M20 20l-4-4"></path>
            </svg>
        </div>
        
        <input 
            x-ref="searchInput"
            id="global-search-input-{{ $this->getId() }}"
            type="search"
            wire:model.live.debounce.250ms="query"
            @focus="open = true"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            style="width: 100%; height: 42px; padding: 0 78px 0 38px; border-radius: 12px; border: 1px solid #E2E4DF; background: #F4F5F2; color: #15191F; font-size: 13.5px; outline: none; transition: all 0.15s ease;"
            onfocus="this.style.background='#FFFFFF'; this.style.borderColor='#2347C4'; this.style.boxShadow='0 0 0 3px rgba(35, 71, 196, 0.12)';"
            onblur="this.style.background='#F4F5F2'; this.style.borderColor='#E2E4DF'; this.style.boxShadow='none';"
        />

        <!-- Right side tools -->
        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center gap-1.5">
            <!-- Loading Spinner -->
            <div wire:loading wire:target="query" style="display: none;" class="flex items-center text-[#2347C4]">
                <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <!-- Clear Button -->
            @if(strlen($query) > 0)
                <button 
                    type="button" 
                    wire:click="clear" 
                    @click="focusSearch()"
                    aria-label="Hapus kata kunci"
                    style="width: 20px; height: 20px; border-radius: 99px; background: #D9DCD5; border: none; color: #4A515C; display: flex; align-items: center; justify-content: center; cursor: pointer; padding: 0;"
                    onmouseover="this.style.background='#C5C9C0'"
                    onmouseout="this.style.background='#D9DCD5'"
                >
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            @endif

            <!-- Shortcut Badge -->
            <span class="hidden sm:inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-mono text-[#717680] bg-[#E5E7E1] border border-[#D5D8D0]" style="line-height: 1.2;">
                Ctrl+K
            </span>
        </div>
    </div>

    <!-- Dropdown Results Panel -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1 scale-98"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-1 scale-98"
        @click.outside="open = false"
        style="position: absolute; left: 0; right: 0; top: 100%; margin-top: 6px; z-index: 9999; background: #FFFFFF; border-radius: 16px; border: 1px solid #E2E4DF; box-shadow: 0 20px 30px -8px rgba(0, 0, 0, 0.15), 0 4px 6px -2px rgba(0, 0, 0, 0.05); overflow: hidden; display: flex; flex-direction: column; max-height: 440px;"
        x-cloak
    >
        <!-- Results Scroll Container -->
        <div style="flex: 1; overflow-y: auto; padding: 6px 0;" class="divide-y divide-[#F0F1EE]">
            @php $hasAnyResults = false; @endphp

            @foreach($results as $category => $items)
                @if(count($items) > 0)
                    @php $hasAnyResults = true; @endphp
                    <div style="padding: 6px 8px;">
                        <!-- Category Header -->
                        <div style="padding: 4px 10px 6px; font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 11px; font-weight: 700; color: #717680; letter-spacing: 0.05em; text-transform: uppercase;">
                            {{ $category }}
                        </div>

                        <!-- Category Items -->
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            @foreach($items as $item)
                                <a 
                                    href="{{ $item['url'] }}" 
                                    wire:navigate
                                    @click="open = false; $wire.set('query', '')"
                                    style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 10px; border-radius: 10px; text-decoration: none; color: #15191F; transition: background 0.15s ease;"
                                    onmouseover="this.style.background='#F4F5F2'"
                                    onmouseout="this.style.background='transparent'"
                                >
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                                        <!-- Icon box -->
                                        <div style="width: 32px; height: 32px; flex: none; border-radius: 8px; display: flex; align-items: center; justify-content: center;
                                            @if(($item['badge_color'] ?? '') === 'purple') background: #F1EFFD; color: #5B32E8;
                                            @elseif(($item['badge_color'] ?? '') === 'green') background: #E9F9EE; color: #1B7A3D;
                                            @elseif(($item['badge_color'] ?? '') === 'amber') background: #FEF4E6; color: #B35E00;
                                            @elseif(($item['badge_color'] ?? '') === 'red') background: #FDEEEE; color: #C5221F;
                                            @else background: #E8EDFC; color: #2347C4; @endif
                                        ">
                                            @if(($item['icon'] ?? '') === 'clipboard-check')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><path d="m9 14 2 2 4-4"></path></svg>
                                            @elseif(($item['icon'] ?? '') === 'book-open')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                                            @elseif(($item['icon'] ?? '') === 'academic-cap')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                                            @elseif(($item['icon'] ?? '') === 'users')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                            @elseif(($item['icon'] ?? '') === 'user')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            @elseif(($item['icon'] ?? '') === 'check-badge')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 3.1 4.41.65.65 4.41L22 12l-1.85 1.84-.65 4.41-4.41.65L12 22l-3.09-1.85-4.41-.65-.65-4.41L2 12l1.85-1.84.65-4.41 4.41-.65L12 2z"></path><path d="m9 12 2 2 4-4"></path></svg>
                                            @elseif(($item['icon'] ?? '') === 'calendar' || ($item['icon'] ?? '') === 'calendar-days')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                            @elseif(($item['icon'] ?? '') === 'table-cells')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M3 9h18"></path><path d="M3 15h18"></path><path d="M9 3v18"></path><path d="M15 3v18"></path></svg>
                                            @elseif(($item['icon'] ?? '') === 'chart-bar' || ($item['icon'] ?? '') === 'document-chart-bar')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                            @elseif(($item['icon'] ?? '') === 'document-text')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            @elseif(($item['icon'] ?? '') === 'bookmark')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"></path></svg>
                                            @elseif(($item['icon'] ?? '') === 'cog-8-tooth')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                            @elseif(($item['icon'] ?? '') === 'plus-circle')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                            @elseif(($item['icon'] ?? '') === 'clock')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                            @else
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                            @endif
                                        </div>

                                        <!-- Title & Subtitle -->
                                        <div style="min-width: 0; flex: 1;">
                                            <div style="font-weight: 600; font-size: 13.5px; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                {{ $item['title'] }}
                                            </div>
                                            @if(!empty($item['subtitle']))
                                                <div style="font-size: 12px; color: #717680; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 1px;">
                                                    {{ $item['subtitle'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Badge & Arrow -->
                                    <div style="display: flex; align-items: center; gap: 8px; flex: none;">
                                        @if(!empty($item['badge']))
                                            <span style="font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 6px;
                                                @if(($item['badge_color'] ?? '') === 'purple') background: #F1EFFD; color: #5B32E8;
                                                @elseif(($item['badge_color'] ?? '') === 'green') background: #E9F9EE; color: #1B7A3D;
                                                @elseif(($item['badge_color'] ?? '') === 'amber') background: #FEF4E6; color: #B35E00;
                                                @elseif(($item['badge_color'] ?? '') === 'red') background: #FDEEEE; color: #C5221F;
                                                @elseif(($item['badge_color'] ?? '') === 'gray') background: #EDEDE8; color: #5A606A;
                                                @else background: #E8EDFC; color: #2347C4; @endif
                                            ">
                                                {{ $item['badge'] }}
                                            </span>
                                        @endif
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#A0A5AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            <!-- Empty State when searching -->
            @if(strlen(trim($query)) >= 1 && !$hasAnyResults)
                <div style="padding: 32px 20px; text-align: center;">
                    <div style="width: 44px; height: 44px; margin: 0 auto 10px; border-radius: 99px; background: #F4F5F2; display: flex; align-items: center; justify-content: center; color: #8A92A0;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            <line x1="8" y1="11" x2="14" y2="11"></line>
                        </svg>
                    </div>
                    <div style="font-weight: 700; font-size: 14px; color: #15191F; margin-bottom: 4px;">
                        Tidak ada hasil ditemukan
                    </div>
                    <div style="font-size: 13px; color: #717680; max-width: 280px; margin: 0 auto;">
                        Tidak ditemukan hasil untuk "<span style="color: #15191F; font-weight: 600;">{{ $query }}</span>". Coba kata kunci lain atau periksa ejaan.
                    </div>
                </div>
            @endif
        </div>

        <!-- Dropdown Footer -->
        <div style="border-top: 1px solid #EDEEEA; background: #FAFBF9; padding: 8px 14px; display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; color: #717680;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span>Tekan <kbd style="padding: 1px 5px; border-radius: 4px; background: #EDEEEA; border: 1px solid #DFE1DC; font-family: monospace; font-size: 11px;">ESC</kbd> untuk menutup</span>
            </div>
            <div style="display: flex; align-items: center; gap: 4px; font-weight: 600; color: #2347C4;">
                <span>{{ \App\Models\Setting::getValue('app_name', 'Ruang Informatika') }}</span>
            </div>
        </div>
    </div>
</div>
