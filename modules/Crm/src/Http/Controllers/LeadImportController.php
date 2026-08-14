<?php

namespace Codovision\Crm\Http\Controllers;

use Codovision\Crm\Models\LeadSource;
use Codovision\Crm\Services\ActivityLogger;
use Codovision\Crm\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class LeadImportController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = Auth::guard('crm')->user();
        abort_unless($user && $user->can('crm.leads.import'), 403);

        return view('crm::leads.import', [
            'sources' => LeadSource::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, LeadService $leadService, ActivityLogger $logger): RedirectResponse
    {
        $user = Auth::guard('crm')->user();
        abort_unless($user && $user->can('crm.leads.import'), 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'lead_source_id' => ['nullable', 'exists:crm_lead_sources,id'],
        ], [
            'file.required' => 'Please choose a CSV file to import.',
            'file.file' => 'The upload must be a valid file.',
            'file.mimes' => 'Only CSV or TXT files are allowed.',
            'file.max' => 'CSV file must be smaller than 2MB.',
            'lead_source_id.exists' => 'Selected source is invalid.',
        ]);

        try {
            $handle = fopen($request->file('file')->getRealPath(), 'r');
            if ($handle === false) {
                return back()->with('crm_error', 'Could not read the uploaded file.');
            }

            $header = fgetcsv($handle) ?: [];
            $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);

            if (!in_array('first_name', $header, true) && !in_array('contact_first_name', $header, true)) {
                fclose($handle);

                return back()->with('crm_error', 'CSV must include a first_name column.');
            }

            $created = 0;
            $skipped = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue;
                }

                $data = @array_combine($header, array_pad($row, count($header), null));
                if (!$data || empty($data['first_name'] ?? $data['contact_first_name'] ?? null)) {
                    $skipped++;
                    continue;
                }

                try {
                    $leadService->create([
                        'contact_first_name' => $data['first_name'] ?? $data['contact_first_name'],
                        'contact_last_name' => $data['last_name'] ?? $data['contact_last_name'] ?? null,
                        'contact_email' => $data['email'] ?? $data['contact_email'] ?? null,
                        'contact_phone' => $data['phone'] ?? $data['contact_phone'] ?? null,
                        'company_name' => $data['company'] ?? $data['company_name'] ?? null,
                        'service_interested' => $data['service'] ?? $data['service_interested'] ?? null,
                        'title' => $data['title'] ?? null,
                        'notes' => $data['notes'] ?? null,
                        'lead_source_id' => $request->integer('lead_source_id') ?: null,
                        'force_create' => true,
                    ], $user);
                    $created++;
                } catch (Throwable) {
                    $skipped++;
                }
            }
            fclose($handle);

            $logger->audit($user, 'leads.imported', null, null, compact('created', 'skipped'));

            return redirect()->route('crm.leads.index')->with(
                'crm_success',
                "Import complete. Created {$created}, skipped {$skipped}."
            );
        } catch (Throwable $e) {
            report($e);

            return back()->with('crm_error', 'Import failed. Please check the CSV format and try again.');
        }
    }
}
