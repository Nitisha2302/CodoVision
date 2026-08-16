@php
    $org = config('seo.organization');
    $founders = config('seo.founders', []);
    $siteUrl = rtrim(config('seo.site_url'), '/');
    $orgId = $siteUrl . '/#organization';
    $websiteId = $siteUrl . '/#website';
    $logoUrl = url($org['logo']);

    $founderNodes = [];
    $founderRefs = [];
    foreach ($founders as $founder) {
        $personId = $siteUrl . '/#' . $founder['id'];
        $founderRefs[] = ['@id' => $personId];

        $person = [
            '@type' => 'Person',
            '@id' => $personId,
            'name' => $founder['name'],
            'worksFor' => ['@id' => $orgId],
            'url' => $founder['url'] ?? ($siteUrl . '/about'),
            'description' => $founder['description'] ?? null,
        ];

        if (!empty($founder['job_title'])) {
            $person['jobTitle'] = $founder['job_title'];
        }
        if (!empty($founder['image'])) {
            $person['image'] = url($founder['image']);
        }
        if (!empty($founder['same_as'])) {
            $person['sameAs'] = array_values($founder['same_as']);
        }

        $founderNodes[] = array_filter($person, fn ($v) => $v !== null && $v !== []);
    }

    $organization = [
        '@type' => ['Organization', 'ProfessionalService', 'LocalBusiness'],
        '@id' => $orgId,
        'name' => $org['name'],
        'legalName' => $org['legal_name'],
        'alternateName' => $org['alternate_name'],
        'url' => $org['url'],
        'description' => $org['description'],
        'email' => 'mailto:' . ltrim($org['email'], 'mailto:'),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $logoUrl,
            'caption' => 'CodoVision LLP logo',
        ],
        'image' => $logoUrl,
        'founder' => $founderRefs,
        'sameAs' => array_values($org['same_as'] ?? []),
    ];

    if (!empty($org['disambiguating_description'])) {
        $organization['disambiguatingDescription'] = $org['disambiguating_description'];
    }

    if (!empty($org['telephone'])) {
        $organization['telephone'] = $org['telephone'];
    }

    if (!empty($org['address'])) {
        $organization['address'] = [
            '@type' => 'PostalAddress',
            'streetAddress' => $org['address']['street'],
            'addressLocality' => $org['address']['locality'],
            'addressRegion' => $org['address']['region'],
            'postalCode' => $org['address']['postal'],
            'addressCountry' => $org['address']['country'],
        ];
    }

    if (!empty($org['area_served'])) {
        $organization['areaServed'] = $org['area_served'];
    }

    $website = [
        '@type' => 'WebSite',
        '@id' => $websiteId,
        'url' => $org['url'],
        'name' => $org['legal_name'],
        'alternateName' => $org['alternate_name'],
        'publisher' => ['@id' => $orgId],
        'inLanguage' => 'en',
    ];

    $graph = array_values(array_filter([
        $organization,
        $website,
        ...$founderNodes,
    ]));

    $payload = [
        '@context' => 'https://schema.org',
        '@graph' => $graph,
    ];
@endphp
<script type="application/ld+json">{!! json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
