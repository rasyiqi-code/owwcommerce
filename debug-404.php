<?php
// Find the right path for wp-load.php
$paths = [
    '/home/rasyiqi/Project/wordpress/theme/../../wordpress/wp-load.php', // Guessing based on structure
    '/home/rasyiqi/Project/wordpress/wp-load.php',
    '/home/rasyiqi/Project/owwcommerce/../../wordpress/wp-load.php',
    '/var/www/html/wp-load.php'
];

$wp_load = null;
foreach ($paths as $p) {
    if (file_exists($p)) {
        $wp_load = $p;
        break;
    }
}

if (!$wp_load) {
    // Try to find it via shell if possible
    $wp_load = shell_exec('find /home/rasyiqi/ -name wp-load.php -maxdepth 5 | head -n 1');
    $wp_load = trim($wp_load);
}

if (!$wp_load || !file_exists($wp_load)) {
    die("Error: wp-load.php not found. Please provide the absolute path to your WordPress root.\n");
}

require_once $wp_load;

echo "WordPress Base Path: " . ABSPATH . "\n";
echo "OwwCommerce Permalink Debug:\n";
echo "---------------------------\n";

$use_shop_base = get_option('owwc_use_shop_base');
$shop_page_id  = get_option('owwc_page_shop_id');
$product_base  = get_option('owwc_product_base');

echo "owwc_use_shop_base: " . ($use_shop_base ? 'true' : 'false') . "\n";
echo "owwc_page_shop_id: " . $shop_page_id . "\n";
echo "owwc_product_base: " . $product_base . "\n";

if ($shop_page_id) {
    $shop_page = get_post($shop_page_id);
    if ($shop_page) {
        echo "Shop Page Title: " . $shop_page->post_title . "\n";
        echo "Shop Page Slug: " . $shop_page->post_name . "\n";
        echo "Shop Page Status: " . $shop_page->post_status . "\n";
        echo "Shop Page URL: " . get_permalink($shop_page_id) . "\n";
    } else {
        echo "Shop Page ID $shop_page_id exists but post not found!\n";
    }
} else {
    echo "No shop page set in OwwCommerce settings.\n";
}

// Check if any other page has slug 'book'
$book_page = get_page_by_path('book');
if ($book_page) {
    echo "Page found with slug 'book': ID " . $book_page->ID . " (" . $book_page->post_title . "), Status: " . $book_page->post_status . "\n";
} else {
    echo "No page found with slug 'book' in global scope.\n";
}

echo "Current Rewrite Rules (First 5 containing 'book'):\n";
global $wp_rewrite;
$rules = $wp_rewrite->wp_rewrite_rules();
$found = 0;
foreach ($rules as $regex => $query) {
    if (strpos($regex, 'book') !== false) {
        echo "Rule: $regex -> $query\n";
        $found++;
        if ($found >= 5) break;
    }
}

echo "Flushing rewrite rules hard...\n";
flush_rewrite_rules(true);
echo "Done.\n";
