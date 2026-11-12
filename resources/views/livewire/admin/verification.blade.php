<div>
    <x-slot:title>Verifikasi Guru</x-slot>
    <x-slot:breadcrumbs>PANEL ADMIN · VERIFIKASI</x-slot>
    <x-slot:description>Guru yang mendaftar sendiri baru bisa masuk setelah disetujui. Hasil persetujuan atau penolakan dikirim ke email pendaftar.</x-slot>
    
    <x-slot:headerActions>
        <span style="display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 0 16px; border-radius: 12px; background: #FDF1DC; color: #8A4B00; font-size: 14px; font-weight: 700">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>
            {{ $pendingCount }} menunggu
        </span>
    </x-slot:headerActions>

    <div style="display: flex; flex-direction: column; gap: 16px">
        @forelse($teachers as $teacher)
            <article style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 20px; display: flex; flex-direction: column; gap: 16px">
                <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-start">
                    <div style="flex: 1 1 340px; min-width: 0; display: flex; gap: 14px; align-items: flex-start">
                        <span style="width: 48px; height: 48px; flex: none; border-radius: 99px; background: #FDF1DC; color: #8A4B00; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 800">{{ $teacher->initials() }}</span>
                        
                        <div style="flex: 1; display: flex; flex-direction: column; gap: 10px; min-width: 0">
                            <div style="display: flex; flex-direction: column; gap: 2px">
                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px">
                                    <h2 style="margin: 0; font-size: 18px; font-weight: 800">{{ $teacher->name }}</h2>
                                    <x-admin.badge status="menunggu" />
                                </div>
                                <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 14px; color: #4A515C">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 7l9 6 9-6"></path></svg>
                                    {{ $teacher->email }}
                                </span>
                            </div>
                            
                            <dl style="margin: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px 20px; font-size: 14px">
                                <div>
                                    <dt style="font-size: 12px; color: #4A515C">NIP / NUPTK</dt>
                                    <dd style="margin: 0; font-weight: 600">{{ $teacher->nip_nuptk ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt style="font-size: 12px; color: #4A515C">Mata pelajaran</dt>
                                    <dd style="margin: 0; font-weight: 600">{{ $teacher->subject ?? 'Informatika' }}</dd>
                                </div>
                                <div>
                                    <dt style="font-size: 12px; color: #4A515C">Mendaftar</dt>
                                    <dd style="margin: 0; font-weight: 600">{{ $teacher->created_at->diffForHumans() }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 8px">
                        <button type="button" wire:click="approve({{ $teacher->id }})" wire:confirm="Yakin ingin menyetujui guru ini?" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 12px; border: 1px solid #2347C4; background: #2347C4; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none; white-space: nowrap">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
                            Setujui
                        </button>
                        <button type="button" wire:click="confirmReject({{ $teacher->id }})" aria-expanded="{{ $rejectingUserId === $teacher->id ? 'true' : 'false' }}" style="display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 12px; border: 1px solid #E3B9A6; background: #FFFFFF; color: #8A3412; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"></path></svg>
                            Tolak
                        </button>
                    </div>
                </div>

                @if($rejectingUserId === $teacher->id)
                    <div style="display: flex; flex-direction: column; gap: 10px; padding: 16px; border-radius: 14px; background: #FBEDE5; border: 1px solid #E3B9A6">
                        <label for="alasan-{{ $teacher->id }}" style="font-size: 14px; font-weight: 700; color: #3D1A0A">Alasan penolakan</label>
                        <textarea id="alasan-{{ $teacher->id }}" wire:model="rejectionReason" rows="3" placeholder="Contoh: Nama tidak terdaftar sebagai guru di sekolah." style="font: inherit; font-size: 14px; padding: 12px 14px; border-radius: 12px; border: 1px solid #D9A58C; background: #FFFFFF; color: #15191F; resize: vertical"></textarea>
                        @error('rejectionReason') <span style="font-size: 13px; color: #B4441A">{{ $message }}</span> @enderror
                        <span style="font-size: 13px; color: #6B2A0E">Alasan ini dikirim ke email pendaftar.</span>
                        <div style="display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px">
                            <button type="button" wire:click="$set('rejectingUserId', null)" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 12px; border: 1px solid #D5D8D1; background: #FFFFFF; color: #15191F; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none; white-space: nowrap">Batal</button>
                            <button type="button" wire:click="reject" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 12px; border: 1px solid #B4441A; background: #B4441A; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; white-space: nowrap">Kirim penolakan</button>
                        </div>
                    </div>
                @endif
            </article>
        @empty
            <article style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 32px 20px; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px">
                <span style="width: 56px; height: 56px; border-radius: 99px; background: #F6F7F4; color: #8A929E; display: flex; align-items: center; justify-content: center">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M8 12.5l2.5 2.5L16 9.5"></path></svg>
                </span>
                <div style="display: flex; flex-direction: column; gap: 4px">
                    <span style="font-size: 16px; font-weight: 700; color: #15191F">Tidak ada verifikasi tertunda</span>
                    <span style="font-size: 14px; color: #4A515C">Semua pendaftaran guru sudah diperiksa.</span>
                </div>
            </article>
        @endforelse
    </div>

    <section aria-labelledby="riwayat" style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 20px 22px; display: flex; flex-direction: column; gap: 4px; margin-top: 16px;">
        <h2 id="riwayat" style="margin: 0; font-size: 18px; font-weight: 800">Riwayat verifikasi</h2>
        <ul style="list-style: none; margin: 0; padding: 0">
            @forelse($verifiedTeachers as $vt)
                <li style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px 14px; padding: 12px 0; border-bottom: 1px solid #EDEEEA">
                    <span style="flex: 1 1 220px; min-width: 0; font-weight: 700; font-size: 14px">{{ $vt->name }}</span>
                    @if($vt->status === 'active')
                        <x-admin.badge status="disetujui" />
                        <span style="flex: 1 1 260px; font-size: 13px; color: #4A515C">
                            {{ $vt->updated_at->diffForHumans() }}
                        </span>
                    @else
                        <x-admin.badge status="ditolak" />
                        <span style="flex: 1 1 260px; font-size: 13px; color: #4A515C">
                            “{{ $vt->rejection_reason }}” · {{ $vt->updated_at->diffForHumans() }}
                        </span>
                    @endif
                </li>
            @empty
                <li style="padding: 12px 0; font-size: 14px; color: #8A929E; text-align: center;">Belum ada riwayat verifikasi</li>
            @endforelse
        </ul>
    </section>

</div>
