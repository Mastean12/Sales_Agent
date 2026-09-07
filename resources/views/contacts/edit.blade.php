<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Contact') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('contacts.update', $contact) }}">
                    @csrf
                    @method('PUT')
                    @include('contacts._form')

                    <div class="mt-6 flex gap-2">
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                        <a href="{{ route('companies.show', $contact->company_id) }}" class="text-sm text-gray-600 self-center">Cancel</a>
                        @can('delete', $contact)
                            <form method="POST" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('Delete this contact?');" class="ml-auto">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm text-red-600">Delete</button>
                            </form>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
