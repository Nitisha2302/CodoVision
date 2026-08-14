<?php

namespace Codovision\Crm;

use Codovision\Crm\Http\Middleware\EnsureCrmAuthenticated;
use Codovision\Crm\Models\CrmUser;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\Team;
use Codovision\Crm\Policies\CrmUserPolicy;
use Codovision\Crm\Policies\LeadPolicy;
use Codovision\Crm\Policies\TeamPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Codovision\Crm\Http\Livewire\Auth\LoginForm;
use Codovision\Crm\Http\Livewire\Dashboard;
use Codovision\Crm\Http\Livewire\Leads\LeadIndex;
use Codovision\Crm\Http\Livewire\Leads\LeadKanban;
use Codovision\Crm\Http\Livewire\Leads\LeadForm;
use Codovision\Crm\Http\Livewire\Leads\LeadShow;
use Codovision\Crm\Http\Livewire\Users\UserIndex;
use Codovision\Crm\Http\Livewire\Teams\TeamIndex;
use Codovision\Crm\Http\Livewire\Mailbox\MailboxIndex;
use Codovision\Crm\Http\Livewire\Mailbox\MailThreadShow;
use Codovision\Crm\Http\Livewire\MailAlertBell;
use Codovision\Crm\Console\SeedCrmCommand;
use Codovision\Crm\Console\SyncMailCommand;

class CrmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/crm.php', 'crm');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'crm');
        $this->registerAuth();
        $this->registerRoutes();
        $this->registerLivewire();
        $this->registerPolicies();

        if ($this->app->runningInConsole()) {
            $this->commands([
                SeedCrmCommand::class,
                SyncMailCommand::class,
            ]);
        }

        Blade::componentNamespace('Codovision\\Crm\\View\\Components', 'crm');
    }

    protected function registerAuth(): void
    {
        config([
            'auth.guards.crm' => [
                'driver' => 'session',
                'provider' => 'crm_users',
            ],
            'auth.providers.crm_users' => [
                'driver' => 'eloquent',
                'model' => CrmUser::class,
            ],
            'auth.passwords.crm_users' => [
                'provider' => 'crm_users',
                'table' => 'crm_password_reset_tokens',
                'expire' => 60,
                'throttle' => 60,
            ],
            'permission.defaults.guard' => 'crm',
        ]);
    }

    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->prefix(config('crm.route_prefix', 'crm'))
            ->name('crm.')
            ->group(__DIR__ . '/../routes/web.php');
    }

    protected function registerLivewire(): void
    {
        Livewire::addPersistentMiddleware([
            EnsureCrmAuthenticated::class,
        ]);

        Livewire::component('crm.login-form', LoginForm::class);
        Livewire::component('crm.dashboard', Dashboard::class);
        Livewire::component('crm.lead-index', LeadIndex::class);
        Livewire::component('crm.lead-kanban', LeadKanban::class);
        Livewire::component('crm.lead-form', LeadForm::class);
        Livewire::component('crm.lead-show', LeadShow::class);
        Livewire::component('crm.user-index', UserIndex::class);
        Livewire::component('crm.team-index', TeamIndex::class);
        Livewire::component('crm.mailbox-index', MailboxIndex::class);
        Livewire::component('crm.mail-thread-show', MailThreadShow::class);
        Livewire::component('crm.mail-alert-bell', MailAlertBell::class);
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(CrmUser::class, CrmUserPolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
    }
}
