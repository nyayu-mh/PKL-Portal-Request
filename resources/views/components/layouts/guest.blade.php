<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Portal Request' }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 antialiased">
    <div class="relative min-h-screen overflow-hidden flex items-center justify-center px-4 py-10">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(99,102,241,0.25),transparent_45%),radial-gradient(circle_at_80%_0%,rgba(236,72,153,0.18),transparent_40%),radial-gradient(circle_at_50%_100%,rgba(16,185,129,0.15),transparent_45%)]"></div>

        <div class="relative w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 text-xl font-bold text-white shadow-lg shadow-indigo-500/30">
                    BTC
                </div>
                <h1 class="text-xl font-semibold text-white">Portal Request</h1>
                <p class="mt-1 text-sm text-slate-400">Brilliant Think Center</p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl">
                {{ $slot }}
            </div>

            <p class="mt-6 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} Brilliant Think Center — Internal System
            </p>
        </div>
    </div>
</body>
</html>
