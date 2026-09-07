<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Opportunities') }}</h2>
            @can('create', \App\Models\Opportunity::class)
                <a href="{{ route('opportunities.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm">
                    {{ __('New Opportunity') }}
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

            <form method="GET" class="flex gap-2 items-center text-sm">
                <label for="stage">Filter by stage:</label>
                <select id="stage" name="stage" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach ($stages as $stage)
                        <option value="{{ $stage->value }}" @selected(request('stage') === $stage->value)>{{ $stage->label() }}</option>
                    @endforeach
                </select>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Company</th>
                            <th class="px-4 py-2 text-left">Stage</th>
                            <th class="px-4 py-2 text-left">Owner</th>
                            <th class="px-4 py-2 text-left">Value</th>
                            <th class="px-4 py-2 text-left">Expected</th>
                            <th class="px-4 py-2 text-left">Updated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($opportunities as $opportunity)
                            <tr>
                                <td class="px-4 py-2">
                                    <a href="{{ route('opportunities.show', $opportunity) }}" class="hover:underline">{{ $opportunity->company->name }}</a>
                                </td>
                                <td class="px-4 py-2">{{ $opportunity->stage->label() }}</td>
                                <td class="px-4 py-2">{{ $opportunity->owner->name }}</td>
                                <td class="px-4 py-2">{{ $opportunity->value ? '$'.number_format($opportunity->value, 0) : '—' }}</td>
                                <td class="px-4 py-2">{{ $opportunity->expected_value ? '$'.number_format($opportunity->expected_value, 0) : '—' }}</td>
                                <td class="px-4 py-2">{{ $opportunity->updated_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No opportunities yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $opportunities->links() }}</div>
        </div>
    </div>
</x-app-layout>
