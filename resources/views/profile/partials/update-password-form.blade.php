<section>
    <h5 class="mb-3">{{ __('Update Password') }}</h5>
    <p class="text-muted mb-4">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="current_password" class="form-label">{{ __('Current Password') }}</label>
            <input id="current_password" name="current_password" type="password" class="form-control @if($errors->updatePassword->get('current_password')) is-invalid @endif" autocomplete="current-password">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('New Password') }}</label>
            <input id="password" name="password" type="password" class="form-control @if($errors->updatePassword->get('password')) is-invalid @endif" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control @if($errors->updatePassword->get('password_confirmation')) is-invalid @endif" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            @if (session('status') === 'password-updated')
                <div class="alert alert-success py-1 px-2 mb-0">{{ __('Saved.') }}</div>
            @endif
        </div>
    </form>
</section>
