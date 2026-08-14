@if($crmFlashSuccess ?? session('crm_success'))
    <div class="crm-alert crm-alert-success">{{ $crmFlashSuccess ?? session('crm_success') }}</div>
@endif
@if($crmFlashError ?? session('crm_error'))
    <div class="crm-alert crm-alert-error">{{ $crmFlashError ?? session('crm_error') }}</div>
@endif
