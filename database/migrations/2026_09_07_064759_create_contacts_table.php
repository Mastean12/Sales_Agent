<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->nullable();
            $table->string('communication_status')->default('not_contacted');
            $table->boolean('opted_out')->default(false);
            $table->string('hubspot_contact_id')->nullable()->unique();
            $table->timestamps();

            $table->index('communication_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
