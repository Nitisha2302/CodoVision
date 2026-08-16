<?php

/**
 * Official SEO / entity signals for CodoVision LLP.
 * Only verified public information — do not invent social URLs or titles.
 */
return [
    'site_url' => 'https://codovision.tech',

    'organization' => [
        'name' => 'CodoVision LLP',
        'legal_name' => 'CodoVision LLP',
        // Brand spelling is CodoVision (with "o"), website codovision.tech
        'alternate_name' => [
            'CodoVision',
            'CodoVision Tech',
            'Codovision LLP',
        ],
        'url' => 'https://codovision.tech/',
        'email' => 'info@codovision.tech',
        'description' => 'CodoVision LLP is a software development company in Mohali, Punjab, India (website: https://codovision.tech/), founded by Raghav Tomar and Nitisha Goyal. We specialize in mobile app development, Flutter, Android, iOS, web development, AI, computer vision and custom software solutions.',
        // Helps Google separate this entity from similarly named companies elsewhere:
        'disambiguating_description' => 'CodoVision LLP (codovision.tech) is based in Mohali, Punjab, India and founded by Raghav Tomar and Nitisha Goyal. Official email: info@codovision.tech.',
        'logo' => '/images/logo.png',
        'same_as' => [
            // Official company LinkedIn (verified in project):
            'https://www.linkedin.com/company/codovisiontech/',
        ],
        // Verified from existing project config / contact page:
        'telephone' => '+917973776933',
        'address' => [
            'street' => 'F547 PH-8A Industrial Area, Sector 75',
            'locality' => 'S.A.S. Nagar (Mohali)',
            'region' => 'Punjab',
            'postal' => '160062',
            'country' => 'IN',
        ],
        'area_served' => [
            ['@type' => 'City', 'name' => 'Mohali'],
            ['@type' => 'State', 'name' => 'Punjab'],
            ['@type' => 'Country', 'name' => 'India'],
        ],
    ],

    'founders' => [
        [
            'id' => 'raghav-tomar',
            'name' => 'Raghav Tomar',
            // Verified on existing about/founder section:
            'job_title' => 'Founder & CEO',
            'image' => '/images/team/raghav-tomar.png',
            'url' => 'https://codovision.tech/about#founder',
            'same_as' => [
                'https://www.linkedin.com/in/raghav-tomar-791ba6253/',
            ],
            'description' => 'Raghav Tomar is Founder & CEO of CodoVision LLP, leading product strategy and client partnerships for mobile apps, web platforms, and intelligent digital systems.',
        ],
        [
            'id' => 'nitisha-goyal',
            'name' => 'Nitisha Goyal',
            // Title not published on the site yet — omit jobTitle in schema.
            'job_title' => null,
            'image' => null,
            'url' => 'https://codovision.tech/about',
            // Add official LinkedIn URL here when available (do not invent):
            'same_as' => [],
            'description' => 'Nitisha Goyal is a founder of CodoVision LLP.',
        ],
    ],

    'defaults' => [
        'title' => 'CodoVision LLP | Software Development Company in Mohali',
        'description' => 'CodoVision LLP (codovision.tech) is a Mohali-based software development company founded by Raghav Tomar and Nitisha Goyal, specializing in mobile apps, Flutter, web, AI and custom software.',
        'og_image' => '/images/logo.png',
        'robots' => 'index,follow',
        'twitter_card' => 'summary_large_image',
    ],

    /**
     * Preferred SEO titles for existing service pages (by slug).
     * Only covers real routes from config/portfolio.php.
     */
    'service_titles' => [
        'web-development' => 'Web Development Company | CodoVision LLP',
        'mobile-app-development' => 'Mobile App Development Company | CodoVision LLP',
        'agentic-ai-solutions' => 'AI Development Company | CodoVision LLP',
        'healthcare-rcm-analytics' => 'Healthcare RCM Analytics Company | CodoVision LLP',
        'ui-ux-design' => 'UI/UX Design Company | CodoVision LLP',
        'automation-solutions' => 'Automation Solutions Company | CodoVision LLP',
    ],
];
