@php
    $noRole = auth()->check() && auth()->user()->roles()->doesntExist();
@endphp

<x-guest-layout>
    <div class="max-w-lg mx-auto mt-16 bg-white shadow-sm rounded-lg p-8 text-center">
        @if ($noRole)
            <h1 class="text-xl font-semibold text-gray-800">Access pending</h1>
            <p class="mt-3 text-sm text-gray-600">
                Your account (<strong>{{ auth()->user()->email }}</strong>) is signed in but has not
                been assigned a role yet, so it cannot see the Revenue Operating Agent's Control Plane.
            </p>
            <p class="mt-3 text-sm text-gray-600">
                Ask a Marixion administrator to assign you a role
                (<code>php artisan users:assign-role {{ auth()->user()->email }} &lt;role&gt;</code>).
            </p>
        @else
            <h1 class="text-xl font-semibold text-gray-800">Not authorized</h1>
            <p class="mt-3 text-sm text-gray-600">
                {{ $exception->getMessage() ?: 'You do not have permission to perform this action.' }}
            </p>
        @endif

        <a href="{{ route('dashboard') }}" class="inline-block mt-6 text-sm text-gray-700 underline">
            Back to Dashboard
        </a>
    </div>
</x-guest-layout>
