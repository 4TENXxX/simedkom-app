<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="simedkom-label text-cyan-600">Bukti publikasi</p>
            <h2 class="mt-1 text-2xl font-extrabold text-slate-900">Ajukan bukti tayang</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('media.clippings.store') }}" enctype="multipart/form-data" class="simedkom-card space-y-6 p-6 sm:p-8">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="simedkom-label" for="contract_id">Kontrak aktif</label>
                    <select id="contract_id" name="contract_id" class="simedkom-input mt-2" required>
                        <option value="">Pilih kontrak</option>
                        @foreach ($contracts as $contract)
                            <option value="{{ $contract->id }}" @selected(old('contract_id') == $contract->id)>
                                {{ $contract->nomor_kontrak }} (sisa kuota: {{ $contract->kuota_tersisa }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="simedkom-label" for="type">Jenis media</label>
                        <select id="type" name="type" class="simedkom-input mt-2" required>
                            @foreach (['online', 'cetak', 'tv', 'radio'] as $type)
                                <option value="{{ $type }}" @selected(old('type', 'online') === $type)>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="simedkom-label" for="published_at">Tanggal tayang</label>
                        <input type="date" id="published_at" name="published_at" value="{{ old('published_at') }}" class="simedkom-input mt-2" required>
                    </div>
                </div>

                <div>
                    <label class="simedkom-label" for="url">URL berita online</label>
                    <input type="url" id="url" name="url" value="{{ old('url') }}" class="simedkom-input mt-2" placeholder="https://">
                </div>

                <div>
                    <label class="simedkom-label" for="title">Judul berita</label>
                    <input id="title" name="title" value="{{ old('title') }}" class="simedkom-input mt-2" required>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="simedkom-label" for="author">Penulis</label>
                        <input id="author" name="author" value="{{ old('author') }}" class="simedkom-input mt-2">
                    </div>
                    <div>
                        <label class="simedkom-label" for="category">Kategori</label>
                        <input id="category" name="category" value="{{ old('category') }}" class="simedkom-input mt-2">
                    </div>
                </div>

                <div>
                    <label class="simedkom-label" for="files">Bukti tayang</label>
                    <input type="file" id="files" name="files[]" accept=".pdf,.jpg,.jpeg,.png,.mp3,.mp4" multiple class="simedkom-input mt-2">
                    <p class="mt-2 text-xs text-slate-500">Wajib untuk media cetak, televisi, dan radio.</p>
                </div>

                <div>
                    <label class="simedkom-label" for="notes">Keterangan</label>
                    <textarea id="notes" name="notes" rows="4" class="simedkom-input mt-2">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end">
                    <button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white">Kirim untuk validasi</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
