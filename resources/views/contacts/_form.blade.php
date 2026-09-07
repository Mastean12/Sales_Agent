@php $contact = $contact ?? null; @endphp

<div>
    <x-input-label for="company_id" value="Company" />
    <select id="company_id" name="company_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
        <option value="">Select a company</option>
        @foreach ($companies as $c)
            <option value="{{ $c->id }}" @selected((int) old('company_id', $selectedCompanyId ?? $contact?->company_id) === $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('company_id')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="name" value="Name" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $contact?->name) }}" required />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="role" value="Role" />
    <x-text-input id="role" name="role" type="text" class="mt-1 block w-full" value="{{ old('role', $contact?->role) }}" />
    <x-input-error :messages="$errors->get('role')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="email" value="Email" />
    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email', $contact?->email) }}" />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="phone" value="Phone" />
    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" value="{{ old('phone', $contact?->phone) }}" />
    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="source" value="Source" />
    <x-text-input id="source" name="source" type="text" class="mt-1 block w-full" value="{{ old('source', $contact?->source) }}" />
    <x-input-error :messages="$errors->get('source')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="communication_status" value="Communication Status" />
    <select id="communication_status" name="communication_status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        @foreach (['not_contacted', 'contacted', 'engaged', 'unresponsive', 'opted_out'] as $status)
            <option value="{{ $status }}" @selected(old('communication_status', $contact?->communication_status ?? 'not_contacted') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('communication_status')" class="mt-2" />
</div>

@if ($contact)
    <div class="mt-4 flex items-center gap-2">
        <input type="checkbox" id="opted_out" name="opted_out" value="1" @checked(old('opted_out', $contact->opted_out)) />
        <x-input-label for="opted_out" value="Opted out of communication" />
    </div>
@endif
