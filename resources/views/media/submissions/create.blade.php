@php($isRevision = isset($submission) && $submission?->status === 'perbaikan')
<x-app-layout>
    <x-slot name="header"><div><p class="simedkom-label text-cyan-600">{{ $isRevision ? 'Perbaikan pengajuan' : 'Pengajuan baru' }}</p><h2 class="mt-1 text-2xl font-extrabold text-slate-900">Profil & dokumen media</h2></div></x-slot>
    <div class="py-8 sm:py-10"><div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        @include('media.submissions._form')
    </div></div>
</x-app-layout>
