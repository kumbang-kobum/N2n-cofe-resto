<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::first();

        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'restaurant_name' => ['nullable', 'string', 'max:255'],
            'restaurant_address' => ['nullable', 'string', 'max:255'],
            'restaurant_phone' => ['nullable', 'string', 'max:255'],
            'tax_enabled' => ['nullable', 'boolean'],
            'pos_low_margin_warning_enabled' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'tv_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime', 'max:102400'],
            'tv_running_text' => ['nullable', 'string', 'max:1000'],
            'license_key' => ['nullable', 'string', 'max:255'],
        ], [
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo hanya mendukung PNG, JPG, JPEG, atau WebP.',
            'logo.max' => 'Logo terlalu besar. Maksimal 2 MB.',
            'tv_video.file' => 'File video tidak valid.',
            'tv_video.mimetypes' => 'Video TV hanya mendukung MP4, WebM, Ogg, atau MOV.',
            'tv_video.max' => 'Video TV terlalu besar. Maksimal 100 MB di aplikasi. Jika tetap gagal, naikkan batas upload PHP/server.',
            'tv_running_text.max' => 'Running text terlalu panjang. Maksimal 1000 karakter.',
        ]);

        $setting = Setting::first() ?? new Setting();

        $setting->restaurant_name = $data['restaurant_name'] ?? null;
        $setting->restaurant_address = $data['restaurant_address'] ?? null;
        $setting->restaurant_phone = $data['restaurant_phone'] ?? null;
        $setting->tax_enabled = $request->boolean('tax_enabled');
        $setting->pos_low_margin_warning_enabled = $request->boolean('pos_low_margin_warning_enabled');
        $setting->tv_running_text = $data['tv_running_text'] ?? null;
        $setting->license_key = $data['license_key'] ?? null;

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }

            $path = $request->file('logo')->store('logos', 'public');
            $setting->logo_path = $path;
        }

        if ($request->boolean('remove_tv_video') && $setting->tv_video_path) {
            Storage::disk('public')->delete($setting->tv_video_path);
            $setting->tv_video_path = null;
        }

        if ($request->hasFile('tv_video')) {
            if ($setting->tv_video_path) {
                Storage::disk('public')->delete($setting->tv_video_path);
            }

            $path = $request->file('tv_video')->store('tv-videos', 'public');
            $setting->tv_video_path = $path;
        }

        $setting->save();

        return redirect()
            ->to($this->settingsEditUrl($request))
            ->with('status', 'Pengaturan berhasil disimpan.');
    }

    protected function settingsEditUrl(Request $request): string
    {
        if ($request->routeIs('manager.*')) {
            return route('manager.settings.edit');
        }

        if ($request->routeIs('cashier.*')) {
            return route('cashier.settings.edit');
        }

        return route('admin.settings.edit');
    }
}
