<section>
    <h5 class="mb-3">{{ __('Profile Information') }}</h5>
    <p class="text-muted mb-4">{{ __("Update your account's profile information and email address.") }}</p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <x-input-error :messages="$errors->get('email')" />
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-warning small mb-1">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="btn btn-link p-0 align-baseline">{{ __('Click here to re-send the verification email.') }}</button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success py-1 px-2 mb-0 small">{{ __('A new verification link has been sent to your email address.') }}</div>
                    @endif
                </div>
            @endif
        </div>

        <div class="mb-3">
            <label for="bio" class="form-label">{{ __('Bio') }}</label>
            <textarea id="bio" name="bio" rows="4" class="form-control @error('bio') is-invalid @enderror" placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error :messages="$errors->get('bio')" />
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">{{ __('Location') }}</label>
            <input id="location" name="location" type="text" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $user->location) }}" placeholder="City, Country">
            <x-input-error :messages="$errors->get('location')" />
        </div>

        <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            @if (session('status') === 'profile-updated')
                <div class="alert alert-success py-1 px-2 mb-0">{{ __('Saved.') }}</div>
            @endif
        </div>
    </form>
</section>
