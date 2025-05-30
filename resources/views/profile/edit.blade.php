<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Profile') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <div class="row justify-content-center g-4">
                <!-- Profile Picture Card -->
                <div class="col-12 col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-2">{{ __('Profile Picture') }}</h5>
                            <p class="card-subtitle mb-3 text-muted">{{ __('Update your profile picture.') }}</p>
                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                <div class="position-relative">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle border border-2 border-secondary shadow-sm" style="width: 96px; height: 96px; object-fit: cover;">
                                    <span class="position-absolute top-50 start-50 translate-middle badge bg-dark bg-opacity-50 text-white d-none" style="cursor:pointer;">Change</span>
                                </div>
                                <form method="post" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="flex-grow-1">
                                    @csrf
                                    @method('patch')
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <input type="file" name="avatar" id="avatar" accept="image/*" class="d-none" onchange="this.form.submit()">
                                        <label for="avatar" class="btn btn-outline-secondary btn-sm">{{ __('Choose new photo') }}</label>
                                        @if ($user->avatar)
                                            <button type="submit" form="remove-avatar" class="btn btn-link text-danger p-0 ms-2">{{ __('Remove') }}</button>
                                        @endif
                                    </div>
                                    <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                                </form>
                                <form id="remove-avatar" method="post" action="{{ route('profile.avatar') }}" class="d-none">
                                    @csrf
                                    @method('delete')
                                </form>
                            </div>
                            @if (session('status') === 'avatar-updated')
                                <div class="alert alert-success mt-3 mb-0 py-2 px-3">{{ __('Profile picture updated.') }}</div>
                            @endif
                            @if (session('status') === 'avatar-removed')
                                <div class="alert alert-success mt-3 mb-0 py-2 px-3">{{ __('Profile picture removed.') }}</div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger mt-3 mb-0 py-2 px-3">{{ session('error') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Profile Information Card -->
                <div class="col-12 col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>
                </div>
                <!-- Password Update Card -->
                <div class="col-12 col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>
                <!-- Two Factor Authentication Card -->
                <div class="col-12 col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            @include('profile.partials.two-factor-authentication-form')
                        </div>
                    </div>
                </div>
                <!-- Delete Account Card -->
                <div class="col-12 col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
