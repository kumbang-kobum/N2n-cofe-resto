@extends('layouts.dashboard')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Profil & Ubah Password</h1>

    <div class="space-y-6">
        <div class="p-4 sm:p-8 bg-white border rounded-lg">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white border rounded-lg">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white border rounded-lg">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection
