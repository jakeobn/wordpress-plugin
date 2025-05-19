<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes
 * @author     OpenHands
 */
class AI_Developer_Assistant_Deactivator {

    /**
     * Plugin deactivation tasks.
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // We don't delete any data or tables on deactivation
        // This ensures user data is preserved if they reactivate the plugin
        
        // If you want to clean up all data on deactivation, uncomment the code below
        /*
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ai_developer_assistant_snippets");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ai_developer_assistant_history");
        delete_option('ai_developer_assistant_options');
        */
    }
}