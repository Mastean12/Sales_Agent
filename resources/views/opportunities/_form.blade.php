@php $opportunity = $opportunity ?? null; @endphp

<div class="space-y-8">
    <div>
        <h3 class="font-medium text-gray-700 mb-3">Overview</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-input-label for="name" value="Opportunity Name" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $opportunity?->name) }}" placeholder="Defaults to the company name if left blank" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            @if (! $opportunity)
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
            @endif

            <div>
                <x-input-label for="owner_id" value="Owner" />
                <select id="owner_id" name="owner_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((int) old('owner_id', $opportunity?->owner_id ?? auth()->id()) === $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('owner_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="value" value="Deal Value ($)" />
                <x-text-input id="value" name="value" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('value', $opportunity?->value) }}" />
                <x-input-error :messages="$errors->get('value')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="probability" value="Probability (%)" />
                <x-text-input id="probability" name="probability" type="number" min="0" max="100" class="mt-1 block w-full" value="{{ old('probability', $opportunity?->probability ?? 10) }}" required />
                <x-input-error :messages="$errors->get('probability')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="next_action" value="Next Action" />
                <x-text-input id="next_action" name="next_action" type="text" class="mt-1 block w-full" value="{{ old('next_action', $opportunity?->next_action) }}" />
                <x-input-error :messages="$errors->get('next_action')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="next_action_due" value="Next Action Due" />
                <x-text-input id="next_action_due" name="next_action_due" type="date" class="mt-1 block w-full" value="{{ old('next_action_due', optional($opportunity?->next_action_due)->format('Y-m-d')) }}" />
                <x-input-error :messages="$errors->get('next_action_due')" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="notes" value="Notes" />
                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('notes', $opportunity?->notes) }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
            </div>
        </div>

        @unless ($opportunity)
            <p class="mt-3 text-xs text-gray-500">
                New opportunities always start at the "Target Account" stage. Moving forward is done from the opportunity page via the controlled workflow.
            </p>
        @endunless
    </div>

    <div>
        <h3 class="font-medium text-gray-700 mb-3">Business Problem</h3>
        <p class="text-xs text-gray-500 mb-3">Manually entered for now — AI-based Problem Intelligence will extract these automatically in a later phase.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-input-label for="problem" value="Problem Statement" />
                <textarea id="problem" name="problem" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('problem', $opportunity?->problem) }}</textarea>
                <x-input-error :messages="$errors->get('problem')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="problem_category" value="Problem Category" />
                <x-text-input id="problem_category" name="problem_category" type="text" class="mt-1 block w-full" value="{{ old('problem_category', $opportunity?->problem_category) }}" />
                <x-input-error :messages="$errors->get('problem_category')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="urgency" value="Urgency" />
                <x-text-input id="urgency" name="urgency" type="text" class="mt-1 block w-full" value="{{ old('urgency', $opportunity?->urgency) }}" />
                <x-input-error :messages="$errors->get('urgency')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="evidence" value="Evidence" />
                <textarea id="evidence" name="evidence" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('evidence', $opportunity?->evidence) }}</textarea>
                <x-input-error :messages="$errors->get('evidence')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="impact" value="Impact" />
                <textarea id="impact" name="impact" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('impact', $opportunity?->impact) }}</textarea>
                <x-input-error :messages="$errors->get('impact')" class="mt-2" />
            </div>
        </div>
    </div>

    <div>
        <h3 class="font-medium text-gray-700 mb-3">Qualification</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="stakeholder" value="Stakeholder" />
                <x-text-input id="stakeholder" name="stakeholder" type="text" class="mt-1 block w-full" value="{{ old('stakeholder', $opportunity?->stakeholder) }}" />
                <x-input-error :messages="$errors->get('stakeholder')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="budget_signal" value="Budget Signal" />
                <x-text-input id="budget_signal" name="budget_signal" type="text" class="mt-1 block w-full" value="{{ old('budget_signal', $opportunity?->budget_signal) }}" />
                <x-input-error :messages="$errors->get('budget_signal')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="business_impact" value="Business Impact" />
                <x-text-input id="business_impact" name="business_impact" type="text" class="mt-1 block w-full" value="{{ old('business_impact', $opportunity?->business_impact) }}" />
                <x-input-error :messages="$errors->get('business_impact')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="decision_process" value="Decision Process" />
                <x-text-input id="decision_process" name="decision_process" type="text" class="mt-1 block w-full" value="{{ old('decision_process', $opportunity?->decision_process) }}" />
                <x-input-error :messages="$errors->get('decision_process')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="fit" value="Fit" />
                <x-text-input id="fit" name="fit" type="text" class="mt-1 block w-full" value="{{ old('fit', $opportunity?->fit) }}" />
                <x-input-error :messages="$errors->get('fit')" class="mt-2" />
            </div>
        </div>
    </div>
</div>
