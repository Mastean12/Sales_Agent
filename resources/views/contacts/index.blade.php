<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Contacts') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Company</th>
                            <th class="px-4 py-2 text-left">Role</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($contacts as $contact)
                            <tr>
                                <td class="px-4 py-2">{{ $contact->name }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('companies.show', $contact->company) }}" class="hover:underline">{{ $contact->company->name }}</a>
                                </td>
                                <td class="px-4 py-2">{{ $contact->role }}</td>
                                <td class="px-4 py-2">{{ $contact->email }}</td>
                                <td class="px-4 py-2">{{ $contact->opted_out ? 'Opted out' : $contact->communication_status }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No contacts yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $contacts->links() }}</div>
        </div>
    </div>
</x-app-layout>
