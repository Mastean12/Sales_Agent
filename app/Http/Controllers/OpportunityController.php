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

        $opportunities = Opportunity::query()
            ->with(['company', 'owner'])
            ->when($request->filled('stage'), fn ($query) => $query->where('stage', $request->string('stage')))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('opportunities.index', compact('opportunities', 'stages'));
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

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'owner_id' => ['required', 'exists:users,id'],
            'problem' => ['nullable', 'string'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['required', 'integer', 'min:0', 'max:100'],
            'next_action' => ['nullable', 'string'],
        ]);

        $opportunity = $action->execute($data, $request->user());

        return redirect()->route('opportunities.show', $opportunity)->with('status', 'Opportunity created.');
    }

    public function show(Opportunity $opportunity): View
    {
        $this->authorize('view', $opportunity);

        $opportunity->load(['company', 'owner', 'auditEvents.actor']);

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

        $data = $request->validate([
            'owner_id' => ['required', 'exists:users,id'],
            'problem' => ['nullable', 'string'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['required', 'integer', 'min:0', 'max:100'],
            'next_action' => ['nullable', 'string'],
        ]);

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
}
