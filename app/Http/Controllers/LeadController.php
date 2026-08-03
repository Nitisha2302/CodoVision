<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $recipient = config('portfolio.lead_email', env('MAIL_FROM_ADDRESS', 'hello@example.com'));

        $body = collect($payload)
            ->map(fn ($value, $key) => strtoupper((string) $key) . ': ' . (is_scalar($value) ? (string) $value : json_encode($value)))
            ->implode("\n");

        Mail::raw($body, function ($message) use ($recipient, $subject) {
            $message->to($recipient)->subject($subject);
        });
    }
}
