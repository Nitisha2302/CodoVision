@extends('layouts.app')

@section('title', 'CodoVision - Data Admin')

@section('content')
<section class="section" style="padding-top:150px;">
    <div class="section-header">
        <div class="section-badge">🔐 Secure Data Page</div>
        <h2>Internal Request Dashboard</h2>
    </div>

    @if(!$authenticated)
        <form method="POST" action="{{ route('data.login') }}" class="detail-card booking-form" style="max-width: 520px; margin: 0 auto;">
            @csrf
            <div class="form-grid" style="grid-template-columns:1fr; margin-bottom:14px;">
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn-primary">Open Data Page →</button>
            @if(!empty($error))
                <p class="review-meta" style="margin-top:10px;color:#fca5a5;">{{ $error }}</p>
            @endif
        </form>
    @else
        <div class="detail-actions" style="justify-content:flex-end; margin-bottom:15px;">
            <a href="{{ route('data.ai-product-generator') }}" class="btn-primary" style="text-decoration:none;display:inline-flex;">AI Product Generator</a>
            <form method="POST" action="{{ route('data.logout') }}">
                @csrf
                <button type="submit" class="btn-secondary">Logout</button>
            </form>
        </div>

        <section class="section" style="padding:0;">
            <div class="section-header">
                <div class="section-badge">💬 Chat Requests</div>
                <h2>All User Chat Leads</h2>
            </div>
            <div class="detail-card table-wrap">
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Project</th>
                            <th>Budget</th>
                            <th>Timeline</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($chatRequests))
                        @foreach($chatRequests as $chat)
                            <tr>
                                <td>{{ $chat['submitted_at'] ?? '-' }}</td>
                                <td>{{ $chat['name'] ?? '-' }}</td>
                                <td>{{ $chat['email'] ?? '-' }}</td>
                                <td>{{ $chat['project_type'] ?? '-' }}</td>
                                <td>{{ $chat['budget_range'] ?? '-' }}</td>
                                <td>{{ $chat['timeline'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                        @else
                            <tr><td colspan="6">No chat requests found.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section" style="padding:28px 0 0;">
            <div class="section-header">
                <div class="section-badge">📦 Package Requests</div>
                <h2>All Package Bookings</h2>
            </div>
            <div class="detail-card table-wrap">
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Package</th>
                            <th>Tech</th>
                            <th>Budget</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($bookingRequests))
                        @foreach($bookingRequests as $booking)
                            <tr>
                                <td>{{ $booking['submitted_at'] ?? '-' }}</td>
                                <td>{{ $booking['name'] ?? '-' }}</td>
                                <td>{{ $booking['email'] ?? '-' }}</td>
                                <td>{{ $booking['package_name'] ?? ($booking['package_id'] ?? '-') }}</td>
                                <td>{{ $booking['selected_technology'] ?? '-' }}</td>
                                <td>{{ $booking['estimated_budget'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                        @else
                            <tr><td colspan="6">No package requests found.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section" style="padding:28px 0 0;">
            <div class="section-header">
                <div class="section-badge">⭐ Reviews</div>
                <h2>Edit or Delete Reviews</h2>
            </div>
            <div class="services-grid">
                @foreach($reviews as $index => $review)
                    <article class="detail-card">
                        <form method="POST" action="{{ route('data.reviews.update', $index) }}" class="booking-form">
                            @csrf
                            <div class="form-grid">
                                <input type="text" name="name" value="{{ $review['name'] ?? '' }}" required>
                                <input type="text" name="role" value="{{ $review['role'] ?? '' }}" required>
                                <input type="text" name="company" value="{{ $review['company'] ?? '' }}" required>
                                <input type="text" name="project" value="{{ $review['project'] ?? '' }}" required>
                                <input type="number" name="rating" min="1" max="5" value="{{ $review['rating'] ?? 5 }}" required>
                                <textarea name="review" required>{{ $review['review'] ?? '' }}</textarea>
                            </div>
                            <div class="detail-actions">
                                <button type="submit" class="btn-primary">Save Review</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('data.reviews.delete', $index) }}" style="margin-top:10px;">
                            @csrf
                            <button type="submit" class="btn-secondary">Delete Review</button>
                        </form>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</section>
@endsection
