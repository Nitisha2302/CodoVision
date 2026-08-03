<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DataController extends Controller
{
    private const REVIEWS_FILE = 'reviews.json';

    public function index(Request $request): View
    {
        $authenticated = (bool) $request->session()->get('data_auth', false);

        if (!$authenticated) {
            return view('data', [
                'authenticated' => false,
                'error' => session('data_login_error'),
            ]);
        }

        $reviews = $this->readJsonArray(self::REVIEWS_FILE, config('portfolio.testimonials', []));
        $chatRequests = $this->readLogJson('leads.log');
        $bookingRequests = $this->readLogJson('bookings.log');

        return view('data', [
            'authenticated' => true,
            'reviews' => $reviews,
            'chatRequests' => $chatRequests,
            'bookingRequests' => $bookingRequests,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if ($data['password'] !== (string) config('portfolio.data_page_password', 'nisha@123')) {
            return back()->with('data_login_error', 'Invalid password.');
        }

        $request->session()->put('data_auth', true);

        return redirect()->route('data.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('data_auth');

        return redirect()->route('data.index');
    }

    public function updateReview(Request $request, int $index): RedirectResponse
    {
        if (!$request->session()->get('data_auth', false)) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:120'],
            'project' => ['required', 'string', 'max:150'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['required', 'string', 'min:20', 'max:1200'],
        ]);

        $reviews = $this->readJsonArray(self::REVIEWS_FILE, config('portfolio.testimonials', []));
        if (!isset($reviews[$index])) {
            return back();
        }

        $reviews[$index] = array_merge($reviews[$index], $data);
        Storage::put(self::REVIEWS_FILE, json_encode(array_values($reviews), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return back();
    }

    public function deleteReview(Request $request, int $index): RedirectResponse
    {
        if (!$request->session()->get('data_auth', false)) {
            abort(403);
        }

        $reviews = $this->readJsonArray(self::REVIEWS_FILE, config('portfolio.testimonials', []));
        if (!isset($reviews[$index])) {
            return back();
        }

        unset($reviews[$index]);
        Storage::put(self::REVIEWS_FILE, json_encode(array_values($reviews), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return back();
    }

    private function readJsonArray(string $file, array $fallback): array
    {
        if (!Storage::exists($file)) {
            return $fallback;
        }

        $decoded = json_decode(Storage::get($file), true);

        return is_array($decoded) ? $decoded : $fallback;
    }

    private function readLogJson(string $file): array
    {
        if (!Storage::exists($file)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', trim((string) Storage::get($file))) ?: [];

        $entries = collect($lines)
            ->filter()
            ->map(function (string $line) {
                $decoded = json_decode($line, true);
                return is_array($decoded) ? $decoded : null;
            })
            ->filter()
            ->values()
            ->all();

        return array_reverse($entries);
    }
}
