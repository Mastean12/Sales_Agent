<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the manually-entered Business Problem / Qualification fields the
 * execution plan's Opportunity detail page calls for (sections 12B/12C).
 * These are plain nullable fields for now — AI-based Problem Intelligence
 * (auto-extraction) is explicitly deferred to a later phase.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->date('next_action_due')->nullable()->after('next_action');
            $table->text('notes')->nullable()->after('next_action_due');

            // Business Problem
            $table->string('problem_category')->nullable()->after('notes');
            $table->text('evidence')->nullable()->after('problem_category');
            $table->text('impact')->nullable()->after('evidence');
            $table->string('urgency')->nullable()->after('impact');

            // Qualification
            $table->string('stakeholder')->nullable()->after('urgency');
            $table->string('budget_signal')->nullable()->after('stakeholder');
            $table->string('business_impact')->nullable()->after('budget_signal');
            $table->string('decision_process')->nullable()->after('business_impact');
            $table->string('fit')->nullable()->after('decision_process');
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'next_action_due', 'notes',
                'problem_category', 'evidence', 'impact', 'urgency',
                'stakeholder', 'budget_signal', 'business_impact', 'decision_process', 'fit',
            ]);
        });
    }
};
