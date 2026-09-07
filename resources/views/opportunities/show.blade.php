@php
    $lostStages = collect($nextStages)->filter(fn ($s) => $s->value === 'closed_lost');
    $forwardStages = collect($nextStages)->reject(fn ($s) => $s->value === 'closed_lost');
@endphp
<x-app-layout>
    <x-slot name="header">
        <div x-data="{ lostModalOpen: false, reason: '' }" class="flex justify-between items-start flex-wrap gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $opportunity->displayName() }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    <a href="{{ route('companies.show', $opportunity->company) }}" class="hover:underline">{{ $opportunity->company->name }}</a>
                    · {{ $opportunity->stage->label() }} · Owner: {{ $opportunity->owner->name }}
                    · {{ $opportunity->value ? '$'.number_format($opportunity->value, 0) : 'No value set' }} at {{ $opportunity->probability }}%
                    @if ($opportunity->expected_value)
                        · Expected ${{ number_format($opportunity->expected_value, 0) }}
                    @endif
                </p>
            </div>

            <div class="flex gap-2 flex-wrap items-center">
                <a href="{{ route('companies.show', $opportunity->company) }}" class="text-sm px-3 py-2 rounded-md border border-gray-300 text-gray-700">View Company</a>
                <button type="button" disabled title="Activity logging module not built yet" class="text-sm px-3 py-2 rounded-md border border-gray-200 text-gray-400 cursor-not-allowed">Add Activity</button>

                @unless ($opportunity->stage->isClosed())
                    @foreach ($forwardStages as $forward)
                        @can('transition', [$opportunity, $forward])
                            <form method="POST" action="{{ route('opportunities.transition', $opportunity) }}">
                                @csrf
                                <input type="hidden" name="to" value="{{ $forward->value }}">
                                <button class="text-sm px-3 py-2 rounded-md bg-gray-800 text-white">Advance &rarr; {{ $forward->label() }}</button>
                            </form>
                        @endcan
                    @endforeach

                    @if ($lostStages->isNotEmpty())
                        @can('transition', [$opportunity, $lostStages->first()])
                            <button type="button" @click="lostModalOpen = true" class="text-sm px-3 py-2 rounded-md border border-red-200 text-red-600">Mark Lost</button>
                        @endcan
                    @endif
                @endunless

                @can('update', $opportunity)
                    <a href="{{ route('opportunities.edit', $opportunity) }}" class="text-sm px-3 py-2 rounded-md border border-gray-300 text-gray-700">Edit</a>
                @endcan
                @can('delete', $opportunity)
                    <form method="POST" action="{{ route('opportunities.destroy', $opportunity) }}" onsubmit="return confirm('Delete this opportunity?');">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm px-3 py-2 rounded-md border border-red-200 text-red-600">Delete</button>
                    </form>
                @endcan
            </div>

            <div x-show="lostModalOpen" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" style="display: none;">
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm" @click.outside="lostModalOpen = false">
                    <h3 class="font-medium text-gray-800 mb-2">Mark opportunity as Closed Lost</h3>
                    <p class="text-sm text-gray-500 mb-3">A reason is required so the pipeline stays auditable.</p>
                    <form method="POST" action="{{ route('opportunities.transition', $opportunity) }}">
                        @csrf
                        <input type="hidden" name="to" value="closed_lost">
                        <textarea name="reason" x-model="reason" rows="3" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Why was this lost?"></textarea>
                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" @click="lostModalOpen = false" class="text-sm text-gray-600 px-3 py-2">Cancel</button>
                            <button type="submit" x-bind:disabled="!reason.trim()" class="text-sm px-3 py-2 rounded bg-red-600 text-white disabled:opacity-50">Confirm Closed Lost</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">{{ session('status') }}</div>
            @endif
            @error('stage')
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">{{ $message }}</div>
            @enderror

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-3">Overview</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div><div class="text-gray-500">Company</div><div>{{ $opportunity->company->name }}</div></div>
                    <div><div class="text-gray-500">Owner</div><div>{{ $opportunity->owner->name }}</div></div>
                    <div><div class="text-gray-500">Stage</div><div>{{ $opportunity->stage->label() }}</div></div>
                    <div><div class="text-gray-500">Value</div><div>{{ $opportunity->value ? '$'.number_format($opportunity->value, 0) : '—' }}</div></div>
                    <div><div class="text-gray-500">Probability</div><div>{{ $opportunity->probability }}%</div></div>
                    <div><div class="text-gray-500">Expected Revenue</div><div>{{ $opportunity->expected_value ? '$'.number_format($opportunity->expected_value, 0) : '—' }}</div></div>
                    <div><div class="text-gray-500">Created</div><div>{{ $opportunity->created_at->format('j M Y') }}</div></div>
                    <div><div class="text-gray-500">Updated</div><div>{{ $opportunity->updated_at->diffForHumans() }}</div></div>
                    @if ($opportunity->stage->isClosed())
                        <div class="col-span-2 sm:col-span-4"><div class="text-gray-500">Closed Reason</div><div>{{ $opportunity->closed_reason ?? '—' }}</div></div>
                    @endif
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-3">Next Action</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div><div class="text-gray-500">Action</div><div>{{ $opportunity->next_action ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Due</div><div>{{ optional($opportunity->next_action_due)->format('j M Y') ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Owner</div><div>{{ $opportunity->owner->name }}</div></div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-3">Business Problem</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="sm:col-span-2"><div class="text-gray-500">Problem Statement</div><div>{{ $opportunity->problem ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Category</div><div>{{ $opportunity->problem_category ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Urgency</div><div>{{ $opportunity->urgency ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Evidence</div><div>{{ $opportunity->evidence ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Impact</div><div>{{ $opportunity->impact ?? '—' }}</div></div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-3">Qualification</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><div class="text-gray-500">Stakeholder</div><div>{{ $opportunity->stakeholder ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Budget Signal</div><div>{{ $opportunity->budget_signal ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Business Impact</div><div>{{ $opportunity->business_impact ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Decision Process</div><div>{{ $opportunity->decision_process ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Fit</div><div>{{ $opportunity->fit ?? '—' }}</div></div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-3">Related Contacts</h3>
                @if ($opportunity->company->contacts->isEmpty())
                    <p class="text-sm text-gray-500">No contacts recorded for this company yet.</p>
                @else
                    <ul class="text-sm divide-y divide-gray-100">
                        @foreach ($opportunity->company->contacts as $contact)
                            <li class="py-2 flex justify-between">
                                <a href="{{ route('contacts.show', $contact) }}" class="hover:underline">{{ $contact->name }}</a>
                                <span class="text-gray-500">{{ $contact->role }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-1">Discovery</h3>
                <p class="text-sm text-gray-400">
                    Pain points, goals, current process, stakeholders and budget signals captured during a structured
                    discovery call will appear here once the Discovery module is built.
                </p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-3">Activity &amp; Audit Timeline</h3>
                <p class="text-xs text-gray-400 mb-3">
                    Every stage change and record update is logged here. Calls, emails and meetings will join this
                    timeline once the Outreach and Conversation modules exist.
                </p>
                @if ($opportunity->auditEvents->isEmpty())
                    <p class="text-sm text-gray-500">No activity yet.</p>
                @else
                    <ul class="text-sm space-y-3">
                        @foreach ($opportunity->auditEvents as $event)
                            <li class="border-b pb-3 last:border-0">
                                <div>
                                    <span class="font-medium">{{ $event->actor?->name ?? 'System' }}</span>
                                    @if ($event->action->value === 'stage_transitioned')
                                        changed stage
                                        <span class="font-medium">{{ \App\Enums\OpportunityStage::from($event->before['stage'])->label() }}</span>
                                        &rarr;
                                        <span class="font-medium">{{ \App\Enums\OpportunityStage::from($event->after['stage'])->label() }}</span>
                                    @else
                                        {{ str($event->action->value)->replace('_', ' ') }}
                                    @endif
                                </div>
                                @if ($event->reason)
                                    <div class="text-gray-600">{{ $event->reason }}</div>
                                @endif
                                <div class="text-gray-400 text-xs">{{ $event->created_at->format('j F Y, H:i') }}</div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
