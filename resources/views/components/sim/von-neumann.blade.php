{{--
    Simulasi model komputer Von Neumann (SPEC bagian 8, subbab 5.2).
    Pemakaian: <x-sim.von-neumann />  atau  <x-sim.von-neumann :a="7" :b="5" title="..." />
    Komponen aktif menyala dan diberi label "Aktif" (status tidak hanya dibedakan warna).
--}}
@props([
    'a' => 12,
    'b' => 30,
    'title' => 'Simulasi: bagaimana komputer menghitung',
])

@php
    $result = $a + $b;
    $expr = "{$a} + {$b}";
    $steps = [
        ['active' => ['in'], 'title' => 'Input dari keyboard',
         'text' => "Kamu mengetik {$expr} lewat keyboard. Perangkat input mengubah ketikan menjadi data biner."],
        ['active' => ['in', 'ram'], 'title' => 'Data & instruksi disimpan di memori',
         'text' => "Angka {$a}, {$b}, dan instruksi TAMBAH dikirim lewat bus lalu disimpan di RAM. Data dan instruksi berada di memori yang sama."],
        ['active' => ['ram', 'cu'], 'title' => 'CU mengambil & menerjemahkan instruksi',
         'text' => 'Unit kendali (CU) mengambil instruksi dari RAM (fetch), lalu menerjemahkannya (decode): "tambahkan dua angka ini".'],
        ['active' => ['cu', 'alu'], 'title' => 'ALU mengeksekusi',
         'text' => "CU memerintahkan ALU. ALU menghitung {$expr} = {$result} (execute)."],
        ['active' => ['alu', 'ram'], 'title' => 'Hasil disimpan ke memori',
         'text' => "Hasil {$result} dikirim kembali dan disimpan di RAM."],
        ['active' => ['ram', 'out'], 'title' => 'Output ke layar',
         'text' => "Hasil {$result} diambil dari RAM dan dikirim ke perangkat output. Layar menampilkan {$result}."],
    ];
@endphp

<figure
    x-data="{
        step: 0,
        steps: @js($steps),
        get current() { return this.steps[this.step] },
        isActive(key) { return this.current.active.includes(key) },
        next() { if (this.step < this.steps.length - 1) this.step++ },
        prev() { if (this.step > 0) this.step-- },
        reset() { this.step = 0 },
    }"
    {{ $attributes->merge(['class' => 'not-prose my-6 rounded-[20px] border border-[#E2E4DF] bg-white p-4 sm:p-6 text-[#15191F]']) }}
    data-component="von-neumann"
>
    <figcaption class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
        <span class="text-base font-semibold">{{ $title }}</span>
        <span class="font-mono text-xs text-[#4A515C]">
            Langkah <span x-text="step + 1"></span>/<span x-text="steps.length"></span>
        </span>
    </figcaption>

    {{-- Diagram: IN · CPU (CU + ALU) · OUT, dengan RAM di bawah --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-[1fr_2fr_1fr]">
        @php
            $box = 'relative rounded-2xl border-2 p-3 transition-colors duration-300 min-h-[88px]';
        @endphp

        {{-- IN --}}
        <div class="{{ $box }}" data-unit="in"
             :class="isActive('in') ? 'border-[#2347C4] bg-[#E8EDFC]' : 'border-[#E2E4DF] bg-[#F4F5F2]'">
            <div class="font-mono text-xs font-semibold">IN</div>
            <div class="text-sm">Keyboard</div>
            <div class="mt-1 font-mono text-sm" x-show="step >= 0">{{ $expr }}</div>
            <span x-show="isActive('in')" class="absolute right-2 top-2 rounded-full bg-[#2347C4] px-2 py-0.5 text-[10px] font-semibold text-white">Aktif</span>
        </div>

        {{-- CPU --}}
        <div class="order-last col-span-2 rounded-2xl border border-dashed border-[#4A515C]/40 p-3 sm:order-none sm:col-span-1">
            <div class="mb-2 font-mono text-xs text-[#4A515C]">CPU</div>
            <div class="grid grid-cols-2 gap-3">
                <div class="{{ $box }}" data-unit="cu"
                     :class="isActive('cu') ? 'border-[#2347C4] bg-[#E8EDFC]' : 'border-[#E2E4DF] bg-[#F4F5F2]'">
                    <div class="font-mono text-xs font-semibold">CU</div>
                    <div class="text-sm">Unit kendali</div>
                    <div class="mt-1 font-mono text-xs" x-show="step >= 2">TAMBAH</div>
                    <span x-show="isActive('cu')" class="absolute right-2 top-2 rounded-full bg-[#2347C4] px-2 py-0.5 text-[10px] font-semibold text-white">Aktif</span>
                </div>
                <div class="{{ $box }}" data-unit="alu"
                     :class="isActive('alu') ? 'border-[#F2A93B] bg-[#FDF1DC]' : 'border-[#E2E4DF] bg-[#F4F5F2]'">
                    <div class="font-mono text-xs font-semibold">ALU</div>
                    <div class="text-sm">Aritmetika & logika</div>
                    <div class="mt-1 font-mono text-sm" x-show="step >= 3">= {{ $result }}</div>
                    <span x-show="isActive('alu')" class="absolute right-2 top-2 rounded-full bg-[#15191F] px-2 py-0.5 text-[10px] font-semibold text-white">Aktif</span>
                </div>
            </div>
        </div>

        {{-- OUT --}}
        <div class="{{ $box }}" data-unit="out"
             :class="isActive('out') ? 'border-[#2347C4] bg-[#E8EDFC]' : 'border-[#E2E4DF] bg-[#F4F5F2]'">
            <div class="font-mono text-xs font-semibold">OUT</div>
            <div class="text-sm">Layar</div>
            <div class="mt-1 font-mono text-2xl font-bold" x-show="step >= 5" x-transition>{{ $result }}</div>
            <span x-show="isActive('out')" class="absolute right-2 top-2 rounded-full bg-[#2347C4] px-2 py-0.5 text-[10px] font-semibold text-white">Aktif</span>
        </div>

        {{-- RAM --}}
        <div class="{{ $box }} order-last col-span-2 sm:col-span-3" data-unit="ram"
             :class="isActive('ram') ? 'border-[#2347C4] bg-[#E8EDFC]' : 'border-[#E2E4DF] bg-[#F4F5F2]'">
            <div class="font-mono text-xs font-semibold">RAM <span class="font-sans font-normal text-[#4A515C]">· memori utama</span></div>
            <div class="mt-2 flex flex-wrap gap-2 font-mono text-xs">
                <span class="rounded-lg border border-[#E2E4DF] bg-white px-2 py-1" x-show="step >= 1">[00] {{ $a }}</span>
                <span class="rounded-lg border border-[#E2E4DF] bg-white px-2 py-1" x-show="step >= 1">[01] {{ $b }}</span>
                <span class="rounded-lg border border-[#E2E4DF] bg-white px-2 py-1" x-show="step >= 1">[02] TAMBAH</span>
                <span class="rounded-lg border border-[#1D6B45] bg-white px-2 py-1 text-[#1D6B45]" x-show="step >= 4">[03] {{ $result }}</span>
                <span class="text-[#4A515C]" x-show="step === 0">(kosong)</span>
            </div>
            <span x-show="isActive('ram')" class="absolute right-2 top-2 rounded-full bg-[#2347C4] px-2 py-0.5 text-[10px] font-semibold text-white">Aktif</span>
        </div>
    </div>

    {{-- Penjelasan langkah --}}
    <div class="mt-4 rounded-2xl bg-[#F4F5F2] p-4" aria-live="polite">
        <div class="text-sm font-semibold">
            <span class="font-mono text-[#2347C4]" x-text="(step + 1) + '.'"></span>
            <span x-text="current.title"></span>
        </div>
        <p class="mt-1 text-sm leading-relaxed text-[#4A515C]" x-text="current.text"></p>
    </div>

    {{-- Kontrol --}}
    <div class="mt-4 flex flex-wrap items-center gap-2">
        <button type="button" @click="prev()" :disabled="step === 0"
                class="min-h-11 rounded-xl border border-[#E2E4DF] bg-white px-4 text-sm font-medium disabled:opacity-40"
                data-action="prev">
            Sebelumnya
        </button>
        <button type="button" x-show="step < steps.length - 1" @click="next()"
                class="min-h-11 flex-1 rounded-xl bg-[#2347C4] px-4 text-sm font-semibold text-white sm:flex-none"
                data-action="next">
            Langkah berikutnya
        </button>
        <button type="button" x-show="step === steps.length - 1" @click="reset()"
                class="min-h-11 flex-1 rounded-xl bg-[#15191F] px-4 text-sm font-semibold text-white sm:flex-none"
                data-action="reset">
            Ulangi simulasi
        </button>
    </div>
</figure>
