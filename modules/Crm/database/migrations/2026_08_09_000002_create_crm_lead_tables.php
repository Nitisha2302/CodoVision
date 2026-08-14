<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('crm_companies')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('job_title')->nullable();
            $table->boolean('is_decision_maker')->default(false);
            $table->timestamps();
        });

        Schema::create('crm_lead_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('crm_lead_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color')->default('#6366f1');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('crm_lost_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('crm_lead_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color')->default('#94a3b8');
            $table->timestamps();
        });

        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_code')->unique();
            $table->foreignId('company_id')->nullable()->constrained('crm_companies')->nullOnDelete();
            $table->foreignId('primary_contact_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
            $table->foreignId('lead_source_id')->nullable()->constrained('crm_lead_sources')->nullOnDelete();
            $table->foreignId('lead_status_id')->constrained('crm_lead_statuses');
            $table->foreignId('assigned_to')->nullable()->constrained('crm_users')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('crm_teams')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('crm_users')->nullOnDelete();

            $table->string('title')->nullable();
            $table->string('campaign')->nullable();
            $table->string('service_interested')->nullable();
            $table->string('priority')->default('medium'); // low|medium|high|urgent
            $table->unsignedTinyInteger('lead_score')->default(0);
            $table->unsignedTinyInteger('probability')->default(10);
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('expected_value', 15, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->date('expected_closing_date')->nullable();

            $table->string('existing_site_app')->nullable();
            $table->text('tech_requirements')->nullable();
            $table->string('timeline')->nullable();
            $table->string('decision_maker')->nullable();
            $table->string('competitor')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('lost_reason_id')->nullable()->constrained('crm_lost_reasons')->nullOnDelete();
            $table->text('lost_notes')->nullable();
            $table->decimal('won_amount', 15, 2)->nullable();
            $table->string('won_currency', 3)->nullable();
            $table->date('won_closing_date')->nullable();
            $table->string('won_service')->nullable();
            $table->string('won_project_type')->nullable();

            $table->timestamp('first_contacted_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lead_status_id', 'assigned_to']);
            $table->index(['team_id', 'priority']);
        });

        Schema::create('crm_lead_tag_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('crm_leads')->cascadeOnDelete();
            $table->foreignId('lead_tag_id')->constrained('crm_lead_tags')->cascadeOnDelete();
            $table->unique(['lead_id', 'lead_tag_id']);
        });

        Schema::create('crm_lead_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('crm_leads')->cascadeOnDelete();
            $table->foreignId('assigned_from')->nullable()->constrained('crm_users')->nullOnDelete();
            $table->foreignId('assigned_to')->constrained('crm_users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('crm_users')->nullOnDelete();
            $table->string('method')->default('manual'); // manual|round_robin
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_lead_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('crm_leads')->cascadeOnDelete();
            $table->foreignId('from_status_id')->nullable()->constrained('crm_lead_statuses')->nullOnDelete();
            $table->foreignId('to_status_id')->constrained('crm_lead_statuses')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('crm_users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('crm_leads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('crm_users')->cascadeOnDelete();
            $table->text('body');
            $table->json('mentions')->nullable();
            $table->boolean('is_internal')->default(true);
            $table->timestamps();
        });

        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('crm_users')->nullOnDelete();
            $table->string('type'); // status_change|assignment|note|created|updated|import|export|won|lost
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['lead_id', 'created_at']);
        });

        Schema::create('crm_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('crm_users')->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('payload')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_audit_logs');
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_notes');
        Schema::dropIfExists('crm_lead_status_history');
        Schema::dropIfExists('crm_lead_assignments');
        Schema::dropIfExists('crm_lead_tag_relations');
        Schema::dropIfExists('crm_leads');
        Schema::dropIfExists('crm_lead_tags');
        Schema::dropIfExists('crm_lost_reasons');
        Schema::dropIfExists('crm_lead_statuses');
        Schema::dropIfExists('crm_lead_sources');
        Schema::dropIfExists('crm_contacts');
        Schema::dropIfExists('crm_companies');
    }
};
