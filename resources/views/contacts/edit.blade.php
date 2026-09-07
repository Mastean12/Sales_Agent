<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Contact') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('contacts.update', $contact) }}" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    @method('PUT')
                    @include('contacts._form')

                    <div class="mt-6 flex gap-2 items-center">
                        <x-primary-button x-bind:disabled="submitting" x-text="submitting ? 'Saving…' : 'Save Changes'"></x-primary-button>
                        <a href="{{ route('contacts.show', $contact) }}" class="text-sm text-gray-600">Cancel</a>
                    </div>
                </form>

                @can('delete', $contact)
                    <form method="POST" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('Delete this contact?');" class="mt-4 pt-4 border-t">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm text-red-600">Delete Contact</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
