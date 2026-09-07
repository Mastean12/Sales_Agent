<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Opportunities') }}</h2>
                <p class="text-sm text-gray-500 mt-1">Manage Marixion's active revenue opportunities.</p>
            </div>
            @can('create', \App\Models\Opportunity::class)
                <a href="{{ route('opportunities.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm shrink-0">
                    {{ __('+ New Opportunity') }}
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

            <div class="flex gap-2 text-sm">
                <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}"
                   class="px-3 py-1.5 rounded-md {{ $view === 'list' ? 'bg-gray-800 text-white' : 'bg-white border border-gray-300 text-gray-700' }}">
                    List View
                </a>
                <a href="{{ request()->fullUrlWithQuery(['view' => 'pipeline']) }}"
                   class="px-3 py-1.5 rounded-md {{ $view === 'pipeline' ? 'bg-gray-800 text-white' : 'bg-white border border-gray-300 text-gray-700' }}">
                    Pipeline View
                </a>
            </div>

            @if ($view === 'pipeline')
                <livewire:opportunity-pipeline-board />
            @else
                <form method="GET" class="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 sm:grid-cols-6 gap-3 text-sm">
                    <input type="hidden" name="view" value="list">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search opportunities…"
                           class="border-gray-300 rounded-md shadow-sm sm:col-span-2" />

                    <select name="stage" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                        <option value="">All stages</option>
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->value }}" @selected(request('stage') === $stage->value)>{{ $stage->label() }}</option>
                        @endforeach
                    </select>

                    <select name="company_id" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                        <option value="">All companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected((string) request('company_id') === (string) $company->id)>{{ $company->name }}</option>
                        @endforeach
                    </select>

                    <select name="owner_id" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                        <option value="">All owners</option>
                        @foreach ($owners as $owner)
                            <option value="{{ $owner->id }}" @selected((string) request('owner_id') === (string) $owner->id)>{{ $owner->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex gap-2">
                        <input type="number" name="min_value" value="{{ request('min_value') }}" placeholder="Min $" class="border-gray-300 rounded-md shadow-sm w-1/2" />
                        <input type="number" name="max_value" value="{{ request('max_value') }}" placeholder="Max $" class="border-gray-300 rounded-md shadow-sm w-1/2" />
                    </div>

                    <div class="sm:col-span-6 flex gap-3">
                        <button class="text-sm text-gray-700 underline">Search</button>
                        @if (request()->anyFilled(['q', 'stage', 'company_id', 'owner_id', 'min_value', 'max_value']))
                            <a href="{{ route('opportunities.index') }}" class="text-sm text-gray-500">Clear filters</a>
                        @endif
                    </div>
                </form>

                @if ($opportunities->isEmpty())
                    <div class="bg-white shadow-sm sm:rounded-lg p-12 text-center">
                        <h3 class="text-gray-800 font-medium">No opportunities yet.</h3>
                        <p class="text-sm text-gray-500 mt-1">Create an opportunity to start tracking potential revenue.</p>
                        @can('create', \App\Models\Opportunity::class)
                            <a href="{{ route('opportunities.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-gray-800 text-white rounded-md text-sm">
                                + New Opportunity
                            </a>
                        @endcan
                    </div>
                @else
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Opportunity</th>
                                    <th class="px-4 py-2 text-left">Company</th>
                                    <th class="px-4 py-2 text-left">Stage</th>
                                    <th class="px-4 py-2 text-left">Owner</th>
                                    <th class="px-4 py-2 text-left">Value</th>
                                    <th class="px-4 py-2 text-left">Probability</th>
                                    <th class="px-4 py-2 text-left">Expected Revenue</th>
                                    <th class="px-4 py-2 text-left">Next Action</th>
                                    <th class="px-4 py-2 text-left">Updated</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($opportunities as $opportunity)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('opportunities.show', $opportunity) }}" class="hover:underline font-medium text-gray-900">{{ $opportunity->displayName() }}</a>
                                        </td>
                                        <td class="px-4 py-2"><a href="{{ route('companies.show', $opportunity->company) }}" class="hover:underline">{{ $opportunity->company->name }}</a></td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ $opportunity->stage->label() }}</span>
                                        </td>
                                        <td class="px-4 py-2">{{ $opportunity->owner->name }}</td>
                                        <td class="px-4 py-2">{{ $opportunity->value ? '$'.number_format($opportunity->value, 0) : '—' }}</td>
                                        <td class="px-4 py-2">{{ $opportunity->probability }}%</td>
                                        <td class="px-4 py-2">{{ $opportunity->expected_value ? '$'.number_format($opportunity->expected_value, 0) : '—' }}</td>
                                        <td class="px-4 py-2 truncate max-w-[12rem]" title="{{ $opportunity->next_action }}">{{ $opportunity->next_action ?? '—' }}</td>
                                        <td class="px-4 py-2">{{ $opportunity->updated_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div>{{ $opportunities->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
