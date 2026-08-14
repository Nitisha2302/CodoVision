<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\AiProductGeneratorController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    $path = public_path('robots.txt');
    abort_unless(is_file($path), 404);

    return response(file_get_contents($path), 200)
        ->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('robots');

Route::get('/', function () {
    $reviewsFromFile = [];
    $reviewsPath = storage_path('app/reviews.json');
    if (file_exists($reviewsPath)) {
        $reviewsFromFile = json_decode((string) file_get_contents($reviewsPath), true) ?: [];
    }



    return view('index', [
        'services' => array_values(config('portfolio.services', [])),
        'featuredProjects' => array_slice(array_values(config('portfolio.projects', [])), 0, 6),
        'technologies' => array_slice(config('portfolio.technologies', []), 0, 8),
        'processPhases' => config('portfolio.process', []),
        'industries' => config('portfolio.industries', []),
        'testimonials' => array_slice(!empty($reviewsFromFile) ? $reviewsFromFile : config('portfolio.testimonials', []), 0, 7),
        'packages' => config('portfolio.packages', []),
    ]);
})->name('home');

Route::get('/services', function () {
    return view('services', [
        'services' => array_values(config('portfolio.services', [])),
    ]);
})->name('services');

Route::get('/services/{slug}', function (string $slug) {
    $allServices = array_values(config('portfolio.services', []));
    $service = config("portfolio.services.{$slug}");

    abort_unless($service, 404);

    $similarServices = collect($allServices)
        ->reject(fn ($entry) => $entry['slug'] === $slug)
        ->map(function (array $entry) use ($service) {
            $overlap = count(array_intersect(
                $entry['recommended_technologies'] ?? [],
                $service['recommended_technologies'] ?? []
            ));
            $entry['overlap_score'] = $overlap;
            return $entry;
        })
        ->sortByDesc('overlap_score')
        ->take(3)
        ->values();

    return view('service-detail', [
        'service' => $service,
        'similarServices' => $similarServices,
    ]);
})->name('services.detail');

Route::get('/process', function () {
    return view('process', [
        'processPhases' => config('portfolio.process', []),
    ]);
})->name('process');
Route::get('/technologies', function () {
    return view('technologies', [
        'technologies' => config('portfolio.technologies', []),
        'projects' => array_values(config('portfolio.projects', [])),
    ]);
})->name('technologies');

Route::get('/projects', function () {
    $reviewsFromFile = [];
    $reviewsPath = storage_path('app/reviews.json');
    if (file_exists($reviewsPath)) {
        $reviewsFromFile = json_decode((string) file_get_contents($reviewsPath), true) ?: [];
    }

    return view('projects', [
        'projects' => array_values(config('portfolio.projects', [])),
        'technologies' => config('portfolio.technologies', []),
        'testimonials' => !empty($reviewsFromFile) ? $reviewsFromFile : config('portfolio.testimonials', []),
    ]);
})->name('projects');

Route::get('/projects/{slug}', function (string $slug) {
    $allProjects = collect(array_values(config('portfolio.projects', [])));
    $project = config("portfolio.projects.{$slug}");

    abort_unless($project, 404);

    $similarProjects = $allProjects
        ->reject(fn ($entry) => $entry['slug'] === $slug)
        ->map(function (array $entry) use ($project) {
            $overlap = count(array_intersect($entry['tags'], $project['tags']));
            $entry['overlap_score'] = $overlap;
            return $entry;
        })
        ->filter(fn ($entry) => $entry['overlap_score'] > 0)
        ->sortByDesc('overlap_score')
        ->take(3)
        ->values();

    if ($similarProjects->isEmpty()) {
        $similarProjects = $allProjects
            ->reject(fn ($entry) => $entry['slug'] === $slug)
            ->take(3)
            ->values();
    }

    return view('project-detail', [
        'project' => $project,
        'similarProjects' => $similarProjects,
    ]);
})->name('projects.detail');

Route::view('/about', 'about')->name('about');
Route::view('/contact', 'contact')->name('contact');
Route::view('/packages', 'packages')->name('packages');
Route::view('/design', 'design')->name('design');
Route::get('/data', [DataController::class, 'index'])->name('data.index');
Route::post('/data/login', [DataController::class, 'login'])->name('data.login');
Route::post('/data/logout', [DataController::class, 'logout'])->name('data.logout');
Route::post('/data/reviews/{index}/update', [DataController::class, 'updateReview'])->name('data.reviews.update');
Route::post('/data/reviews/{index}/delete', [DataController::class, 'deleteReview'])->name('data.reviews.delete');
Route::get('/data/ai-product-generator', [AiProductGeneratorController::class, 'index'])->name('data.ai-product-generator');
Route::post('/data/ai-product-generator/generate', [AiProductGeneratorController::class, 'generate'])->name('data.ai-product-generator.generate');

Route::post('/chatbot/submit', [LeadController::class, 'submitChatbot'])->name('chatbot.submit');
Route::post('/book-package', [LeadController::class, 'submitBooking'])->name('package.book');
Route::post('/book-meeting', [LeadController::class, 'submitMeeting'])->name('meeting.book');
Route::post('/reviews/submit', [LeadController::class, 'submitReview'])->name('reviews.submit');
Route::get('/reviews/feed', [LeadController::class, 'reviewsFeed'])->name('reviews.feed');

Route::get('/tools', function () {
    return view('tools');
})->name('tools');
