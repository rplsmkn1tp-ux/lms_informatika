<div style="max-width: 860px; margin: 0 auto; padding: 32px 24px 64px; display: flex; flex-direction: column; gap: 24px">
    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" style="font-size: 13px; color: #4A515C; display: flex; flex-wrap: wrap; gap: 6px; align-items: center">
        <a href="{{ route('siswa.tugas') }}" wire:navigate style="color: #4A515C; text-decoration: none; font-weight: 500;">Daftar Tugas</a>
        <span aria-hidden="true">/</span>
        <span style="color: #15191F; font-weight: 700; max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $assessment->title }}</span>
    </nav>

    <!-- Header & Info Tugas -->
    <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 28px; display: flex; flex-direction: column; gap: 16px">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
            <div style="display: flex; flex-direction: column; gap: 6px">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span style="padding: 4px 10px; border-radius: 8px; background: #E8EDFC; color: #2347C4; font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 700">TUGAS</span>
                    @if($mNo = $assessment->effectiveMeetingNo())
                        <span style="padding: 4px 10px; border-radius: 8px; background: #FEF3C7; color: #92400E; font-size: 12px; font-weight: 700">
                            Pertemuan ke-{{ $mNo }}
                        </span>
                    @endif
                    @if($assessment->lesson)
                        <span style="padding: 4px 10px; border-radius: 8px; background: #E0E7FF; color: #3730A3; font-size: 12px; font-weight: 600">
                            Materi: {{ $assessment->lesson->title }}
                        </span>
                    @endif
                    @if($assessment->isGroup())
                        <span style="padding: 4px 10px; border-radius: 8px; background: #EDE9FE; color: #6D28D9; font-size: 12px; font-weight: 700">
                            Tugas Kelompok (Maks. {{ $assessment->max_group_members ?? 4 }} Siswa)
                        </span>
                    @endif
                    @if($isPastDue)
                        <span style="padding: 4px 10px; border-radius: 8px; background: #FEE2E2; color: #B91C1C; font-size: 11px; font-weight: 700">Lewat Tenggat</span>
                    @endif
                </div>
                <h1 style="margin: 0; font-size: 26px; font-weight: 800; line-height: 1.3; color: #15191F">{{ $assessment->title }}</h1>
            </div>

            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px; text-align: right">
                <span style="font-size: 12px; color: #4A515C">Nilai Maksimal:</span>
                <span style="font-size: 20px; font-weight: 800; color: #15191F">{{ $assessment->max_score }}</span>
            </div>
        </div>

        <!-- Tenggat & Info Guru -->
        <div style="display: flex; flex-wrap: wrap; gap: 16px; font-size: 13px; color: #4A515C; padding-top: 12px; border-top: 1px solid #EDEEEA">
            <div>
                Tenggat: <strong style="color: #15191F">{{ $dueAtCarbon ? $dueAtCarbon->translatedFormat('d F Y, H:i') . ' WIB' : 'Tanpa batas waktu' }}</strong>
            </div>
            <div>&bull;</div>
            <div>
                Guru: <strong style="color: #15191F">{{ $assessment->creator->name ?? 'Guru Informatika' }}</strong>
            </div>
            <div>&bull;</div>
            <div>
                Kumpul Ulang: <strong style="color: #15191F">{{ $assessment->allow_resubmit ? 'Diizinkan' : 'Hanya 1 Kali' }}</strong>
            </div>
        </div>

        <!-- Instruksi Guru -->
        @if($assessment->instructions)
            <div style="margin-top: 8px; padding-top: 16px; border-top: 1px solid #EDEEEA; display: flex; flex-direction: column; gap: 6px">
                <span style="font-size: 12px; font-weight: 700; color: #4A515C; text-transform: uppercase; letter-spacing: 0.05em">Petunjuk Pengerjaan:</span>
                <div style="font-size: 14px; color: #2B303A; line-height: 1.6; white-space: pre-line">
                    {{ $assessment->instructions }}
                </div>
            </div>
        @endif

        <!-- Lampiran Guru -->
        @if(!empty($assessment->teacher_attachments))
            <div style="margin-top: 8px; padding-top: 16px; border-top: 1px solid #EDEEEA; display: flex; flex-direction: column; gap: 10px">
                <span style="font-size: 12px; font-weight: 700; color: #4A515C; text-transform: uppercase; letter-spacing: 0.05em">Lampiran dari Guru:</span>
                <div style="display: flex; flex-wrap: wrap; gap: 10px">
                    @foreach($assessment->teacher_attachments as $idx => $att)
                        @if(($att['type'] ?? '') === 'file')
                            <a href="{{ route('guru.tugas.download-attachment', [$assessment->id, $idx]) }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 10px; background: #F4F5F2; border: 1px solid #E2E4DF; color: #2347C4; font-size: 13px; font-weight: 600; text-decoration: none;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                <span>{{ $att['name'] }}</span>
                            </a>
                        @else
                            <a href="{{ $att['url'] }}" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 10px; background: #FEF3C7; border: 1px solid #FDE68A; color: #92400E; font-size: 13px; font-weight: 600; text-decoration: none;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                <span>{{ $att['name'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Alert Flash -->
    @if(session()->has('success'))
        <div style="padding: 16px 20px; border-radius: 16px; background: #E7F3EC; border: 1px solid #C4E2D2; color: #1D6B45; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 10px">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session()->has('error'))
        <div style="padding: 16px 20px; border-radius: 16px; background: #FEE2E2; border: 1px solid #FECACA; color: #B91C1C; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 10px">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Banner Status Penilaian -->
    @if($submission)
        @if($submission->status === \App\Models\Submission::STATUS_GRADED)
            <div style="background: #FFFFFF; border: 2px solid #1D6B45; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 14px">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px">
                    <div>
                        <span style="font-size: 12px; font-weight: 700; color: #1D6B45; text-transform: uppercase; letter-spacing: 0.05em">Status Penilaian</span>
                        <h3 style="margin: 4px 0 0; font-size: 18px; font-weight: 800; color: #15191F">Tugas Telah Dinilai oleh Guru</h3>
                        <span style="font-size: 12px; color: #4A515C">Dikumpulkan pada {{ \Carbon\Carbon::parse($submission->submitted_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</span>
                    </div>
                    <div style="padding: 12px 20px; border-radius: 14px; background: #E7F3EC; text-align: center">
                        <span style="font-size: 11px; font-weight: 700; color: #1D6B45; text-transform: uppercase; display: block">Nilai Anda</span>
                        <span style="font-size: 32px; font-weight: 800; color: #1D6B45; line-height: 1.1">{{ round($submission->score, 1) }}</span>
                        <span style="font-size: 11px; color: #4A515C">/ {{ $assessment->max_score }}</span>
                    </div>
                </div>

                @if($submission->feedback)
                    <div style="padding: 14px; border-radius: 12px; background: #F4F5F2; font-size: 13px; color: #2B303A; line-height: 1.5">
                        <strong style="color: #15191F">Catatan / Umpan Balik Guru:</strong><br>
                        {{ $submission->feedback }}
                    </div>
                @endif
            </div>
        @elseif($submission->status === \App\Models\Submission::STATUS_REVISION)
            <div style="background: #FFFBEB; border: 2px solid #F59E0B; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 12px">
                <div style="display: flex; align-items: center; gap: 10px; color: #B45309;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <h3 style="margin: 0; font-size: 17px; font-weight: 800">Guru Meminta Revisi Tugas</h3>
                </div>
                @if($submission->feedback)
                    <div style="padding: 12px 16px; border-radius: 12px; background: #FFFFFF; font-size: 13px; color: #78350F; line-height: 1.5; border: 1px solid #FDE68A">
                        <strong>Catatan Guru:</strong> {{ $submission->feedback }}
                    </div>
                @endif
                <p style="margin: 0; font-size: 13px; color: #92400E">Silakan perbaiki jawaban Anda di bawah ini dan klik tombol <strong>Kumpulkan Ulang Jawaban</strong>.</p>
            </div>
        @elseif($submission->status === \App\Models\Submission::STATUS_SUBMITTED)
            <div style="background: #ECFDF5; border: 1.5px solid #A7F3D0; border-radius: 20px; padding: 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px">
                <div style="display: flex; align-items: center; gap: 14px">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #D1FAE5; color: #059669; display: flex; align-items: center; justify-content: center">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #065F46">Tugas sudah Anda kirim</h3>
                        <span style="font-size: 13px; color: #047857">Terkirim pada {{ \Carbon\Carbon::parse($submission->submitted_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</span>
                        @if($submission->is_late)
                            <span style="font-size: 11px; font-weight: 700; color: #DC2626; margin-left: 6px">(Terlambat)</span>
                        @endif
                    </div>
                </div>
                <span style="font-size: 13px; font-weight: 700; color: #065F46; background: #D1FAE5; padding: 6px 14px; border-radius: 10px; border: 1px solid #A7F3D0">
                    Menunggu Penilaian Guru
                </span>
            </div>
        @endif
    @endif

    <!-- Pesan jika terkunci -->
    @if($isLocked && !empty($lockReason) && !$assessment->isGroup())
        <div style="padding: 16px 20px; border-radius: 16px; background: #F4F5F2; border: 1px solid #E2E4DF; color: #4A515C; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #6B7280"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <span>{{ $lockReason }}</span>
        </div>
    @endif

    <!-- Bagian Tugas Kelompok -->
    @if($assessment->isGroup())
        @if($myGroup)
            <!-- Kartu Informasi Kelompok Siswa -->
            <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 18px">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid #EDEEEA; padding-bottom: 16px">
                    <div style="display: flex; align-items: center; gap: 12px">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: #EDE9FE; color: #6D28D9; display: flex; align-items: center; justify-content: center">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px">
                                <span style="font-size: 11px; font-weight: 700; color: #6D28D9; text-transform: uppercase; letter-spacing: 0.05em">Kelompok Anda</span>
                                @if($isLeader)
                                    <span style="font-size: 11px; font-weight: 700; background: #EDE9FE; color: #6D28D9; padding: 2px 8px; border-radius: 6px">Anda adalah Ketua</span>
                                @else
                                    <span style="font-size: 11px; font-weight: 700; background: #F3F4F6; color: #4B5563; padding: 2px 8px; border-radius: 6px">Anggota</span>
                                @endif
                            </div>
                            <h2 style="margin: 2px 0 0; font-size: 20px; font-weight: 800; color: #15191F">{{ $myGroup->name }}</h2>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px">
                        <span style="font-size: 13px; color: #4A515C; background: #F4F5F2; padding: 6px 12px; border-radius: 10px">
                            Anggota: <strong style="color: #15191F">{{ $myGroup->members->count() }}</strong> / {{ $assessment->max_group_members ?? 4 }}
                        </span>
                        @if($isLeader && (!$submission || $canResubmit))
                            <button 
                                type="button" 
                                wire:click="disbandGroup" 
                                wire:confirm="Yakin ingin membubarkan kelompok ini? Semua anggota akan dikeluarkan."
                                style="background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; padding: 6px 12px; border-radius: 10px; font-size: 12px; font-weight: 600; cursor: pointer"
                            >
                                Bubarkan Kelompok
                            </button>
                        @elseif(!$isLeader && (!$submission || $canResubmit))
                            <button 
                                type="button" 
                                wire:click="leaveGroup" 
                                wire:confirm="Yakin ingin keluar dari kelompok ini?"
                                style="background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; padding: 6px 12px; border-radius: 10px; font-size: 12px; font-weight: 600; cursor: pointer"
                            >
                                Keluar Kelompok
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Daftar Anggota -->
                <div style="display: flex; flex-direction: column; gap: 10px">
                    <span style="font-size: 12px; font-weight: 700; color: #4A515C; text-transform: uppercase; letter-spacing: 0.05em">Daftar Anggota Kelompok:</span>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px">
                        @foreach($myGroup->members as $member)
                            @php
                                $isThisMemberLeader = ((int)$member->id === (int)$myGroup->leader_id);
                            @endphp
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 12px; background: {{ $isThisMemberLeader ? '#F5F3FF' : '#F9FAFB' }}; border: 1px solid {{ $isThisMemberLeader ? '#DDD6FE' : '#E5E7EB' }}">
                                <div style="display: flex; align-items: center; gap: 10px">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $isThisMemberLeader ? '#7C3AED' : '#E5E7EB' }}; color: {{ $isThisMemberLeader ? '#FFFFFF' : '#374151' }}; font-weight: 700; font-size: 12px; display: flex; align-items: center; justify-content: center">
                                        {{ substr($member->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="font-size: 13px; font-weight: 700; color: #15191F">
                                            {{ $member->name }}
                                            @if($member->id === auth()->id())
                                                <span style="font-size: 11px; color: #6D28D9; font-weight: 600">(Anda)</span>
                                            @endif
                                        </div>
                                        <div style="font-size: 11px; color: #6B7280; font-weight: 500">
                                            {{ $isThisMemberLeader ? 'Ketua Kelompok' : 'Anggota' }}
                                        </div>
                                    </div>
                                </div>

                                @if($isLeader && !$isThisMemberLeader && (!$submission || $canResubmit))
                                    <button 
                                        type="button" 
                                        wire:click="removeMemberFromGroup({{ $member->id }})" 
                                        wire:confirm="Keluarkan {{ $member->name }} dari kelompok?"
                                        title="Keluarkan dari kelompok"
                                        style="background: none; border: none; color: #DC2626; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Tambah Anggota (Jika Ketua & Belum Penuh & Belum Kumpul/Revisi) -->
                @if($isLeader && $myGroup->members->count() < ($assessment->max_group_members ?? 4) && (!$submission || $canResubmit))
                    <div style="padding-top: 14px; border-top: 1px solid #EDEEEA; display: flex; flex-direction: column; gap: 8px">
                        <span style="font-size: 12px; font-weight: 700; color: #4A515C">Tambah Teman Sekelas ke Kelompok:</span>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center">
                            <select 
                                wire:model="selectedNewMemberId" 
                                style="flex: 1; min-width: 200px; padding: 8px 12px; font-size: 13px; border-radius: 10px; border: 1px solid #E2E4DF; background: #FFFFFF; outline: none"
                            >
                                <option value="">-- Pilih Siswa yang Belum Memiliki Kelompok --</option>
                                @foreach($availableStudents as $avail)
                                    <option value="{{ $avail->id }}">{{ $avail->name }} (NISN: {{ $avail->nisn ?? '-' }})</option>
                                @endforeach
                            </select>
                            <button 
                                type="button" 
                                wire:click="addSelectedMember"
                                style="padding: 8px 16px; border-radius: 10px; background: #6D28D9; color: #FFFFFF; font-weight: 700; font-size: 13px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px"
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>Tambah ke Kelompok</span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Laporan Peran & Kontribusi Anggota -->
                <div style="padding-top: 14px; border-top: 1px solid #EDEEEA; display: flex; flex-direction: column; gap: 8px">
                    <div style="display: flex; align-items: center; justify-content: space-between">
                        <span style="font-size: 13px; font-weight: 700; color: #15191F">
                            Laporan Peran & Kontribusi Anggota
                        </span>
                        <span style="font-size: 11px; color: #6B7280">Dibaca guru saat penilaian</span>
                    </div>

                    @if($isLeader && !$isLocked)
                        <textarea 
                            wire:model="contributionNotes" 
                            rows="3" 
                            placeholder="Tuliskan pembagian tugas/peran anggota kelompok di sini. Contoh:&#10;1. {{ auth()->user()->name }} (Ketua): Membuat materi & presentasi&#10;2. Budi: Mengumpulkan data referensi"
                            style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 13px; font-family: inherit; border-radius: 10px; border: 1px solid #E2E4DF; background: #FFFFFF; outline: none; line-height: 1.5; resize: vertical"
                        ></textarea>
                        <span style="font-size: 11px; color: #6B7280">Catatan peran/kontribusi ini akan disimpan saat Anda mengklik tombol "Kumpulkan Tugas".</span>
                    @else
                        @if(!empty($myGroup->contribution_notes))
                            <div style="padding: 12px 14px; border-radius: 10px; background: #F9FAFB; border: 1px solid #E5E7EB; font-size: 13px; color: #374151; line-height: 1.5; white-space: pre-line">
                                {{ $myGroup->contribution_notes }}
                            </div>
                        @else
                            <div style="padding: 10px 14px; border-radius: 10px; background: #F9FAFB; border: 1px solid #E5E7EB; font-size: 12px; color: #9CA3AF; font-style: italic">
                                Belum ada catatan peran/kontribusi yang dicantumkan oleh ketua kelompok.
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @else
            <!-- Form Bentuk Kelompok Baru -->
            <div style="background: #FFFFFF; border: 2px dashed #C4B5FD; border-radius: 20px; padding: 28px; display: flex; flex-direction: column; gap: 18px">
                <div style="display: flex; align-items: flex-start; gap: 14px">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: #EDE9FE; color: #6D28D9; display: flex; align-items: center; justify-content: center; flex-shrink: 0">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #6D28D9; text-transform: uppercase; letter-spacing: 0.05em">Tugas Kelompok</span>
                        <h2 style="margin: 4px 0 0; font-size: 18px; font-weight: 800; color: #15191F">Anda Belum Memiliki Kelompok</h2>
                        <p style="margin: 4px 0 0; font-size: 13px; color: #4A515C; line-height: 1.5">
                            Tugas ini dikerjakan secara berkelompok (maksimal <strong>{{ $assessment->max_group_members ?? 4 }} siswa</strong> per kelompok). Silakan bentuk kelompok baru di bawah ini sebagai Ketua, atau Anda dapat menunggu dimasukkan oleh ketua kelompok lain atau guru.
                        </p>
                    </div>
                </div>

                <div style="padding: 20px; border-radius: 14px; background: #F9FAFB; border: 1px solid #E5E7EB; display: flex; flex-direction: column; gap: 14px">
                    <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #111827">Form Bentuk Kelompok Baru:</h3>

                    <div style="display: flex; flex-direction: column; gap: 6px">
                        <label style="font-size: 13px; font-weight: 600; color: #374151">Nama Kelompok <span style="color: #DC2626">*</span></label>
                        <input 
                            type="text" 
                            wire:model="newGroupName" 
                            placeholder="Contoh: Kelompok 1 / Tim Cyber Security"
                            style="padding: 10px 14px; font-size: 13px; border-radius: 10px; border: 1px solid #D1D5DB; background: #FFFFFF; outline: none"
                        >
                        @error('newGroupName') <span style="font-size: 12px; color: #DC2626;">{{ $message }}</span> @enderror
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 6px">
                        <label style="font-size: 13px; font-weight: 600; color: #374151">
                            Pilih Anggota Kelompok (Maksimal {{ ($assessment->max_group_members ?? 4) - 1 }} teman):
                        </label>
                        <div style="max-height: 180px; overflow-y: auto; padding: 10px; background: #FFFFFF; border: 1px solid #D1D5DB; border-radius: 10px; display: flex; flex-direction: column; gap: 6px">
                            @forelse($availableStudents as $avail)
                                <label style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: #374151; cursor: pointer; padding: 4px 6px; border-radius: 6px;">
                                    <input 
                                        type="checkbox" 
                                        wire:model="selectedMemberIds" 
                                        value="{{ $avail->id }}"
                                        style="width: 16px; height: 16px; accent-color: #6D28D9; cursor: pointer"
                                    >
                                    <span>{{ $avail->name }} <span style="font-size: 11px; color: #9CA3AF">(NISN: {{ $avail->nisn ?? '-' }})</span></span>
                                </label>
                            @empty
                                <div style="font-size: 12px; color: #9CA3AF; font-style: italic; padding: 8px">
                                    Semua teman sekelas Anda saat ini sudah bergabung ke kelompok lain.
                                </div>
                            @endforelse
                        </div>
                        @error('selectedMemberIds') <span style="font-size: 12px; color: #DC2626;">{{ $message }}</span> @enderror
                    </div>

                    <div style="display: flex; justify-content: flex-end; padding-top: 6px">
                        <button 
                            type="button" 
                            wire:click="createGroup"
                            style="padding: 10px 22px; border-radius: 10px; background: #6D28D9; color: #FFFFFF; font-weight: 700; font-size: 14px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Bentuk Kelompok & Jadi Ketua</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Form Pengumpulan Jawaban -->
    <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 28px; display: flex; flex-direction: column; gap: 20px">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #EDEEEA; padding-bottom: 12px">
            <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: #15191F">
                {{ $submission ? ($assessment->isGroup() ? 'Jawaban Kelompok' : 'Jawaban Anda') : ($assessment->isGroup() ? 'Form Pengumpulan Tugas Kelompok' : 'Form Pengumpulan Jawaban') }}
            </h2>
            @if($assessment->isGroup())
                <span style="font-size: 12px; font-weight: 600; color: #6D28D9; background: #EDE9FE; padding: 4px 10px; border-radius: 8px">
                    {{ $isLeader ? 'Anda Mengumpulkan Mewakili Kelompok' : 'Pengumpulan Dilakukan oleh Ketua' }}
                </span>
            @endif
        </div>

        @if($assessment->isGroup() && !$isLeader)
            <div style="padding: 14px 18px; border-radius: 12px; background: #F5F3FF; border: 1px solid #DDD6FE; color: #5B21B6; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>
                    Pengumpulan berkas jawaban dilakukan oleh Ketua Kelompok (<strong>{{ $myGroup?->leader?->name ?? 'Ketua' }}</strong>). Anda dapat melihat berkas dan status penilaian di bawah ini setelah ketua mengumpulkan.
                </span>
            </div>
        @endif

        <form wire:submit="submit" novalidate style="display: flex; flex-direction: column; gap: 20px">
            @php
                $types = $assessment->submission_types ?? ['file'];
            @endphp

            <!-- 1. Tipe Upload File -->
            @if(in_array('file', $types))
                <div style="display: flex; flex-direction: column; gap: 10px">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
                        <label style="font-size: 14px; font-weight: 700; color: #15191F">Berkas Jawaban (Dokumen/Gambar/ZIP)</label>
                        @if($submission && $submission->file_path && !$uploadedFile)
                            <span style="font-size: 11px; font-weight: 700; color: #16A34A; background: #DCFCE7; padding: 3px 8px; border-radius: 6px; border: 1px solid #BBF7D0;">
                                ✓ Sudah Ada Berkas Tersimpan
                            </span>
                        @endif
                    </div>

                    @if($submission && $submission->file_path)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: 12px; background: #F8FAFC; border: 1px solid #E2E8F0; gap: 12px;">
                            <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                <div style="width: 36px; height: 36px; border-radius: 10px; background: #EEF2FF; color: #2347C4; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                </div>
                                <div style="display: flex; flex-direction: column; min-width: 0;">
                                    <span style="font-size: 13px; font-weight: 700; color: #1E293B; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Berkas Jawaban Anda Sebelumnya</span>
                                    <span style="font-size: 11px; color: #64748B;">Klik tombol di kanan untuk mengunduh kembali berkas</span>
                                </div>
                            </div>
                            <a href="{{ route('guru.tugas.submission.download', $submission->id) }}" style="font-size: 12px; font-weight: 700; color: #2347C4; text-decoration: none; padding: 7px 14px; border-radius: 10px; background: #E8EDFC; border: 1px solid #C7D7FE; display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#DBE5FE'" onmouseout="this.style.backgroundColor='#E8EDFC'">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                <span>Unduh File</span>
                            </a>
                        </div>
                    @endif

                    @if(!$isLocked)
                        <div x-data="{ isDragging: false, uploadError: null }" 
                             x-on:livewire-upload-error="uploadError = 'Gagal mengunggah berkas. Pastikan ukuran file tidak melebihi batas 20 MB dan format sesuai.'"
                             x-on:livewire-upload-start="uploadError = null"
                             style="display: flex; flex-direction: column; gap: 10px;">
                            <!-- Custom Interactive Upload Dropzone -->
                            <div 
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop="isDragging = false"
                                class="relative rounded-2xl p-6 text-center transition-all duration-200 cursor-pointer"
                                :class="isDragging ? 'bg-[#EEF2FF] border-[#2347C4] shadow-md scale-[1.01]' : 'bg-[#F8FAFC] hover:bg-[#EEF2FF]/40 border-slate-300 hover:border-[#2347C4]'"
                                style="border-width: 2px; border-style: dashed;"
                            >
                                <input 
                                    type="file" 
                                    wire:model="uploadedFile" 
                                    id="fileUploadInput"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.jpg,.jpeg,.png,.webp"
                                >
                                
                                <div class="flex flex-col items-center pointer-events-none">
                                    <div class="w-14 h-14 rounded-2xl bg-white shadow-xs border border-slate-200 flex items-center justify-center text-[#2347C4] mb-3 transition-transform"
                                         :style="isDragging ? 'background-color: #2347C4; color: #FFFFFF; border-color: #2347C4;' : ''">
                                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                             <polyline points="17 8 12 3 7 8"></polyline>
                                            <line x1="12" y1="3" x2="12" y2="15"></line>
                                        </svg>
                                    </div>
                                    
                                    <div class="flex flex-wrap items-center justify-center gap-2 mb-2">
                                        <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white shadow-sm transition-all"
                                              style="background-color: #2347C4;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"></path></svg>
                                            <span>Pilih Berkas Jawaban</span>
                                        </span>
                                        <span class="text-xs sm:text-sm font-semibold text-slate-500">atau seret dan lepas berkas ke sini</span>
                                    </div>

                                    <p class="text-xs text-slate-500 max-w-md m-0">
                                        Format yang didukung: <span class="font-semibold text-slate-700">PPT/PPTX, PDF, DOC/DOCX, XLS/XLSX, Gambar, atau ZIP</span> (Maks. 20 MB).
                                    </p>
                                </div>
                            </div>

                            <!-- Upload Loading Indicator -->
                            <div wire:loading.flex wire:target="uploadedFile" class="p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-[#2347C4] text-xs font-semibold items-center gap-2.5 animate-pulse" style="display: none;">
                                <svg class="animate-spin h-4 w-4 text-[#2347C4] shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Sedang memproses & mengunggah berkas ke sistem... Mohon tunggu sebentar.</span>
                            </div>

                            <!-- Upload Client Error from Alpine -->
                            <template x-if="uploadError">
                                <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold flex items-center gap-2">
                                    <svg class="w-4 h-4 text-red-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                    <span x-text="uploadError"></span>
                                </div>
                            </template>

                            <!-- Error Message -->
                            @error('uploadedFile')
                                <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold flex items-center gap-2">
                                    <svg class="w-4 h-4 text-red-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror

                            <!-- Preview Berkas yang Baru Dipilih -->
                            @if($uploadedFile)
                                <div class="p-4 rounded-xl bg-emerald-50/80 border border-emerald-200 flex items-center justify-between gap-3 shadow-xs transition-all">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-xs font-bold text-emerald-900 truncate max-w-[220px] sm:max-w-md">
                                                    {{ $uploadedFile->getClientOriginalName() }}
                                                </span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 shrink-0">
                                                    Siap Dikumpulkan
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-emerald-700 mt-0.5 m-0">
                                                Ukuran: {{ round($uploadedFile->getSize() / 1024, 1) > 1024 ? round($uploadedFile->getSize() / 1048576, 2) . ' MB' : round($uploadedFile->getSize() / 1024, 1) . ' KB' }}
                                                &bull; Berkas akan disimpan saat Anda menekan tombol "Kumpulkan Jawaban".
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button 
                                            type="button" 
                                            wire:click="removeUploadedFile" 
                                            class="text-xs font-bold text-red-600 hover:text-red-800 px-3 py-1.5 rounded-lg border border-red-200 bg-white hover:bg-red-50 transition-colors flex items-center gap-1 cursor-pointer"
                                            title="Batalkan berkas ini"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            <span>Hapus</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <!-- 2. Tipe Tulis Teks -->
            @if(in_array('text', $types))
                <div style="display: flex; flex-direction: column; gap: 8px">
                    <label style="font-size: 14px; font-weight: 700; color: #15191F">Teks Jawaban Langsung</label>
                    <textarea 
                        wire:model="textContent" 
                        rows="6" 
                        placeholder="Ketik jawaban atau penjelasan tugas Anda di sini..."
                        {{ $isLocked ? 'disabled' : '' }}
                        style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-family: inherit; border-radius: 12px; border: 1px solid #E2E4DF; background: {{ $isLocked ? '#F4F5F2' : '#FFFFFF' }}; outline: none; line-height: 1.5; resize: vertical"
                    ></textarea>
                    @error('textContent') <span style="font-size: 12px; color: #DC2626;">{{ $message }}</span> @enderror
                </div>
            @endif

            <!-- 3. Tipe Tautan URL -->
            @if(in_array('link', $types))
                <div style="display: flex; flex-direction: column; gap: 8px">
                    <label style="font-size: 14px; font-weight: 700; color: #15191F">Tautan Tugas (URL)</label>
                    <input 
                        type="url" 
                        wire:model="linkUrl" 
                        placeholder="https://..."
                        {{ $isLocked ? 'disabled' : '' }}
                        style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 12px; border: 1px solid #E2E4DF; background: {{ $isLocked ? '#F4F5F2' : '#FFFFFF' }}; outline: none;"
                    >
                    @if($isLocked && $linkUrl)
                        <div style="margin-top: 2px;">
                            <a href="{{ $linkUrl }}" target="_blank" style="font-size: 12px; color: #2347C4; font-weight: 600; text-decoration: underline;">
                                Buka Tautan yang Anda Kirim &nearr;
                            </a>
                        </div>
                    @endif
                    @error('linkUrl') <span style="font-size: 12px; color: #DC2626;">{{ $message }}</span> @enderror
                </div>
            @endif

            <!-- Pesan Notifikasi Tepat di Atas Tombol (Mudah Terlihat Oleh Siswa) -->
            @if(session()->has('error'))
                <div style="padding: 12px 16px; border-radius: 12px; background: #FEE2E2; border: 1px solid #FECACA; color: #B91C1C; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Tombol Kumpulkan / Status Tugas Sudah Terkirim -->
            @if(!$isLocked)
                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; padding-top: 14px; border-top: 1px solid #EDEEEA">
                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        wire:target="submit,uploadedFile"
                        style="min-height: 48px; padding: 0 32px; border-radius: 12px; background: #2347C4; color: #FFFFFF; font-weight: 700; font-size: 15px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(35, 71, 196, 0.25);"
                        onmouseover="this.style.backgroundColor='#1A3596'"
                        onmouseout="this.style.backgroundColor='#2347C4'"
                    >
                        <span wire:loading.remove wire:target="submit,uploadedFile" class="inline-flex items-center gap-2">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>{{ $submission ? ($submission->status === \App\Models\Submission::STATUS_REVISION ? 'Kumpulkan Ulang Jawaban Revisi' : 'Kirim Ulang Tugas') : 'Kirim Tugas' }}</span>
                        </span>
                        <span wire:loading.inline-flex wire:target="uploadedFile" class="items-center gap-2" style="display: none;">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Mengunggah Berkas...</span>
                        </span>
                        <span wire:loading.inline-flex wire:target="submit" class="items-center gap-2" style="display: none;">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Sedang Mengirim Tugas...</span>
                        </span>
                    </button>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 12px; padding-top: 16px; border-top: 1px solid #EDEEEA">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 16px 20px; border-radius: 16px; background: #ECFDF5; border: 1.5px solid #A7F3D0;">
                        <div style="display: flex; align-items: center; gap: 12px">
                            <div style="width: 42px; height: 42px; border-radius: 12px; background: #D1FAE5; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 15px; font-weight: 800; color: #065F46">Tugas sudah Anda kirim</h4>
                                <p style="margin: 2px 0 0; font-size: 12px; color: #047857">
                                    @if($submission && $submission->submitted_at)
                                        Terkirim pada {{ \Carbon\Carbon::parse($submission->submitted_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB.
                                    @endif
                                    {{ $lockReason ?: 'Menunggu pemeriksaan dan penilaian dari guru.' }}
                                </p>
                            </div>
                        </div>
                        <span style="font-size: 12px; font-weight: 700; color: #065F46; background: #D1FAE5; padding: 6px 14px; border-radius: 10px; border: 1px solid #A7F3D0;">
                            {{ $submission && $submission->status === \App\Models\Submission::STATUS_GRADED ? 'Sudah Dinilai' : 'Menunggu Penilaian Guru' }}
                        </span>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>
