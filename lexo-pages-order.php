<?php

/**
 * Plugin Name:       LEXO Pages Order
 * Plugin URI:        https://github.com/lexo-ch/lexo-pages-order/
 * Description:       Subpages menu order.
 * Version:           1.0.6
 * Requires at least: 4.7
 * Requires PHP:      7.4.1
 * Author:            LEXO GmbH
 * Author URI:        https://www.lexo.ch
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       po
 * Domain Path:       /languages
 * Update URI:        lexo-pages-order
 */

namespace LEXO\PO;

use Exception;
use LEXO\PO\Activation;
use LEXO\PO\Deactivation;
use LEXO\PO\Uninstalling;
use LEXO\PO\Core\Bootloader;

// Prevent direct access
!defined('WPINC')
    && die;

// Define Main plugin file
!defined('LEXO\PO\FILE')
    && define('LEXO\PO\FILE', __FILE__);

// Define plugin name
!defined('LEXO\PO\PLUGIN_NAME')
    && define('LEXO\PO\PLUGIN_NAME', get_file_data(FILE, [
        'Plugin Name' => 'Plugin Name'
    ])['Plugin Name']);

// Define plugin slug
!defined('LEXO\PO\PLUGIN_SLUG')
    && define('LEXO\PO\PLUGIN_SLUG', get_file_data(FILE, [
        'Update URI' => 'Update URI'
    ])['Update URI']);

// Define Basename
!defined('LEXO\PO\BASENAME')
    && define('LEXO\PO\BASENAME', plugin_basename(FILE));

// Define internal path
!defined('LEXO\PO\PATH')
    && define('LEXO\PO\PATH', plugin_dir_path(FILE));

// Define assets path
!defined('LEXO\PO\ASSETS')
    && define('LEXO\PO\ASSETS', trailingslashit(PATH) . 'assets');

// Define internal url
!defined('LEXO\PO\URL')
    && define('LEXO\PO\URL', plugin_dir_url(FILE));

// Define internal version
!defined('LEXO\PO\VERSION')
    && define('LEXO\PO\VERSION', get_file_data(FILE, [
        'Version' => 'Version'
    ])['Version']);

// Define min PHP version
!defined('LEXO\PO\MIN_PHP_VERSION')
    && define('LEXO\PO\MIN_PHP_VERSION', get_file_data(FILE, [
        'Requires PHP' => 'Requires PHP'
    ])['Requires PHP']);

// Define min WP version
!defined('LEXO\PO\MIN_WP_VERSION')
    && define('LEXO\PO\MIN_WP_VERSION', get_file_data(FILE, [
        'Requires at least' => 'Requires at least'
    ])['Requires at least']);

// Define Text domain
!defined('LEXO\PO\DOMAIN')
    && define('LEXO\PO\DOMAIN', get_file_data(FILE, [
        'Text Domain' => 'Text Domain'
    ])['Text Domain']);

// Define locales folder (with all translations)
!defined('LEXO\PO\LOCALES')
    && define('LEXO\PO\LOCALES', 'languages');

!defined('LEXO\PO\CACHE_KEY')
    && define('LEXO\PO\CACHE_KEY', DOMAIN . '_cache_key_update');

!defined('LEXO\PO\UPDATE_PATH')
    && define('LEXO\PO\UPDATE_PATH', 'https://wprepo.lexo.ch/public/lexo-pages-order/info.json');

if (!file_exists($composer = PATH . '/vendor/autoload.php')) {
    wp_die('Error locating autoloader in LEXO Pages Order.
        Please run a following command:<pre>composer install</pre>', 'po');
}

require $composer;

register_activation_hook(FILE, function () {
    (new Activation())->run();
});

register_deactivation_hook(FILE, function () {
    (new Deactivation())->run();
});

if (!function_exists('po_uninstall')) {
    function po_uninstall()
    {
        (new Uninstalling())->run();
    }
}
register_uninstall_hook(FILE, __NAMESPACE__ . '\po_uninstall');

try {
    Bootloader::getInstance()->run();
} catch (Exception $e) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');

    deactivate_plugins(FILE);

    wp_die($e->getMessage());
}
