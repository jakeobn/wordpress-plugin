<?php
/**
 * Fired during plugin activation
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes
 * @author     OpenHands
 */
class AI_Developer_Assistant_Activator {

    /**
     * Initialize plugin settings and create necessary database tables.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Create database tables for storing code snippets and history
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table for storing code snippets
        $table_name = $wpdb->prefix . 'ai_developer_assistant_snippets';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            language varchar(50) NOT NULL,
            code longtext NOT NULL,
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // Table for storing prompt history
        $history_table = $wpdb->prefix . 'ai_developer_assistant_history';
        $history_sql = "CREATE TABLE $history_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            prompt text NOT NULL,
            response longtext NOT NULL,
            language varchar(50) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($history_sql);
        
        // Set default plugin options
        $default_options = array(
            'api_key' => '',
            'enable_php_injection' => false,
            'default_language' => 'php',
            'enabled_languages' => array('php', 'javascript', 'python', 'nodejs'),
            'max_history_items' => 50,
        );
        
        add_option('ai_developer_assistant_options', $default_options);
    }
}