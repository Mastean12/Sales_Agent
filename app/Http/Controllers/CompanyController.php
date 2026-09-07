<?php

namespace App\Http\Controllers;

use App\Actions\Companies\CreateCompanyAction;
use App\Actions\Companies\DeleteCompanyAction;
use App\Actions\Companies\UpdateCompanyAction;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Company::class);

        $companies = Company::query()
            ->withCount(['contacts', 'opportunities'])
            ->orderBy('name')
            ->paginate(20);

        return view('companies.index', compact('companies'));
    }

    public function create(): View
    {
        $this->authorize('create', Company::class);

        return view('companies.create');
    }

    public function store(Request $request, CreateCompanyAction $action): RedirectResponse
    {
        $this->authorize('create', Company::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:companies,domain'],
            'industry' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'icp_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'source' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:prospecting,active,inactive,disqualified'],
        ]);

        $company = $action->execute($data, $request->user());

        return redirect()->route('companies.show', $company)->with('status', 'Company created.');
    }

    public function show(Company $company): View
    {
        $this->authorize('view', $company);

        $company->load(['contacts', 'opportunities.owner']);

        return view('companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        $this->authorize('update', $company);

        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company, UpdateCompanyAction $action): RedirectResponse
    {
        $this->authorize('update', $company);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:companies,domain,'.$company->id],
            'industry' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'icp_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'source' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:prospecting,active,inactive,disqualified'],
        ]);

        $action->execute($company, $data, $request->user());

        return redirect()->route('companies.show', $company)->with('status', 'Company updated.');
    }

    public function destroy(Request $request, Company $company, DeleteCompanyAction $action): RedirectResponse
    {
        $this->authorize('delete', $company);

        $action->execute($company, $request->user());

        return redirect()->route('companies.index')->with('status', 'Company deleted.');
    }
}
