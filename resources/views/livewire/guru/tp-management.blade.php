<div class="max-w-[1280px] mx-auto px-6 py-8 flex flex-col gap-8 font-['Plus_Jakarta_Sans']">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-2">
        <h1 class="text-2xl font-bold text-[#15191F] m-0" style="color: #15191F;">Susun TP & ATP</h1>
        <div class="flex gap-3 w-full sm:w-auto">
            <button type="button" wire:click.prevent="copyTemplate" class="flex-1 sm:flex-none px-4 py-2 border border-[#E2E4DF] bg-white text-[#4A515C] rounded-xl font-bold text-sm hover:bg-[#F4F5F2] transition-colors whitespace-nowrap" style="background-color: #F4F5F2; color: #4A515C; border-color: #E2E4DF;">
                Salin Template
            </button>
            <button type="button" wire:click.prevent="create" class="flex-1 sm:flex-none px-4 py-2 bg-[#2347C4] text-white rounded-xl font-bold text-sm hover:bg-[#1A3596] transition-colors whitespace-nowrap shadow-sm" style="background-color: #2347C4; color: #ffffff;">
                Tambah TP
            </button>
        </div>
    </div>

    <!-- Flash message -->
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex justify-between items-center shadow-sm" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-700 hover:text-green-900">&times;</button>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex justify-between items-center shadow-sm" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-700 hover:text-red-900">&times;</button>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-[#E2E4DF] overflow-hidden shadow-sm" style="border-color: #E2E4DF;">
        <div class="flex flex-col w-full"
            x-data="{
                dragging: null,
                dropping: null,
                items: {{ $myTps->pluck('id')->toJson() }},
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
                        $wire.updateOrder(this.items);
                    }
                    
                    this.dragging = null;
                    this.dropping = null;
                }
            }">
            
            @forelse($myTps as $tp)
                <div wire:key="tp-{{ $tp->id }}"
                     class="flex items-center gap-2 sm:gap-4 p-4 border-b border-[#E2E4DF] bg-white transition-colors last:border-b-0"
                     :class="{'bg-[#F4F5F2]': dropping === {{ $tp->id }}}"
                     @dragover="dragOver($event, {{ $tp->id }})"
                     @dragleave="dropping = null"
                     @drop="drop($event, {{ $tp->id }})">
                    
                    <!-- Drag Handle -->
                    <div class="cursor-grab text-[#C5CBD6] hover:text-[#4A515C] p-1 flex-none touch-none"
                         draggable="true"
                         @dragstart="dragStart($event, {{ $tp->id }})"
                         @dragend="dragEnd($event)">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"></circle><circle cx="9" cy="5" r="1"></circle><circle cx="9" cy="19" r="1"></circle><circle cx="15" cy="12" r="1"></circle><circle cx="15" cy="5" r="1"></circle><circle cx="15" cy="19" r="1"></circle></svg>
                    </div>

                    <div class="flex-1 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 overflow-hidden">
                        <div class="flex-none font-['JetBrains_Mono'] font-bold text-[#2347C4] text-sm sm:w-16" style="color: #2347C4;">
                            {{ $tp->code }}
                        </div>
                        <div class="flex-1 flex flex-col gap-1 min-w-0">
                            <h3 class="font-bold text-[#15191F] text-base leading-snug m-0 truncate" style="color: #15191F;">{{ $tp->title }}</h3>
                            <p class="text-xs text-[#4A515C] line-clamp-1 m-0" style="color: #4A515C;">{{ $tp->description ?: 'Tidak ada deskripsi' }}</p>
                        </div>
                        <div class="flex-none flex items-center gap-2">
                            <span class="text-xs font-bold px-2 py-1 bg-[#F4F5F2] text-[#4A515C] border border-[#E2E4DF] rounded-md whitespace-nowrap" title="Elemen: {{ $tp->element->name }}">
                                {{ \Illuminate\Support\Str::limit($tp->element->name, 10) }}
                            </span>
                            <span class="text-xs font-bold px-2 py-1 bg-[#E8EDFC] text-[#2347C4] border border-[#BFDBFE] rounded-md whitespace-nowrap" style="background-color: #E8EDFC; color: #2347C4; border-color: #BFDBFE;">
                                KKTP: {{ $tp->kktp }}
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center flex-none ml-2">
                        <!-- Manage Lesson -->
                        <a href="{{ route('guru.lessons') }}?tp={{ $tp->id }}" title="Kelola Materi" class="p-2 text-[#4A515C] hover:bg-[#F4F5F2] hover:text-[#2347C4] rounded-lg transition-colors" wire:navigate>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path></svg>
                        </a>
                        <!-- Edit -->
                        <button type="button" wire:click.stop="edit({{ $tp->id }})" title="Ubah" class="p-2 text-[#4A515C] hover:bg-[#F4F5F2] hover:text-[#2347C4] rounded-lg transition-colors">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <!-- Delete -->
                        <button type="button" wire:click.stop="delete({{ $tp->id }})" wire:confirm="Yakin ingin menghapus TP ini?\nSemua materi di dalamnya juga akan terhapus." title="Hapus" class="p-2 text-[#4A515C] hover:bg-[#FEF3F2] hover:text-[#B4441A] rounded-lg transition-colors">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-10 flex flex-col items-center justify-center text-center gap-3">
                    <div class="w-16 h-16 rounded-full bg-[#F4F5F2] flex items-center justify-center text-[#4A515C]" style="background-color: #F4F5F2; color: #4A515C;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <p class="text-[#4A515C] text-sm font-medium" style="color: #4A515C;">Belum ada Tujuan Pembelajaran yang Anda miliki.<br>Silakan salin dari template atau buat baru.</p>
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Modal Form TP -->
    <div x-data="{ show: @entangle('isModalOpen').live }" 
         x-show="show" 
         x-cloak 
         style="display: none;"
         class="relative z-50" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop (Overlay gelap) -->
        <div x-show="show" 
             @click="$wire.closeModal()"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-40 transition-opacity" aria-hidden="true"></div>

        <!-- Kontainer pembungkus (Wrapper) -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
            
            <!-- Kontainer Modal Utama (Form) -->
            <div x-show="show" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative z-50 bg-white p-6 rounded-2xl shadow-xl w-full max-w-lg mx-auto border border-[#E2E4DF] pointer-events-auto" style="border-color: #E2E4DF;">
                
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-[#15191F]" style="color: #15191F;">{{ $tpId ? 'Ubah TP' : 'Tambah TP Baru' }}</h2>
                    <button type="button" wire:click="closeModal" class="text-[#4A515C] hover:text-[#15191F] transition-colors" style="color: #4A515C; color: #15191F;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                
                <form wire:submit="save" class="flex flex-col gap-4">
                    <div>
                        <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">Elemen</label>
                        <select wire:model="element_id" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #F4F5F2; color: #15191F; border: 1px solid #E2E4DF;">
                            <option value="">Pilih Elemen</option>
                            @foreach($elements as $el)
                                <option value="{{ $el->id }}">{{ $el->name }}</option>
                            @endforeach
                        </select>
                        @error('element_id') <span class="text-xs font-medium mt-1" style="color: #DC2626;">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">Kode TP</label>
                        <input type="text" wire:model="code" placeholder="Contoh: BK-1" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #F4F5F2; color: #15191F; border: 1px solid #E2E4DF;">
                        @error('code') <span class="text-xs font-medium mt-1" style="color: #DC2626;">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">Judul / Topik</label>
                        <input type="text" wire:model="title" placeholder="Judul ringkas (misal: Berpikir Komputasional)" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #F4F5F2; color: #15191F; border: 1px solid #E2E4DF;">
                        @error('title') <span class="text-xs font-medium mt-1" style="color: #DC2626;">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">Deskripsi Lengkap</label>
                        <textarea wire:model="description" rows="3" placeholder="Jelaskan cakupan tujuan pembelajaran ini..." class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #F4F5F2; color: #15191F; border: 1px solid #E2E4DF;"></textarea>
                        @error('description') <span class="text-xs font-medium mt-1" style="color: #DC2626;">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">Urutan (ATP)</label>
                            <input type="number" wire:model="sort_order" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #F4F5F2; color: #15191F; border: 1px solid #E2E4DF;">
                            @error('sort_order') <span class="text-xs font-medium mt-1" style="color: #DC2626;">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">KKTP</label>
                            <input type="number" wire:model="kktp" min="0" max="100" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #F4F5F2; color: #15191F; border: 1px solid #E2E4DF;">
                            @error('kktp') <span class="text-xs font-medium mt-1" style="color: #DC2626;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-3 pt-4 border-t border-[#E2E4DF]" style="border-color: #E2E4DF;">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border border-[#E2E4DF] bg-white text-[#4A515C] rounded-xl font-bold text-sm hover:bg-[#F4F5F2] transition-colors shadow-sm" style="background-color: #ffffff; color: #4A515C; border: 1px solid #E2E4DF;">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-[#2347C4] text-white rounded-xl font-bold text-sm hover:bg-[#1A3596] transition-colors shadow-sm" style="background-color: #2347C4; color: #ffffff; border: none;">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
