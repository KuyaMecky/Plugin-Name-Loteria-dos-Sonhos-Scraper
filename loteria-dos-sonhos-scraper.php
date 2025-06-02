<?php
/**
 * Plugin Name: Loteria dos Sonhos Scraper
 * Description: A WordPress plugin to scrape lottery results from Loteria dos Sonhos website.
 * Version: 1.0
 * Author: Michael Tallada
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu
add_action('admin_menu', 'loteria_dos_sonhos_admin_menu');

function loteria_dos_sonhos_admin_menu() {
    add_options_page(
        'Loteria dos Sonhos Settings',
        'Loteria dos Sonhos',
        'manage_options',
        'loteria-dos-sonhos',
        'loteria_dos_sonhos_settings_page'
    );
}

function loteria_dos_sonhos_settings_page() {
    ?>
    <div class="wrap">
        <h1>Loteria dos Sonhos Scraper Settings</h1>
        <h2>Available Shortcodes:</h2>
        <ul>
            <li><strong>[loteria_dos_sonhos_home]</strong> - Main page results</li>
            <li><strong>[loteria_dos_sonhos_results]</strong> - Results page</li>
            <li><strong>[loteria_dos_sonhos_live]</strong> - Live results</li>
            <li><strong>[loteria_dos_sonhos_yesterday]</strong> - Yesterday's results</li>
            <li><strong>[loteria_dos_sonhos_palpite]</strong> - Palpite LDS page</li>
        </ul>
        <p>Use these shortcodes in your posts or pages to display the scraped content.</p>
        <p><strong>Note:</strong> All external links will be redirected to: https://seo813.pages.dev?agentid=Bet606</p>
    </div>
    <?php
}

// Main page scraper shortcode
function loteria_dos_sonhos_home_shortcode() {
    $url = 'https://loteriadossonhos.net/';
    return loteria_dos_sonhos_scrape_content($url, 'home');
}
add_shortcode('loteria_dos_sonhos_home', 'loteria_dos_sonhos_home_shortcode');

// Results page scraper shortcode
function loteria_dos_sonhos_results_shortcode() {
    $url = 'https://loteriadossonhos.net/resultados/';
    return loteria_dos_sonhos_scrape_content($url, 'results');
}
add_shortcode('loteria_dos_sonhos_results', 'loteria_dos_sonhos_results_shortcode');

// Live results scraper shortcode
function loteria_dos_sonhos_live_shortcode() {
    $url = 'https://loteriadossonhos.net/loteria-dos-sonhos-ao-vivo/';
    return loteria_dos_sonhos_scrape_content($url, 'live');
}
add_shortcode('loteria_dos_sonhos_live', 'loteria_dos_sonhos_live_shortcode');

// Yesterday results scraper shortcode
function loteria_dos_sonhos_yesterday_shortcode() {
    $url = 'https://loteriadossonhos.net/loteria-dos-sonhos-de-ontem/';
    return loteria_dos_sonhos_scrape_content($url, 'yesterday');
}
add_shortcode('loteria_dos_sonhos_yesterday', 'loteria_dos_sonhos_yesterday_shortcode');

// Palpite LDS scraper shortcode
function loteria_dos_sonhos_palpite_shortcode() {
    $url = 'https://loteriadossonhos.net/palpite/';
    return loteria_dos_sonhos_scrape_content($url, 'palpite');
}
add_shortcode('loteria_dos_sonhos_palpite', 'loteria_dos_sonhos_palpite_shortcode');

// Main scraping function
function loteria_dos_sonhos_scrape_content($url, $type = 'home') {
    // Set custom user agent and headers
    $args = array(
        'timeout'     => 30,
        'redirection' => 5,
        'httpversion' => '1.1',
        'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'headers'     => array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en;q=0.8',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
        ),
    );

    $response = wp_remote_get($url, $args);
    
    if (is_wp_error($response)) {
        return '<div class="loteria-error">Failed to fetch data from ' . esc_url($url) . '</div>';
    }

    $body = wp_remote_retrieve_body($response);
    
    if (empty($body)) {
        return '<div class="loteria-error">No content received from the website.</div>';
    }

    // Load HTML
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress HTML parsing errors
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $body);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    
    // Try different selectors based on common WordPress themes and structures
    $selectors = array(
        '//main[@class="site-main"]',
        '//div[@class="content-area"]',
        '//div[@class="entry-content"]',
        '//article',
        '//div[@class="post-content"]',
        '//div[@class="page-content"]',
        '//div[contains(@class, "content")]',
        '//body'
    );
    
    $content_div = null;
    foreach ($selectors as $selector) {
        $nodes = $xpath->query($selector);
        if ($nodes->length > 0) {
            $content_div = $nodes->item(0);
            break;
        }
    }
    
    if (!$content_div) {
        return '<div class="loteria-error">Content container not found.</div>';
    }

    // List of elements to remove (ads, navigation, etc.)
    $elements_to_remove = array(
        // Common ad containers
        '//div[contains(@class, "ad")]',
        '//div[contains(@class, "advertisement")]',
        '//div[contains(@class, "banner")]',
        '//div[contains(@id, "ad")]',
        '//ins[@class="adsbygoogle"]',
        
        // Navigation and menu elements
        '//nav',
        '//header',
        '//footer',
        '//div[@class="site-header"]',
        '//div[@class="site-footer"]',
        
        // Social media and sharing
        '//div[contains(@class, "social")]',
        '//div[contains(@class, "share")]',
        '//div[contains(@class, "sharing")]',
        
        // Comments and forms
        '//div[@id="comments"]',
        '//div[@class="comment-form"]',
        '//form',
        
        // Sidebar elements
        '//aside',
        '//div[@class="sidebar"]',
        '//div[@class="widget"]',
        
        // Scripts and styles that might interfere
        '//script',
        '//style[not(@type) or @type="text/css"]',
        '//noscript',
        
        // Contact and WhatsApp elements
        '//div[contains(text(), "WhatsApp")]',
        '//a[contains(@href, "whatsapp")]',
        '//div[contains(text(), "85 99440-2326")]',
        
        // YouTube promotion
        '//div[contains(text(), "Youtube")]',
        '//div[contains(text(), "Inscreva-se")]',
    );

    // Remove unwanted elements
    foreach ($elements_to_remove as $query) {
        $nodes = $xpath->query($query);
        foreach ($nodes as $node) {
            if ($node && $node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }
    }

    // Update image URLs to absolute paths
    $images = $content_div->getElementsByTagName('img');
    foreach ($images as $img) {
        $src = $img->getAttribute('src');
        if (!empty($src) && strpos($src, 'http') !== 0) {
            // Convert relative URLs to absolute
            if (strpos($src, '//') === 0) {
                $img->setAttribute('src', 'https:' . $src);
            } elseif (strpos($src, '/') === 0) {
                $img->setAttribute('src', 'https://loteriadossonhos.net' . $src);
            } else {
                $img->setAttribute('src', 'https://loteriadossonhos.net/' . ltrim($src, '/'));
            }
        }
    }

    // Update link URLs - ALL EXTERNAL LINKS NOW REDIRECT TO YOUR SPECIFIED URL
    $redirect_url = 'https://seo813.pages.dev?agentid=Bet606';
    $links = $content_div->getElementsByTagName('a');
    foreach ($links as $link) {
        $href = $link->getAttribute('href');
        
        // Skip empty hrefs and anchor links
        if (empty($href) || strpos($href, '#') === 0) {
            continue;
        }
        
        // Check if it's an external link or internal link from the scraped site
        $is_external = false;
        
        if (strpos($href, 'http') === 0) {
            // Absolute URL - check if it's external
            $parsed_url = parse_url($href);
            if ($parsed_url && isset($parsed_url['host']) && $parsed_url['host'] !== parse_url(get_site_url(), PHP_URL_HOST)) {
                $is_external = true;
            }
        } else {
            // Relative URL - convert to absolute first, then treat as external
            if (strpos($href, '/') === 0) {
                $full_url = 'https://loteriadossonhos.net' . $href;
            } else {
                $full_url = 'https://loteriadossonhos.net/' . ltrim($href, '/');
            }
            $is_external = true; // All scraped links are considered external
        }
        
        if ($is_external) {
            // Redirect all external links to your specified URL
            $link->setAttribute('href', $redirect_url);
            $link->setAttribute('target', '_blank');
            $link->setAttribute('rel', 'noopener noreferrer');
            
            // Add a data attribute to track the original URL if needed
            if (isset($full_url)) {
                $link->setAttribute('data-original-url', $full_url);
            } else {
                $link->setAttribute('data-original-url', $href);
            }
        }
    }

    // Add custom CSS classes for styling
    if ($content_div->hasAttribute('class')) {
        $content_div->setAttribute('class', $content_div->getAttribute('class') . ' loteria-dos-sonhos-content');
    } else {
        $content_div->setAttribute('class', 'loteria-dos-sonhos-content');
    }

    // Get the HTML content
    $html_content = $dom->saveHTML($content_div);
    
    // Clean up and add wrapper
    $html_content = '<div class="loteria-dos-sonhos-wrapper loteria-' . esc_attr($type) . '">' . $html_content . '</div>';
    
    // Add basic styling
    $html_content .= '<style>
        .loteria-dos-sonhos-wrapper {
            max-width: 100%;
            overflow-x: auto;
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .loteria-dos-sonhos-wrapper img {
            max-width: 100%;
            height: auto;
        }
        .loteria-dos-sonhos-wrapper table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .loteria-dos-sonhos-wrapper th,
        .loteria-dos-sonhos-wrapper td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .loteria-dos-sonhos-wrapper th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .loteria-error {
            color: #d32f2f;
            background-color: #ffebee;
            padding: 15px;
            border: 1px solid #e57373;
            border-radius: 4px;
            margin: 10px 0;
        }
        /* Style for redirected links */
        .loteria-dos-sonhos-wrapper a[data-original-url] {
            position: relative;
        }
        .loteria-dos-sonhos-wrapper a[data-original-url]:hover::after {
            content: "Redirected link";
            position: absolute;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            top: -30px;
            left: 0;
            white-space: nowrap;
            z-index: 1000;
        }
    </style>';
    
    return $html_content;
}

// Add caching functionality
function loteria_dos_sonhos_get_cached_content($url, $type, $cache_duration = 300) { // 5 minutes cache
    $cache_key = 'loteria_dos_sonhos_' . md5($url . $type);
    $cached_content = get_transient($cache_key);
    
    if ($cached_content !== false) {
        return $cached_content;
    }
    
    $content = loteria_dos_sonhos_scrape_content($url, $type);
    set_transient($cache_key, $content, $cache_duration);
    
    return $content;
}

// Cached shortcode versions
function loteria_dos_sonhos_home_cached_shortcode() {
    return loteria_dos_sonhos_get_cached_content('https://loteriadossonhos.net/', 'home');
}
add_shortcode('loteria_dos_sonhos_home_cached', 'loteria_dos_sonhos_home_cached_shortcode');

function loteria_dos_sonhos_results_cached_shortcode() {
    return loteria_dos_sonhos_get_cached_content('https://loteriadossonhos.net/resultados/', 'results');
}
add_shortcode('loteria_dos_sonhos_results_cached', 'loteria_dos_sonhos_results_cached_shortcode');

// Cached Palpite shortcode
function loteria_dos_sonhos_palpite_cached_shortcode() {
    return loteria_dos_sonhos_get_cached_content('https://loteriadossonhos.net/palpite/', 'palpite');
}
add_shortcode('loteria_dos_sonhos_palpite_cached', 'loteria_dos_sonhos_palpite_cached_shortcode');

// Clear cache function (can be called manually or via cron)
function loteria_dos_sonhos_clear_cache() {
    $cache_keys = array(
        'loteria_dos_sonhos_' . md5('https://loteriadossonhos.net/home'),
        'loteria_dos_sonhos_' . md5('https://loteriadossonhos.net/resultados/results'),
        'loteria_dos_sonhos_' . md5('https://loteriadossonhos.net/loteria-dos-sonhos-ao-vivo/live'),
        'loteria_dos_sonhos_' . md5('https://loteriadossonhos.net/loteria-dos-sonhos-de-ontem/yesterday'),
        'loteria_dos_sonhos_' . md5('https://loteriadossonhos.net/palpite/palpite'),
    );
    
    foreach ($cache_keys as $key) {
        delete_transient($key);
    }
}

// Add admin action to clear cache
add_action('wp_ajax_clear_loteria_cache', 'loteria_dos_sonhos_clear_cache');

// Activation hook
register_activation_hook(__FILE__, 'loteria_dos_sonhos_activate');
function loteria_dos_sonhos_activate() {
    // Clear any existing cache on activation
    loteria_dos_sonhos_clear_cache();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'loteria_dos_sonhos_deactivate');
function loteria_dos_sonhos_deactivate() {
    // Clear cache on deactivation
    loteria_dos_sonhos_clear_cache();
}

// Optional: Add JavaScript to handle click tracking
add_action('wp_footer', 'loteria_dos_sonhos_add_click_tracking');
function loteria_dos_sonhos_add_click_tracking() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Track clicks on redirected links
        const redirectedLinks = document.querySelectorAll('.loteria-dos-sonhos-wrapper a[data-original-url]');
        redirectedLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                // Optional: Add analytics tracking here
                console.log('Redirected link clicked:', this.getAttribute('data-original-url'));
            });
        });
    });
    </script>
    <?php
}
?>