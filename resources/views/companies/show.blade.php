<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $company->name }}</h2>
            <div class="flex gap-2">
                @can('update', $company)
                    <a href="{{ route('companies.edit', $company) }}" class="text-sm text-gray-600 self-center">Edit</a>
                @endcan
                @can('delete', $company)
                    <form method="POST" action="{{ route('companies.destroy', $company) }}" onsubmit="return confirm('Delete this company and all its contacts/opportunities?');">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm text-red-600">Delete</button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><div class="text-gray-500">Domain</div><div>{{ $company->domain ?? '—' }}</div></div>
                <div><div class="text-gray-500">Industry</div><div>{{ $company->industry ?? '—' }}</div></div>
                <div><div class="text-gray-500">Location</div><div>{{ $company->location ?? '—' }}</div></div>
                <div><div class="text-gray-500">ICP Score</div><div>{{ $company->icp_score ?? '—' }}</div></div>
                <div><div class="text-gray-500">Source</div><div>{{ $company->source ?? '—' }}</div></div>
                <div><div class="text-gray-500">Status</div><div>{{ ucfirst($company->status) }}</div></div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-medium">Contacts</h3>
                    @can('create', \App\Models\Contact::class)
                        <a href="{{ route('contacts.create', ['company_id' => $company->id]) }}" class="text-sm text-gray-600">Add contact</a>
                    @endcan
                </div>
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="py-1">Name</th>
                            <th class="py-1">Role</th>
                            <th class="py-1">Email</th>
                            <th class="py-1">Status</th>
                            <th class="py-1"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($company->contacts as $contact)
                            <tr>
                                <td class="py-1">{{ $contact->name }}</td>
                                <td class="py-1">{{ $contact->role }}</td>
                                <td class="py-1">{{ $contact->email }}</td>
                                <td class="py-1">{{ $contact->opted_out ? 'Opted out' : $contact->communication_status }}</td>
                                <td class="py-1">
                                    @can('update', $contact)
                                        <a href="{{ route('contacts.edit', $contact) }}" class="text-gray-600">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-3 text-gray-500">No contacts yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-medium">Opportunities</h3>
                    @can('create', \App\Models\Opportunity::class)
                        <a href="{{ route('opportunities.create', ['company_id' => $company->id]) }}" class="text-sm text-gray-600">Add opportunity</a>
                    @endcan
                </div>
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="py-1">Stage</th>
                            <th class="py-1">Owner</th>
                            <th class="py-1">Value</th>
                            <th class="py-1">Probability</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($company->opportunities as $opportunity)
                            <tr>
                                <td class="py-1">
                                    <a href="{{ route('opportunities.show', $opportunity) }}" class="hover:underline">{{ $opportunity->stage->label() }}</a>
                                </td>
                                <td class="py-1">{{ $opportunity->owner->name }}</td>
                                <td class="py-1">{{ $opportunity->value ? '$'.number_format($opportunity->value, 0) : '—' }}</td>
                                <td class="py-1">{{ $opportunity->probability }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-3 text-gray-500">No opportunities yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
