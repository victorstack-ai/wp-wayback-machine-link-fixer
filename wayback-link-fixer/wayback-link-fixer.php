<?php
/**
 * Plugin Name: Wayback Machine Link Fixer
 * Description: Rewrites broken external links to the latest Wayback Machine snapshot when available.
 * Version: 0.1.0
 * Author: VictorStack AI
 * License: GPL-2.0-or-later
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use WaybackLinkFixer\WaybackLinkFixer;

$wayback_link_fixer = new WaybackLinkFixer();
$wayback_link_fixer->registerHooks();
