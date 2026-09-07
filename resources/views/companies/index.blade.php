<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Companies') }}</h2>
            @can('create', \App\Models\Company::class)
                <a href="{{ route('companies.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm">
                    {{ __('New Company') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Industry</th>
                            <th class="px-4 py-2 text-left">ICP Score</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Contacts</th>
                            <th class="px-4 py-2 text-left">Opportunities</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($companies as $company)
                            <tr>
                                <td class="px-4 py-2">
                                    <a href="{{ route('companies.show', $company) }}" class="text-gray-900 font-medium hover:underline">
                                        {{ $company->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-2">{{ $company->industry }}</td>
                                <td class="px-4 py-2">{{ $company->icp_score }}</td>
                                <td class="px-4 py-2">{{ $company->status }}</td>
                                <td class="px-4 py-2">{{ $company->contacts_count }}</td>
                                <td class="px-4 py-2">{{ $company->opportunities_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">No companies yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $companies->links() }}</div>
        </div>
    </div>
</x-app-layout>
