<?php

namespace App\Http\Controllers;

use App\Actions\Opportunities\CreateOpportunityAction;
use App\Actions\Opportunities\DeleteOpportunityAction;
use App\Actions\Opportunities\UpdateOpportunityAction;
use App\Enums\OpportunityStage;
use App\Exceptions\InvalidStageTransitionException;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\User;
use App\Workflows\OpportunityWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class OpportunityController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Opportunity::class);

        $stages = OpportunityStage::cases();
        $view = $request->string('view', 'list') === 'pipeline' ? 'pipeline' : 'list';

        $opportunities = Opportunity::query()
            ->with(['company', 'owner'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $term));
                });
            })
            ->when($request->filled('stage'), fn ($query) => $query->where('stage', $request->string('stage')))
            ->when($request->filled('company_id'), fn ($query) => $query->where('company_id', $request->integer('company_id')))
            ->when($request->filled('owner_id'), fn ($query) => $query->where('owner_id', $request->integer('owner_id')))
            ->when($request->filled('min_value'), fn ($query) => $query->where('value', '>=', $request->float('min_value')))
            ->when($request->filled('max_value'), fn ($query) => $query->where('value', '<=', $request->float('max_value')))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $companies = Company::query()->orderBy('name')->get(['id', 'name']);
        $owners = User::query()->orderBy('name')->get(['id', 'name']);

        return view('opportunities.index', compact('opportunities', 'stages', 'view', 'companies', 'owners'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Opportunity::class);

        $companies = Company::query()->orderBy('name')->get(['id', 'name']);
        $owners = User::query()->orderBy('name')->get(['id', 'name']);
        $selectedCompanyId = $request->integer('company_id') ?: null;

        return view('opportunities.create', compact('companies', 'owners', 'selectedCompanyId'));
    }

    public function store(Request $request, CreateOpportunityAction $action): RedirectResponse
    {
        $this->authorize('create', Opportunity::class);

        $data = $this->validated($request);

        $opportunity = $action->execute($data, $request->user());

        return redirect()->route('opportunities.show', $opportunity)->with('status', 'Opportunity created.');
    }

    public function show(Opportunity $opportunity): View
    {
        $this->authorize('view', $opportunity);

        $opportunity->load([
            'company.contacts',
            'owner',
            'auditEvents' => fn ($q) => $q->latest()->with('actor'),
        ]);

        $nextStages = OpportunityWorkflow::allowedNextStages($opportunity->stage);

        return view('opportunities.show', compact('opportunity', 'nextStages'));
    }

    public function edit(Opportunity $opportunity): View
    {
        $this->authorize('update', $opportunity);

        $owners = User::query()->orderBy('name')->get(['id', 'name']);

        return view('opportunities.edit', compact('opportunity', 'owners'));
    }

    public function update(Request $request, Opportunity $opportunity, UpdateOpportunityAction $action): RedirectResponse
    {
        $this->authorize('update', $opportunity);

        $data = $this->validated($request);

        $action->execute($opportunity, $data, $request->user());

        return redirect()->route('opportunities.show', $opportunity)->with('status', 'Opportunity updated.');
    }

    public function transition(Request $request, Opportunity $opportunity, OpportunityWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'to' => ['required', 'string', 'in:'.implode(',', OpportunityStage::values())],
            'reason' => ['nullable', 'string'],
        ]);

        $to = OpportunityStage::from($data['to']);

        $this->authorize('transition', [$opportunity, $to]);

        try {
            $workflow->transition($opportunity, $to, $request->user(), $data['reason'] ?? null);
        } catch (InvalidStageTransitionException|InvalidArgumentException $exception) {
            return back()->withErrors(['stage' => $exception->getMessage()]);
        }

        return redirect()->route('opportunities.show', $opportunity)->with('status', 'Opportunity moved to '.$to->label().'.');
    }

    public function destroy(Request $request, Opportunity $opportunity, DeleteOpportunityAction $action): RedirectResponse
    {
        $this->authorize('delete', $opportunity);

        $action->execute($opportunity, $request->user());

        return redirect()->route('opportunities.index')->with('status', 'Opportunity deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'owner_id' => ['required', 'exists:users,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'problem' => ['nullable', 'string'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['required', 'integer', 'min:0', 'max:100'],
            'next_action' => ['nullable', 'string'],
            'next_action_due' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'problem_category' => ['nullable', 'string', 'max:255'],
            'evidence' => ['nullable', 'string'],
            'impact' => ['nullable', 'string'],
            'urgency' => ['nullable', 'string', 'max:255'],
            'stakeholder' => ['nullable', 'string', 'max:255'],
            'budget_signal' => ['nullable', 'string', 'max:255'],
            'business_impact' => ['nullable', 'string', 'max:255'],
            'decision_process' => ['nullable', 'string', 'max:255'],
            'fit' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
