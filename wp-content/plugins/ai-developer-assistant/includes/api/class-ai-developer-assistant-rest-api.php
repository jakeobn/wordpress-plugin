<?php
/**
 * Handles REST API endpoints for the plugin
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes/api
 */

/**
 * Handles REST API endpoints for the plugin.
 *
 * This class defines all code necessary to handle REST API requests.
 *
 * @since      1.0.0
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes/api
 * @author     OpenHands
 */
class AI_Developer_Assistant_REST_API {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The Anthropic API handler.
     *
     * @since    1.0.0
     * @access   private
     * @var      AI_Developer_Assistant_Anthropic_API    $anthropic_api    The Anthropic API handler.
     */
    private $anthropic_api;

    /**
     * The Code Handler.
     *
     * @since    1.0.0
     * @access   private
     * @var      AI_Developer_Assistant_Code_Handler    $code_handler    The Code Handler.
     */
    private $code_handler;
    
    /**
     * The Error Debugger.
     *
     * @since    1.0.0
     * @access   private
     * @var      AI_Developer_Assistant_Error_Debugger    $error_debugger    The Error Debugger.
     */
    private $error_debugger;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->anthropic_api = new AI_Developer_Assistant_Anthropic_API();
        $this->code_handler = new AI_Developer_Assistant_Code_Handler();
        
        // Initialize the error debugger
        require_once plugin_dir_path(dirname(__FILE__)) . 'utils/class-ai-developer-assistant-error-debugger.php';
        $this->error_debugger = new AI_Developer_Assistant_Error_Debugger($this->anthropic_api);
    }

    /**
     * Register the REST API routes.
     *
     * @since    1.0.0
     */
    public function register_routes() {
        register_rest_route('ai-developer-assistant/v1', '/generate-code', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_code'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        register_rest_route('ai-developer-assistant/v1', '/save-snippet', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_snippet'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        register_rest_route('ai-developer-assistant/v1', '/get-snippets', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_snippets'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        register_rest_route('ai-developer-assistant/v1', '/delete-snippet', array(
            'methods' => 'POST',
            'callback' => array($this, 'delete_snippet'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        register_rest_route('ai-developer-assistant/v1', '/get-history', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_history'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        register_rest_route('ai-developer-assistant/v1', '/inject-php-code', array(
            'methods' => 'POST',
            'callback' => array($this, 'inject_php_code'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        register_rest_route('ai-developer-assistant/v1', '/export-code', array(
            'methods' => 'POST',
            'callback' => array($this, 'export_code'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        register_rest_route('ai-developer-assistant/v1', '/generate-plugin', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_plugin'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        register_rest_route('ai-developer-assistant/v1', '/test-api-connection', array(
            'methods' => 'GET',
            'callback' => array($this, 'test_api_connection'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        register_rest_route('ai-developer-assistant/v1', '/update-settings', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_settings'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        register_rest_route('ai-developer-assistant/v1', '/debug-error', array(
            'methods' => 'POST',
            'callback' => array($this, 'debug_error'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        register_rest_route('ai-developer-assistant/v1', '/patch-error', array(
            'methods' => 'POST',
            'callback' => array($this, 'patch_error'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
    }

    /**
     * Check if the user has permission to access the API.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request.
     * @return   bool
     */
    public function check_permissions($request) {
        return current_user_can('manage_options');
    }

    /**
     * Generate code using Anthropic Claude.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request.
     * @return   WP_REST_Response
     */
    public function generate_code($request) {
        $params = $request->get_params();
        
        if (!isset($params['prompt']) || !isset($params['language'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing required parameters.',
            ), 400);
        }
        
        $prompt = sanitize_textarea_field($params['prompt']);
        $language = sanitize_text_field($params['language']);
        $history = isset($params['history']) ? $params['history'] : array();
        
        $response = $this->anthropic_api->generate_code($prompt, $language, $history);
        
        if ($response['success']) {
            return new WP_REST_Response($response, 200);
        } else {
            return new WP_REST_Response($response, 500);
        }
    }

    /**
     * Save a code snippet.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request.
     * @return   WP_REST_Response
     */
    public function save_snippet($request) {
        $params = $request->get_params();
        
        if (!isset($params['title']) || !isset($params['code']) || !isset($params['language'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing required parameters.',
            ), 400);
        }
        
        $title = sanitize_text_field($params['title']);
        $code = $params['code']; // We don't sanitize code as it might break it
        $language = sanitize_text_field($params['language']);
        $description = isset($params['description']) ? sanitize_textarea_field($params['description']) : '';
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_developer_assistant_snippets';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'title' => $title,
                'language' => $language,
                'code' => $code,
                'description' => $description,
                'created_at' => current_time('mysql'),
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );
        
        if ($result) {
            return new WP_REST_Response(array(
                'success' => true,
                'message' => 'Snippet saved successfully.',
                'id' => $wpdb->insert_id,
            ), 200);
        } else {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Failed to save snippet.',
            ), 500);
        }
    }

    /**
     * Get all saved snippets.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request.
     * @return   WP_REST_Response
     */
    public function get_snippets($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_developer_assistant_snippets';
        
        $snippets = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A);
        
        return new WP_REST_Response(array(
            'success' => true,
            'snippets' => $snippets,
        ), 200);
    }

    /**
     * Get prompt history.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request.
     * @return   WP_REST_Response
     */
    public function get_history($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_developer_assistant_history';
        
        $history = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A);
        
        return new WP_REST_Response(array(
            'success' => true,
            'history' => $history,
        ), 200);
    }

    /**
     * Inject PHP code into a file.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request.
     * @return   WP_REST_Response
     */
    public function inject_php_code($request) {
        $params = $request->get_params();
        
        if (!isset($params['code']) || !isset($params['target'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing required parameters.',
            ), 400);
        }
        
        $code = $params['code']; // We don't sanitize code as it might break it
        $target = sanitize_text_field($params['target']);
        
        // Check if PHP code injection is enabled
        $options = get_option('ai_developer_assistant_options');
        if (!isset($options['enable_php_injection']) || !$options['enable_php_injection']) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'PHP code injection is disabled. Enable it in the plugin settings.',
            ), 403);
        }
        
        $result = $this->code_handler->inject_php_code($code, $target);
        
        if ($result['success']) {
            return new WP_REST_Response($result, 200);
        } else {
            return new WP_REST_Response($result, 500);
        }
    }

    /**
     * Export code as a file.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request.
     * @return   WP_REST_Response
     */
    public function export_code($request) {
        $params = $request->get_params();
        
        if (!isset($params['code']) || !isset($params['language']) || !isset($params['filename'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing required parameters.',
            ), 400);
        }
        
        $code = $params['code']; // We don't sanitize code as it might break it
        $language = sanitize_text_field($params['language']);
        $filename = sanitize_file_name($params['filename']);
        
        $result = $this->code_handler->export_code($code, $language, $filename);
        
        if ($result['success']) {
            return new WP_REST_Response($result, 200);
        } else {
            return new WP_REST_Response($result, 500);
        }
    }

    /**
     * Test the API connection.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request.
     * @return   WP_REST_Response
     */
    public function test_api_connection($request) {
        $result = $this->anthropic_api->test_connection();
        
        if ($result['success']) {
            return new WP_REST_Response($result, 200);
        } else {
            return new WP_REST_Response($result, 500);
        }
    }

    /**
     * Delete a code snippet.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request.
     * @return   WP_REST_Response
     */
    public function delete_snippet($request) {
        $params = $request->get_params();
        
        if (!isset($params['id'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing required parameters.',
            ), 400);
        }
        
        $snippet_id = intval($params['id']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_developer_assistant_snippets';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $snippet_id),
            array('%d')
        );
        
        if ($result) {
            return new WP_REST_Response(array(
                'success' => true,
                'message' => 'Snippet deleted successfully.',
            ), 200);
        } else {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Failed to delete snippet.',
            ), 500);
        }
    }
    
    /**
     * Generate a WordPress plugin.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request.
     * @return   WP_REST_Response
     */
    public function generate_plugin($request) {
        $params = $request->get_params();
        
        // Verify nonce
        if (!isset($params['nonce']) || !wp_verify_nonce($params['nonce'], 'ai_developer_assistant_generate_plugin')) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Invalid security token.',
            ), 403);
        }
        
        // Check required parameters
        if (!isset($params['plugin_name']) || !isset($params['plugin_slug']) || !isset($params['plugin_prompt'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing required parameters.',
            ), 400);
        }
        
        // Load the plugin generator
        require_once plugin_dir_path(dirname(__FILE__)) . 'utils/class-ai-developer-assistant-plugin-generator.php';
        $plugin_generator = new AI_Developer_Assistant_Plugin_Generator($this->anthropic_api);
        
        // Generate the plugin
        $result = $plugin_generator->generate_plugin($params);
        
        if ($result['success']) {
            return new WP_REST_Response($result, 200);
        } else {
            return new WP_REST_Response($result, 500);
        }
    }

    /**
     * Update plugin settings.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request.
     * @return   WP_REST_Response
     */
    public function update_settings($request) {
        $params = $request->get_params();
        
        if (!isset($params['settings'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing required parameters.',
            ), 400);
        }
        
        $settings = $params['settings'];
        $current_options = get_option('ai_developer_assistant_options', array());
        
        // Update only the provided settings
        foreach ($settings as $key => $value) {
            $current_options[$key] = $value;
        }
        
        $result = update_option('ai_developer_assistant_options', $current_options);
        
        if ($result) {
            return new WP_REST_Response(array(
                'success' => true,
                'message' => 'Settings updated successfully.',
            ), 200);
        } else {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Failed to update settings.',
            ), 500);
        }
    }
    
    /**
     * Debug an error message using AI.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request.
     * @return   WP_REST_Response
     */
    public function debug_error($request) {
        $params = $request->get_params();
        
        // Verify nonce
        if (!isset($params['nonce']) || !wp_verify_nonce($params['nonce'], 'ai_developer_assistant_debug_error')) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Invalid security token.',
            ), 403);
        }
        
        // Check required parameters
        if (!isset($params['error_message'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing required parameters.',
            ), 400);
        }
        
        // Prepare error data
        $error_data = array(
            'error_message' => $params['error_message'],
            'error_context' => isset($params['error_context']) ? $params['error_context'] : '',
            'error_file' => isset($params['error_file']) ? $params['error_file'] : '',
        );
        
        // Debug the error
        $result = $this->error_debugger->debug_error($error_data);
        
        if ($result['success']) {
            return new WP_REST_Response($result, 200);
        } else {
            return new WP_REST_Response($result, 500);
        }
    }
    
    /**
     * Patch an error in a file.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request.
     * @return   WP_REST_Response
     */
    public function patch_error($request) {
        $params = $request->get_params();
        
        // Verify nonce
        if (!isset($params['nonce']) || !wp_verify_nonce($params['nonce'], 'ai_developer_assistant_debug_error')) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Invalid security token.',
            ), 403);
        }
        
        // Check required parameters
        if (!isset($params['error_file']) || !isset($params['code_fix'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing required parameters.',
            ), 400);
        }
        
        // Patch the error
        $result = $this->error_debugger->patch_error($params['error_file'], $params['code_fix']);
        
        if ($result['success']) {
            return new WP_REST_Response($result, 200);
        } else {
            return new WP_REST_Response($result, 500);
        }
    }
}