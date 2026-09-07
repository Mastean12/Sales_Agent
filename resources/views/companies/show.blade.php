<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $company->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $company->domain ?? 'No domain' }} · {{ $company->industry ?? 'Industry unknown' }} · {{ $company->location ?? 'Location unknown' }}
                    · <span class="capitalize">{{ $company->status }}</span>
                    · Owner: {{ $company->owner?->name ?? 'Unassigned' }}
                </p>
            </div>
            <div class="flex gap-2 shrink-0">
                @can('create', \App\Models\Contact::class)
                    <a href="{{ route('contacts.create', ['company_id' => $company->id]) }}" class="text-sm px-3 py-2 rounded-md border border-gray-300 text-gray-700">Add Contact</a>
                @endcan
                @can('create', \App\Models\Opportunity::class)
                    <a href="{{ route('opportunities.create', ['company_id' => $company->id]) }}" class="text-sm px-3 py-2 rounded-md border border-gray-300 text-gray-700">Create Opportunity</a>
                @endcan
                @can('update', $company)
                    <a href="{{ route('companies.edit', $company) }}" class="text-sm px-3 py-2 rounded-md bg-gray-800 text-white">Edit</a>
                @endcan
                @can('delete', $company)
                    <form method="POST" action="{{ route('companies.destroy', $company) }}" onsubmit="return confirm('Delete this company and all its contacts/opportunities?');">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm px-3 py-2 rounded-md border border-red-200 text-red-600">Delete</button>
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

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-3">Overview</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div><div class="text-gray-500">Domain</div><div>{{ $company->domain ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Industry</div><div>{{ $company->industry ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Location</div><div>{{ $company->location ?? '—' }}</div></div>
                    <div><div class="text-gray-500">ICP Score</div><div>{{ $company->icp_score ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Source</div><div>{{ $company->source ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Status</div><div class="capitalize">{{ $company->status }}</div></div>
                    <div><div class="text-gray-500">Owner</div><div>{{ $company->owner?->name ?? 'Unassigned' }}</div></div>
                </div>
                @if ($company->notes)
                    <div class="mt-4 text-sm">
                        <div class="text-gray-500">Notes</div>
                        <div class="whitespace-pre-line">{{ $company->notes }}</div>
                    </div>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-1">Research Summary</h3>
                <p class="text-sm text-gray-400">
                    Prospect intelligence has not been generated for this company yet. This section will populate once the Prospect Intelligence module is built.
                </p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-medium">Contacts</h3>
                    @can('create', \App\Models\Contact::class)
                        <a href="{{ route('contacts.create', ['company_id' => $company->id]) }}" class="text-sm text-gray-600">Add contact</a>
                    @endcan
                </div>
                @if ($company->contacts->isEmpty())
                    <p class="text-sm text-gray-500">No contacts found. Add a contact to begin building your decision-maker network.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm divide-y divide-gray-100">
                            <thead>
                                <tr class="text-left text-gray-500">
                                    <th class="py-1 pr-4">Name</th>
                                    <th class="py-1 pr-4">Role</th>
                                    <th class="py-1 pr-4">Email</th>
                                    <th class="py-1 pr-4">Phone</th>
                                    <th class="py-1 pr-4">Communication Status</th>
                                    <th class="py-1"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($company->contacts as $contact)
                                    <tr>
                                        <td class="py-1 pr-4">
                                            <a href="{{ route('contacts.show', $contact) }}" class="hover:underline">{{ $contact->name }}</a>
                                        </td>
                                        <td class="py-1 pr-4">{{ $contact->role }}</td>
                                        <td class="py-1 pr-4">{{ $contact->email }}</td>
                                        <td class="py-1 pr-4">{{ $contact->phone }}</td>
                                        <td class="py-1 pr-4">{{ $contact->opted_out ? 'Opted out' : str($contact->communication_status)->headline() }}</td>
                                        <td class="py-1">
                                            @can('update', $contact)
                                                <a href="{{ route('contacts.edit', $contact) }}" class="text-gray-600">Edit</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-medium">Opportunities</h3>
                    @can('create', \App\Models\Opportunity::class)
                        <a href="{{ route('opportunities.create', ['company_id' => $company->id]) }}" class="text-sm text-gray-600">Add opportunity</a>
                    @endcan
                </div>
                @if ($company->opportunities->isEmpty())
                    <p class="text-sm text-gray-500">No opportunities yet. Create an opportunity to start tracking potential revenue.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm divide-y divide-gray-100">
                            <thead>
                                <tr class="text-left text-gray-500">
                                    <th class="py-1 pr-4">Opportunity</th>
                                    <th class="py-1 pr-4">Stage</th>
                                    <th class="py-1 pr-4">Value</th>
                                    <th class="py-1 pr-4">Probability</th>
                                    <th class="py-1 pr-4">Owner</th>
                                    <th class="py-1">Next Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($company->opportunities as $opportunity)
                                    <tr>
                                        <td class="py-1 pr-4">
                                            <a href="{{ route('opportunities.show', $opportunity) }}" class="hover:underline">{{ $opportunity->displayName() }}</a>
                                        </td>
                                        <td class="py-1 pr-4">{{ $opportunity->stage->label() }}</td>
                                        <td class="py-1 pr-4">{{ $opportunity->value ? '$'.number_format($opportunity->value, 0) : '—' }}</td>
                                        <td class="py-1 pr-4">{{ $opportunity->probability }}%</td>
                                        <td class="py-1 pr-4">{{ $opportunity->owner->name }}</td>
                                        <td class="py-1">{{ $opportunity->next_action ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-1">Activity</h3>
                <p class="text-sm text-gray-400">
                    Calls, emails and meetings will appear here once the Outreach and Conversation modules are built.
                </p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-3">Audit History</h3>
                @if ($company->auditEvents->isEmpty())
                    <p class="text-sm text-gray-500">No audit events yet.</p>
                @else
                    <ul class="text-sm space-y-2">
                        @foreach ($company->auditEvents as $event)
                            <li class="border-b pb-2 last:border-0">
                                <span class="font-medium">{{ $event->actor?->name ?? 'System' }}</span>
                                {{ str($event->action->value)->replace('_', ' ') }}
                                @if ($event->reason)
                                    — {{ $event->reason }}
                                @endif
                                <span class="text-gray-400"> · {{ $event->created_at->format('j F Y, H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
