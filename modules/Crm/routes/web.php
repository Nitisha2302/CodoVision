<?php

use Codovision\Crm\Http\Controllers\AuthController;
use Codovision\Crm\Http\Controllers\LeadImportController;
use Codovision\Crm\Http\Controllers\MailAttachmentController;
use Codovision\Crm\Http\Livewire\Auth\LoginForm;
use Codovision\Crm\Http\Livewire\Dashboard;
use Codovision\Crm\Http\Livewire\Leads\LeadForm;
use Codovision\Crm\Http\Livewire\Leads\LeadIndex;
use Codovision\Crm\Http\Livewire\Leads\LeadKanban;
use Codovision\Crm\Http\Livewire\Leads\LeadShow;
use Codovision\Crm\Http\Livewire\Mailbox\MailboxIndex;
use Codovision\Crm\Http\Livewire\Mailbox\MailThreadShow;
use Codovision\Crm\Http\Livewire\Teams\TeamIndex;
use Codovision\Crm\Http\Livewire\Users\UserIndex;
use Codovision\Crm\Http\Middleware\EnsureCrmAuthenticated;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('crm.dashboard');
})->name('home');

Route::middleware('guest:crm')->group(function () {
    Route::get('/login', LoginForm::class)->name('login');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware(EnsureCrmAuthenticated::class)
    ->name('logout');

Route::middleware(EnsureCrmAuthenticated::class)->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/leads', LeadIndex::class)->name('leads.index');
    Route::get('/leads/kanban', LeadKanban::class)->name('leads.kanban');
    Route::get('/leads/create', LeadForm::class)->name('leads.create');
    Route::get('/leads/import', [LeadImportController::class, 'show'])->name('leads.import');
    Route::post('/leads/import', [LeadImportController::class, 'store'])->name('leads.import.store');
    Route::get('/leads/{lead}/edit', LeadForm::class)->name('leads.edit');
    Route::get('/leads/{lead}', LeadShow::class)->name('leads.show');

    Route::get('/mailbox', MailboxIndex::class)->name('mailbox.index');
    Route::get('/mailbox/threads/{thread}', MailThreadShow::class)->name('mailbox.thread');
    Route::get('/mailbox/attachments/{attachment}', [MailAttachmentController::class, 'download'])->name('mailbox.attachment');

    Route::get('/users', UserIndex::class)->name('users.index');
    Route::get('/teams', TeamIndex::class)->name('teams.index');
});
