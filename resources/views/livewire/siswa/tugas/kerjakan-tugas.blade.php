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
    @if($isLocked && !empty($lockReason))
        <div style="padding: 16px 20px; border-radius: 16px; background: #F4F5F2; border: 1px solid #E2E4DF; color: #4A515C; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #6B7280"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <span>{{ $lockReason }}</span>
        </div>
    @endif

    <!-- Form Pengumpulan Jawaban -->
    <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 28px; display: flex; flex-direction: column; gap: 20px">
        <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: #15191F; border-bottom: 1px solid #EDEEEA; pb: 12px; padding-bottom: 12px">
            {{ $submission ? 'Jawaban Anda' : 'Form Pengumpulan Jawaban' }}
        </h2>

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
                                Format yang didukung: PDF, Word, Excel, PPT, Gambar, atau ZIP (Maks. 10 MB).
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
