<?php

namespace Codovision\Crm\Http\Livewire\Users;

use Codovision\Crm\Http\Livewire\Concerns\InteractsWithCrmAuth;
use Codovision\Crm\Models\CrmUser;
use Codovision\Crm\Models\Team;
use Codovision\Crm\Services\ActivityLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Throwable;

class UserIndex extends Component
{
    use InteractsWithCrmAuth;

    public ?int $editingId = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $phone = '';
    public string $team_id = '';
    public string $role = 'Sales Executive';
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:180',
                Rule::unique('crm_users', 'email')->ignore($this->editingId),
            ],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:8', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'team_id' => ['nullable', 'exists:crm_teams,id'],
            'role' => ['required', Rule::exists('crm_roles', 'name')->where(fn ($q) => $q->where('guard_name', 'crm'))],
            'is_active' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'A CRM user with this email already exists.',
            'password.required' => 'Password is required for new users.',
            'password.min' => 'Password must be at least 8 characters.',
            'team_id.exists' => 'Selected team is invalid.',
            'role.required' => 'Role is required.',
            'role.exists' => 'Selected role is invalid.',
        ];
    }

    public function mount(): void
    {
        $this->crmAuthorize('viewAny', CrmUser::class);
    }

    public function edit(int $userId): void
    {
        $this->clearCrmFlash();
        $this->resetValidation();

        try {
            $user = CrmUser::with('roles')->findOrFail($userId);
            $this->crmAuthorize('update', $user);

            $this->editingId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->password = '';
            $this->phone = $user->phone ?? '';
            $this->team_id = (string) ($user->team_id ?? '');
            $this->role = $user->roles->first()?->name ?? 'Sales Executive';
            $this->is_active = (bool) $user->is_active;
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not load user for editing.');
        }
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
        $this->clearCrmFlash();
        $this->resetValidation();
    }

    public function save(ActivityLogger $logger): void
    {
        $this->clearCrmFlash();

        try {
            $actor = $this->crmUser();
            $data = $this->validate();

            if ($this->editingId) {
                $user = CrmUser::findOrFail($this->editingId);
                $this->crmAuthorize('update', $user);

                if ($user->id === $actor->id && !$this->is_active) {
                    $this->crmFlashError('You cannot deactivate your own account.');

                    return;
                }

                if ($user->id === $actor->id && $data['role'] !== 'Admin' && $user->isAdmin()) {
                    $this->crmFlashError('You cannot remove your own Admin role.');

                    return;
                }

                if (!$actor->isAdmin() && $data['role'] === 'Admin') {
                    $this->crmFlashError('Only an Admin can assign the Admin role.');

                    return;
                }

                $user->fill([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => filled($data['phone'] ?? null) ? $data['phone'] : null,
                    'team_id' => filled($data['team_id'] ?? null) ? (int) $data['team_id'] : null,
                    'is_active' => $this->is_active,
                ]);

                if (filled($this->password)) {
                    $user->password = $this->password;
                }

                $user->save();
                $user->syncRoles([$data['role']]);

                $logger->audit($actor, 'user.updated', CrmUser::class, $user->id, [
                    'email' => $user->email,
                    'team_id' => $user->team_id,
                    'role' => $data['role'],
                ]);

                $this->resetForm();
                $this->crmFlashSuccess('User "' . $user->name . '" updated successfully.');

                return;
            }

            $this->crmAuthorize('create', CrmUser::class);

            if (!$actor->isAdmin() && $data['role'] === 'Admin') {
                $this->crmFlashError('Only an Admin can create Admin users.');

                return;
            }

            $user = CrmUser::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => filled($data['phone'] ?? null) ? $data['phone'] : null,
                'team_id' => filled($data['team_id'] ?? null) ? (int) $data['team_id'] : null,
                'is_active' => $this->is_active,
            ]);

            $user->assignRole($data['role']);
            $logger->audit($actor, 'user.created', CrmUser::class, $user->id, ['email' => $user->email]);

            $this->resetForm();
            $this->crmFlashSuccess('User "' . $user->name . '" created successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not save user. Please check the details and try again.');
        }
    }

    public function toggleActive(int $userId, ActivityLogger $logger): void
    {
        $this->clearCrmFlash();

        try {
            $actor = $this->crmUser();
            $user = CrmUser::findOrFail($userId);
            $this->crmAuthorize('update', $user);

            if ($user->id === $actor->id) {
                $this->crmFlashError('You cannot deactivate your own account.');

                return;
            }

            $user->is_active = !$user->is_active;
            $user->save();
            $logger->audit($actor, 'user.toggled', CrmUser::class, $user->id, ['is_active' => $user->is_active]);
            $this->crmFlashSuccess($user->is_active ? 'User activated.' : 'User deactivated.');

            if ($this->editingId === $user->id) {
                $this->is_active = (bool) $user->is_active;
            }
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not update user status.');
        }
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->reset(['name', 'email', 'password', 'phone', 'team_id']);
        $this->role = 'Sales Executive';
        $this->is_active = true;
    }

    public function render()
    {
        $actor = $this->crmUser();
        $roles = Role::where('guard_name', 'crm')->orderBy('name')->pluck('name');
        if (!$actor->isAdmin()) {
            $roles = $roles->reject(fn ($name) => $name === 'Admin')->values();
        }

        return view('crm::livewire.users.user-index', [
            'users' => CrmUser::with(['team', 'roles'])->orderBy('name')->get(),
            'teams' => Team::where('is_active', true)->orderBy('name')->get(),
            'roles' => $roles,
            'isEditing' => (bool) $this->editingId,
        ])->layout('crm::layouts.app');
    }
}
