<?php

namespace App\Http\Controllers;

use App\Actions\Contacts\CreateContactAction;
use App\Actions\Contacts\UpdateContactAction;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Contact::class);

        $contacts = Contact::query()
            ->with('company')
            ->orderBy('name')
            ->paginate(20);

        return view('contacts.index', compact('contacts'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Contact::class);

        $companies = Company::query()->orderBy('name')->get(['id', 'name']);
        $selectedCompanyId = $request->integer('company_id') ?: null;

        return view('contacts.create', compact('companies', 'selectedCompanyId'));
    }

    public function store(Request $request, CreateContactAction $action): RedirectResponse
    {
        $this->authorize('create', Contact::class);

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:255'],
            'communication_status' => ['required', 'string', 'in:not_contacted,contacted,engaged,unresponsive,opted_out'],
        ]);

        $contact = $action->execute($data, $request->user());

        return redirect()->route('companies.show', $contact->company_id)->with('status', 'Contact created.');
    }

    public function edit(Contact $contact): View
    {
        $this->authorize('update', $contact);

        $companies = Company::query()->orderBy('name')->get(['id', 'name']);

        return view('contacts.edit', compact('contact', 'companies'));
    }

    public function update(Request $request, Contact $contact, UpdateContactAction $action): RedirectResponse
    {
        $this->authorize('update', $contact);

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:255'],
            'communication_status' => ['required', 'string', 'in:not_contacted,contacted,engaged,unresponsive,opted_out'],
            'opted_out' => ['sometimes', 'boolean'],
        ]);

        $action->execute($contact, $data, $request->user());

        return redirect()->route('companies.show', $contact->company_id)->with('status', 'Contact updated.');
    }

    public function destroy(Request $request, Contact $contact): RedirectResponse
    {
        $this->authorize('delete', $contact);

        $companyId = $contact->company_id;
        $contact->delete();

        return redirect()->route('companies.show', $companyId)->with('status', 'Contact deleted.');
    }
}
