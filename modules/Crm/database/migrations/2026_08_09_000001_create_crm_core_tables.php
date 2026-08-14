<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('crm_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('crm_teams')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('assignment_weight')->default(1);
            $table->timestamp('last_assigned_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('crm_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        $teams = config('permission.column_names.team_foreign_key') ?? 'team_id';

        Schema::create('crm_permissions', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('crm_roles', static function (Blueprint $table) use ($teams) {
            $table->bigIncrements('id');
            if (config('permission.teams')) {
                $table->unsignedBigInteger($teams)->nullable();
                $table->index($teams, 'crm_roles_team_foreign_key_index');
            }
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            if (config('permission.teams')) {
                $table->unique([$teams, 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        Schema::create('crm_model_has_permissions', static function (Blueprint $table) use ($teams) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger(config('permission.column_names.model_morph_key'));
            $table->index([config('permission.column_names.model_morph_key'), 'model_type'], 'crm_model_has_permissions_model_id_model_type_index');
            $table->foreign('permission_id')->references('id')->on('crm_permissions')->onDelete('cascade');
            if (config('permission.teams')) {
                $table->unsignedBigInteger($teams);
                $table->index($teams, 'crm_model_has_permissions_team_foreign_key_index');
                $table->primary([$teams, 'permission_id', config('permission.column_names.model_morph_key'), 'model_type'], 'crm_model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary(['permission_id', config('permission.column_names.model_morph_key'), 'model_type'], 'crm_model_has_permissions_permission_model_type_primary');
            }
        });

        Schema::create('crm_model_has_roles', static function (Blueprint $table) use ($teams) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger(config('permission.column_names.model_morph_key'));
            $table->index([config('permission.column_names.model_morph_key'), 'model_type'], 'crm_model_has_roles_model_id_model_type_index');
            $table->foreign('role_id')->references('id')->on('crm_roles')->onDelete('cascade');
            if (config('permission.teams')) {
                $table->unsignedBigInteger($teams);
                $table->index($teams, 'crm_model_has_roles_team_foreign_key_index');
                $table->primary([$teams, 'role_id', config('permission.column_names.model_morph_key'), 'model_type'], 'crm_model_has_roles_role_model_type_primary');
            } else {
                $table->primary(['role_id', config('permission.column_names.model_morph_key'), 'model_type'], 'crm_model_has_roles_role_model_type_primary');
            }
        });

        Schema::create('crm_role_has_permissions', static function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->foreign('permission_id')->references('id')->on('crm_permissions')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('crm_roles')->onDelete('cascade');
            $table->primary(['permission_id', 'role_id'], 'crm_role_has_permissions_permission_id_role_id_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_role_has_permissions');
        Schema::dropIfExists('crm_model_has_roles');
        Schema::dropIfExists('crm_model_has_permissions');
        Schema::dropIfExists('crm_roles');
        Schema::dropIfExists('crm_permissions');
        Schema::dropIfExists('crm_password_reset_tokens');
        Schema::dropIfExists('crm_users');
        Schema::dropIfExists('crm_teams');
    }
};
