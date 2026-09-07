<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Companies') }}</h2>
                <p class="text-sm text-gray-500 mt-1">Manage target accounts, prospects and client organizations.</p>
            </div>
            @can('create', \App\Models\Company::class)
                <a href="{{ route('companies.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm shrink-0">
                    {{ __('+ Add Company') }}
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
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search companies…"
                       class="border-gray-300 rounded-md shadow-sm sm:col-span-2" />

                <select name="status" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (\App\Http\Controllers\CompanyController::STATUSES as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>

                <select name="industry" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">All industries</option>
                    @foreach ($industries as $industry)
                        <option value="{{ $industry }}" @selected(request('industry') === $industry)>{{ $industry }}</option>
                    @endforeach
                </select>

                <select name="owner_id" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">All owners</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((string) request('owner_id') === (string) $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </select>

                <label class="flex items-center gap-2 sm:col-span-5">
                    <input type="checkbox" name="has_open_opportunity" value="1" @checked(request('has_open_opportunity')) onchange="this.form.submit()" />
                    Has open opportunity
                </label>

                <div class="sm:col-span-5 flex gap-3">
                    <button class="text-sm text-gray-700 underline">Search</button>
                    @if (request()->anyFilled(['q', 'status', 'industry', 'owner_id', 'has_open_opportunity']))
                        <a href="{{ route('companies.index') }}" class="text-sm text-gray-500">Clear filters</a>
                    @endif
                </div>
            </form>

            @if ($companies->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-12 text-center">
                    <h3 class="text-gray-800 font-medium">No companies yet.</h3>
                    <p class="text-sm text-gray-500 mt-1">Start building your revenue pipeline by adding your first target account.</p>
                    @can('create', \App\Models\Company::class)
                        <a href="{{ route('companies.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-gray-800 text-white rounded-md text-sm">
                            + Add Company
                        </a>
                    @endcan
                </div>
            @else
                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Company</th>
                                <th class="px-4 py-2 text-left">Domain</th>
                                <th class="px-4 py-2 text-left">Industry</th>
                                <th class="px-4 py-2 text-left">Location</th>
                                <th class="px-4 py-2 text-left">ICP Score</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Owner</th>
                                <th class="px-4 py-2 text-left">Open Opps</th>
                                <th class="px-4 py-2 text-left">Last Activity</th>
                                <th class="px-4 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($companies as $company)
                                <tr>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('companies.show', $company) }}" class="text-gray-900 font-medium hover:underline">
                                            {{ $company->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2 text-gray-500">{{ $company->domain }}</td>
                                    <td class="px-4 py-2">{{ $company->industry }}</td>
                                    <td class="px-4 py-2">{{ $company->location }}</td>
                                    <td class="px-4 py-2">{{ $company->icp_score }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ ucfirst($company->status) }}</span>
                                    </td>
                                    <td class="px-4 py-2">{{ $company->owner?->name ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $company->opportunities_count }}</td>
                                    <td class="px-4 py-2 text-gray-500">{{ $company->updated_at->diffForHumans() }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('companies.show', $company) }}" class="text-gray-600 hover:underline">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>{{ $companies->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
