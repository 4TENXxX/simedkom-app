<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="simedkom-label text-cyan-600">Kemitraan media</p>
            <h2 class="mt-1 text-2xl font-extrabold text-slate-900">Tambah jurnalis</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('media.journalists.store') }}" enctype="multipart/form-data" class="simedkom-card space-y-6 p-6 sm:p-8">
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
                    <label class="simedkom-label" for="name">Nama lengkap</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="simedkom-input mt-2" required>
                </div>

                <div>
                    <label class="simedkom-label" for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="simedkom-input mt-2" required>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="simedkom-label" for="password">Password</label>
                        <input type="password" id="password" name="password" class="simedkom-input mt-2" required>
                    </div>
                    <div>
                        <label class="simedkom-label" for="password_confirmation">Konfirmasi password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="simedkom-input mt-2" required>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-6">
                    <p class="simedkom-label">Persyaratan wartawan</p>
                    <p class="mt-1 text-xs text-slate-500">Unggah pas foto wartawan dan sertifikat uji kompetensi wartawan. Maksimal 5 MB per berkas.</p>
                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="simedkom-label" for="foto_wartawan">Pas foto wartawan</label>
                            <input type="file" id="foto_wartawan" name="foto_wartawan" accept=".jpg,.jpeg,.png" class="simedkom-input mt-2" required>
                        </div>
                        <div>
                            <label class="simedkom-label" for="sertifikat_kompetensi">Sertifikat kompetensi wartawan</label>
                            <input type="file" id="sertifikat_kompetensi" name="sertifikat_kompetensi" accept=".pdf,.jpg,.jpeg" class="simedkom-input mt-2" required>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="simedkom-label" for="phone">Telepon</label>
                        <input id="phone" name="phone" value="{{ old('phone') }}" class="simedkom-input mt-2">
                    </div>
                    <div>
                        <label class="simedkom-label" for="position">Posisi</label>
                        <input id="position" name="position" value="{{ old('position') }}" class="simedkom-input mt-2">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white">Simpan jurnalis</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
