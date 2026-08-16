<?php

namespace Codovision\Crm\Database\Seeders;

use Codovision\Crm\Models\CrmUser;
use Codovision\Crm\Models\LeadSource;
use Codovision\Crm\Models\LeadStatus;
use Codovision\Crm\Models\LostReason;
use Codovision\Crm\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CrmDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'crm.leads.view',
            'crm.leads.view_team',
            'crm.leads.view_all',
            'crm.leads.create',
            'crm.leads.update',
            'crm.leads.delete',
            'crm.leads.reassign',
            'crm.leads.export',
            'crm.leads.import',
            'crm.users.manage',
            'crm.teams.manage',
            'crm.mailbox.view',
            'crm.mailbox.view_all',
            'crm.mailbox.send',
            'crm.mailbox.sync',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'crm');
        }

        $admin = Role::findOrCreate('Admin', 'crm');
        $manager = Role::findOrCreate('Sales Manager', 'crm');
        $executive = Role::findOrCreate('Sales Executive', 'crm');
        $viewer = Role::findOrCreate('Viewer', 'crm');

        $admin->syncPermissions($permissions);
        $manager->syncPermissions([
            'crm.leads.view',
            'crm.leads.view_team',
            'crm.leads.create',
            'crm.leads.update',
            'crm.leads.reassign',
            'crm.leads.export',
            'crm.leads.import',
            'crm.users.manage',
            'crm.teams.manage',
            'crm.mailbox.view',
            'crm.mailbox.view_all',
            'crm.mailbox.send',
            'crm.mailbox.sync',
        ]);
        $executive->syncPermissions([
            'crm.leads.view',
            'crm.leads.create',
            'crm.leads.update',
            'crm.mailbox.view',
            'crm.mailbox.send',
        ]);
        $viewer->syncPermissions([
            'crm.leads.view',
            'crm.leads.view_team',
            'crm.mailbox.view',
        ]);

        $statuses = [
            ['name' => 'New', 'slug' => 'new', 'color' => '#6366f1', 'sort_order' => 1],
            ['name' => 'Contacted', 'slug' => 'contacted', 'color' => '#0ea5e9', 'sort_order' => 2],
            ['name' => 'Qualified', 'slug' => 'qualified', 'color' => '#14b8a6', 'sort_order' => 3],
            ['name' => 'Requirement Received', 'slug' => 'requirement_received', 'color' => '#8b5cf6', 'sort_order' => 4],
            ['name' => 'Proposal Sent', 'slug' => 'proposal_sent', 'color' => '#f59e0b', 'sort_order' => 5],
            ['name' => 'Negotiation', 'slug' => 'negotiation', 'color' => '#f97316', 'sort_order' => 6],
            ['name' => 'Won', 'slug' => 'won', 'color' => '#22c55e', 'sort_order' => 7, 'is_won' => true],
            ['name' => 'Lost', 'slug' => 'lost', 'color' => '#ef4444', 'sort_order' => 8, 'is_lost' => true],
        ];

        foreach ($statuses as $status) {
            LeadStatus::updateOrCreate(['slug' => $status['slug']], $status + ['is_active' => true]);
        }

        foreach (['Website', 'Referral', 'LinkedIn', 'WhatsApp', 'Cold Call', 'Campaign', 'Upwork', 'Freelance', 'Other'] as $source) {
            LeadSource::updateOrCreate(
                ['slug' => Str::slug($source)],
                ['name' => $source, 'is_active' => true]
            );
        }

        foreach (['Budget', 'Timing', 'Chose Competitor', 'No Response', 'Not a Fit', 'Other'] as $reason) {
            LostReason::updateOrCreate(['name' => $reason], ['is_active' => true]);
        }

        $team = Team::updateOrCreate(
            ['slug' => 'sales-core'],
            ['name' => 'Sales Core', 'description' => 'Primary sales team', 'is_active' => true]
        );

        $adminUser = CrmUser::updateOrCreate(
            ['email' => 'admin@codovision.tech'],
            [
                'name' => 'CRM Admin',
                'password' => 'Admin@12345',
                'team_id' => $team->id,
                'is_active' => true,
                'phone' => '+917973776933',
            ]
        );
        $adminUser->syncRoles(['Admin']);

        $managerUser = CrmUser::updateOrCreate(
            ['email' => 'manager@codovision.tech'],
            [
                'name' => 'Sales Manager',
                'password' => 'Manager@12345',
                'team_id' => $team->id,
                'is_active' => true,
            ]
        );
        $managerUser->syncRoles(['Sales Manager']);

        $execUser = CrmUser::updateOrCreate(
            ['email' => 'executive@codovision.tech'],
            [
                'name' => 'Sales Executive',
                'password' => 'Executive@12345',
                'team_id' => $team->id,
                'is_active' => true,
            ]
        );
        $execUser->syncRoles(['Sales Executive']);
    }
}
