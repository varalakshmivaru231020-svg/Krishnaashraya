<?php
declare(strict_types=1);

/**
 * Search-engine data for every public page: Google title, description and share image.
 * Admin -> SEO can override the title and description of any page (settings seo_{key}_title / _description);
 * everything else falls back to sensible text built from the page's own content.
 */

/** Plain one-line text, cut at a word boundary to at most $max characters. */
function seo_clip(string $text, int $max = 158): string
{
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)));
    if (mb_strlen($text) <= $max) {
        return $text;
    }
    $cut = mb_substr($text, 0, $max - 1);
    $space = mb_strrpos($cut, ' ');
    return rtrim($space > $max * 0.6 ? mb_substr($cut, 0, $space) : $cut, " ,.;:-–—") . '…';
}

/** "Page | Site name", or just the page name when that would be too long for Google (about 60 characters). */
function seo_title_with_site(string $page): string
{
    $site = setting('site_name', 'Sri Krishnashraya Educational Trust');
    foreach ([$page . ' | ' . $site, $page . ' | Krishnashraya Trust', $page . ' | Krishnashraya'] as $title) {
        if (mb_strlen($title) <= 60) {
            return $title;
        }
    }
    return $page;
}

/**
 * Every public page with its defaults. Each entry: key, label, group, path, title, description, image.
 * The order is the order shown in Admin -> SEO.
 */
function seo_pages(): array
{
    static $pages = null;
    if ($pages !== null) {
        return $pages;
    }
    $site = setting('site_name', 'Sri Krishnashraya Educational Trust');
    $firstSlide = (string) db_value('SELECT image FROM home_slides WHERE is_active = 1 ORDER BY sort_order, id LIMIT 1');
    $city = 'Bangalore';

    $pages = [
        ['key' => 'home', 'label' => 'Home', 'group' => 'Main pages', 'path' => '',
         'title' => $site . ', ' . $city,
         'description' => seo_clip("Help orphaned and underprivileged children in $city with food, a safe home and education. Donate online (80G tax benefit), volunteer or sponsor a child."),
         'image' => $firstSlide ?: 'assets/images/slider/slider-1.webp'],
        ['key' => 'about', 'label' => 'About Us', 'group' => 'Main pages', 'path' => 'about',
         'title' => seo_title_with_site('About Us'),
         'description' => seo_clip(about('about_text') ?: "About $site: our story, vision and mission for children in $city."),
         'image' => about('about_image')],
        ['key' => 'projects', 'label' => 'Our Projects', 'group' => 'Main pages', 'path' => 'projects',
         'title' => seo_title_with_site('Our Projects'),
         'description' => seo_clip("Ongoing and completed projects of $site: education, nutrition and care for underprivileged children in $city. Choose a project to support."),
         'image' => page_banner('projects')['image']],
        ['key' => 'activities', 'label' => 'Our Activities', 'group' => 'Main pages', 'path' => 'our-activities',
         'title' => seo_title_with_site('Our Activities'),
         'description' => seo_clip("Education programs, food and nutrition, community support, events and everyday moments with the children of $site, $city."),
         'image' => page_banner('activities')['image']],
        ['key' => 'donate', 'label' => 'Donate', 'group' => 'Main pages', 'path' => 'donate',
         'title' => seo_title_with_site('Donate Online'),
         'description' => seo_clip("Donate online to $site by UPI, card or net banking. Your gift gives a child food, shelter and education. 80G tax-exemption receipt available."),
         'image' => page_banner('donate')['image']],
        ['key' => 'contact', 'label' => 'Contact Us', 'group' => 'Main pages', 'path' => 'contact',
         'title' => seo_title_with_site('Contact Us'),
         'description' => seo_clip("Contact $site in $city — visit the children, volunteer, sponsor a child or ask about donating. Call " . setting('phone_1') . '.'),
         'image' => page_banner('contact')['image']],
    ];

    foreach (db_all('SELECT id, slug, title, summary, image FROM projects WHERE is_active = 1 ORDER BY status = "completed", sort_order, id') as $p) {
        $pages[] = ['key' => 'project_' . $p['id'], 'label' => $p['title'], 'group' => 'Projects', 'path' => 'projects/' . $p['slug'],
                    'title' => seo_title_with_site($p['title']),
                    'description' => seo_clip((string) $p['summary'] ?: $p['title'] . " — a project of $site."),
                    'image' => $p['image']];
    }
    foreach (db_all('SELECT id, slug, title, description, cover_image, banner_image FROM activity_categories WHERE is_active = 1 ORDER BY sort_order, id') as $c) {
        $pages[] = ['key' => 'activity_' . $c['id'], 'label' => $c['title'], 'group' => 'Activities', 'path' => $c['slug'],
                    'title' => seo_title_with_site($c['title']),
                    'description' => seo_clip(rtrim((string) $c['description'] ?: $c['title'] . " at $site", ' .') . ". Photos and stories from the children of $site, $city."),
                    'image' => $c['banner_image'] ?: $c['cover_image']];
    }
    return $pages;
}

/** The final title, description, image and canonical address for a page, with any admin overrides applied. */
function seo_for_path(string $path): ?array
{
    foreach (seo_pages() as $page) {
        if ($page['path'] === $path) {
            $customTitle = setting('seo_' . $page['key'] . '_title');
            $customDesc = setting('seo_' . $page['key'] . '_description');
            return $page + [
                'final_title'       => $customTitle !== '' ? $customTitle : $page['title'],
                'final_description' => $customDesc !== '' ? $customDesc : $page['description'],
                'custom'            => $customTitle !== '' || $customDesc !== '',
                'url'               => site_origin() . url($path),
            ];
        }
    }
    return null;
}

/** Organisation details for Google (schema.org NGO), shown on the home page. */
function seo_organisation_jsonld(): string
{
    $address = preg_replace('/\s+/', ' ', setting('address_registered'));
    $data = [
        '@context'     => 'https://schema.org',
        '@type'        => 'NGO',
        'name'         => setting('site_name', 'Sri Krishnashraya Educational Trust'),
        'url'          => site_origin() . url(''),
        'logo'         => site_origin() . url('assets/images/sket-logo-web.webp'),
        'description'  => seo_clip(setting('footer_about'), 300),
        'email'        => setting('email_1'),
        'telephone'    => setting('phone_1'),
        'address'      => ['@type' => 'PostalAddress', 'streetAddress' => $address, 'addressLocality' => 'Bangalore', 'addressRegion' => 'Karnataka', 'addressCountry' => 'IN'],
        'sameAs'       => array_values(social_links()),
    ];
    return json_encode(array_filter($data), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
}
