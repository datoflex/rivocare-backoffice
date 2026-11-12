<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * No clinical or topic-related data is stored in this system by design.
     * Columns holding personal contact data use Laravel's `encrypted` cast,
     * so they are declared as `text` here.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('lead');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('street')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city')->nullable();
            $table->string('billing_email')->nullable();
            $table->date('contract_starts_on')->nullable();
            $table->date('contract_ends_on')->nullable();
            $table->decimal('first_session_price', 10, 2)->nullable();
            $table->decimal('success_fee', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('company_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->text('email')->nullable();
            $table->text('phone')->nullable();
            $table->string('preferred_contact')->default('email');
            $table->json('languages')->nullable();
            $table->string('insurance_type')->nullable();
            $table->string('preferred_therapist_gender')->nullable();
            $table->string('session_setting')->default('either');
            $table->string('postal_code', 10)->nullable();
            $table->string('city')->nullable();
            $table->timestamp('consent_given_at')->nullable();
            $table->timestamp('consent_withdrawn_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('therapists', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('practice_name')->nullable();
            $table->string('street')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city')->nullable();
            $table->boolean('offers_online')->default(false);
            $table->boolean('offers_in_person')->default(true);
            $table->json('insurance_types')->nullable();
            $table->json('approaches')->nullable();
            $table->json('focus_areas')->nullable();
            $table->json('languages')->nullable();
            $table->string('status')->default('active');
            $table->string('capacity')->default('available');
            $table->string('capacity_note')->nullable();
            $table->decimal('first_session_fee', 10, 2)->nullable();
            $table->date('agreement_signed_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('placements', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('company_contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('therapist_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stage')->default('new');
            $table->flowforgePositionColumn('position');
            $table->timestamp('first_session_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('close_reason')->nullable();
            $table->timestamps();

            $table->unique(['stage', 'position'], 'placements_unique_position_per_stage');
        });

        Schema::create('placement_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('therapist_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('contacted');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['placement_id', 'therapist_id']);
        });

        Schema::create('task_templates', function (Blueprint $table) {
            $table->id();
            $table->string('stage');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('responsible')->default('us');
            $table->unsignedSmallInteger('due_in_days')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('taskable');
            $table->foreignId('task_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('responsible')->default('us');
            $table->string('status')->default('open');
            $table->date('due_on')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_on']);
        });

        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->morphs('notable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('note');
            $table->text('body');
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable');
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });

        Schema::create('billing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained()->restrictOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('therapist_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('type');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('status')->default('open');
            $table->string('invoice_number')->nullable();
            $table->date('invoiced_on')->nullable();
            $table->date('paid_on')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_items');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_templates');
        Schema::dropIfExists('placement_candidates');
        Schema::dropIfExists('placements');
        Schema::dropIfExists('therapists');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('company_contacts');
        Schema::dropIfExists('companies');
    }
};
