<x-guest-layout>
    <div class="space-y-6">
        <div class="space-y-2">
            <div class="section-kicker">Secure Access</div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-950">Masuk ke dashboard operasional</h1>
            <p class="text-sm leading-6 text-slate-500">
                Gunakan akun admin, manager, atau kasir untuk mengakses POS, stok, dan laporan.
            </p>
        </div>

        <x-auth-session-status class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div class="space-y-2">
                <x-input-label for="email" :value="__('Email')" class="text-sm font-semibold text-slate-700" />
                <x-text-input id="email" class="dashboard-input block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="text-sm text-red-600" />
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between gap-3">
                    <x-input-label for="password" :value="__('Password')" class="text-sm font-semibold text-slate-700" />
                    @if (Route::has('password.request'))
                        <a class="text-sm font-medium text-blue-700 transition hover:text-blue-800" href="{{ route('password.request') }}">
                            Lupa password?
                        </a>
                    @endif
                </div>

                <x-text-input id="password" class="dashboard-input block w-full"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />

                <x-input-error :messages="$errors->get('password')" class="text-sm text-red-600" />
            </div>

            <label for="remember_me" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500" name="remember">
                <span class="text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>

            <button type="submit" class="btn-primary w-full py-3 text-base">
                {{ __('Log in') }}
            </button>
        </form>
    </div>
</x-guest-layout>
