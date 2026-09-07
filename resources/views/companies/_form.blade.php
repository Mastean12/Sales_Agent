@php $company = $company ?? null; @endphp

<div>
    <x-input-label for="name" value="Name" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $company?->name) }}" required />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="domain" value="Domain" />
    <x-text-input id="domain" name="domain" type="text" class="mt-1 block w-full" value="{{ old('domain', $company?->domain) }}" />
    <x-input-error :messages="$errors->get('domain')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="industry" value="Industry" />
    <x-text-input id="industry" name="industry" type="text" class="mt-1 block w-full" value="{{ old('industry', $company?->industry) }}" />
    <x-input-error :messages="$errors->get('industry')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="location" value="Location" />
    <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" value="{{ old('location', $company?->location) }}" />
    <x-input-error :messages="$errors->get('location')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="icp_score" value="ICP Score (0-100)" />
    <x-text-input id="icp_score" name="icp_score" type="number" min="0" max="100" class="mt-1 block w-full" value="{{ old('icp_score', $company?->icp_score) }}" />
    <x-input-error :messages="$errors->get('icp_score')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="source" value="Source" />
    <x-text-input id="source" name="source" type="text" class="mt-1 block w-full" value="{{ old('source', $company?->source) }}" />
    <x-input-error :messages="$errors->get('source')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="status" value="Status" />
    <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        @foreach (['prospecting', 'active', 'inactive', 'disqualified'] as $status)
            <option value="{{ $status }}" @selected(old('status', $company?->status ?? 'prospecting') === $status)>{{ ucfirst($status) }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>
