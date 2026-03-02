@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-[#e4e4f0] mb-2">Profile Settings</h1>
            <p class="text-sm text-[#6b6b80]">Manage your account information and security</p>
        </div>

        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] p-6">
            <h2 class="text-lg font-semibold text-[#e4e4f0] mb-4">Profile Information</h2>
            <livewire:profile.update-profile-information-form />
        </div>

        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] p-6">
            <h2 class="text-lg font-semibold text-[#e4e4f0] mb-4">Change Password</h2>
            <livewire:profile.update-password-form />
        </div>

        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] p-6">
            <h2 class="text-lg font-semibold text-[#e4e4f0] mb-4">Delete Account</h2>
            <livewire:profile.delete-user-form />
        </div>
    </div>
</div>
@endsection
