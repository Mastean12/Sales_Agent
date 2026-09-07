<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Contacts') }}</h2>
                <p class="text-sm text-gray-500 mt-1">Manage decision-makers and business contacts.</p>
            </div>
            @can('create', \App\Models\Contact::class)
                <a href="{{ route('contacts.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm shrink-0">
                    {{ __('+ Add Contact') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <form method="GET" class="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 sm:grid-cols-5 gap-3 text-sm">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search contacts…"
                       class="border-gray-300 rounded-md shadow-sm sm:col-span-2" />

                <select name="company_id" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">All companies</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected((string) request('company_id') === (string) $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>

                <select name="communication_status" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (\App\Http\Controllers\ContactController::COMMUNICATION_STATUSES as $status)
                        <option value="{{ $status }}" @selected(request('communication_status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>

                <select name="owner_id" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">All owners</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((string) request('owner_id') === (string) $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </select>

                <select name="suppressed" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">Suppressed & active</option>
                    <option value="0" @selected(request('suppressed') === '0')>Active only</option>
                    <option value="1" @selected(request('suppressed') === '1')>Suppressed only</option>
                </select>

                <div class="sm:col-span-5 flex gap-3">
                    <button class="text-sm text-gray-700 underline">Search</button>
                    @if (request()->anyFilled(['q', 'company_id', 'communication_status', 'owner_id', 'suppressed']))
                        <a href="{{ route('contacts.index') }}" class="text-sm text-gray-500">Clear filters</a>
                    @endif
                </div>
            </form>

            @if ($contacts->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-12 text-center">
                    <h3 class="text-gray-800 font-medium">No contacts found.</h3>
                    <p class="text-sm text-gray-500 mt-1">Add a contact to begin building your decision-maker network.</p>
                    @can('create', \App\Models\Contact::class)
                        <a href="{{ route('contacts.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-gray-800 text-white rounded-md text-sm">
                            + Add Contact
                        </a>
                    @endcan
                </div>
            @else
                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Name</th>
                                <th class="px-4 py-2 text-left">Company</th>
                                <th class="px-4 py-2 text-left">Role</th>
                                <th class="px-4 py-2 text-left">Email</th>
                                <th class="px-4 py-2 text-left">Phone</th>
                                <th class="px-4 py-2 text-left">Source</th>
                                <th class="px-4 py-2 text-left">Communication Status</th>
                                <th class="px-4 py-2 text-left">Owner</th>
                                <th class="px-4 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($contacts as $contact)
                                <tr>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('contacts.show', $contact) }}" class="text-gray-900 font-medium hover:underline">{{ $contact->name }}</a>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('companies.show', $contact->company) }}" class="hover:underline">{{ $contact->company->name }}</a>
                                    </td>
                                    <td class="px-4 py-2">{{ $contact->role }}</td>
                                    <td class="px-4 py-2">{{ $contact->email }}</td>
                                    <td class="px-4 py-2">{{ $contact->phone }}</td>
                                    <td class="px-4 py-2">{{ $contact->source }}</td>
                                    <td class="px-4 py-2">
                                        @if ($contact->opted_out)
                                            <span class="px-2 py-0.5 rounded-full text-xs bg-red-50 text-red-600">Opted out</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_', ' ', $contact->communication_status)) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">{{ $contact->owner?->name ?? '—' }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('contacts.show', $contact) }}" class="text-gray-600 hover:underline">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>{{ $contacts->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
