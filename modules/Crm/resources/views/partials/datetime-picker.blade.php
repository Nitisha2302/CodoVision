@php
    /** @var string $model Livewire property name */
    $type = $type ?? 'datetime-local'; // date|datetime-local
    $label = $label ?? null;
    $required = $required ?? false;
    $placeholder = $placeholder ?? ($type === 'date' ? 'Select date' : 'Select date & time');
@endphp
<div class="crm-field">
    @if($label)
        <label class="crm-label">{{ $label }}{{ $required ? ' *' : '' }}</label>
    @endif
    <div class="crm-datepicker-wrap">
        <input
            class="crm-input crm-datepicker"
            type="{{ $type }}"
            wire:model="{{ $model }}"
            inputmode="none"
            autocomplete="off"
            onkeydown="if(event.key!=='Tab'&&event.key!=='Escape'){event.preventDefault();}"
            onpaste="event.preventDefault();"
            onclick="this.showPicker&&this.showPicker();"
            onfocus="this.showPicker&&this.showPicker();"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
        >
        <button type="button" class="crm-datepicker-btn" tabindex="-1" onclick="const i=this.previousElementSibling; i.focus(); i.showPicker&&i.showPicker();" title="Open calendar" aria-label="Open calendar">📅</button>
    </div>
    @include('crm::partials.field-error', ['name' => $model])
</div>
