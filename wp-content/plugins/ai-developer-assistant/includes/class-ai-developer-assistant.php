<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes
 * @author     OpenHands
 */

class AI_Developer_Assistant {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      AI_Developer_Assistant_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('AI_DEVELOPER_ASSISTANT_VERSION')) {
            $this->version = AI_DEVELOPER_ASSISTANT_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'ai-developer-assistant';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_api_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ai-developer-assistant-loader.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ai-developer-assistant-admin.php';

        /**
         * The class responsible for handling API requests to Anthropic Claude.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/api/class-ai-developer-assistant-anthropic-api.php';

        /**
         * The class responsible for code injection and file operations.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-ai-developer-assistant-code-handler.php';

        /**
         * The class responsible for generating WordPress plugins.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-ai-developer-assistant-plugin-generator.php';
        
        /**
         * The class responsible for debugging and explaining errors.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-ai-developer-assistant-error-debugger.php';

        /**
         * The class responsible for defining REST API endpoints.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/api/class-ai-developer-assistant-rest-api.php';

        $this->loader = new AI_Developer_Assistant_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new AI_Developer_Assistant_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('init', $plugin_admin, 'register_ajax_handlers');
    }

    /**
     * Register all of the hooks related to the API functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_api_hooks() {
        $plugin_api = new AI_Developer_Assistant_REST_API($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('rest_api_init', $plugin_api, 'register_routes');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    AI_Developer_Assistant_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}