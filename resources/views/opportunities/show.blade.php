<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $opportunity->company->name }} — {{ $opportunity->stage->label() }}
            </h2>
            <div class="flex gap-2">
                @can('update', $opportunity)
                    <a href="{{ route('opportunities.edit', $opportunity) }}" class="text-sm text-gray-600 self-center">Edit</a>
                @endcan
                @can('delete', $opportunity)
                    <form method="POST" action="{{ route('opportunities.destroy', $opportunity) }}" onsubmit="return confirm('Delete this opportunity?');">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm text-red-600">Delete</button>
                    </form>
                @endcan
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

            <div class="bg-white shadow-sm sm:rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><div class="text-gray-500">Owner</div><div>{{ $opportunity->owner->name }}</div></div>
                <div><div class="text-gray-500">Value</div><div>{{ $opportunity->value ? '$'.number_format($opportunity->value, 0) : '—' }}</div></div>
                <div><div class="text-gray-500">Probability</div><div>{{ $opportunity->probability }}%</div></div>
                <div><div class="text-gray-500">Expected Revenue</div><div>{{ $opportunity->expected_value ? '$'.number_format($opportunity->expected_value, 0) : '—' }}</div></div>
                <div class="col-span-2 sm:col-span-4"><div class="text-gray-500">Business Problem</div><div>{{ $opportunity->problem ?? '—' }}</div></div>
                <div class="col-span-2 sm:col-span-4"><div class="text-gray-500">Next Action</div><div>{{ $opportunity->next_action ?? '—' }}</div></div>
                @if ($opportunity->stage->isClosed())
                    <div class="col-span-2 sm:col-span-4"><div class="text-gray-500">Closed Reason</div><div>{{ $opportunity->closed_reason ?? '—' }}</div></div>
                @endif
            </div>

            @unless ($opportunity->stage->isClosed())
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-medium mb-3">Move Stage</h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse ($nextStages as $next)
                            @can('transition', [$opportunity, $next])
                                <form method="POST" action="{{ route('opportunities.transition', $opportunity) }}"
                                      onsubmit="{{ $next->value === 'closed_lost' ? "return (function(f){var r=prompt('Reason for closing lost:'); if(!r) return false; f.querySelector('[name=reason]').value=r; return true;})(this)" : '' }}">
                                    @csrf
                                    <input type="hidden" name="to" value="{{ $next->value }}">
                                    <input type="hidden" name="reason" value="">
                                    <button class="px-3 py-2 text-sm rounded-md bg-gray-800 text-white">
                                        Move to {{ $next->label() }}
                                    </button>
                                </form>
                            @endcan
                        @empty
                            <p class="text-sm text-gray-500">No further transitions available.</p>
                        @endforelse
                    </div>
                </div>
            @endunless

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-medium mb-3">Audit Trail</h3>
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="py-1">When</th>
                            <th class="py-1">Actor</th>
                            <th class="py-1">Action</th>
                            <th class="py-1">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($opportunity->auditEvents->sortByDesc('created_at') as $event)
                            <tr>
                                <td class="py-1">{{ $event->created_at->format('Y-m-d H:i') }}</td>
                                <td class="py-1">{{ $event->actor?->name ?? 'System' }}</td>
                                <td class="py-1">{{ $event->action->value }}</td>
                                <td class="py-1">{{ $event->reason ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-3 text-gray-500">No audit events yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
