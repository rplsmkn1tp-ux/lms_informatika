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
            <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px">
                <div style="display: flex; align-items: center; gap: 14px">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #E8EDFC; color: #2347C4; display: flex; align-items: center; justify-content: center">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #15191F">Tugas Telah Dikumpulkan</h3>
                        <span style="font-size: 12px; color: #4A515C">Dikirim pada {{ \Carbon\Carbon::parse($submission->submitted_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</span>
                        @if($submission->is_late)
                            <span style="font-size: 11px; font-weight: 700; color: #DC2626; margin-left: 6px">(Terlambat)</span>
                        @endif
                    </div>
                </div>
                <span style="font-size: 13px; font-weight: 700; color: #B45309; background: #FEF3C7; padding: 6px 14px; border-radius: 10px;">
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

        <form wire:submit.prevent="submit" style="display: flex; flex-direction: column; gap: 20px">
            @php
                $types = $assessment->submission_types ?? ['file'];
            @endphp

            <!-- 1. Tipe Upload File -->
            @if(in_array('file', $types))
                <div style="display: flex; flex-direction: column; gap: 8px">
                    <label style="font-size: 14px; font-weight: 700; color: #15191F">Berkas Jawaban (Dokumen/Gambar/ZIP)</label>

                    @if($submission && $submission->file_path)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: 12px; background: #F4F5F2; border: 1px solid #E2E4DF">
                            <div style="display: flex; align-items: center; gap: 10px">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #2347C4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                <span style="font-size: 13px; font-weight: 600; color: #15191F">Berkas yang telah diunggah</span>
                            </div>
                            <a href="{{ route('guru.tugas.submission.download', $submission->id) }}" style="font-size: 12px; font-weight: 700; color: #2347C4; text-decoration: none; padding: 6px 12px; border-radius: 8px; background: #E8EDFC;">
                                Unduh File Anda
                            </a>
                        </div>
                    @endif

                    @if(!$isLocked)
                        <div style="margin-top: 4px;">
                            <input 
                                type="file" 
                                wire:model="uploadedFile" 
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.jpg,.jpeg,.png,.webp"
                                style="font-size: 13px"
                            >
                            <span style="font-size: 12px; color: #6B7280; display: block; margin-top: 4px;">
                                Format yang didukung: PPT/PPTX, PDF, DOC/DOCX, XLS/XLSX, Gambar, atau ZIP (Maks. 20 MB).
                            </span>
                            <div wire:loading wire:target="uploadedFile" style="font-size: 12px; color: #2347C4; margin-top: 4px;">
                                Mengunggah file ke preview...
                            </div>
                            @error('uploadedFile') <span style="font-size: 12px; color: #DC2626; display: block; margin-top: 4px;">{{ $message }}</span> @enderror
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

            <!-- Tombol Kumpulkan -->
            @if(!$isLocked)
                <div style="display: flex; justify-content: flex-end; padding-top: 12px; border-top: 1px solid #EDEEEA">
                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        style="min-height: 46px; padding: 0 28px; border-radius: 12px; background: #2347C4; color: #FFFFFF; font-weight: 700; font-size: 15px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s;"
                        onmouseover="this.style.backgroundColor='#1A3596'"
                        onmouseout="this.style.backgroundColor='#2347C4'"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>{{ $submission && $submission->status === \App\Models\Submission::STATUS_REVISION ? 'Kumpulkan Ulang Jawaban Revisi' : 'Kumpulkan Tugas' }}</span>
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>
