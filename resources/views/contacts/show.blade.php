<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $contact->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $contact->role ?? 'Role unknown' }} at
                    <a href="{{ route('companies.show', $contact->company) }}" class="hover:underline">{{ $contact->company->name }}</a>
                </p>
            </div>
            <div class="flex gap-2 shrink-0">
                @can('create', \App\Models\Opportunity::class)
                    <a href="{{ route('opportunities.create', ['company_id' => $contact->company_id]) }}" class="text-sm px-3 py-2 rounded-md border border-gray-300 text-gray-700">Create Opportunity</a>
                @endcan
                <button type="button" disabled title="Outreach module not built yet" class="text-sm px-3 py-2 rounded-md border border-gray-200 text-gray-400 cursor-not-allowed">Start Outreach</button>
                @can('update', $contact)
                    <a href="{{ route('contacts.edit', $contact) }}" class="text-sm px-3 py-2 rounded-md bg-gray-800 text-white">Edit</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div><div class="text-gray-500">Email</div><div>{{ $contact->email ?? '—' }}</div></div>
                <div><div class="text-gray-500">Phone</div><div>{{ $contact->phone ?? '—' }}</div></div>
                <div><div class="text-gray-500">Source</div><div>{{ $contact->source ?? '—' }}</div></div>
                <div><div class="text-gray-500">Communication Status</div><div>{{ $contact->opted_out ? 'Opted out' : str($contact->communication_status)->headline() }}</div></div>
                <div><div class="text-gray-500">Owner</div><div>{{ $contact->owner?->name ?? 'Unassigned' }}</div></div>
                <div><div class="text-gray-500">Company</div><div><a href="{{ route('companies.show', $contact->company) }}" class="hover:underline">{{ $contact->company->name }}</a></div></div>
                @if ($contact->notes)
                    <div class="col-span-2 sm:col-span-3"><div class="text-gray-500">Notes</div><div class="whitespace-pre-line">{{ $contact->notes }}</div></div>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-3">Opportunities at {{ $contact->company->name }}</h3>
                @if ($contact->opportunities->isEmpty())
                    <p class="text-sm text-gray-500">No opportunities yet for this company.</p>
                @else
                    <table class="min-w-full text-sm divide-y divide-gray-100">
                        <thead>
                            <tr class="text-left text-gray-500">
                                <th class="py-1 pr-4">Opportunity</th>
                                <th class="py-1 pr-4">Stage</th>
                                <th class="py-1 pr-4">Value</th>
                                <th class="py-1">Owner</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($contact->opportunities as $opportunity)
                                <tr>
                                    <td class="py-1 pr-4"><a href="{{ route('opportunities.show', $opportunity) }}" class="hover:underline">{{ $opportunity->displayName() }}</a></td>
                                    <td class="py-1 pr-4">{{ $opportunity->stage->label() }}</td>
                                    <td class="py-1 pr-4">{{ $opportunity->value ? '$'.number_format($opportunity->value, 0) : '—' }}</td>
                                    <td class="py-1">{{ $opportunity->owner->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-1">Communication History</h3>
                <p class="text-sm text-gray-400">Sent/received messages will appear here once the Outreach and Conversation modules are built.</p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-1">Activities</h3>
                <p class="text-sm text-gray-400">Calls, meetings and tasks tied to this contact will appear here.</p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-3">Audit History</h3>
                @if ($contact->auditEvents->isEmpty())
                    <p class="text-sm text-gray-500">No audit events yet.</p>
                @else
                    <ul class="text-sm space-y-2">
                        @foreach ($contact->auditEvents as $event)
                            <li class="border-b pb-2 last:border-0">
                                <span class="font-medium">{{ $event->actor?->name ?? 'System' }}</span>
                                {{ str($event->action->value)->replace('_', ' ') }}
                                <span class="text-gray-400"> · {{ $event->created_at->format('j F Y, H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
