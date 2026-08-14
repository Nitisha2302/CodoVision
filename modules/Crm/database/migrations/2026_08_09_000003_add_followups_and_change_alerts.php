<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->timestamp('next_follow_up_at')->nullable()->after('first_contacted_at');
            $table->string('follow_up_note')->nullable()->after('next_follow_up_at');
            $table->index('next_follow_up_at');
        });

        Schema::create('crm_lead_change_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('crm_leads')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('crm_users')->nullOnDelete();
            $table->string('action'); // created|updated|status_change|assignment|note|follow_up|import
            $table->string('summary');
            $table->json('meta')->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->foreignId('read_by')->nullable()->constrained('crm_users')->nullOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_lead_change_alerts');

        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropIndex(['next_follow_up_at']);
            $table->dropColumn(['next_follow_up_at', 'follow_up_note']);
        });
    }
};
