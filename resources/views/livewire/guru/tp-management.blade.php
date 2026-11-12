<div class="max-w-[1280px] mx-auto px-6 py-8 flex flex-col gap-6 font-['Plus_Jakarta_Sans']">

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#15191F] m-0">Susun TP & ATP</h1>
            <p class="text-sm text-[#4A515C] mt-1">
                Kelola Alur Tujuan Pembelajaran (ATP) Informatika per elemen kurikulum (BK & LD) secara terstruktur.
            </p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <button type="button" wire:click.prevent="copyTemplate" 
                    class="flex-1 sm:flex-none px-4 py-2.5 border border-[#E2E4DF] bg-white text-[#4A515C] rounded-xl font-bold text-sm hover:bg-[#F4F5F2] hover:text-[#15191F] transition-colors whitespace-nowrap shadow-sm flex items-center justify-center gap-2">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                <span>Salin Template</span>
            </button>
            <button type="button" wire:click.prevent="create" 
                    class="flex-1 sm:flex-none px-5 py-2.5 bg-[#2347C4] text-white rounded-xl font-bold text-sm hover:bg-[#1A3596] transition-colors whitespace-nowrap shadow-sm flex items-center justify-center gap-2"
                    style="background-color: #2347C4; color: #FFFFFF;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Tambah TP</span>
            </button>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex justify-between items-center shadow-sm" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-2.5">
                <svg class="text-green-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-700 hover:text-green-950 text-lg font-bold">&times;</button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex justify-between items-center shadow-sm" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-2.5">
                <svg class="text-red-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-700 hover:text-red-950 text-lg font-bold">&times;</button>
        </div>
    @endif

    {{-- Control Toolbar: Element Tabs & Search --}}
    <div class="bg-white p-4 rounded-2xl border border-[#E2E4DF] shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        {{-- Element Tabs Switcher --}}
        <div class="flex items-center gap-2.5 flex-wrap flex-1">
            <span class="text-xs font-bold text-[#4A515C] uppercase tracking-wider mr-1">Pilih Elemen:</span>
            
            @php
                $bkElement = $allElements->firstWhere('code', 'BK');
                $ldElement = $allElements->firstWhere('code', 'LD');
                $isBkActive = (string)$selectedElement === (string)($bkElement?->id);
                $isLdActive = (string)$selectedElement === (string)($ldElement?->id);
                $isAllActive = $selectedElement === '';
            @endphp

            {{-- Tab BK --}}
            @if($bkElement)
                <button type="button" 
                        wire:click="$set('selectedElement', '{{ $bkElement->id }}')" 
                        class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer"
                        style="{{ $isBkActive ? 'background: #2347C4; color: #FFFFFF; box-shadow: 0 4px 12px rgba(35, 71, 196, 0.25);' : 'background: #EEF2FF; color: #2347C4; border: 1px solid #BFDBFE;' }}">
                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $isBkActive ? '#FFFFFF' : '#2347C4' }};"></span>
                    <span>BK - Berpikir Komputasional</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-mono font-bold" 
                          style="{{ $isBkActive ? 'background: rgba(255, 255, 255, 0.2); color: #FFFFFF;' : 'background: #FFFFFF; color: #2347C4;' }}">
                        {{ $countsByElement[$bkElement->id] ?? 0 }} ATP
                    </span>
                </button>
            @endif

            {{-- Tab LD --}}
            @if($ldElement)
                <button type="button" 
                        wire:click="$set('selectedElement', '{{ $ldElement->id }}')" 
                        class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer"
                        style="{{ $isLdActive ? 'background: #059669; color: #FFFFFF; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);' : 'background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;' }}">
                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $isLdActive ? '#FFFFFF' : '#059669' }};"></span>
                    <span>LD - Literasi Digital</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-mono font-bold"
                          style="{{ $isLdActive ? 'background: rgba(255, 255, 255, 0.2); color: #FFFFFF;' : 'background: #FFFFFF; color: #059669;' }}">
                        {{ $countsByElement[$ldElement->id] ?? 0 }} ATP
                    </span>
                </button>
            @endif

            {{-- Tab Semua --}}
            <button type="button" 
                    wire:click="$set('selectedElement', '')" 
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer"
                    style="{{ $isAllActive ? 'background: #15191F; color: #FFFFFF;' : 'background: #F4F5F2; color: #4A515C; border: 1px solid #E2E4DF;' }}">
                <span>Tampilkan Semua</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono font-bold"
                      style="{{ $isAllActive ? 'background: rgba(255, 255, 255, 0.2); color: #FFFFFF;' : 'background: #FFFFFF; color: #4A515C;' }}">
                    {{ array_sum($countsByElement) }}
                </span>
            </button>
        </div>

        {{-- Search Input (Flex container, icon and input side-by-side, perfectly centered) --}}
        <label class="w-full md:w-64 flex items-center gap-2.5 px-3.5 py-2 rounded-xl border border-[#E2E4DF] bg-[#F8F9FA] text-[#8C95A3] focus-within:bg-white focus-within:border-[#2347C4] focus-within:ring-2 focus-within:ring-[#2347C4]/20 transition-all cursor-text shadow-xs"
               style="height: 42px; box-sizing: border-box;">
            <svg class="w-4 h-4 flex-none text-[#8C95A3]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Cari kode atau topik TP..." 
                   class="flex-1 min-w-0 border-0 outline-none bg-transparent text-sm text-[#15191F] placeholder:text-[#8C95A3] p-0 focus:ring-0 focus:outline-none"
                   style="border: none; outline: none; background: transparent; padding: 0; box-shadow: none;">
            @if($search)
                <button type="button" 
                        wire:click="$set('search', '')" 
                        class="text-sm font-bold text-[#8C95A3] hover:text-[#15191F] p-0.5 rounded cursor-pointer leading-none"
                        title="Hapus pencarian">
                    &times;
                </button>
            @endif
        </label>
    </div>

    {{-- Grouped by Element: Accordion Dropdown Cards --}}
    <div class="flex flex-col gap-6"
         x-data="{ 
             activeAccordion: @js($selectedElement ? (int)$selectedElement : ($elements->count() === 1 ? $elements->first()->id : null)),
             toggleAccordion(id) {
                 this.activeAccordion = (this.activeAccordion === id ? null : id);
             }
         }">
        
        @forelse($elements as $element)
            @php
                $isBk = $element->code === 'BK';
                $isLd = $element->code === 'LD';
                $elementTps = $myTps->where('element_id', $element->id);
                $tpCount = $elementTps->count();

                // Theme styling with bulletproof inline styles
                if ($isBk) {
                    $themeColor = '#2347C4';
                    $boxGradient = 'linear-gradient(135deg, #2347C4 0%, #173296 100%)';
                    $boxShadow = '0 4px 12px rgba(35, 71, 196, 0.28)';
                    $headerBg = 'linear-gradient(90deg, #F5F8FF 0%, #FFFFFF 100%)';
                    $pillBg = '#EEF2FF';
                    $pillBorder = '#BFDBFE';
                    $pillColor = '#2347C4';
                    $tagText = 'Computational Thinking';
                    $subTitle = 'Fase E • Algoritma, Struktur Data & Pemecahan Masalah Komputasi';
                    $topBorderColor = '#2347C4';
                } elseif ($isLd) {
                    $themeColor = '#059669';
                    $boxGradient = 'linear-gradient(135deg, #059669 0%, #047857 100%)';
                    $boxShadow = '0 4px 12px rgba(5, 150, 105, 0.28)';
                    $headerBg = 'linear-gradient(90deg, #F2FAF6 0%, #FFFFFF 100%)';
                    $pillBg = '#ECFDF5';
                    $pillBorder = '#A7F3D0';
                    $pillColor = '#059669';
                    $tagText = 'Digital Literacy';
                    $subTitle = 'Fase E • Pemanfaatan Teknologi, Etika Informasi & Keamanan Digital';
                    $topBorderColor = '#059669';
                } else {
                    $themeColor = '#7C3AED';
                    $boxGradient = 'linear-gradient(135deg, #7C3AED 0%, #6D28D9 100%)';
                    $boxShadow = '0 4px 12px rgba(124, 58, 237, 0.28)';
                    $headerBg = 'linear-gradient(90deg, #FAF5FF 0%, #FFFFFF 100%)';
                    $pillBg = '#F3E8FF';
                    $pillBorder = '#DDD6FE';
                    $pillColor = '#7C3AED';
                    $tagText = 'Informatika';
                    $subTitle = 'Fase E • ' . ($element->description ?: 'Kurikulum Merdeka');
                    $topBorderColor = '#7C3AED';
                }
            @endphp

            <div wire:key="element-card-{{ $element->id }}" 
                 class="bg-white rounded-2xl overflow-hidden shadow-sm transition-all duration-200"
                 style="border: 1px solid #E2E4DF; border-top: 4px solid {{ $topBorderColor }};">
                
                {{-- Dropdown Header (Click to Expand / Collapse) --}}
                <div @click="toggleAccordion({{ $element->id }})" 
                     class="p-5 flex items-center justify-between gap-4 cursor-pointer select-none transition-colors hover:bg-opacity-80"
                     style="background: {{ $headerBg }}; border-bottom: 1px solid #E2E4DF;">
                    
                    {{-- Left: Badge & Element Info --}}
                    <div class="flex items-center gap-4 min-w-0">
                        {{-- Icon / Code Box --}}
                        <div class="flex items-center justify-center font-['JetBrains_Mono'] font-extrabold text-base flex-none"
                             style="width: 46px; height: 46px; border-radius: 14px; background: {{ $boxGradient }}; color: #FFFFFF; box-shadow: {{ $boxShadow }};">
                            {{ $element->code }}
                        </div>

                        {{-- Text Section --}}
                        <div class="min-w-0 flex flex-col gap-1">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h2 class="text-lg font-bold text-[#15191F] m-0 tracking-tight">
                                    {{ $element->name }}
                                </h2>
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full whitespace-nowrap"
                                      style="background: {{ $pillBg }}; color: {{ $pillColor }}; border: 1px solid {{ $pillBorder }};">
                                    {{ $tagText }}
                                </span>
                            </div>
                            <p class="text-xs text-[#4A515C] font-medium m-0 truncate">
                                {{ $subTitle }}
                            </p>
                        </div>
                    </div>

                    {{-- Right: Count, Action & Dropdown Toggle Chevron --}}
                    <div class="flex items-center gap-3 flex-none">
                        {{-- ATP Count Pill --}}
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl whitespace-nowrap"
                              style="background: {{ $pillBg }}; color: {{ $pillColor }}; border: 1px solid {{ $pillBorder }};">
                            <span>{{ $tpCount }} TP / ATP</span>
                        </span>

                        {{-- Quick Add TP in this Element --}}
                        <button type="button" 
                                @click.stop="$wire.create({{ $element->id }})" 
                                class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl transition-colors shadow-2xs cursor-pointer"
                                style="background: #FFFFFF; color: {{ $themeColor }}; border: 1px solid {{ $pillBorder }};"
                                title="Tambah TP di elemen ini">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Tambah TP {{ $element->code }}</span>
                        </button>

                        {{-- Dropdown Toggle Button with Chevron --}}
                        <div class="flex items-center justify-center transition-transform duration-200"
                             style="width: 38px; height: 38px; border-radius: 12px; background: #FFFFFF; border: 1px solid #E2E4DF; color: #4A515C;"
                             :class="{ 'rotate-180': activeAccordion === {{ $element->id }} }">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Dropdown Body Content (Hidden by default until clicked) --}}
                <div x-show="activeAccordion === {{ $element->id }}" 
                     x-cloak 
                     style="display: none;"
                     x-transition:enter="transition ease-out duration-200" 
                     x-transition:enter-start="opacity-0 -translate-y-2" 
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2">
                    
                    <div class="flex flex-col w-full"
                        x-data="{
                            dragging: null,
                            dropping: null,
                            items: {{ $elementTps->pluck('id')->values()->toJson() }},
                            dragStart(e, id) {
                                this.dragging = id;
                                e.dataTransfer.effectAllowed = 'move';
                                e.dataTransfer.setData('text/plain', id);
                                e.target.classList.add('opacity-50');
                            },
                            dragOver(e, id) {
                                e.preventDefault();
                                this.dropping = id;
                            },
                            dragEnd(e) {
                                e.target.classList.remove('opacity-50');
                                this.dragging = null;
                                this.dropping = null;
                            },
                            drop(e, targetId) {
                                e.preventDefault();
                                const sourceId = this.dragging;
                                if(sourceId === targetId || !sourceId) {
                                    this.dropping = null;
                                    this.dragging = null;
                                    return;
                                }
                                const sourceIndex = this.items.indexOf(sourceId);
                                const targetIndex = this.items.indexOf(targetId);
                                
                                if(sourceIndex !== -1 && targetIndex !== -1) {
                                    this.items.splice(sourceIndex, 1);
                                    this.items.splice(targetIndex, 0, sourceId);
                                    $wire.updateOrder(this.items, {{ $element->id }});
                                }
                                
                                this.dragging = null;
                                this.dropping = null;
                            }
                        }">

                        @forelse($elementTps as $tp)
                            <div wire:key="tp-{{ $tp->id }}"
                                 class="flex items-center gap-3 sm:gap-4 p-4 border-b border-[#E2E4DF] bg-white transition-colors last:border-b-0 hover:bg-[#FAFAF9]"
                                 :class="{'bg-[#F4F5F2]': dropping === {{ $tp->id }}}"
                                 @dragover="dragOver($event, {{ $tp->id }})"
                                 @dragleave="dropping = null"
                                 @drop="drop($event, {{ $tp->id }})">
                                
                                {{-- Drag Handle --}}
                                <div class="cursor-grab text-[#C5CBD6] hover:text-[#4A515C] p-1.5 flex-none touch-none rounded-lg hover:bg-[#F4F5F2] transition-colors"
                                     draggable="true"
                                     title="Tarik untuk mengubah urutan ATP"
                                     @dragstart="dragStart($event, {{ $tp->id }})"
                                     @dragend="dragEnd($event)">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1.5"></circle><circle cx="9" cy="5" r="1.5"></circle><circle cx="9" cy="19" r="1.5"></circle><circle cx="15" cy="12" r="1.5"></circle><circle cx="15" cy="5" r="1.5"></circle><circle cx="15" cy="19" r="1.5"></circle></svg>
                                </div>

                                {{-- TP Information --}}
                                <div class="flex-1 flex flex-col md:flex-row md:items-center gap-2 md:gap-4 overflow-hidden">
                                    {{-- Code Badge --}}
                                    <div class="flex-none font-['JetBrains_Mono'] font-bold text-sm px-2.5 py-1 rounded-lg inline-flex items-center justify-center w-fit md:w-20"
                                         style="background: {{ $pillBg }}; color: {{ $pillColor }}; border: 1px solid {{ $pillBorder }};">
                                        {{ $tp->code }}
                                    </div>

                                    {{-- Title & Description --}}
                                    <div class="flex-1 flex flex-col gap-0.5 min-w-0">
                                        <h3 class="font-bold text-[#15191F] text-base leading-snug m-0 truncate">
                                            {{ $tp->title }}
                                        </h3>
                                        <p class="text-xs text-[#4A515C] line-clamp-1 m-0">
                                            {{ $tp->description ?: 'Tidak ada deskripsi rinci' }}
                                        </p>
                                    </div>

                                    {{-- Badges: KKTP & Lessons count --}}
                                    <div class="flex-none flex items-center gap-2 mt-1 md:mt-0 flex-wrap">
                                        {{-- Lesson count --}}
                                        <a href="{{ route('guru.lessons') }}?tp={{ $tp->id }}" wire:navigate
                                           class="text-xs font-semibold px-2.5 py-1 bg-[#F4F5F2] text-[#4A515C] border border-[#E2E4DF] rounded-lg hover:bg-[#E2E4DF] transition-colors"
                                           title="Lihat materi di ATP ini">
                                            {{ $tp->lessons->count() }} Subbab Materi
                                        </a>

                                        {{-- KKTP --}}
                                        <span class="text-xs font-bold px-2.5 py-1 bg-white text-[#15191F] border border-[#E2E4DF] rounded-lg shadow-2xs whitespace-nowrap">
                                            KKTP: <strong style="color: {{ $themeColor }}">{{ $tp->kktp ?? $defaultKktp }}</strong>
                                        </span>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex items-center gap-1 flex-none ml-2">
                                    {{-- Manage Lessons --}}
                                    <a href="{{ route('guru.lessons') }}?tp={{ $tp->id }}" 
                                       title="Kelola Materi Subbab" 
                                       class="p-2 text-[#4A515C] hover:bg-[#F4F5F2] hover:text-[#2347C4] rounded-xl transition-colors" 
                                       wire:navigate>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path></svg>
                                    </a>

                                    {{-- Edit TP --}}
                                    <button type="button" 
                                            wire:click.stop="edit({{ $tp->id }})" 
                                            title="Ubah TP" 
                                            class="p-2 text-[#4A515C] hover:bg-[#F4F5F2] hover:text-[#2347C4] rounded-xl transition-colors">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>

                                    {{-- Delete TP --}}
                                    <button type="button" 
                                            wire:click.stop="delete({{ $tp->id }})" 
                                            wire:confirm="Yakin ingin menghapus TP {{ $tp->code }} ini?&#10;Periksa apakah sudah ada materi atau tugas yang tertaut." 
                                            title="Hapus TP" 
                                            class="p-2 text-[#4A515C] hover:bg-[#FEF3F2] hover:text-[#DC2626] rounded-xl transition-colors">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 flex flex-col items-center justify-center text-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-[#F4F5F2] flex items-center justify-center text-[#4A515C]">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                </div>
                                <p class="text-sm font-medium text-[#4A515C] m-0">
                                    Belum ada Tujuan Pembelajaran pada elemen <strong>{{ $element->name }}</strong>.
                                </p>
                                <button type="button" wire:click="create({{ $element->id }})" 
                                        class="px-4 py-2 text-xs font-bold rounded-xl text-white transition-colors shadow-sm cursor-pointer"
                                        style="background: {{ $themeColor }};">
                                    + Tambah TP di {{ $element->code }}
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-[#E2E4DF] p-12 flex flex-col items-center justify-center text-center gap-3 shadow-sm">
                <div class="w-16 h-16 rounded-full bg-[#F4F5F2] flex items-center justify-center text-[#4A515C]">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                </div>
                <h3 class="text-base font-bold text-[#15191F] m-0">Tidak ada elemen yang sesuai</h3>
                <p class="text-[#4A515C] text-sm max-w-sm m-0">Silakan ubah filter pencarian atau pilih elemen lain pada tab di atas.</p>
                <button type="button" wire:click="$set('selectedElement', '')" class="mt-2 px-4 py-2 text-xs font-bold rounded-xl bg-[#F4F5F2] text-[#15191F] hover:bg-[#E2E4DF]">
                    Tampilkan Semua Elemen
                </button>
            </div>
        @endforelse
    </div>
    
    {{-- Modal Form TP --}}
    <div x-data="{ show: @entangle('isModalOpen').live }" 
         x-show="show" 
         x-cloak 
         style="display: none;"
         class="relative z-50" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        {{-- Backdrop --}}
        <div x-show="show" 
             @click="$wire.closeModal()"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-40 transition-opacity" aria-hidden="true"></div>

        {{-- Wrapper --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
            
            {{-- Modal Box --}}
            <div x-show="show" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative z-50 bg-white p-6 rounded-2xl shadow-xl w-full max-w-lg mx-auto border border-[#E2E4DF] pointer-events-auto">
                
                <div class="flex justify-between items-center mb-6 border-b border-[#E2E4DF] pb-4">
                    <div>
                        <h2 class="text-xl font-bold text-[#15191F] m-0">{{ $tpId ? 'Ubah Tujuan Pembelajaran' : 'Tambah TP Baru' }}</h2>
                        <p class="text-xs text-[#4A515C] mt-0.5">Tentukan elemen, kode unik ATP, dan bobot KKTP.</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="p-1.5 rounded-lg text-[#4A515C] hover:bg-[#F4F5F2] hover:text-[#15191F] transition-colors">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                
                <form wire:submit="save" class="flex flex-col gap-4">
                    <div>
                        <label class="block text-sm font-bold text-[#15191F] mb-1">Elemen Kurikulum <span class="text-red-500">*</span></label>
                        <select wire:model.live="element_id" class="w-full rounded-xl shadow-sm px-4 py-2.5 text-sm font-medium border border-[#E2E4DF] bg-[#F8F9FA] text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4]">
                            <option value="">-- Pilih Elemen --</option>
                            @foreach($allElements as $el)
                                <option value="{{ $el->id }}">{{ $el->code }} - {{ $el->name }}</option>
                            @endforeach
                        </select>
                        @error('element_id') <span class="text-xs font-semibold mt-1 text-[#DC2626] block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-[#15191F] mb-1">Kode TP / ATP <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="code" placeholder="Kode ATP unik" class="w-full rounded-xl shadow-sm px-4 py-2.5 text-sm font-mono font-bold border border-[#E2E4DF] bg-[#F8F9FA] text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4]">
                            @error('code') <span class="text-xs font-semibold mt-1 text-[#DC2626] block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#15191F] mb-1">Urutan ATP <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="sort_order" min="1" class="w-full rounded-xl shadow-sm px-4 py-2.5 text-sm font-bold border border-[#E2E4DF] bg-[#F8F9FA] text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4]">
                            @error('sort_order') <span class="text-xs font-semibold mt-1 text-[#DC2626] block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#15191F] mb-1">Judul / Topik Pembelajaran <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="title" placeholder="Contoh: Struktur data & algoritma standar" class="w-full rounded-xl shadow-sm px-4 py-2.5 text-sm border border-[#E2E4DF] bg-[#F8F9FA] text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4]">
                        @error('title') <span class="text-xs font-semibold mt-1 text-[#DC2626] block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#15191F] mb-1">Deskripsi Lengkap</label>
                        <textarea wire:model="description" rows="3" placeholder="Jelaskan cakupan tujuan pembelajaran atau kriteria materi..." class="w-full rounded-xl shadow-sm px-4 py-2.5 text-sm border border-[#E2E4DF] bg-[#F8F9FA] text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4]"></textarea>
                        @error('description') <span class="text-xs font-semibold mt-1 text-[#DC2626] block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#15191F] mb-1">Nilai KKTP</label>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model="kktp" min="0" max="100" class="w-32 rounded-xl shadow-sm px-4 py-2.5 text-sm font-bold border border-[#E2E4DF] bg-[#F8F9FA] text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4]">
                            <span class="text-xs text-[#4A515C]">(Default kriteria ketercapaian: {{ $defaultKktp }})</span>
                        </div>
                        @error('kktp') <span class="text-xs font-semibold mt-1 text-[#DC2626] block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-4 flex justify-end gap-3 pt-4 border-t border-[#E2E4DF]">
                        <button type="button" wire:click="closeModal" class="px-5 py-2.5 border border-[#E2E4DF] bg-white text-[#4A515C] rounded-xl font-bold text-sm hover:bg-[#F4F5F2] transition-colors shadow-sm">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-[#2347C4] text-white rounded-xl font-bold text-sm hover:bg-[#1A3596] transition-colors shadow-sm">
                            {{ $tpId ? 'Simpan Perubahan' : 'Buat TP Baru' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
