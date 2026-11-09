@php($isRevision = isset($submission) && $submission?->status === 'perbaikan')
<form method="POST" action="{{ $isRevision ? route('media.submissions.update', $submission) : route('media.submissions.store') }}" enctype="multipart/form-data" class="simedkom-card overflow-hidden">
    @csrf
    @if($isRevision) @method('PATCH') @endif
    <div class="flex items-start justify-between gap-4 border-b border-slate-100 bg-slate-50/70 px-6 py-6 sm:px-8">
        <div>
            <p class="text-sm leading-6 text-slate-500">@if($isRevision) Perbarui data atau ganti dokumen yang diminta admin, lalu kirim ulang untuk diverifikasi.@else Lengkapi informasi di bawah ini. Data akan digunakan dalam proses verifikasi administrasi media.@endif</p>
            @if($isRevision && $submission->catatan_admin)
                <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900"><strong>Catatan admin:</strong> {{ $submission->catatan_admin }}</p>
            @endif
        </div>
        @if($isRevision)<button type="button" x-on:click="$dispatch('close-modal', 'submission-form')" class="text-2xl leading-none text-slate-400 hover:text-slate-700" aria-label="Tutup">×</button>@endif
    </div>
    <div class="space-y-8 p-6 sm:p-8">
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><p class="font-bold">Periksa kembali data Anda</p><ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <section>
            <div class="mb-5 flex items-center gap-3"><span class="grid h-8 w-8 place-items-center rounded-lg bg-cyan-100 text-sm font-extrabold text-cyan-700">01</span><div><h3 class="font-extrabold text-slate-900">Informasi media</h3><p class="text-xs text-slate-500">Identitas dasar perusahaan atau kanal media</p></div></div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2"><label class="simedkom-label" for="nama_media">Nama media</label><input id="nama_media" name="nama_media" value="{{ old('nama_media', auth()->user()->mediaProfile?->nama_media) }}" class="simedkom-input" required></div>
                <div><label class="simedkom-label" for="jenis_media">Jenis media</label><select id="jenis_media" name="jenis_media" class="simedkom-input" required><option value="">Pilih jenis media</option>@foreach(['online' => 'Media online', 'cetak' => 'Media cetak', 'tv' => 'Televisi'] as $value => $label)<option value="{{ $value }}" @selected(old('jenis_media', auth()->user()->mediaProfile?->jenis_media) === $value)>{{ $label }}</option>@endforeach</select></div>
                <div><label class="simedkom-label" for="tahun_anggaran">Tahun anggaran</label><input id="tahun_anggaran" name="tahun_anggaran" type="number" value="{{ old('tahun_anggaran', $submission?->tahun_anggaran ?? date('Y')) }}" class="simedkom-input" required></div>
                <div class="sm:col-span-2"><label class="simedkom-label" for="alamat">Alamat kantor</label><textarea id="alamat" name="alamat" rows="3" class="simedkom-input" required>{{ old('alamat', auth()->user()->mediaProfile?->alamat) }}</textarea></div>
                <div><label class="simedkom-label" for="no_whatsapp">Nomor WhatsApp</label><input id="no_whatsapp" name="no_whatsapp" value="{{ old('no_whatsapp', auth()->user()->mediaProfile?->no_whatsapp) }}" class="simedkom-input" placeholder="08xxxxxxxxxx" required></div>
                <div><label class="simedkom-label" for="link_website">Website</label><input id="link_website" type="url" name="link_website" value="{{ old('link_website', auth()->user()->mediaProfile?->link_website) }}" placeholder="https://website-anda.id" class="simedkom-input"></div>
            </div>
        </section>
        <section class="border-t border-slate-100 pt-8">
            @php($documentTypes = \App\Models\Document::types())
            <div class="mb-5 flex items-center gap-3"><span class="grid h-8 w-8 place-items-center rounded-lg bg-cyan-100 text-sm font-extrabold text-cyan-700">02</span><div><h3 class="font-extrabold text-slate-900">Dokumen persyaratan</h3><p class="text-xs text-slate-500">@if($isRevision) Pilih hanya dokumen yang ingin diganti. Berkas lama tetap digunakan jika tidak diganti. @else Semua dokumen wajib diunggah dalam format PDF atau JPG, maksimal 5 MB per dokumen. @endif</p></div></div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($documentTypes as $key => $label)
                    @php($existingDocument = $submission?->documents->firstWhere('jenis_dokumen', $key))
                    <label class="group flex min-h-40 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 p-5 text-center transition hover:border-cyan-400 hover:bg-cyan-50/60"><span class="grid h-11 w-11 place-items-center rounded-xl bg-slate-100 text-xs font-extrabold text-slate-600 group-hover:bg-cyan-100 group-hover:text-cyan-700">{{ $loop->iteration }}</span><span class="mt-3 text-sm font-bold text-slate-700">{{ $label }}</span><input type="file" name="documents[{{ $key }}]" accept=".pdf,.jpg,.jpeg" class="sr-only" @required(!$isRevision) onchange="this.closest('label').querySelector('[data-file]').textContent = this.files[0]?.name || 'Klik untuk memilih file'"><span data-file class="mt-1 max-w-full truncate text-xs text-slate-400">{{ $isRevision && $existingDocument ? 'Berkas tersimpan · klik untuk mengganti' : 'Klik untuk memilih file' }}</span></label>
                @endforeach
            </div>
        </section>
    </div>
    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-5 sm:flex-row sm:justify-end sm:px-8"><button type="button" x-on:click="$dispatch('close-modal', 'submission-form')" class="rounded-xl px-5 py-3 text-center text-sm font-bold text-slate-600 hover:bg-slate-100">Batal</button><button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-slate-900/10 hover:bg-slate-700">{{ $isRevision ? 'Kirim perbaikan →' : 'Kirim untuk verifikasi →' }}</button></div>
</form>
