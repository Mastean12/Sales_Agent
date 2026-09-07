<div x-data="{ lostModalOpen: false, lostOpportunityId: null, lostReason: '' }" wire:loading.class="opacity-60" wire:target="transitionTo">
    @if ($transitionError)
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {{ $transitionError }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <div class="flex gap-4 min-w-max pb-4">
            @foreach ($this->stages as $stage)
                <div class="bg-white shadow-sm rounded-lg w-72 shrink-0">
                    <div class="px-4 py-3 border-b font-medium text-sm text-gray-700 flex justify-between items-center">
                        <span>{{ $stage->label() }}</span>
                        <span class="text-gray-400">{{ $this->opportunitiesByStage[$stage->value]->count() }}</span>
                    </div>
                    <div class="p-3 space-y-3 max-h-[36rem] overflow-y-auto">
                        @forelse ($this->opportunitiesByStage[$stage->value] as $opportunity)
                            <div class="border rounded p-3 text-sm space-y-1.5">
                                <a href="{{ route('opportunities.show', $opportunity) }}" class="font-semibold text-gray-900 hover:underline block">
                                    {{ $opportunity->displayName() }}
                                </a>
                                <div class="text-gray-500">{{ $opportunity->company->name }}</div>
                                <div class="text-gray-500">Owner: {{ $opportunity->owner->name }}</div>
                                <div class="flex justify-between text-gray-700">
                                    <span>{{ $opportunity->value ? '$'.number_format($opportunity->value, 0) : '—' }}</span>
                                    <span>{{ $opportunity->probability }}%</span>
                                </div>
                                @if ($opportunity->next_action)
                                    <div class="text-xs text-gray-500 truncate" title="{{ $opportunity->next_action }}">
                                        Next: {{ $opportunity->next_action }}
                                    </div>
                                @endif

                                @php $next = collect($this->allowedNextStages($opportunity)); @endphp
                                <div class="flex flex-wrap gap-1 pt-1">
                                    @foreach ($next->reject(fn ($s) => $s->value === 'closed_lost') as $forward)
                                        @can('transition', [$opportunity, $forward])
                                            <button
                                                type="button"
                                                wire:click="transitionTo({{ $opportunity->id }}, '{{ $forward->value }}')"
                                                wire:loading.attr="disabled"
                                                class="flex-1 text-xs px-2 py-1 rounded bg-gray-800 text-white hover:bg-gray-700 disabled:opacity-50"
                                            >
                                                Advance &rarr; {{ $forward->label() }}
                                            </button>
                                        @endcan
                                    @endforeach

                                    @if ($next->contains(fn ($s) => $s->value === 'closed_lost'))
                                        @can('transition', [$opportunity, \App\Enums\OpportunityStage::ClosedLost])
                                            <button
                                                type="button"
                                                @click="lostModalOpen = true; lostOpportunityId = {{ $opportunity->id }}; lostReason = ''"
                                                class="text-xs px-2 py-1 rounded border border-red-200 text-red-600 hover:bg-red-50"
                                            >
                                                Mark Lost
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4">No opportunities</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div x-show="lostModalOpen" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm" @click.outside="lostModalOpen = false">
            <h3 class="font-medium text-gray-800 mb-2">Mark opportunity as Closed Lost</h3>
            <p class="text-sm text-gray-500 mb-3">A reason is required so the pipeline stays auditable.</p>
            <textarea x-model="lostReason" rows="3" class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Why was this lost?"></textarea>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" @click="lostModalOpen = false" class="text-sm text-gray-600 px-3 py-2">Cancel</button>
                <button
                    type="button"
                    x-bind:disabled="!lostReason.trim()"
                    @click="$wire.transitionTo(lostOpportunityId, 'closed_lost', lostReason); lostModalOpen = false"
                    class="text-sm px-3 py-2 rounded bg-red-600 text-white disabled:opacity-50"
                >
                    Confirm Closed Lost
                </button>
            </div>
        </div>
    </div>
</div>
