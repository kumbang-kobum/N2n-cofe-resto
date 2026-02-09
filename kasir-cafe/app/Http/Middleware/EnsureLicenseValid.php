<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLicenseValid
{
    protected function generateInstallCode(): string
    {
        return strtoupper(bin2hex(random_bytes(8)));
    }

    protected function normalizeKey(string $key): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/i', '', $key));
    }

    protected function expectedKey(string $installCode, string $masterKey): string
    {
        $raw = hash_hmac('sha256', $installCode, $masterKey);
        return strtoupper(substr($raw, 0, 32));
    }

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();
        $path = trim($request->path(), '/');

        // Allow access to settings page to input license (admin only route anyway)
        if ($routeName === 'admin.settings.edit' || $routeName === 'admin.settings.update' || $path === 'logout') {
            return $next($request);
        }

        $setting = Setting::first();

        if (! $setting) {
            $setting = Setting::create([
                'installed_at' => now(),
                'installation_code' => $this->generateInstallCode(),
            ]);
        } elseif (! $setting->installed_at || ! $setting->installation_code) {
            $setting->installed_at = now();
            if (! $setting->installation_code) {
                $setting->installation_code = $this->generateInstallCode();
            }
            $setting->save();
        }

        // If license key exists, validate
        if (! empty($setting->license_key)) {
            $master = (string) config('license.master_key', '');
            if ($master !== '') {
                $expected = $this->expectedKey($setting->installation_code, $master);
                if ($this->normalizeKey($setting->license_key) !== $expected) {
                    return response()->view('errors.license_expired', [
                        'message' => 'License key tidak valid.',
                        'can_set_license' => true,
                    ], 403);
                }
            }

            return $next($request);
        }

        // Trial mode: 30 days from installed_at
        $trialEndsAt = Carbon::parse($setting->installed_at)->addDays(30)->endOfDay();
        if (now()->greaterThan($trialEndsAt)) {
            return response()->view('errors.license_expired', [
                'message' => 'Masa trial 30 hari sudah berakhir. Silakan masukkan license key.',
                'can_set_license' => true,
            ], 403);
        }

        return $next($request);
    }
}
