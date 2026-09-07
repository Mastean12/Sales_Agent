<?php

namespace App\Enums;

/**
 * The controlled opportunity pipeline (execution plan, sections 2 and 12).
 *
 * Closed Won/Lost is modelled as two terminal states so a lost reason and a
 * won outcome are never ambiguous in reporting or HubSpot sync.
 */
enum OpportunityStage: string
{
    case TargetAccount = 'target_account';
    case ProspectIdentified = 'prospect_identified';
    case EngagedProspect = 'engaged_prospect';
    case QualifiedDiscovery = 'qualified_discovery';
    case SalesQualifiedOpportunity = 'sales_qualified_opportunity';
    case TechnicalSolutionDiscovery = 'technical_solution_discovery';
    case Proposal = 'proposal';
    case Negotiation = 'negotiation';
    case ClosedWon = 'closed_won';
    case ClosedLost = 'closed_lost';

    public function label(): string
    {
        return match ($this) {
            self::TargetAccount => 'Target Account',
            self::ProspectIdentified => 'Prospect Identified',
            self::EngagedProspect => 'Engaged Prospect',
            self::QualifiedDiscovery => 'Qualified Discovery',
            self::SalesQualifiedOpportunity => 'Sales Qualified Opportunity',
            self::TechnicalSolutionDiscovery => 'Technical / Solution Discovery',
            self::Proposal => 'Proposal',
            self::Negotiation => 'Negotiation',
            self::ClosedWon => 'Closed Won',
            self::ClosedLost => 'Closed Lost',
        };
    }

    /**
     * Owning role for this stage, per the execution plan's "Controlled
     * Revenue Journey" table (section 2). Used to decide who may transition
     * an opportunity out of the stage. "Agent" (AI/automation layer) is
     * treated as Sales/Management until the automation layer exists.
     */
    public function ownerRoles(): array
    {
        return match ($this) {
            self::TargetAccount => ['Sales'],
            self::ProspectIdentified => ['Sales'],
            self::EngagedProspect => ['Sales'],
            self::QualifiedDiscovery => ['Sales'],
            self::SalesQualifiedOpportunity => ['Sales', 'Sales Manager', 'Founder/Management'],
            self::TechnicalSolutionDiscovery => ['Technical/Delivery'],
            self::Proposal => ['Sales', 'Founder/Management'],
            self::Negotiation => ['Sales', 'Sales Manager', 'Founder/Management'],
            self::ClosedWon, self::ClosedLost => ['Founder/Management'],
        };
    }

    public function isClosed(): bool
    {
        return $this === self::ClosedWon || $this === self::ClosedLost;
    }

    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
