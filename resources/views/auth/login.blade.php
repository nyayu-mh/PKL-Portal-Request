<x-layouts.guest title="Login">
    <h2 class="mb-1 text-lg font-semibold text-white">Masuk ke akun Anda</h2>
    <p class="mb-6 text-sm text-slate-400">Gunakan email &amp; password yang didaftarkan oleh Admin.</p>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-300">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-300">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full rounded-lg border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400"
                placeholder="nama@brilliantthinkcenter.com">
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-slate-300">Password</label>
            <input id="password" type="password" name="password" required
                class="w-full rounded-lg border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400"
                placeholder="••••••••">
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-400">
            <input type="checkbox" name="remember" class="rounded border-white/20 bg-white/5 text-indigo-500 focus:ring-indigo-400">
            Ingat saya
        </label>

        <button type="submit"
            class="w-full rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:opacity-90">
            Masuk
        </button>
    </form>

    <p class="mt-6 text-center text-xs text-slate-500">
        Belum punya akun? Kirim email ke Admin/IT untuk didaftarkan.
    </p>
</x-layouts.guest>
