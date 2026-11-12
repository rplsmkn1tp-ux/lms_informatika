<div class="relative" x-data="{ open: @entangle('isOpen') }" @click.outside="open = false">
    <!-- Bell Button -->
    <button 
        type="button" 
        @click="open = !open"
        aria-label="Notifikasi{{ $unreadCount > 0 ? ', ' . $unreadCount . ' belum dibaca' : '' }}" 
        style="position: relative; width: 44px; height: 44px; flex: none; border-radius: 12px; border: 1px solid #E2E4DF; background: #FFFFFF; color: #15191F; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;"
        onmouseover="this.style.backgroundColor='#F9FAF8'"
        onmouseout="this.style.backgroundColor='#FFFFFF'"
    >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M6 16V11a6 6 0 0 1 12 0v5l2 2H4z"></path>
            <path d="M10 20a2 2 0 0 0 4 0"></path>
        </svg>

        @if($unreadCount > 0)
            <span style="position: absolute; top: -4px; right: -4px; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 99px; background: #E0892A; color: #FFFFFF; font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid #FFFFFF; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Panel -->
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-150" 
        x-transition:enter-start="transform opacity-0 scale-95" 
        x-transition:enter-end="transform opacity-100 scale-100" 
        x-transition:leave="transition ease-in duration-100" 
        x-transition:leave-start="transform opacity-100 scale-100" 
        x-transition:leave-end="transform opacity-0 scale-95" 
        class="absolute right-0 z-50 mt-2 origin-top-right rounded-2xl bg-white shadow-xl ring-1 ring-black/5" 
        style="display: none; width: min(380px, calc(100vw - 32px)); border: 1px solid #E2E4DF; overflow: hidden; box-shadow: 0 12px 32px -4px rgba(21, 25, 31, 0.12);"
    >
        <!-- Panel Header -->
        <div style="padding: 14px 16px; border-bottom: 1px solid #EDEEEA; display: flex; align-items: center; justify-content: space-between; background: #FAFBF9;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 15px; font-weight: 800; color: #15191F;">Pemberitahuan</span>
                @if($unreadCount > 0)
                    <span style="padding: 1px 7px; border-radius: 99px; background: #E8EDFC; color: #2347C4; font-size: 12px; font-weight: 700;">
                        {{ $unreadCount }} baru
                    </span>
                @endif
            </div>

            @if($unreadCount > 0)
                <button 
                    type="button" 
                    wire:click="markAllAsRead" 
                    style="border: none; background: transparent; font-size: 12px; font-weight: 600; color: #2347C4; cursor: pointer; padding: 4px 6px; border-radius: 6px; transition: background 0.15s;"
                    onmouseover="this.style.backgroundColor='#E8EDFC'"
                    onmouseout="this.style.backgroundColor='transparent'"
                >
                    Tandai dibaca
                </button>
            @endif
        </div>

        <!-- Filter Tabs -->
        <div style="display: flex; gap: 6px; padding: 8px 14px; border-bottom: 1px solid #EDEEEA; background: #FFFFFF;">
            <button 
                type="button" 
                wire:click="setFilter('all')" 
                style="padding: 5px 12px; border-radius: 8px; border: none; font-size: 13px; font-weight: {{ $filter === 'all' ? '700' : '500' }}; background: {{ $filter === 'all' ? '#15191F' : '#F4F5F2' }}; color: {{ $filter === 'all' ? '#FFFFFF' : '#4A515C' }}; cursor: pointer;"
            >
                Semua
            </button>
            <button 
                type="button" 
                wire:click="setFilter('unread')" 
                style="padding: 5px 12px; border-radius: 8px; border: none; font-size: 13px; font-weight: {{ $filter === 'unread' ? '700' : '500' }}; background: {{ $filter === 'unread' ? '#15191F' : '#F4F5F2' }}; color: {{ $filter === 'unread' ? '#FFFFFF' : '#4A515C' }}; cursor: pointer;"
            >
                Belum Dibaca {{ $unreadCount > 0 ? '(' . $unreadCount . ')' : '' }}
            </button>
        </div>

        <!-- Notification List -->
        <div style="max-height: 380px; overflow-y: auto;">
            @forelse($notifications as $item)
                <div 
                    wire:click="markAsRead('{{ $item['key'] }}', '{{ $item['url'] }}')"
                    style="display: flex; align-items: flex-start; gap: 12px; padding: 12px 16px; border-bottom: 1px solid #F4F5F2; cursor: pointer; transition: background 0.15s; background: {{ $item['is_read'] ? '#FFFFFF' : '#F7F9FE' }};"
                    onmouseover="this.style.backgroundColor='#EFF3FC'"
                    onmouseout="this.style.backgroundColor='{{ $item['is_read'] ? '#FFFFFF' : '#F7F9FE' }}'"
                >
                    <!-- Type Icon -->
                    <div style="width: 36px; height: 36px; flex-shrink: 0; border-radius: 10px; background: {{ $item['bg'] }}; color: {{ $item['color'] }}; display: flex; align-items: center; justify-content: center;">
                        @if($item['type'] === 'grade' || $item['type'] === 'submission')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><polyline points="9 15 11 17 15 13"></polyline></svg>
                        @elseif($item['type'] === 'revision')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        @elseif($item['type'] === 'task')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        @elseif($item['type'] === 'lesson')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        @elseif($item['type'] === 'announcement')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l19-9-9 19-2-8-8-2z"></path></svg>
                        @else
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        @endif
                    </div>

                    <!-- Content -->
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span style="font-size: 13px; font-weight: 700; color: #15191F; line-height: 1.3; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                {{ $item['title'] }}
                            </span>
                            @if(!$item['is_read'])
                                <span style="width: 7px; height: 7px; flex-shrink: 0; border-radius: 99px; background: #2347C4;"></span>
                            @endif
                        </div>
                        <p style="margin: 3px 0 0; font-size: 12px; color: #4A515C; line-height: 1.4;">
                            {{ $item['message'] }}
                        </p>
                        <span style="display: inline-block; margin-top: 4px; font-size: 11px; color: #8C939E; font-family: 'JetBrains Mono', monospace;">
                            {{ \Carbon\Carbon::parse($item['created_at'])->setTimezone('Asia/Jakarta')->diffForHumans() }}
                        </span>
                    </div>
                </div>
            @empty
                <div style="padding: 36px 16px; text-align: center; color: #6D747D;">
                    <div style="width: 44px; height: 44px; margin: 0 auto 10px; border-radius: 99px; background: #F4F5F2; display: flex; align-items: center; justify-content: center; color: #8C939E;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </div>
                    <span style="font-size: 14px; font-weight: 700; color: #15191F; display: block;">Tidak ada pemberitahuan</span>
                    <span style="font-size: 12px; color: #6D747D;">Pemberitahuan aktivitas pembelajaran akan muncul di sini.</span>
                </div>
            @endforelse
        </div>
    </div>
</div>

