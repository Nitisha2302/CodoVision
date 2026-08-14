<?php

namespace Codovision\Crm\Http\Livewire\Teams;

use Codovision\Crm\Http\Livewire\Concerns\InteractsWithCrmAuth;
use Codovision\Crm\Models\Team;
use Codovision\Crm\Services\ActivityLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

class TeamIndex extends Component
{
    use InteractsWithCrmAuth;

    public ?int $editingId = null;
    public string $name = '';
    public string $description = '';
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
                Rule::unique('crm_teams', 'name')->ignore($this->editingId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Team name is required.',
            'name.min' => 'Team name must be at least 2 characters.',
            'name.max' => 'Team name may not be greater than 120 characters.',
            'name.unique' => 'A team with this name already exists.',
            'description.max' => 'Description may not be greater than 500 characters.',
        ];
    }

    public function mount(): void
    {
        $this->crmAuthorize('viewAny', Team::class);
    }

    public function edit(int $teamId): void
    {
        $this->clearCrmFlash();
        $this->resetValidation();

        try {
            $team = Team::findOrFail($teamId);
            $this->crmAuthorize('update', $team);

            $this->editingId = $team->id;
            $this->name = $team->name;
            $this->description = $team->description ?? '';
            $this->is_active = (bool) $team->is_active;
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not load team for editing.');
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
                $team = Team::findOrFail($this->editingId);
                $this->crmAuthorize('update', $team);

                $team->fill([
                    'name' => $data['name'],
                    'description' => filled($data['description'] ?? null) ? $data['description'] : null,
                    'is_active' => $this->is_active,
                ]);
                $team->save();

                $logger->audit($actor, 'team.updated', Team::class, $team->id, [
                    'name' => $team->name,
                    'is_active' => $team->is_active,
                ]);

                $this->resetForm();
                $this->crmFlashSuccess('Team "' . $team->name . '" updated successfully.');

                return;
            }

            $this->crmAuthorize('create', Team::class);

            $baseSlug = Str::slug($data['name']) ?: 'team';
            $slug = $baseSlug;
            $i = 1;
            while (Team::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i;
                $i++;
            }

            $team = Team::create([
                'name' => $data['name'],
                'slug' => $slug,
                'description' => filled($data['description'] ?? null) ? $data['description'] : null,
                'is_active' => true,
            ]);

            $logger->audit($actor, 'team.created', Team::class, $team->id, ['name' => $team->name]);
            $this->resetForm();
            $this->crmFlashSuccess('Team "' . $team->name . '" created successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not save team. Please try again.');
        }
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->reset(['name', 'description']);
        $this->is_active = true;
    }

    public function render()
    {
        return view('crm::livewire.teams.team-index', [
            'teams' => Team::withCount('users')->with(['users' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get(),
            'isEditing' => (bool) $this->editingId,
        ])->layout('crm::layouts.app');
    }
}
