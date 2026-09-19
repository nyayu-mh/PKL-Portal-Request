@php
    $user = auth()->user();

    $pendingApprovalCount = \App\Models\ErfRequest::visibleTo($user)->where('atasan_user_id', $user->id)->where('approval_status', 'menunggu')->count()
        + \App\Models\GaRequest::visibleTo($user)->where('atasan_user_id', $user->id)->where('approval_status', 'menunggu')->count();

    // Hak akses menu per grup (role), diatur Super Admin lewat Setting > Manajemen Grup & Akses.
    // Super Admin selalu full akses; role lain pakai $default kalau belum pernah diatur eksplisit.
    $menuVisible = function (string $menuKey, bool $default) use ($user) {
        if ($user->isAdmin()) {
            return true;
        }

        return \App\Models\MenuPermission::allows($user->role, $menuKey, 'view') ?? $default;
    };

    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'show' => $menuVisible('dashboard', true)],
        ['label' => 'Approval Saya', 'route' => 'approval.index', 'icon' => 'check-badge', 'show' => $menuVisible('approval', true), 'badge' => $pendingApprovalCount],
        ['label' => 'Request ERF', 'route' => 'erf.index', 'icon' => 'user-plus', 'show' => $user->canAccessErf() && $menuVisible('erf', true)],
        ['label' => 'Request GA', 'route' => 'ga.index', 'icon' => 'wrench', 'show' => $user->canAccessGa() && $menuVisible('ga', true)],
        ['label' => 'Request Tech', 'route' => 'placeholder.tech', 'icon' => 'code', 'show' => $menuVisible('tech', true)],
        ['label' => 'Creative Design', 'route' => 'placeholder.creative', 'icon' => 'palette', 'show' => $menuVisible('creative', true)],
        ['label' => 'Business Trip', 'route' => 'placeholder.trip', 'icon' => 'plane', 'show' => $menuVisible('business_trip', true)],
    ];

    $masterItems = [
        ['label' => 'Data Karyawan', 'route' => 'master.karyawan.index', 'show' => ($user->isAdmin() || $user->isHr()) && $menuVisible('master_karyawan', true)],
        ['label' => 'Jabatan & TTF', 'route' => 'master.jabatan-ttf.index', 'show' => ($user->isAdmin() || $user->isHr()) && $menuVisible('master_jabatan_ttf', true)],
        ['label' => 'Kalender Kerja', 'route' => 'master.kalender-kerja.index', 'show' => ($user->isAdmin() || $user->isHr()) && $menuVisible('master_kalender', true)],
        ['label' => 'Divisi', 'route' => 'master.divisi.index', 'show' => ($user->isAdmin() || $user->isHr()) && $menuVisible('master_divisi', true)],
        ['label' => 'Jabatan', 'route' => 'master.jabatan.index', 'show' => ($user->isAdmin() || $user->isHr()) && $menuVisible('master_jabatan', true)],
    ];
    $showMasterGroup = collect($masterItems)->contains(fn ($i) => $i['show']);
    $masterActive = collect($masterItems)->contains(fn ($i) => $i['show'] && request()->routeIs($i['route'].'*'));

    $settingItems = [
        ['label' => 'Manajemen User', 'route' => 'setting.users.index', 'show' => $user->isAdmin()],
        ['label' => 'Manajemen Grup & Akses', 'route' => 'setting.grup-akses.index', 'show' => $user->isAdmin()],
    ];
    $showSettingGroup = collect($settingItems)->contains(fn ($i) => $i['show']);
    $settingActive = collect($settingItems)->contains(fn ($i) => $i['show'] && request()->routeIs($i['route'].'*'));

    $icons = [
        'settings' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'chevron-right' => 'M8.25 4.5l7.5 7.5-7.5 7.5',
        'home' => 'M2.25 12l8.954-8.955a1.125 1.125 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75',
        'user-plus' => 'M19 8v6m3-3h-6M12 6a3 3 0 11-6 0 3 3 0 016 0zM4.5 20.25a7.5 7.5 0 0113 0',
        'wrench' => 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085',
        'code' => 'M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5',
        'palette' => 'M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42',
        'plane' => 'M6 12L3.269 3.126A59.769 59.769 0 0121.485 12 59.768 59.768 0 013.27 20.876L5.999 12zm0 0h7.5',
        'check-badge' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
        'box' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
        'users' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="h-full bg-slate-50 antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-950 text-slate-300 transition-transform duration-200 lg:static lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 text-sm font-bold text-white">
                    BTC
                </div>
                <div class="leading-tight">
                    <p class="text-sm font-semibold text-white">Portal Request</p>
                    <p class="text-[11px] text-slate-400">Brilliant Think Center</p>
                </div>
            </div>

            <nav class="space-y-1 px-3 py-5">
                @foreach ($navItems as $item)
                    @continue(!$item['show'])
                    @php $active = request()->routeIs($item['route'].'*'); @endphp
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] }}" />
                        </svg>
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if (!empty($item['badge']))
                            <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[11px] font-semibold text-white">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach

                @if ($showMasterGroup)
                    <div class="mt-3" x-data="{ open: {{ $masterActive ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open"
                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:bg-white/5 hover:text-white">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['box'] }}" />
                            </svg>
                            <span class="flex-1 text-left">Master Data</span>
                            <svg class="h-4 w-4 shrink-0 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['chevron-right'] }}" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition x-cloak class="mt-1 space-y-1 pl-8">
                            @foreach ($masterItems as $item)
                                @continue(!$item['show'])
                                @php $itemActive = request()->routeIs($item['route'].'*'); @endphp
                                <a href="{{ route($item['route']) }}"
                                    class="block rounded-lg px-3 py-2 text-sm transition {{ $itemActive ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($showSettingGroup)
                    <div class="mt-1" x-data="{ open: {{ $settingActive ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open"
                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:bg-white/5 hover:text-white">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['settings'] }}" />
                            </svg>
                            <span class="flex-1 text-left">Setting</span>
                            <svg class="h-4 w-4 shrink-0 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['chevron-right'] }}" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition x-cloak class="mt-1 space-y-1 pl-8">
                            @foreach ($settingItems as $item)
                                @continue(!$item['show'])
                                @php $itemActive = request()->routeIs($item['route'].'*'); @endphp
                                <a href="{{ route($item['route']) }}"
                                    class="block rounded-lg px-3 py-2 text-sm transition {{ $itemActive ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </nav>

            <div class="absolute inset-x-0 bottom-0 border-t border-white/10 p-4">
                <div class="flex items-center gap-3 rounded-lg bg-white/5 px-3 py-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-500 text-xs font-semibold text-white">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1 leading-tight">
                        <p class="truncate text-xs font-medium text-white">{{ $user->name }}</p>
                        <p class="truncate text-[11px] text-slate-400">{{ $user->roleLabel() }}@if ($user->brand) &middot; {{ $user->brandLabel() }}@endif</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="fixed inset-0 z-30 bg-black/50 lg:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/80 px-4 backdrop-blur sm:px-6">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </button>
                    <h1 class="text-base font-semibold text-slate-900 sm:text-lg">{{ $header ?? ($title ?? 'Dashboard') }}</h1>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-900">
                        Keluar
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H3" />
                        </svg>
                    </button>
                </form>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @if (session('success') || session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: @json(session('success') ? 'success' : 'error'),
                    title: @json(session('success') ? 'Berhasil' : 'Gagal'),
                    text: @json(session('success') ?: session('error')),
                    confirmButtonColor: '#6366f1',
                    timer: 3000,
                    timerProgressBar: true,
                });
            });
        </script>
    @endif

    @livewireScripts
</body>
</html>
