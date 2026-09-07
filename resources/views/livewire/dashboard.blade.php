<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Revenue Pipeline') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($transitionError)
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    {{ $transitionError }}
                </div>
            @endif

            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Open Opportunities</div>
                    <div class="text-2xl font-semibold">{{ $this->stats['open_count'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Open Pipeline Value</div>
                    <div class="text-2xl font-semibold">${{ number_format($this->stats['pipeline_value'], 0) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Expected Revenue</div>
                    <div class="text-2xl font-semibold">${{ number_format($this->stats['expected_value'], 0) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm text-gray-500">Closed Won</div>
                    <div class="text-2xl font-semibold">{{ $this->stats['closed_won_count'] }}</div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <div class="flex gap-4 min-w-max pb-4">
                    @foreach ($this->stages as $stage)
                        <div class="bg-white shadow-sm rounded-lg w-72 shrink-0">
                            <div class="px-4 py-3 border-b font-medium text-sm text-gray-700 flex justify-between items-center">
                                <span>{{ $stage->label() }}</span>
                                <span class="text-gray-400">{{ $this->opportunitiesByStage[$stage->value]->count() }}</span>
                            </div>
                            <div class="p-3 space-y-3 max-h-[32rem] overflow-y-auto">
                                @foreach ($this->opportunitiesByStage[$stage->value] as $opportunity)
                                    <div class="border rounded p-3 text-sm space-y-2">
                                        <div class="font-semibold">
                                            <a href="{{ route('opportunities.show', $opportunity) }}" class="hover:underline">
                                                {{ $opportunity->company->name }}
                                            </a>
                                        </div>
                                        <div class="text-gray-500">Owner: {{ $opportunity->owner->name }}</div>
                                        @if ($opportunity->value)
                                            <div class="text-gray-700">${{ number_format($opportunity->value, 0) }}</div>
                                        @endif

                                        @foreach ($this->allowedNextStages($opportunity) as $next)
                                            @can('transition', [$opportunity, $next])
                                                <button
                                                    type="button"
                                                    wire:click="transitionTo({{ $opportunity->id }}, '{{ $next->value }}', {{ $next->value === 'closed_lost' ? "prompt('Reason for closing lost:')" : 'null' }})"
                                                    class="w-full text-left text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200"
                                                >
                                                    Move to {{ $next->label() }} &rarr;
                                                </button>
                                            @endcan
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
