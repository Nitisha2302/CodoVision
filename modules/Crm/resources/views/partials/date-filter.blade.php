@php
    $datePresets = $datePresets ?? [
        'all' => 'All time',
        'today' => 'Today',
        '7d' => 'Last 7 days',
        '30d' => 'Last 30 days',
        'month' => 'This month',
        'custom' => 'Custom range',
    ];
@endphp
<div class="crm-card crm-date-filter" style="margin-bottom:14px;">
    <div class="crm-date-filter-head">
        <div>
            <h3 style="margin:0;font-size:15px;">Date filter</h3>
            <p class="crm-muted" style="margin:4px 0 0;font-size:12px;">{{ $dateFilterLabel ?? 'All time' }}</p>
        </div>
        <button class="crm-btn crm-btn-secondary crm-btn-sm" type="button" wire:click="clearDateFilter">Clear</button>
    </div>
    <div class="crm-grid crm-grid-4 crm-date-filter-grid">
        <div class="crm-field" style="margin:0;">
            <label class="crm-label">Range</label>
            <select class="crm-select" wire:model.live="datePreset">
                @foreach($datePresets as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field" style="margin:0;">
            <label class="crm-label">From</label>
            <input class="crm-input" type="date" wire:model.live="dateFrom">
        </div>
        <div class="crm-field" style="margin:0;">
            <label class="crm-label">To</label>
            <input class="crm-input" type="date" wire:model.live="dateTo">
        </div>
    </div>
</div>
