<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;

class PublicDisplayController extends Controller
{
    public function tvInformation()
    {
        $settings = Setting::first();

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('tv-information', compact('settings', 'products'));
    }
}
