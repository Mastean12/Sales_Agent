<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->nullable()->unique();
            $table->string('industry')->nullable();
            $table->string('location')->nullable();
            $table->unsignedTinyInteger('icp_score')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('prospecting');
            $table->string('hubspot_company_id')->nullable()->unique();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
