<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    private const REVIEWS_FILE = 'reviews.json';

    public function submitChatbot(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'mobile' => ['required', 'string', 'max:40'],
            'country' => ['required', 'string', 'max:100'],
            'project_type' => ['required', 'string', 'max:150'],
            'preferred_technology' => ['nullable', 'string', 'max:150'],
            'budget_range' => ['nullable', 'string', 'max:120'],
            'timeline' => ['nullable', 'string', 'max:120'],
            'requirements' => ['nullable', 'string', 'max:1500'],
            'chat_transcript' => ['required', 'string', 'min:10'],
        ]);

        $payload = [
            'source' => 'chatbot',
            'submitted_at' => now()->toDateTimeString(),
            ...$data,
        ];

        Storage::append('leads.log', json_encode($payload, JSON_UNESCAPED_UNICODE));
        $this->mailLead('New Chatbot Lead', $payload);

        return response()->json([
            'message' => 'Thank you. Your details were submitted successfully.',
        ]);
    }

    public function submitBooking(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'mobile' => ['required', 'string', 'max:40'],
            'country' => ['required', 'string', 'max:100'],
            'package_id' => ['required', 'string', 'max:80'],
            'package_mode' => ['nullable', 'string', 'max:40'],
            'selected_technology' => ['nullable', 'string', 'max:150'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'estimated_budget' => ['nullable', 'string', 'max:120'],
            'required_features' => ['nullable', 'string', 'max:2000'],
            'message' => ['nullable', 'string', 'max:1500'],
        ]);

        $payload = [
            'source' => 'package_booking',
            'submitted_at' => now()->toDateTimeString(),
            ...$data,
        ];

        $selectedPackage = collect(config('portfolio.packages', []))
            ->firstWhere('id', $data['package_id']);

        if (is_array($selectedPackage)) {
            $payload['package_name'] = $selectedPackage['name'] ?? $data['package_id'];
            $payload['package_price'] = $selectedPackage['price'] ?? 'Custom';
            $payload['package_timeline'] = $selectedPackage['timeline'] ?? 'Custom';
        } else {
            $payload['package_name'] = str_replace('-', ' ', $data['package_id']);
        }

        Storage::append('bookings.log', json_encode($payload, JSON_UNESCAPED_UNICODE));
        $this->mailLead('New Service Package Booking', $payload);

        return back()->with('booking_success', 'Package request sent successfully. Our team will contact you shortly.');
    }

    public function submitMeeting(Request $request): RedirectResponse
    {
        $allowedSlots = config('portfolio.meeting_slots', []);
        $allowedTypes = config('portfolio.meeting_types', []);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['required', 'string', 'max:40'],
            'meeting_date' => ['required', 'date', 'after_or_equal:today'],
            'meeting_slot' => ['required', 'string', Rule::in($allowedSlots)],
            'meeting_type' => ['required', 'string', Rule::in($allowedTypes)],
            'meeting_about' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $meetingDate = Carbon::parse($data['meeting_date'])->startOfDay();

        if ($meetingDate->isSunday()) {
            return back()
                ->withInput()
                ->withErrors(['meeting_date' => 'Please choose a Monday–Saturday date. Sunday slots are unavailable.']);
        }

        if ($meetingDate->gt(now()->addDays(60)->startOfDay())) {
            return back()
                ->withInput()
                ->withErrors(['meeting_date' => 'Please choose a date within the next 60 days.']);
        }

        $payload = [
            'source' => 'meeting_booking',
            'submitted_at' => now()->toDateTimeString(),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'meeting_date' => $meetingDate->toDateString(),
            'meeting_date_display' => $meetingDate->format('l, F j, Y'),
            'meeting_slot' => $data['meeting_slot'],
            'meeting_type' => $data['meeting_type'],
            'meeting_about' => $data['meeting_about'],
            'timezone' => 'IST (India Standard Time)',
        ];

        Storage::append('meetings.log', json_encode($payload, JSON_UNESCAPED_UNICODE));

        try {
            $this->assertMailConfigured();
            $this->mailLead('New Meeting Booking — ' . $payload['meeting_date_display'], $payload);
            $this->mailMeetingConfirmation($payload);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with(
                'meeting_success',
                'Meeting is saved, but email could not be sent yet. Our team will still contact you at ' . $data['email'] . '. (Mail setup needed)'
            )->withErrors([
                'email' => 'Email sending failed: ' . $exception->getMessage(),
            ]);
        }

        return back()->with(
            'meeting_success',
            'Meeting booked successfully. A confirmation email with your meeting details has been sent to ' . $data['email'] . '.'
        );
    }

    public function submitReview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:120'],
            'project' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:180'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['required', 'string', 'min:20', 'max:1200'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $allReviews = $this->allReviews();

        $imagePath = null;
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $filename = 'review-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $targetDirectory = public_path('images/reviews');
            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }
            $file->move($targetDirectory, $filename);
            $imagePath = 'images/reviews/' . $filename;
        }

        $entry = [
            'name' => $data['name'],
            'role' => $data['role'],
            'company' => $data['company'],
            'project' => $data['project'],
            'rating' => (int) $data['rating'],
            'review' => $data['review'],
            'image' => $imagePath ?: ('https://i.pravatar.cc/240?u=' . urlencode(strtolower(($data['email'] ?? $data['name']) . '-' . now()->timestamp))),
            'created_at' => now()->toDateTimeString(),
        ];

        array_unshift($allReviews, $entry);
        Storage::put(self::REVIEWS_FILE, json_encode($allReviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->mailLead('New Client Review Submitted', $entry);

        return response()->json([
            'message' => 'Thanks for your review. It is now visible on the site.',
            'review' => $entry,
        ]);
    }

    public function reviewsFeed(): JsonResponse
    {
        return response()->json([
            'reviews' => array_slice($this->allReviews(), 0, 20),
        ]);
    }

    private function allReviews(): array
    {
        $seedReviews = config('portfolio.testimonials', []);

        if (!Storage::exists(self::REVIEWS_FILE)) {
            return $seedReviews;
        }

        $raw = Storage::get(self::REVIEWS_FILE);
        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            return $seedReviews;
        }

        return $decoded;
    }

    private function mailLead(string $subject, array $payload): void
    {
        $recipient = config('portfolio.lead_email', env('MAIL_FROM_ADDRESS', 'info@codovision.tech'));
        $fromAddress = config('mail.from.address', $recipient);
        $fromName = config('mail.from.name', config('portfolio.company_name', 'CodoVision'));

        $body = collect($payload)
            ->map(fn ($value, $key) => strtoupper((string) $key) . ': ' . (is_scalar($value) ? (string) $value : json_encode($value)))
            ->implode("\n");

        Mail::raw($body, function ($message) use ($recipient, $subject, $fromAddress, $fromName, $payload) {
            $message->from($fromAddress, $fromName)
                ->to($recipient)
                ->subject($subject);

            if (!empty($payload['email']) && filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
                $message->replyTo($payload['email'], $payload['name'] ?? null);
            }
        });
    }

    private function mailMeetingConfirmation(array $payload): void
    {
        $companyName = config('portfolio.company_name', 'CodoVision');
        $companyEmail = config('portfolio.company_email', 'info@codovision.tech');
        $companyPhone = config('portfolio.company_phone', '+917973776933');
        $companyAddress = config('portfolio.company_address', '');
        $fromAddress = config('mail.from.address', $companyEmail);
        $fromName = config('mail.from.name', $companyName);

        $body = <<<TEXT
Hi {$payload['name']},

Thank you for booking a meeting with {$companyName}.

Your meeting details:
---------------------
Date: {$payload['meeting_date_display']}
Time Slot: {$payload['meeting_slot']}
Timezone: {$payload['timezone']}
Meeting Type: {$payload['meeting_type']}
About the meeting: {$payload['meeting_about']}

Your contact details:
---------------------
Name: {$payload['name']}
Email: {$payload['email']}
Phone: {$payload['phone']}

Our team will join this meeting as scheduled. If you need to reschedule, reply to this email or contact us.

{$companyName}
Email: {$companyEmail}
Phone: {$companyPhone}
Address: {$companyAddress}

Warm regards,
{$companyName} Team
TEXT;

        Mail::raw($body, function ($message) use ($payload, $companyName, $companyEmail, $fromAddress, $fromName) {
            $message->from($fromAddress, $fromName)
                ->to($payload['email'])
                ->replyTo($companyEmail, $companyName)
                ->subject('Meeting Confirmed — ' . $payload['meeting_date_display'] . ' | ' . $companyName);
        });
    }

    private function assertMailConfigured(): void
    {
        $mailer = (string) config('mail.default');
        $password = (string) env('MAIL_PASSWORD', '');

        if ($mailer === 'log') {
            throw new \RuntimeException('Mail is set to log mode. Configure SMTP in .env to send real emails.');
        }

        if ($mailer === 'smtp' && trim($password) === '') {
            throw new \RuntimeException('MAIL_PASSWORD is empty. Add your email SMTP password in .env to send meeting emails.');
        }
    }
}
