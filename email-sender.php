<?php
/**
 * Plugin Name: Custom Email Sender
 * Description: Send custom HTML emails with multiple attachments from WordPress admin
 * Version: 1.1.3
 * Author: Syed Badar
 */

if (!defined('ABSPATH')) {
    exit;
}

// ─── Constants ────────────────────────────────────────────────────────────────
define('CES_VERSION', '1.1.3');
define('CES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CES_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));

// ─── Includes ─────────────────────────────────────────────────────────────────
require_once CES_PLUGIN_DIR . 'includes/class-ces-utils.php';
require_once CES_PLUGIN_DIR . 'includes/class-ces-mailer.php';
require_once CES_PLUGIN_DIR . 'includes/class-ces-admin.php';

// ─── Initialization ───────────────────────────────────────────────────────────
function ces_init()
{
    new CES_Admin();
}
add_action('plugins_loaded', 'ces_init');