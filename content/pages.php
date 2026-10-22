<?php

declare(strict_types=1);

return [
    'page-one' => [
        'title' => 'Sample Page One',
        'nav_title' => 'Page one',
        'description' => 'A sample page with its own URL and metadata, used to demonstrate social sharing and click tracking.',
        'image' => '/assets/img/og-page-one.png',
        'body' => [
            '<p class="lead">This page exists to test the sharing and tracking flow. Its URL, title, description, and social image are different from the home page and from Sample Page Two.</p>',
            '<h2>Test the share buttons</h2>',
            '<p>Use any of the five buttons in the share panel below. The site opens the selected platform\'s share window with this page URL and metadata, then sends the click to the back-end tracking endpoint.</p>',
            '<p>Each platform receives the same page URL but formats the share differently. Facebook reads the Open Graph tags on this page, while X, WhatsApp, and Telegram use the title and URL passed to their share dialogs. Email opens a pre-filled message in your mail client.</p>',
            '<h2>Verify the tracked click</h2>',
            '<p>Open the admin dashboard and filter by this page. You should see the platform, page title, URL, and timestamp for every recorded share. The date range, platform, page, and grouping controls can all be combined.</p>',
            '<p>Share clicks are sent with <code>navigator.sendBeacon</code> so the request survives the popup or mail-client handoff. If the browser does not support beacons, the site falls back to a keepalive fetch request.</p>',
            '<h2>Try the other sample page</h2>',
            '<p>Sample Page Two uses different metadata and content. Sharing both pages is the quickest way to confirm that the dashboard groups clicks by the current page URL rather than treating every share as the same event.</p>',
        ],
    ],

    'page-two' => [
        'title' => 'Sample Page Two',
        'nav_title' => 'Page two',
        'description' => 'A second sample page for testing per-page sharing metadata and dashboard filtering by platform and URL.',
        'image' => '/assets/img/og-page-two.png',
        'body' => [
            '<p class="lead">This is the second sample page in the Social Share Tracker demo. It shares the same layout as Sample Page One, but has its own title, description, image, and URL.</p>',
            '<h2>Page-specific metadata</h2>',
            '<p>The page head contains a canonical URL, Open Graph tags, and Twitter Card tags. Those values come from the page configuration, not from user input, so the preview shown by each social platform matches the page being shared.</p>',
            '<p>Because the metadata differs per page, the dashboard can separate activity by URL. You can confirm this by sharing both sample pages and comparing the recent clicks table.</p>',
            '<h2>Filter the analytics</h2>',
            '<p>The admin dashboard supports a date range, a social platform filter, a page filter, and day, week, or month grouping. The charts and CSV export reuse the same validated filters.</p>',
            '<p>Platforms are stored in a lookup table rather than a fixed list. Adding another platform is a matter of inserting one row with its name, color, and share URL template; the share bar, filter, and charts pick it up automatically.</p>',
            '<h2>What the tracker stores</h2>',
            '<p>Each click records the platform, page URL, page title, page path, a hashed IP address for rate limiting, and the click timestamp. Raw IP addresses are never stored.</p>',
            '<p>Use the share panel below to create a click, then switch back to the dashboard to see it appear.</p>',
        ],
    ],
];
