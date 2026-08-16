<?php

namespace Codovision\Crm\Http\Livewire\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait FiltersByDateRange
{
    public string $datePreset = 'all';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function updatedDatePreset(): void
    {
        $this->syncPresetDates();
        $this->afterDateFilterChanged();
    }

    public function updatedDateFrom(): void
    {
        if ($this->datePreset !== 'custom') {
            $this->datePreset = 'custom';
        }
        $this->afterDateFilterChanged();
    }

    public function updatedDateTo(): void
    {
        if ($this->datePreset !== 'custom') {
            $this->datePreset = 'custom';
        }
        $this->afterDateFilterChanged();
    }

    public function clearDateFilter(): void
    {
        $this->datePreset = 'all';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->afterDateFilterChanged();
    }

    protected function afterDateFilterChanged(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    protected function syncPresetDates(): void
    {
        $today = now()->startOfDay();

        switch ($this->datePreset) {
            case 'today':
                $this->dateFrom = $today->toDateString();
                $this->dateTo = $today->toDateString();
                break;
            case '7d':
                $this->dateFrom = $today->copy()->subDays(6)->toDateString();
                $this->dateTo = $today->toDateString();
                break;
            case '30d':
                $this->dateFrom = $today->copy()->subDays(29)->toDateString();
                $this->dateTo = $today->toDateString();
                break;
            case 'month':
                $this->dateFrom = $today->copy()->startOfMonth()->toDateString();
                $this->dateTo = $today->toDateString();
                break;
            case 'all':
                $this->dateFrom = '';
                $this->dateTo = '';
                break;
            case 'custom':
            default:
                break;
        }
    }

    protected function applyDateRangeFilter(Builder $query, ?string $table = null): Builder
    {
        $column = $table ? "{$table}.created_at" : 'created_at';

        $from = $this->normalizedDate($this->dateFrom);
        $to = $this->normalizedDate($this->dateTo);

        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return $query
            ->when($from, fn (Builder $q) => $q->whereDate($column, '>=', $from->toDateString()))
            ->when($to, fn (Builder $q) => $q->whereDate($column, '<=', $to->toDateString()));
    }

    protected function normalizedDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function dateFilterLabel(): string
    {
        if ($this->datePreset === 'all' && $this->dateFrom === '' && $this->dateTo === '') {
            return 'All time';
        }

        $from = $this->dateFrom ?: '…';
        $to = $this->dateTo ?: '…';

        return "Created: {$from} → {$to}";
    }
}
