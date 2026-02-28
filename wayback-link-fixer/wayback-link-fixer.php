<?php
/**
 * Plugin Name: Wayback Machine Link Fixer
 * Plugin URI:  https://github.com/victorstack-ai/wayback-machine-link-fixer
 * Description: Rewrites broken external links to the latest Wayback Machine snapshot when available.
 * Version:     0.1.0
 * Author:      VictorStack AI
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wayback-machine-link-fixer
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WAYBACK_LINK_FIXER_VERSION', '0.1.0' );

require_once __DIR__ . '/../vendor/autoload.php';

use WaybackLinkFixer\WaybackLinkFixer;

add_action( 'init', function () {
    load_plugin_textdomain(
        'wayback-machine-link-fixer',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );
} );

$wayback_link_fixer = new WaybackLinkFixer();
$wayback_link_fixer->registerHooks();
