<div>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Revenue Pipeline') }}</h2>
            <p class="text-sm text-gray-500 mt-1">Overview of Marixion's active commercial opportunities and next actions.</p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <livewire:opportunity-pipeline-board />
        </div>
    </div>
</div>
