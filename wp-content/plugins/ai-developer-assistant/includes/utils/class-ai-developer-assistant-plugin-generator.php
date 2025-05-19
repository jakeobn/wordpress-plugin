<?php
/**
 * Plugin Generator Utility
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes/utils
 */

/**
 * Plugin Generator Utility
 *
 * This class handles the generation of WordPress plugins using AI.
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes/utils
 * @author     OpenHands
 */
class AI_Developer_Assistant_Plugin_Generator {

    /**
     * The Anthropic API handler.
     *
     * @since    1.0.0
     * @access   private
     * @var      AI_Developer_Assistant_Anthropic_API    $anthropic_api    The Anthropic API handler.
     */
    private $anthropic_api;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    AI_Developer_Assistant_Anthropic_API    $anthropic_api    The Anthropic API handler.
     */
    public function __construct($anthropic_api) {
        $this->anthropic_api = $anthropic_api;
    }

    /**
     * Generate a WordPress plugin from a prompt.
     *
     * @since    1.0.0
     * @param    array    $plugin_data    The plugin data.
     * @return   array                    The result of the plugin generation.
     */
    public function generate_plugin($plugin_data) {
        // Validate plugin data
        if (empty($plugin_data['plugin_name']) || empty($plugin_data['plugin_slug']) || empty($plugin_data['plugin_prompt'])) {
            return array(
                'success' => false,
                'message' => 'Missing required plugin data.',
            );
        }

        // Sanitize plugin slug
        $plugin_slug = sanitize_title($plugin_data['plugin_slug']);
        
        // Create the prompt for the AI
        $prompt = $this->create_plugin_generation_prompt($plugin_data);
        
        // Call the Anthropic API
        $response = $this->anthropic_api->generate_code($prompt, 'php');
        
        if (!$response['success']) {
            return $response;
        }
        
        // Parse the AI response to extract files
        $files = $this->parse_plugin_files($response['code']);
        
        if (empty($files)) {
            return array(
                'success' => false,
                'message' => 'Failed to parse plugin files from AI response.',
            );
        }
        
        // Create the plugin directory
        $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
        
        // Check if the plugin directory already exists
        if (file_exists($plugin_dir)) {
            return array(
                'success' => false,
                'message' => 'A plugin with this slug already exists.',
            );
        }
        
        // Create the plugin directory
        if (!mkdir($plugin_dir, 0755, true)) {
            return array(
                'success' => false,
                'message' => 'Failed to create plugin directory.',
            );
        }
        
        // Create the plugin files
        foreach ($files as $file_path => $file_content) {
            $full_path = $plugin_dir . '/' . $file_path;
            
            // Create directories if needed
            $dir_path = dirname($full_path);
            if (!file_exists($dir_path)) {
                mkdir($dir_path, 0755, true);
            }
            
            // Write the file
            file_put_contents($full_path, $file_content);
        }
        
        // Create a ZIP file
        $zip_file = $plugin_dir . '.zip';
        $this->create_zip_archive($plugin_dir, $zip_file);
        
        return array(
            'success' => true,
            'message' => 'Plugin generated successfully.',
            'plugin_dir' => $plugin_dir,
            'plugin_url' => plugins_url($plugin_slug),
            'zip_file' => $zip_file,
            'zip_url' => content_url('plugins/' . $plugin_slug . '.zip'),
            'files' => array_keys($files),
        );
    }

    /**
     * Create a prompt for plugin generation.
     *
     * @since    1.0.0
     * @param    array    $plugin_data    The plugin data.
     * @return   string                   The prompt for the AI.
     */
    private function create_plugin_generation_prompt($plugin_data) {
        $plugin_name = sanitize_text_field($plugin_data['plugin_name']);
        $plugin_slug = sanitize_title($plugin_data['plugin_slug']);
        $plugin_description = sanitize_textarea_field($plugin_data['plugin_description']);
        $plugin_author = sanitize_text_field($plugin_data['plugin_author']);
        $plugin_prompt = sanitize_textarea_field($plugin_data['plugin_prompt']);
        $plugin_components = isset($plugin_data['plugin_components']) ? $plugin_data['plugin_components'] : array();
        
        $prompt = "Generate a complete WordPress plugin with the following details:\n\n";
        $prompt .= "Plugin Name: $plugin_name\n";
        $prompt .= "Plugin Slug: $plugin_slug\n";
        $prompt .= "Description: $plugin_description\n";
        $prompt .= "Author: $plugin_author\n\n";
        $prompt .= "Requirements:\n$plugin_prompt\n\n";
        
        $prompt .= "Components to include:\n";
        if (in_array('settings', $plugin_components)) {
            $prompt .= "- Settings page\n";
        }
        if (in_array('shortcode', $plugin_components)) {
            $prompt .= "- Shortcode functionality\n";
        }
        if (in_array('block', $plugin_components)) {
            $prompt .= "- Gutenberg block\n";
        }
        if (in_array('cpt', $plugin_components)) {
            $prompt .= "- Custom Post Type\n";
        }
        
        $prompt .= "\nPlease generate all necessary files for a complete, working WordPress plugin following WordPress coding standards and best practices. Include proper file headers, activation/deactivation hooks, and security measures.\n\n";
        $prompt .= "For each file, start with a line containing the file path relative to the plugin root, enclosed in triple backticks, followed by the file content, and end with triple backticks. For example:\n\n";
        $prompt .= "```$plugin_slug.php```\n<?php\n// Plugin code here\n```\n\n";
        $prompt .= "```includes/class-example.php```\n<?php\n// Class code here\n```\n\n";
        $prompt .= "Please provide all necessary files for a complete plugin structure.";
        
        return $prompt;
    }

    /**
     * Parse plugin files from AI response.
     *
     * @since    1.0.0
     * @param    string    $response    The AI response.
     * @return   array                  The parsed files.
     */
    private function parse_plugin_files($response) {
        $files = array();
        
        // Match file blocks in the format: ```file_path``` file_content ```
        $pattern = '/```(.*?)```\s*(.*?)```/s';
        preg_match_all($pattern, $response, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            if (count($match) >= 3) {
                $file_path = trim($match[1]);
                $file_content = trim($match[2]);
                
                // Clean up file path
                $file_path = preg_replace('/^["`\']+|["`\']+$/', '', $file_path);
                
                // Add file to the list
                $files[$file_path] = $file_content;
            }
        }
        
        return $files;
    }

    /**
     * Create a ZIP archive of the plugin.
     *
     * @since    1.0.0
     * @param    string    $source_dir    The source directory.
     * @param    string    $zip_file      The ZIP file path.
     * @return   boolean                  Whether the ZIP creation was successful.
     */
    private function create_zip_archive($source_dir, $zip_file) {
        if (!class_exists('ZipArchive')) {
            return false;
        }
        
        $zip = new ZipArchive();
        if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        
        $source_dir = str_replace('\\', '/', realpath($source_dir));
        $plugin_name = basename($source_dir);
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source_dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $file_path = str_replace('\\', '/', $file->getRealPath());
                $relative_path = $plugin_name . '/' . substr($file_path, strlen($source_dir) + 1);
                
                $zip->addFile($file_path, $relative_path);
            }
        }
        
        $zip->close();
        
        return true;
    }
}