<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden font-sans text-slate-900 antialiased">
        <div class="flex min-h-screen items-center justify-center bg-[#f4f7fb] px-4 py-10 sm:px-6">
            <div class="grid w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl shadow-slate-900/10 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="relative hidden overflow-hidden bg-slate-900 p-10 text-white lg:block"><div class="relative z-10 flex h-full flex-col justify-between"><div><div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-xl bg-cyan-400 font-extrabold text-slate-950">SK</span><span class="font-extrabold tracking-[0.18em]">SIMEDKOM</span></div><p class="mt-16 max-w-xs text-3xl font-extrabold leading-tight">Satu ruang untuk administrasi media yang lebih tertata.</p><p class="mt-5 max-w-sm text-sm leading-6 text-slate-300">Kelola pengajuan, dokumen, dan proses verifikasi dengan alur yang jelas.</p></div><p class="text-xs font-semibold uppercase tracking-widest text-cyan-300">Media Communication System</p></div><div class="absolute -bottom-24 -right-16 h-72 w-72 rounded-full border-[36px] border-cyan-400/10"></div></div>
                <div class="p-6 sm:p-10">
                    <div class="mb-8 lg:hidden"><span class="grid h-11 w-11 place-items-center rounded-xl bg-slate-900 font-extrabold text-cyan-300">SK</span><p class="mt-3 text-sm font-extrabold tracking-[0.18em]">SIMEDKOM</p></div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
