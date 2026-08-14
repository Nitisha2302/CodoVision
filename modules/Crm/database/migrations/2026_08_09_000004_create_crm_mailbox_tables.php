<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_mail_threads', function (Blueprint $table) {
            $table->id();
            $table->string('subject')->nullable();
            $table->string('participants_hash', 64)->nullable()->index();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('crm_users')->nullOnDelete();
            $table->string('primary_email')->nullable()->index();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->boolean('is_archived')->default(false)->index();
            $table->unsignedInteger('message_count')->default(0);
            $table->unsignedInteger('unread_count')->default(0);
            $table->timestamps();
        });

        Schema::create('crm_mail_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('crm_mail_threads')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('crm_users')->nullOnDelete(); // sender (outbound)
            $table->string('direction', 16); // inbound|outbound
            $table->string('folder', 64)->default('INBOX')->index();
            $table->string('message_uid')->nullable()->index();
            $table->string('message_id')->nullable()->unique();
            $table->string('in_reply_to')->nullable()->index();
            $table->string('from_email')->nullable()->index();
            $table->string('from_name')->nullable();
            $table->json('to_emails')->nullable();
            $table->json('cc_emails')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->boolean('is_seen')->default(false)->index();
            $table->boolean('is_starred')->default(false)->index();
            $table->boolean('has_attachments')->default(false);
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('synced_at')->nullable();
            $table->string('send_status', 32)->nullable(); // queued|sent|failed|null
            $table->text('send_error')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_mail_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('crm_mail_messages')->cascadeOnDelete();
            $table->string('filename');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('content_id')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_mail_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('crm_mail_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('crm_users')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->string('summary');
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->unique(['message_id', 'user_id']);
            $table->index(['user_id', 'is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_mail_alerts');
        Schema::dropIfExists('crm_mail_attachments');
        Schema::dropIfExists('crm_mail_messages');
        Schema::dropIfExists('crm_mail_threads');
    }
};
