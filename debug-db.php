<?php
define('WP_USE_THEMES', false);
require_once('/home/rasyiqi/Local Sites/crediblemark/app/public/wp-load.php');

global $wpdb;
$table_name = $wpdb->prefix . 'oww_products';

echo "Table: $table_name\n";
$columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");

foreach ($columns as $column) {
    echo "- " . $column->Field . " (" . $column->Type . ")\n";
}

$db_version = get_option('owwcommerce_version');
echo "\nDB Version Option: $db_version\n";
echo "Defined Version: 1.3.5\n";
