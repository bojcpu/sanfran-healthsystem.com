@extends('layouts.guest')

@section('content')
    <div class="flex h-full w-full max-h-full items-center justify-center">
        <div class="login-form-container w-full max-w-md p-4 sm:p-5 lg:max-w-lg lg:p-6">
            <div class="card w-full overflow-hidden shadow-2xl">
                <div class="border-b border-gray-100 bg-gray-50 px-5 py-3.5 sm:px-6 sm:py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Staff Login</h2>
                    <p class="text-sm text-gray-500">Sign in to access the dashboard.</p>
                    <a href="{{ route('home') }}" class="mt-1.5 inline-flex text-sm font-medium text-primary hover:text-primary-700">
                        ← Back to announcements
                    </a>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-4 p-5 sm:space-y-5 sm:p-6">
                    @csrf

                    @if ($errors->any())
                        <x-alert type="error">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </x-alert>
                    @endif

                    <x-form.input
                        label="Username"
                        name="username"
                        :value="old('username')"
                        placeholder="Enter your username"
                        required
                        autocomplete="username"
                    />

                    <x-form.input
                        label="Password"
                        name="password"
                        type="password"
                        placeholder="Enter your password"
                        required
                        showPasswordToggle
                        autocomplete="current-password"
                    />

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                @checked(old('remember'))
                                class="rounded border-gray-300 text-primary focus:ring-primary/30"
                            >
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="btn-primary w-full">
                        Sign In
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
