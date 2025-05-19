<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks for
 * enqueuing the admin-specific stylesheet and JavaScript.
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/admin
 * @author     OpenHands
 */
class AI_Developer_Assistant_Admin {

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
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        $screen = get_current_screen();
        if (strpos($screen->id, 'ai-developer-assistant') !== false) {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ai-developer-assistant-admin.css', array(), $this->version, 'all');
            
            // CodeMirror for syntax highlighting
            wp_enqueue_style('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css', array(), '5.65.2');
            wp_enqueue_style('codemirror-theme', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css', array(), '5.65.2');
            
            // Dashicons for GitHub integration
            wp_enqueue_style('dashicons');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        if (strpos($screen->id, 'ai-developer-assistant') !== false) {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ai-developer-assistant-admin.js', array('jquery'), $this->version, false);
            
            // CodeMirror for syntax highlighting
            wp_enqueue_script('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js', array(), '5.65.2', true);
            wp_enqueue_script('codemirror-mode-php', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js', array('codemirror'), '5.65.2', true);
            wp_enqueue_script('codemirror-mode-javascript', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js', array('codemirror'), '5.65.2', true);
            wp_enqueue_script('codemirror-mode-python', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/python/python.min.js', array('codemirror'), '5.65.2', true);
            
            // Plugin generator script (only on the generate plugin page)
            if (isset($_GET['page']) && $_GET['page'] === 'ai-developer-assistant-generate-plugin') {
                wp_enqueue_script($this->plugin_name . '-plugin-generator', plugin_dir_url(__FILE__) . 'js/ai-developer-assistant-plugin-generator.js', array('jquery'), $this->version, true);
            }
            
            // Error debugger script (only on the error debugger page)
            if (isset($_GET['page']) && $_GET['page'] === 'ai-developer-assistant-error-debugger') {
                wp_enqueue_script($this->plugin_name . '-error-debugger', plugin_dir_url(__FILE__) . 'js/ai-developer-assistant-error-debugger.js', array('jquery', 'codemirror'), $this->version, true);
            }
            
            // GitHub integration script (only on the GitHub page)
            if (isset($_GET['page']) && $_GET['page'] === 'ai-developer-assistant-github') {
                wp_enqueue_script($this->plugin_name . '-github', plugin_dir_url(__FILE__) . 'js/ai-developer-assistant-github.js', array('jquery', 'codemirror'), $this->version, true);
                
                // Additional CodeMirror modes for GitHub integration
                wp_enqueue_script('codemirror-mode-htmlmixed', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js', array('codemirror'), '5.65.2', true);
                wp_enqueue_script('codemirror-mode-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js', array('codemirror'), '5.65.2', true);
                wp_enqueue_script('codemirror-mode-xml', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js', array('codemirror'), '5.65.2', true);
                wp_enqueue_script('codemirror-mode-markdown', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/markdown/markdown.min.js', array('codemirror'), '5.65.2', true);
                wp_enqueue_script('codemirror-mode-sql', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/sql/sql.min.js', array('codemirror'), '5.65.2', true);
                wp_enqueue_script('codemirror-mode-shell', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/shell/shell.min.js', array('codemirror'), '5.65.2', true);
            }
            
            // Localize script with plugin data
            wp_localize_script($this->plugin_name, 'aiDevAssistant', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'rest_url' => rest_url('ai-developer-assistant/v1'),
                'nonce' => wp_create_nonce('wp_rest'),
                'plugin_url' => plugin_dir_url(__FILE__),
            ));
            
            // Also localize the plugin generator script
            if (isset($_GET['page']) && $_GET['page'] === 'ai-developer-assistant-generate-plugin') {
                wp_localize_script($this->plugin_name . '-plugin-generator', 'aiDevAssistant', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'rest_url' => rest_url('ai-developer-assistant/v1'),
                    'nonce' => wp_create_nonce('wp_rest'),
                    'plugin_url' => plugin_dir_url(__FILE__),
                ));
            }
        }
    }

    /**
     * Add menu items to the admin dashboard.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        // Main menu item
        add_menu_page(
            __('AI Developer Assistant', 'ai-developer-assistant'),
            __('AI Developer Assistant', 'ai-developer-assistant'),
            'manage_options',
            'ai-developer-assistant',
            array($this, 'display_chat_interface_page'),
            'dashicons-code-standards',
            30
        );
        
        // Chat Interface submenu
        add_submenu_page(
            'ai-developer-assistant',
            __('Chat Interface', 'ai-developer-assistant'),
            __('Chat Interface', 'ai-developer-assistant'),
            'manage_options',
            'ai-developer-assistant',
            array($this, 'display_chat_interface_page')
        );
        
        // Generate Plugin submenu
        add_submenu_page(
            'ai-developer-assistant',
            __('Generate Plugin', 'ai-developer-assistant'),
            __('Generate Plugin', 'ai-developer-assistant'),
            'manage_options',
            'ai-developer-assistant-generate-plugin',
            array($this, 'display_generate_plugin_page')
        );
        
        // Error Debugger submenu
        add_submenu_page(
            'ai-developer-assistant',
            __('Error Debugger', 'ai-developer-assistant'),
            __('Error Debugger', 'ai-developer-assistant'),
            'manage_options',
            'ai-developer-assistant-error-debugger',
            array($this, 'display_error_debugger_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'ai-developer-assistant',
            __('Settings', 'ai-developer-assistant'),
            __('Settings', 'ai-developer-assistant'),
            'manage_options',
            'ai-developer-assistant-settings',
            array($this, 'display_settings_page')
        );
        
        // Saved Snippets submenu
        add_submenu_page(
            'ai-developer-assistant',
            __('Saved Snippets', 'ai-developer-assistant'),
            __('Saved Snippets', 'ai-developer-assistant'),
            'manage_options',
            'ai-developer-assistant-snippets',
            array($this, 'display_snippets_page')
        );
        
        // GitHub Integration submenu
        add_submenu_page(
            'ai-developer-assistant',
            __('GitHub Integration', 'ai-developer-assistant'),
            __('GitHub Integration', 'ai-developer-assistant'),
            'manage_options',
            'ai-developer-assistant-github',
            array($this, 'display_github_page')
        );
    }

    /**
     * Display the chat interface page.
     *
     * @since    1.0.0
     */
    public function display_chat_interface_page() {
        include_once 'partials/ai-developer-assistant-admin-chat.php';
    }

    /**
     * Display the settings page.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        include_once 'partials/ai-developer-assistant-admin-settings.php';
    }

    /**
     * Display the saved snippets page.
     *
     * @since    1.0.0
     */
    public function display_snippets_page() {
        include_once 'partials/ai-developer-assistant-admin-snippets.php';
    }
    
    /**
     * Display the generate plugin page.
     *
     * @since    1.0.0
     */
    public function display_generate_plugin_page() {
        include_once 'partials/ai-developer-assistant-admin-generate-plugin.php';
    }
    
    /**
     * Display the error debugger page.
     *
     * @since    1.0.0
     */
    public function display_error_debugger_page() {
        include_once 'partials/ai-developer-assistant-admin-error-debugger.php';
    }
    
    /**
     * Display the GitHub integration page.
     *
     * @since    1.0.0
     */
    public function display_github_page() {
        include_once 'partials/ai-developer-assistant-admin-github.php';
    }
    
    /**
     * Register AJAX handlers for GitHub integration.
     *
     * @since    1.0.0
     */
    public function register_ajax_handlers() {
        // GitHub integration AJAX handlers
        add_action('wp_ajax_ai_developer_assistant_github_connect', array($this, 'ajax_github_connect'));
        add_action('wp_ajax_ai_developer_assistant_github_disconnect', array($this, 'ajax_github_disconnect'));
        add_action('wp_ajax_ai_developer_assistant_get_repositories', array($this, 'ajax_get_repositories'));
        add_action('wp_ajax_ai_developer_assistant_get_gists', array($this, 'ajax_get_gists'));
        add_action('wp_ajax_ai_developer_assistant_get_gist', array($this, 'ajax_get_gist'));
        add_action('wp_ajax_ai_developer_assistant_create_gist', array($this, 'ajax_create_gist'));
        add_action('wp_ajax_ai_developer_assistant_create_file', array($this, 'ajax_create_file'));
        add_action('wp_ajax_ai_developer_assistant_get_file_content', array($this, 'ajax_get_file_content'));
        add_action('wp_ajax_ai_developer_assistant_get_commit_history', array($this, 'ajax_get_commit_history'));
    }
    
    /**
     * AJAX handler for connecting to GitHub.
     *
     * @since    1.0.0
     */
    public function ajax_github_connect() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_developer_assistant_github_connect')) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Check if token is provided
        if (!isset($_POST['github_token']) || empty($_POST['github_token'])) {
            wp_send_json_error(array('message' => 'GitHub token is required.'));
        }
        
        // Save token to options
        $options = get_option('ai_developer_assistant_options', array());
        $options['github_access_token'] = sanitize_text_field($_POST['github_token']);
        update_option('ai_developer_assistant_options', $options);
        
        // Test connection
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-ai-developer-assistant-github-api.php';
        $github_api = new AI_Developer_Assistant_GitHub_API();
        $result = $github_api->test_connection();
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => 'Successfully connected to GitHub.',
                'user' => $result['user'],
            ));
        } else {
            // If connection failed, remove token
            $options['github_access_token'] = '';
            update_option('ai_developer_assistant_options', $options);
            
            wp_send_json_error(array('message' => $result['message']));
        }
    }
    
    /**
     * AJAX handler for disconnecting from GitHub.
     *
     * @since    1.0.0
     */
    public function ajax_github_disconnect() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_developer_assistant_github_connect')) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Remove token from options
        $options = get_option('ai_developer_assistant_options', array());
        $options['github_access_token'] = '';
        update_option('ai_developer_assistant_options', $options);
        
        wp_send_json_success(array('message' => 'Successfully disconnected from GitHub.'));
    }
    
    /**
     * AJAX handler for getting repositories.
     *
     * @since    1.0.0
     */
    public function ajax_get_repositories() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_developer_assistant_github_connect')) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Get repositories
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-ai-developer-assistant-github-api.php';
        $github_api = new AI_Developer_Assistant_GitHub_API();
        $result = $github_api->get_repositories();
        
        if ($result['success']) {
            wp_send_json_success(array(
                'repositories' => $result['repositories'],
            ));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
    
    /**
     * AJAX handler for getting gists.
     *
     * @since    1.0.0
     */
    public function ajax_get_gists() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_developer_assistant_github_connect')) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Get gists
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-ai-developer-assistant-github-api.php';
        $github_api = new AI_Developer_Assistant_GitHub_API();
        $result = $github_api->get_gists();
        
        if ($result['success']) {
            wp_send_json_success(array(
                'gists' => $result['gists'],
            ));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
    
    /**
     * AJAX handler for getting a specific gist.
     *
     * @since    1.0.0
     */
    public function ajax_get_gist() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_developer_assistant_github_connect')) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Check if gist ID is provided
        if (!isset($_POST['gist_id']) || empty($_POST['gist_id'])) {
            wp_send_json_error(array('message' => 'Gist ID is required.'));
        }
        
        // Get gist
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-ai-developer-assistant-github-api.php';
        $github_api = new AI_Developer_Assistant_GitHub_API();
        $result = $github_api->get_gist(sanitize_text_field($_POST['gist_id']));
        
        if ($result['success']) {
            wp_send_json_success(array(
                'gist' => $result['gist'],
            ));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
    
    /**
     * AJAX handler for creating a gist.
     *
     * @since    1.0.0
     */
    public function ajax_create_gist() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_developer_assistant_create_gist')) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Check required fields
        if (!isset($_POST['filename']) || empty($_POST['filename']) || !isset($_POST['content']) || empty($_POST['content'])) {
            wp_send_json_error(array('message' => 'Filename and content are required.'));
        }
        
        // Create gist
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-ai-developer-assistant-github-api.php';
        $github_api = new AI_Developer_Assistant_GitHub_API();
        $result = $github_api->create_gist(
            sanitize_file_name($_POST['filename']),
            $_POST['content'], // Don't sanitize content as it might break code
            isset($_POST['description']) ? sanitize_text_field($_POST['description']) : '',
            isset($_POST['public']) && $_POST['public'] === 'true'
        );
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => 'Gist created successfully.',
                'gist' => $result['gist'],
                'html_url' => $result['html_url'],
            ));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
    
    /**
     * AJAX handler for creating a file in a repository.
     *
     * @since    1.0.0
     */
    public function ajax_create_file() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_developer_assistant_create_file')) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Check required fields
        if (!isset($_POST['repo_full_name']) || empty($_POST['repo_full_name']) ||
            !isset($_POST['file_path']) || empty($_POST['file_path']) ||
            !isset($_POST['content']) || empty($_POST['content']) ||
            !isset($_POST['commit_message']) || empty($_POST['commit_message'])) {
            wp_send_json_error(array('message' => 'Repository, file path, content, and commit message are required.'));
        }
        
        // Create file
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-ai-developer-assistant-github-api.php';
        $github_api = new AI_Developer_Assistant_GitHub_API();
        $result = $github_api->create_file(
            sanitize_text_field($_POST['repo_full_name']),
            sanitize_text_field($_POST['file_path']),
            $_POST['content'], // Don't sanitize content as it might break code
            sanitize_text_field($_POST['commit_message']),
            isset($_POST['branch_name']) ? sanitize_text_field($_POST['branch_name']) : 'main'
        );
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => 'File created successfully.',
                'content' => $result['content'],
                'commit' => $result['commit'],
            ));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
    
    /**
     * AJAX handler for getting file content from a repository.
     *
     * @since    1.0.0
     */
    public function ajax_get_file_content() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_developer_assistant_fetch_file')) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Check required fields
        if (!isset($_POST['repo_full_name']) || empty($_POST['repo_full_name']) ||
            !isset($_POST['file_path']) || empty($_POST['file_path'])) {
            wp_send_json_error(array('message' => 'Repository and file path are required.'));
        }
        
        // Get file content
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-ai-developer-assistant-github-api.php';
        $github_api = new AI_Developer_Assistant_GitHub_API();
        $result = $github_api->get_file_content(
            sanitize_text_field($_POST['repo_full_name']),
            sanitize_text_field($_POST['file_path']),
            isset($_POST['branch_name']) ? sanitize_text_field($_POST['branch_name']) : 'main'
        );
        
        if ($result['success']) {
            wp_send_json_success(array(
                'content' => $result['content'],
                'sha' => $result['sha'],
                'size' => $result['size'],
                'name' => $result['name'],
                'path' => $result['path'],
                'url' => $result['url'],
            ));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
    
    /**
     * AJAX handler for getting commit history.
     *
     * @since    1.0.0
     */
    public function ajax_get_commit_history() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_developer_assistant_commit_history')) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Check required fields
        if (!isset($_POST['repo_full_name']) || empty($_POST['repo_full_name'])) {
            wp_send_json_error(array('message' => 'Repository is required.'));
        }
        
        // Get commit history
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-ai-developer-assistant-github-api.php';
        $github_api = new AI_Developer_Assistant_GitHub_API();
        $result = $github_api->get_commit_history(
            sanitize_text_field($_POST['repo_full_name']),
            isset($_POST['file_path']) ? sanitize_text_field($_POST['file_path']) : '',
            isset($_POST['branch_name']) ? sanitize_text_field($_POST['branch_name']) : 'main'
        );
        
        if ($result['success']) {
            wp_send_json_success(array(
                'commits' => $result['commits'],
            ));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
}