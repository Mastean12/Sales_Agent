<?php

use App\Enums\OpportunityStage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('stage')->default(OpportunityStage::TargetAccount->value);
            $table->text('problem')->nullable();
            $table->decimal('value', 12, 2)->nullable();
            $table->unsignedTinyInteger('probability')->default(0);
            $table->decimal('expected_value', 12, 2)->nullable();
            $table->text('next_action')->nullable();
            $table->text('closed_reason')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('hubspot_deal_id')->nullable()->unique();
            $table->timestamps();

            $table->index('stage');
            $table->index(['company_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
