@props(['step' => 1])

<div x-data="vonNeumannSim()" style="display: flex; flex-direction: column; gap: 14px">
    <div style="border-radius: 18px; background: #151A22; border: 1px solid #232A35; padding: 20px">
        <div style="display: grid; grid-template-columns: minmax(0, 1fr) 28px minmax(0, 1.6fr) 28px minmax(0, 1fr); align-items: center">
            <div :style="`border-color: ${c.inp.border}; background: ${c.inp.bg};`" style="grid-column: 1; grid-row: 1; padding: 12px; border-radius: 12px; border: 2px solid #2B323D; display: flex; flex-direction: column; gap: 2px; min-width: 0">
                <span :style="`color: ${c.inp.code}`" style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; font-weight: 600;">IN</span>
                <span :style="`color: ${c.inp.name}`" style="font-size: 14px; font-weight: 700;">Perangkat input</span>
                <span :style="`color: ${c.inp.sub}`" style="font-size: 12px;">Keyboard</span>
            </div>
            <span style="grid-column: 2; grid-row: 1; display: flex; justify-content: center; color: #5C6676">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
            </span>
            <div style="grid-column: 3; grid-row: 1; padding: 10px; border-radius: 14px; border: 1.5px dashed #3A4250; display: flex; flex-direction: column; gap: 8px; min-width: 0">
                <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 11px; letter-spacing: 0.08em; color: #9AA3B2">CPU</span>
                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px">
                    <div :style="`border-color: ${c.cu.border}; background: ${c.cu.bg};`" style="padding: 10px; border-radius: 10px; border: 2px solid #2B323D; display: flex; flex-direction: column; gap: 2px; min-width: 0">
                        <span :style="`color: ${c.cu.code}`" style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; font-weight: 600;">CU</span>
                        <span :style="`color: ${c.cu.name}`" style="font-size: 13px; font-weight: 700;">Unit kendali</span>
                    </div>
                    <div :style="`border-color: ${c.alu.border}; background: ${c.alu.bg};`" style="padding: 10px; border-radius: 10px; border: 2px solid #2B323D; display: flex; flex-direction: column; gap: 2px; min-width: 0">
                        <span :style="`color: ${c.alu.code}`" style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; font-weight: 600;">ALU</span>
                        <span :style="`color: ${c.alu.name}`" style="font-size: 13px; font-weight: 700;">Aritmetika &amp; logika</span>
                    </div>
                </div>
            </div>
            <span style="grid-column: 4; grid-row: 1; display: flex; justify-content: center; color: #5C6676">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
            </span>
            <div :style="`border-color: ${c.outp.border}; background: ${c.outp.bg};`" style="grid-column: 5; grid-row: 1; padding: 12px; border-radius: 12px; border: 2px solid #2B323D; display: flex; flex-direction: column; gap: 2px; min-width: 0">
                <span :style="`color: ${c.outp.code}`" style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; font-weight: 600;">OUT</span>
                <span :style="`color: ${c.outp.name}`" style="font-size: 14px; font-weight: 700;">Perangkat output</span>
                <span :style="`color: ${c.outp.sub}`" style="font-size: 12px;">Monitor</span>
            </div>
            <span style="grid-column: 3; grid-row: 2; display: flex; justify-content: center; padding: 6px 0; color: #5C6676">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v16M8 8l4-4 4 4M8 16l4 4 4-4"></path></svg>
            </span>
            <div :style="`border-color: ${c.mem.border}; background: ${c.mem.bg};`" style="grid-column: 3; grid-row: 3; padding: 12px; border-radius: 12px; border: 2px solid #2B323D; display: flex; flex-direction: column; gap: 2px; min-width: 0">
                <span :style="`color: ${c.mem.code}`" style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; font-weight: 600;">RAM</span>
                <span :style="`color: ${c.mem.name}`" style="font-size: 14px; font-weight: 700;">Memori utama</span>
                <span :style="`color: ${c.mem.sub}`" style="font-size: 12px;">Data + program</span>
            </div>
        </div>

        <div aria-live="polite" style="margin-top: 18px; padding: 16px 18px; border-radius: 14px; background: #0E1117; border: 1px solid #232A35; display: flex; flex-direction: column; gap: 6px">
            <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 11px; letter-spacing: 0.08em; color: #8B95A5" x-text="`LANGKAH ${step + 1} DARI 6`"></span>
            <span style="font-size: 18px; font-weight: 700; color: #FFFFFF" x-text="stepTitle"></span>
            <span style="font-size: 15px; color: #C5CBD6" x-text="stepText"></span>
            <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 14px; color: #F2C27B" x-text="stepData"></span>
        </div>

        <div style="margin-top: 16px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px">
            <button type="button" @click="prev" :disabled="step === 0" :style="step === 0 ? 'opacity: 0.4' : 'opacity: 1'" style="display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 0 16px; border-radius: 10px; border: 1px solid #3A4250; background: transparent; color: #E7EAF0; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"></path></svg>Sebelumnya
            </button>
            
            <div aria-hidden="true" style="display: flex; align-items: center; gap: 6px">
                <template x-for="(d, i) in dots" :key="i">
                    <span :style="`height: 8px; width: ${d.w}; border-radius: 99px; background: ${d.color}`"></span>
                </template>
            </div>
            
            <button type="button" @click="next" style="display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 10px; border: 0; background: #F2A93B; color: #15191F; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer" x-text="step >= 5 ? 'Ulangi simulasi' : 'Langkah berikutnya'"></button>
        </div>
    </div>
</div>

<script>
function vonNeumannSim() {
    return {
        step: 0,
        stepsData: [
            { active: 'inp', title: 'Input', text: 'Kamu mengetik 12 + 30 di keyboard. Perangkat input mengubah tombol yang ditekan menjadi data biner.', data: 'Masukan: "12 + 30"' },
            { active: 'mem', title: 'Disimpan di memori', text: 'Data dan instruksi program kalkulator disimpan bersama di memori utama. Inilah ciri khas arsitektur Von Neumann.', data: 'RAM: 12, 30, instruksi TAMBAH' },
            { active: 'cu', title: 'Ambil dan terjemahkan instruksi', text: 'Unit kendali mengambil instruksi dari memori lewat bus, lalu menerjemahkannya menjadi perintah: jumlahkan dua bilangan.', data: 'Instruksi: TAMBAH' },
            { active: 'alu', title: 'Eksekusi', text: 'ALU mengerjakan operasi aritmetika. Hasil sementara disimpan di register CPU.', data: '12 + 30 = 42' },
            { active: 'mem', title: 'Hasil disimpan', text: 'Hasil perhitungan dikirim kembali ke memori agar bisa dipakai program kalkulator.', data: 'RAM: 42' },
            { active: 'outp', title: 'Output', text: 'Hasil dikirim ke perangkat output, sehingga layar menampilkan angka 42.', data: 'Layar: 42' }
        ],
        get currentStep() {
            return this.stepsData[this.step];
        },
        get stepTitle() { return this.currentStep.title; },
        get stepText() { return this.currentStep.text; },
        get stepData() { return this.currentStep.data; },
        get c() {
            const ids = ['inp', 'mem', 'cu', 'alu', 'outp'];
            const styles = {};
            ids.forEach(id => {
                const on = this.currentStep.active === id;
                styles[id] = {
                    bg: on ? '#F2A93B' : '#1A2029',
                    border: on ? '#F2A93B' : '#2B323D',
                    name: on ? '#15191F' : '#E7EAF0',
                    sub: on ? '#3D2A10' : '#9AA3B2',
                    code: on ? '#15191F' : '#F2A93B'
                };
            });
            return styles;
        },
        get dots() {
            return this.stepsData.map((_, i) => ({
                w: i === this.step ? '22px' : '8px',
                color: i === this.step ? '#F2A93B' : (i < this.step ? '#8B95A5' : '#2B323D')
            }));
        },
        prev() {
            if (this.step > 0) this.step--;
        },
        next() {
            if (this.step >= 5) this.step = 0;
            else this.step++;
        }
    }
}
</script>
