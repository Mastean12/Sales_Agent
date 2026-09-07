<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Contact') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('contacts.store') }}" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    @include('contacts._form')

                    <div class="mt-6 flex gap-2">
                        <x-primary-button x-bind:disabled="submitting" x-text="submitting ? 'Creating…' : 'Create Contact'"></x-primary-button>
                        <a href="{{ route('contacts.index') }}" class="text-sm text-gray-600 self-center">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
