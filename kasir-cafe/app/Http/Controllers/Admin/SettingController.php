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
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'license_key' => ['nullable', 'string', 'max:255'],
        ]);

        $setting = Setting::first() ?? new Setting();

        $setting->restaurant_name = $data['restaurant_name'] ?? null;
        $setting->restaurant_address = $data['restaurant_address'] ?? null;
        $setting->restaurant_phone = $data['restaurant_phone'] ?? null;
        $setting->license_key = $data['license_key'] ?? null;

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }

            $path = $request->file('logo')->store('logos', 'public');
            $setting->logo_path = $path;
        }

        $setting->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Pengaturan berhasil disimpan.');
    }
}
