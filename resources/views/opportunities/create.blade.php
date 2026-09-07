<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Opportunity') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('opportunities.store') }}">
                    @csrf

                    <div>
                        <x-input-label for="company_id" value="Company" />
                        <select id="company_id" name="company_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">Select a company</option>
                            @foreach ($companies as $c)
                                <option value="{{ $c->id }}" @selected((int) old('company_id', $selectedCompanyId) === $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('company_id')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="owner_id" value="Owner" />
                        <select id="owner_id" name="owner_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}" @selected((int) old('owner_id', auth()->id()) === $owner->id)>{{ $owner->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('owner_id')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="problem" value="Business Problem" />
                        <textarea id="problem" name="problem" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('problem') }}</textarea>
                        <x-input-error :messages="$errors->get('problem')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="value" value="Deal Value ($)" />
                        <x-text-input id="value" name="value" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('value') }}" />
                        <x-input-error :messages="$errors->get('value')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="probability" value="Probability (%)" />
                        <x-text-input id="probability" name="probability" type="number" min="0" max="100" class="mt-1 block w-full" value="{{ old('probability', 10) }}" required />
                        <x-input-error :messages="$errors->get('probability')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="next_action" value="Next Action" />
                        <textarea id="next_action" name="next_action" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('next_action') }}</textarea>
                        <x-input-error :messages="$errors->get('next_action')" class="mt-2" />
                    </div>

                    <p class="mt-4 text-xs text-gray-500">
                        New opportunities always start at the "Target Account" stage. Moving forward is done from the opportunity page.
                    </p>

                    <div class="mt-6 flex gap-2">
                        <x-primary-button>{{ __('Create Opportunity') }}</x-primary-button>
                        <a href="{{ route('opportunities.index') }}" class="text-sm text-gray-600 self-center">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
