<?php
/**
 * Plugin Name: AI Developer Assistant
 * Plugin URI: https://example.com/ai-developer-assistant
 * Description: An AI-powered coding assistant inside WordPress that uses Anthropic Claude 3.7 API for multi-language code generation and developer support.
 * Version: 1.0.0
 * Author: OpenHands
 * Author URI: https://example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ai-developer-assistant
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Current plugin version.
 */
define('AI_DEVELOPER_ASSISTANT_VERSION', '1.0.0');
define('AI_DEVELOPER_ASSISTANT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AI_DEVELOPER_ASSISTANT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_ai_developer_assistant() {
    require_once AI_DEVELOPER_ASSISTANT_PLUGIN_DIR . 'includes/class-ai-developer-assistant-activator.php';
    AI_Developer_Assistant_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_ai_developer_assistant() {
    require_once AI_DEVELOPER_ASSISTANT_PLUGIN_DIR . 'includes/class-ai-developer-assistant-deactivator.php';
    AI_Developer_Assistant_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_ai_developer_assistant');
register_deactivation_hook(__FILE__, 'deactivate_ai_developer_assistant');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once AI_DEVELOPER_ASSISTANT_PLUGIN_DIR . 'includes/class-ai-developer-assistant.php';

/**
 * Begins execution of the plugin.
 */
function run_ai_developer_assistant() {
    $plugin = new AI_Developer_Assistant();
    $plugin->run();
}
run_ai_developer_assistant();