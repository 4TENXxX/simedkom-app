<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="simedkom-label text-cyan-600">Diseminasi informasi</p>
            <h2 class="mt-1 text-2xl font-extrabold text-slate-900">Buat siaran pers</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.press-releases.store') }}" enctype="multipart/form-data" class="simedkom-card space-y-6 p-6 sm:p-8">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="simedkom-label" for="title">Judul</label>
                    <input id="title" name="title" value="{{ old('title') }}" class="simedkom-input mt-2" required>
                </div>

                <div>
                    <label class="simedkom-label" for="summary">Ringkasan</label>
                    <textarea id="summary" name="summary" rows="2" class="simedkom-input mt-2">{{ old('summary') }}</textarea>
                </div>

                <div>
                    <label class="simedkom-label" for="body">Isi berita</label>
                    <textarea id="body" name="body" rows="8" class="simedkom-input mt-2" required>{{ old('body') }}</textarea>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="simedkom-label" for="category">Kategori</label>
                        <input id="category" name="category" value="{{ old('category') }}" class="simedkom-input mt-2">
                    </div>
                    <div>
                        <label class="simedkom-label" for="status">Status</label>
                        <select id="status" name="status" class="simedkom-input mt-2" required>
                            @foreach (['draft', 'terjadwal', 'dipublikasikan', 'diarsipkan'] as $status)
                                <option value="{{ $status }}" @selected(old('status', 'draft') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="simedkom-label" for="published_at">Tanggal publikasi terjadwal</label>
                    <input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at') }}" class="simedkom-input mt-2">
                </div>

                <div>
                    <label class="simedkom-label" for="attachment">Lampiran</label>
                    <input type="file" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.mp3,.mp4" class="simedkom-input mt-2">
                </div>

                <div class="flex justify-end">
                    <button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white">Simpan siaran pers</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
