<?php

namespace App\Http\Controllers;

use App\Actions\Companies\CreateCompanyAction;
use App\Actions\Companies\DeleteCompanyAction;
use App\Actions\Companies\UpdateCompanyAction;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public const STATUSES = ['prospecting', 'active', 'inactive', 'disqualified'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Company::class);

        $companies = Company::query()
            ->withCount('opportunities')
            ->with('owner')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('domain', 'like', $term)
                        ->orWhere('industry', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('industry'), fn ($query) => $query->where('industry', $request->string('industry')))
            ->when($request->filled('owner_id'), fn ($query) => $query->where('owner_id', $request->integer('owner_id')))
            ->when($request->boolean('has_open_opportunity'), function ($query) {
                $query->whereHas('opportunities', fn ($q) => $q->whereNotIn('stage', ['closed_won', 'closed_lost']));
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $industries = Company::query()->whereNotNull('industry')->distinct()->orderBy('industry')->pluck('industry');
        $owners = User::query()->orderBy('name')->get(['id', 'name']);

        return view('companies.index', compact('companies', 'industries', 'owners'));
    }

    public function create(): View
    {
        $this->authorize('create', Company::class);

        $owners = User::query()->orderBy('name')->get(['id', 'name']);

        return view('companies.create', compact('owners'));
    }

    public function store(Request $request, CreateCompanyAction $action): RedirectResponse
    {
        $this->authorize('create', Company::class);

        $data = $this->validated($request);

        $existing = $data['domain'] ? Company::firstWhere('domain', $data['domain']) : null;

        if ($existing) {
            return back()->withInput()->withErrors([
                'domain' => "A company with this domain already exists: \"{$existing->name}\". Open it instead of creating a duplicate.",
            ]);
        }

        $company = $action->execute($data, $request->user());

        return redirect()->route('companies.show', $company)->with('status', 'Company created.');
    }

    public function show(Company $company): View
    {
        $this->authorize('view', $company);

        $company->load(['owner', 'contacts.owner', 'opportunities.owner', 'auditEvents' => fn ($q) => $q->latest()->with('actor')]);

        return view('companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        $this->authorize('update', $company);

        $owners = User::query()->orderBy('name')->get(['id', 'name']);

        return view('companies.edit', compact('company', 'owners'));
    }

    public function update(Request $request, Company $company, UpdateCompanyAction $action): RedirectResponse
    {
        $this->authorize('update', $company);

        $data = $this->validated($request, $company);

        $existing = $data['domain'] ? Company::where('domain', $data['domain'])->where('id', '!=', $company->id)->first() : null;

        if ($existing) {
            return back()->withInput()->withErrors([
                'domain' => "A company with this domain already exists: \"{$existing->name}\".",
            ]);
        }

        $action->execute($company, $data, $request->user());

        return redirect()->route('companies.show', $company)->with('status', 'Company updated.');
    }

    public function destroy(Request $request, Company $company, DeleteCompanyAction $action): RedirectResponse
    {
        $this->authorize('delete', $company);

        $action->execute($company, $request->user());

        return redirect()->route('companies.index')->with('status', 'Company deleted.');
    }

    private function validated(Request $request, ?Company $company = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'icp_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'source' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(self::STATUSES)],
            'owner_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['domain'] = ! empty($data['domain']) ? strtolower(trim($data['domain'])) : null;

        return $data;
    }
}
