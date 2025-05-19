<?php
/**
 * Handles code injection and file operations
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes/utils
 */

/**
 * Handles code injection and file operations.
 *
 * This class defines all code necessary to handle code injection and file operations.
 *
 * @since      1.0.0
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes/utils
 * @author     OpenHands
 */
class AI_Developer_Assistant_Code_Handler {

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Nothing to initialize
    }

    /**
     * Inject PHP code into a file.
     *
     * @since    1.0.0
     * @param    string    $code      The PHP code to inject.
     * @param    string    $target    The target file or location ('functions', 'new_plugin', 'custom_file').
     * @return   array     The result of the operation.
     */
    public function inject_php_code($code, $target) {
        // Validate the PHP code
        if (!$this->validate_php_code($code)) {
            return array(
                'success' => false,
                'message' => 'Invalid PHP code. Please check for syntax errors.',
            );
        }

        // Determine the target file path
        $file_path = '';
        $is_new_file = false;
        
        switch ($target) {
            case 'functions':
                $theme_dir = get_template_directory();
                $file_path = $theme_dir . '/functions.php';
                break;
                
            case 'new_plugin':
                // Generate a unique plugin file name based on timestamp
                $timestamp = time();
                $plugin_dir = WP_PLUGIN_DIR;
                $plugin_name = 'ai-generated-plugin-' . $timestamp;
                $plugin_dir_path = $plugin_dir . '/' . $plugin_name;
                
                // Create the plugin directory if it doesn't exist
                if (!file_exists($plugin_dir_path)) {
                    mkdir($plugin_dir_path, 0755, true);
                }
                
                $file_path = $plugin_dir_path . '/' . $plugin_name . '.php';
                $is_new_file = true;
                
                // Add plugin header if it's a new plugin
                $plugin_header = "<?php\n/**\n * Plugin Name: AI Generated Plugin $timestamp\n * Description: Automatically generated plugin by AI Developer Assistant\n * Version: 1.0.0\n * Author: AI Developer Assistant\n */\n\n";
                $code = $plugin_header . $code;
                break;
                
            case 'custom_file':
                // If a custom file path is provided in the code as a comment
                if (preg_match('/\/\*\s*Target:\s*([^\*]+)\s*\*\//', $code, $matches)) {
                    $file_path = trim($matches[1]);
                    // Remove the target comment from the code
                    $code = preg_replace('/\/\*\s*Target:\s*([^\*]+)\s*\*\//', '', $code);
                } else {
                    // Default to a new file in the uploads directory
                    $upload_dir = wp_upload_dir();
                    $timestamp = time();
                    $file_path = $upload_dir['basedir'] . '/ai-generated-code-' . $timestamp . '.php';
                }
                
                // Check if the file exists
                $is_new_file = !file_exists($file_path);
                break;
                
            default:
                return array(
                    'success' => false,
                    'message' => 'Invalid target specified.',
                );
        }
        
        // Ensure the file path is valid
        if (empty($file_path)) {
            return array(
                'success' => false,
                'message' => 'Invalid file path.',
            );
        }
        
        // Create a backup of the existing file if it's not a new file
        if (!$is_new_file) {
            $backup_result = $this->create_backup($file_path);
            if (!$backup_result['success']) {
                return $backup_result;
            }
        }
        
        // Write the code to the file
        if ($is_new_file) {
            // For new files, just write the code
            $result = file_put_contents($file_path, $code);
        } else {
            // For existing files, append the code
            $existing_code = file_get_contents($file_path);
            
            // Remove closing PHP tag if it exists
            $existing_code = rtrim($existing_code);
            if (substr($existing_code, -2) === '?>') {
                $existing_code = substr($existing_code, 0, -2);
            }
            
            // Add a newline separator
            $existing_code .= "\n\n// Code added by AI Developer Assistant\n";
            
            // Append the new code
            $result = file_put_contents($file_path, $existing_code . $code);
        }
        
        if ($result === false) {
            return array(
                'success' => false,
                'message' => 'Failed to write code to file. Check file permissions.',
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Code successfully injected into ' . basename($file_path),
            'file_path' => $file_path,
            'is_new_file' => $is_new_file,
        );
    }

    /**
     * Export code as a file.
     *
     * @since    1.0.0
     * @param    string    $code       The code to export.
     * @param    string    $language   The programming language.
     * @param    string    $filename   The filename to use.
     * @return   array     The result of the operation.
     */
    public function export_code($code, $language, $filename) {
        // Determine the file extension based on the language
        $extension = $this->get_file_extension($language);
        
        // Ensure the filename has the correct extension
        if (pathinfo($filename, PATHINFO_EXTENSION) !== $extension) {
            $filename .= '.' . $extension;
        }
        
        // Create the uploads directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/ai-developer-assistant-exports';
        
        if (!file_exists($export_dir)) {
            mkdir($export_dir, 0755, true);
        }
        
        // Full path to the file
        $file_path = $export_dir . '/' . sanitize_file_name($filename);
        
        // Write the code to the file
        $result = file_put_contents($file_path, $code);
        
        if ($result === false) {
            return array(
                'success' => false,
                'message' => 'Failed to write code to file. Check file permissions.',
            );
        }
        
        // Generate the download URL
        $file_url = $upload_dir['baseurl'] . '/ai-developer-assistant-exports/' . sanitize_file_name($filename);
        
        return array(
            'success' => true,
            'message' => 'Code successfully exported as ' . $filename,
            'file_path' => $file_path,
            'file_url' => $file_url,
            'filename' => $filename,
        );
    }

    /**
     * Validate PHP code for syntax errors.
     *
     * @since    1.0.0
     * @param    string    $code    The PHP code to validate.
     * @return   bool      Whether the code is valid.
     */
    private function validate_php_code($code) {
        // Create a temporary file
        $temp_file = tempnam(sys_get_temp_dir(), 'php_validate_');
        file_put_contents($temp_file, $code);
        
        // Use PHP's built-in syntax check
        $output = array();
        $return_var = 0;
        exec('php -l ' . escapeshellarg($temp_file) . ' 2>&1', $output, $return_var);
        
        // Clean up the temporary file
        unlink($temp_file);
        
        // Return true if the syntax is valid (return_var = 0)
        return $return_var === 0;
    }

    /**
     * Create a backup of a file.
     *
     * @since    1.0.0
     * @param    string    $file_path    The path to the file.
     * @return   array     The result of the operation.
     */
    private function create_backup($file_path) {
        if (!file_exists($file_path)) {
            return array(
                'success' => false,
                'message' => 'File does not exist: ' . $file_path,
            );
        }
        
        // Create the backups directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/ai-developer-assistant-backups';
        
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        // Generate a unique backup filename
        $timestamp = time();
        $backup_filename = basename($file_path) . '.' . $timestamp . '.bak';
        $backup_path = $backup_dir . '/' . $backup_filename;
        
        // Copy the file to the backup location
        if (!copy($file_path, $backup_path)) {
            return array(
                'success' => false,
                'message' => 'Failed to create backup of ' . basename($file_path),
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Backup created successfully: ' . $backup_filename,
            'backup_path' => $backup_path,
        );
    }

    /**
     * Get the file extension for a programming language.
     *
     * @since    1.0.0
     * @param    string    $language    The programming language.
     * @return   string    The file extension.
     */
    private function get_file_extension($language) {
        switch ($language) {
            case 'php':
                return 'php';
            case 'javascript':
                return 'js';
            case 'python':
                return 'py';
            case 'nodejs':
                return 'js';
            default:
                return 'txt';
        }
    }
}