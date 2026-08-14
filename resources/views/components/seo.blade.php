@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'robots' => null,
    'ogTitle' => null,
    'ogDescription' => null,
    'ogUrl' => null,
    'ogImage' => null,
    'ogType' => 'website',
    'twitterTitle' => null,
    'twitterDescription' => null,
    'twitterImage' => null,
    'twitterCard' => null,
    'keywords' => null,
    'author' => null,
])

@php
    $org = config('seo.organization');
    $defaults = config('seo.defaults');

    $resolvedTitle = $title ?: $defaults['title'];
    $resolvedDescription = $description ?: $defaults['description'];
    $resolvedCanonical = $canonical ?: url()->current();
    $resolvedRobots = $robots ?: $defaults['robots'];
    $resolvedOgTitle = $ogTitle ?: $resolvedTitle;
    $resolvedOgDescription = $ogDescription ?: $resolvedDescription;
    $resolvedOgUrl = $ogUrl ?: $resolvedCanonical;
    $resolvedOgImage = $ogImage ?: url($defaults['og_image']);
    $resolvedTwitterTitle = $twitterTitle ?: $resolvedOgTitle;
    $resolvedTwitterDescription = $twitterDescription ?: $resolvedOgDescription;
    $resolvedTwitterImage = $twitterImage ?: $resolvedOgImage;
    $resolvedTwitterCard = $twitterCard ?: $defaults['twitter_card'];
    $resolvedAuthor = $author ?: $org['legal_name'];
@endphp

<meta name="author" content="{{ $resolvedAuthor }}">
<meta name="description" content="{{ $resolvedDescription }}">
@if(filled($keywords))
<meta name="keywords" content="{{ $keywords }}">
@endif
<meta name="robots" content="{{ $resolvedRobots }}">
<meta name="googlebot" content="{{ str_contains($resolvedRobots, 'noindex') ? 'noindex,nofollow' : 'index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1' }}">
<link rel="canonical" href="{{ $resolvedCanonical }}">

<meta property="og:type" content="{{ $ogType }}">
<meta property="og:site_name" content="{{ $org['legal_name'] }}">
<meta property="og:title" content="{{ $resolvedOgTitle }}">
<meta property="og:description" content="{{ $resolvedOgDescription }}">
<meta property="og:url" content="{{ $resolvedOgUrl }}">
<meta property="og:image" content="{{ $resolvedOgImage }}">

<meta name="twitter:card" content="{{ $resolvedTwitterCard }}">
<meta name="twitter:title" content="{{ $resolvedTwitterTitle }}">
<meta name="twitter:description" content="{{ $resolvedTwitterDescription }}">
<meta name="twitter:image" content="{{ $resolvedTwitterImage }}">

<title>{{ $resolvedTitle }}</title>
