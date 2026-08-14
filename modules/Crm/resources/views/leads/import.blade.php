@extends('crm::layouts.static')

@section('content')
<div class="crm-topbar">
    <div>
        <h2 style="margin:0;">Import leads (CSV)</h2>
        <p class="crm-muted" style="margin:4px 0 0;">Required column: <code>first_name</code>. Optional: last_name, email, phone, company, service, title, notes</p>
    </div>
    <a class="crm-btn crm-btn-secondary" href="{{ route('crm.leads.index') }}">Back</a>
</div>

@if(session('crm_success'))
    <div class="crm-alert crm-alert-success">{{ session('crm_success') }}</div>
@endif
@if(session('crm_error'))
    <div class="crm-alert crm-alert-error">{{ session('crm_error') }}</div>
@endif

<div class="crm-card" style="max-width:640px;">
    <form method="POST" action="{{ route('crm.leads.import.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="crm-field">
            <label class="crm-label">CSV file *</label>
            <input class="crm-input" type="file" name="file" accept=".csv,text/csv" required>
            @error('file') <div class="crm-field-error">{{ $message }}</div> @enderror
        </div>
        <div class="crm-field">
            <label class="crm-label">Default source</label>
            <select class="crm-select" name="lead_source_id">
                <option value="">None</option>
                @foreach($sources as $source)
                    <option value="{{ $source->id }}" @selected(old('lead_source_id') == $source->id)>{{ $source->name }}</option>
                @endforeach
            </select>
            @error('lead_source_id') <div class="crm-field-error">{{ $message }}</div> @enderror
        </div>
        <button class="crm-btn" type="submit">Import</button>
    </form>
</div>
@endsection
