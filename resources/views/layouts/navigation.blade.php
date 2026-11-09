<nav class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-[68px] items-center justify-between gap-3 sm:gap-6 lg:h-[76px]">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-900 text-sm font-extrabold text-cyan-300 shadow-lg shadow-slate-900/10">SK</span>
                <span class="hidden sm:block"><span class="block text-sm font-extrabold tracking-[0.18em] text-slate-900">SIMEDKOM</span><span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Media Communication System</span></span>
            </a>
            <div class="hidden items-center gap-2 lg:flex">
                @if (Auth::user()->isAdminArea())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('admin.*') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">Verifikasi</a>
                    <a href="{{ route('admin.contracts.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('admin.contracts.*') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">Kontrak</a>
                    <a href="{{ route('admin.clippings.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('admin.clippings.*') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">Kliping</a>
                    <a href="{{ route('admin.press-releases.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('admin.press-releases.*') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">Diseminasi</a>
                    <a href="{{ route('admin.invoices.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('admin.invoices.*') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">E-Billing</a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('admin.audit-logs.*') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">Audit log</a>
                    <a href="{{ route('admin.settings.keywords') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('admin.settings.*') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">Pengaturan</a>
                    <a href="{{ route('admin.reports.clippings') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-50 hover:text-slate-900">Export laporan</a>
                @elseif (Auth::user()->role === 'media')
                    @php($mediaApproved = Auth::user()->submissions()->where('status', 'disetujui')->exists())
                    <a href="{{ route('media.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('media.dashboard') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">Ringkasan</a>
                    <a href="{{ route('media.submissions.create') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('media.submissions.*') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">Pengajuan</a>
                    @foreach ([['media.contracts.index', 'Kontrak'], ['media.clippings.index', 'Kliping'], ['media.press-releases.index', 'Siaran pers'], ['media.invoices.index', 'E-Billing'], ['media.journalists.index', 'Jurnalis']] as [$route, $label])
                        @if ($mediaApproved)
                            <a href="{{ route($route) }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs(str_replace('.index', '.*', $route)) ? 'bg-cyan-50 text-cyan-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">{{ $label }}</a>
                        @else
                            <span title="Menunggu persetujuan pengajuan perusahaan" class="cursor-not-allowed rounded-lg px-3 py-2 text-sm font-semibold text-slate-300">{{ $label }} (terkunci)</span>
                        @endif
                    @endforeach
                @else
                    <a href="{{ route('journalist.press-releases.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('journalist.*') ? 'bg-cyan-50 text-cyan-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">Siaran pers</a>
                @endif
            </div>
            <div class="hidden items-center gap-3 lg:flex">
                <a href="{{ route('notifications.index') }}" title="Notifikasi" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-500 hover:bg-cyan-50 hover:text-cyan-700">N</a>
                <div class="h-8 w-px bg-slate-200"></div>
                <div class="text-right"><p class="text-sm font-bold text-slate-800">{{ Auth::user()->name }}</p><p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ Auth::user()->role }}</p></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button title="Keluar" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-500 hover:border-red-200 hover:bg-red-50 hover:text-red-600">↗</button></form>
            </div>
            <button type="button" popovertarget="mobile-menu" popovertargetaction="toggle" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-600 lg:hidden" aria-label="Buka menu">
                <span aria-hidden="true">☰</span>
            </button>
        </div>
        <div id="mobile-menu" popover class="fixed inset-x-0 top-[68px] z-30 m-0 max-h-[calc(100vh-68px)] w-full overflow-y-auto border-t border-slate-100 bg-white px-4 py-4 shadow-lg lg:hidden">
            <div class="grid gap-1">
                <div class="mb-2 flex items-center justify-between border-b border-slate-100 px-3 pb-4">
                    <div>
                        <p class="text-sm font-bold text-slate-800">{{ Auth::user()->name }}</p>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ Auth::user()->role }}</p>
                    </div>
                    <a href="{{ route('notifications.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-cyan-700 hover:bg-cyan-50">Notifikasi</a>
                </div>
                @if (Auth::user()->isAdminArea())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Verifikasi</a>
                    <a href="{{ route('admin.contracts.index') }}" class="rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kontrak</a>
                    <a href="{{ route('admin.clippings.index') }}" class="rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kliping</a>
                    <a href="{{ route('admin.press-releases.index') }}" class="rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Diseminasi</a>
                    <a href="{{ route('admin.invoices.index') }}" class="rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">E-Billing</a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Audit log</a>
                    <a href="{{ route('admin.settings.keywords') }}" class="rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Pengaturan</a>
                    <a href="{{ route('admin.reports.clippings') }}" class="rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Export laporan</a>
                @elseif (Auth::user()->role === 'media')
                    @php($mediaApproved = Auth::user()->submissions()->where('status', 'disetujui')->exists())
                    <a href="{{ route('media.dashboard') }}" class="rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Ringkasan</a>
                    <a href="{{ route('media.submissions.create') }}" class="rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Pengajuan baru</a>
                    @foreach ([['media.contracts.index', 'Kontrak'], ['media.clippings.index', 'Kliping'], ['media.press-releases.index', 'Siaran pers'], ['media.invoices.index', 'E-Billing'], ['media.journalists.index', 'Jurnalis']] as [$route, $label])
                        @if ($mediaApproved)
                            <a href="{{ route($route) }}" class="rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ $label }}</a>
                        @else
                            <span title="Menunggu persetujuan pengajuan perusahaan" class="cursor-not-allowed rounded-lg px-3 py-3 text-sm font-semibold text-slate-300">{{ $label }} (terkunci)</span>
                        @endif
                    @endforeach
                @else
                    <a href="{{ route('journalist.press-releases.index') }}" class="rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Siaran pers</a>
                @endif
                <a href="{{ route('profile.edit') }}" class="rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Profil akun</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-lg px-3 py-3 text-left text-sm font-semibold text-red-600 hover:bg-red-50">Keluar</button></form>
            </div>
        </div>
    </div>
</nav>
