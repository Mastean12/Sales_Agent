<?php

namespace App\Http\Controllers;

use App\Actions\Contacts\CreateContactAction;
use App\Actions\Contacts\DeleteContactAction;
use App\Actions\Contacts\UpdateContactAction;
use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactController extends Controller
{
    public const COMMUNICATION_STATUSES = ['not_contacted', 'contacted', 'engaged', 'unresponsive', 'opted_out'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Contact::class);

        $contacts = Contact::query()
            ->with(['company', 'owner'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('role', 'like', $term)
                        ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $term));
                });
            })
            ->when($request->filled('company_id'), fn ($query) => $query->where('company_id', $request->integer('company_id')))
            ->when($request->filled('communication_status'), fn ($query) => $query->where('communication_status', $request->string('communication_status')))
            ->when($request->filled('owner_id'), fn ($query) => $query->where('owner_id', $request->integer('owner_id')))
            ->when($request->filled('source'), fn ($query) => $query->where('source', $request->string('source')))
            ->when($request->get('suppressed') === '1', fn ($query) => $query->where('opted_out', true))
            ->when($request->get('suppressed') === '0', fn ($query) => $query->where('opted_out', false))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $companies = Company::query()->orderBy('name')->get(['id', 'name']);
        $owners = User::query()->orderBy('name')->get(['id', 'name']);
        $sources = Contact::query()->whereNotNull('source')->distinct()->orderBy('source')->pluck('source');

        return view('contacts.index', compact('contacts', 'companies', 'owners', 'sources'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Contact::class);

        $companies = Company::query()->orderBy('name')->get(['id', 'name']);
        $owners = User::query()->orderBy('name')->get(['id', 'name']);
        $selectedCompanyId = $request->integer('company_id') ?: null;

        return view('contacts.create', compact('companies', 'owners', 'selectedCompanyId'));
    }

    public function store(Request $request, CreateContactAction $action): RedirectResponse
    {
        $this->authorize('create', Contact::class);

        $data = $this->validated($request);

        if ($duplicate = $this->findDuplicate($data)) {
            return back()->withInput()->withErrors([
                'email' => "{$duplicate->name} at this company already has this email address.",
            ]);
        }

        $contact = $action->execute($data, $request->user());

        return redirect()->route('contacts.show', $contact)->with('status', 'Contact created.');
    }

    public function show(Contact $contact): View
    {
        $this->authorize('view', $contact);

        $contact->load(['company', 'owner', 'opportunities.owner', 'auditEvents' => fn ($q) => $q->latest()->with('actor')]);

        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact): View
    {
        $this->authorize('update', $contact);

        $companies = Company::query()->orderBy('name')->get(['id', 'name']);
        $owners = User::query()->orderBy('name')->get(['id', 'name']);

        return view('contacts.edit', compact('contact', 'companies', 'owners'));
    }

    public function update(Request $request, Contact $contact, UpdateContactAction $action): RedirectResponse
    {
        $this->authorize('update', $contact);

        $data = $this->validated($request);

        if ($duplicate = $this->findDuplicate($data, $contact)) {
            return back()->withInput()->withErrors([
                'email' => "{$duplicate->name} at this company already has this email address.",
            ]);
        }

        $action->execute($contact, $data, $request->user());

        return redirect()->route('contacts.show', $contact)->with('status', 'Contact updated.');
    }

    public function destroy(Request $request, Contact $contact, DeleteContactAction $action): RedirectResponse
    {
        $this->authorize('delete', $contact);

        $companyId = $contact->company_id;
        $action->execute($contact, $request->user());

        return redirect()->route('companies.show', $companyId)->with('status', 'Contact deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:255'],
            'communication_status' => ['required', 'string', Rule::in(self::COMMUNICATION_STATUSES)],
            'owner_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'opted_out' => ['sometimes', 'boolean'],
        ]);

        $data['email'] = ! empty($data['email']) ? strtolower(trim($data['email'])) : null;

        return $data;
    }

    private function findDuplicate(array $data, ?Contact $ignore = null): ?Contact
    {
        if (! $data['email']) {
            return null;
        }

        return Contact::query()
            ->where('company_id', $data['company_id'])
            ->where('email', $data['email'])
            ->when($ignore, fn ($query) => $query->where('id', '!=', $ignore->id))
            ->first();
    }
}
