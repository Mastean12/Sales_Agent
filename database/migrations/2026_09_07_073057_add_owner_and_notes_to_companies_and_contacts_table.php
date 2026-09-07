<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable()->after('status');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('company_id')->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable()->after('communication_status');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
            $table->dropColumn('notes');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
            $table->dropColumn('notes');
        });
    }
};
