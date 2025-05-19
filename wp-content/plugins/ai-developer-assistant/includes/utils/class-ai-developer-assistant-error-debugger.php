<?php
/**
 * Error Debugger Utility
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes/utils
 */

/**
 * Error Debugger Utility
 *
 * This class handles the debugging and explanation of WordPress and PHP errors using AI.
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes/utils
 * @author     OpenHands
 */
class AI_Developer_Assistant_Error_Debugger {

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
     * Debug an error message using AI.
     *
     * @since    1.0.0
     * @param    array    $error_data    The error data.
     * @return   array                   The result of the error debugging.
     */
    public function debug_error($error_data) {
        // Validate error data
        if (empty($error_data['error_message'])) {
            return array(
                'success' => false,
                'message' => 'Missing required error message.',
            );
        }

        // Create the prompt for the AI
        $prompt = $this->create_error_debugging_prompt($error_data);
        
        // Call the Anthropic API
        $response = $this->anthropic_api->generate_code($prompt, 'php');
        
        if (!$response['success']) {
            return $response;
        }
        
        // Parse the AI response to extract the analysis
        $analysis = $this->parse_error_analysis($response['code']);
        
        if (empty($analysis)) {
            return array(
                'success' => false,
                'message' => 'Failed to parse error analysis from AI response.',
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Error analyzed successfully.',
            'explanation' => isset($analysis['explanation']) ? $analysis['explanation'] : '',
            'cause' => isset($analysis['cause']) ? $analysis['cause'] : '',
            'solution' => isset($analysis['solution']) ? $analysis['solution'] : '',
            'code_fix' => isset($analysis['code_fix']) ? $analysis['code_fix'] : '',
            'can_patch' => isset($analysis['can_patch']) ? $analysis['can_patch'] : false,
        );
    }

    /**
     * Create a prompt for error debugging.
     *
     * @since    1.0.0
     * @param    array    $error_data    The error data.
     * @return   string                  The prompt for the AI.
     */
    private function create_error_debugging_prompt($error_data) {
        $error_message = sanitize_textarea_field($error_data['error_message']);
        $error_context = isset($error_data['error_context']) ? sanitize_textarea_field($error_data['error_context']) : '';
        $error_file = isset($error_data['error_file']) ? sanitize_text_field($error_data['error_file']) : '';
        
        $prompt = "You are an expert WordPress and PHP developer. I need you to analyze this error message and provide a detailed explanation and solution.\n\n";
        $prompt .= "ERROR MESSAGE:\n$error_message\n\n";
        
        if (!empty($error_context)) {
            $prompt .= "ADDITIONAL CONTEXT:\n$error_context\n\n";
        }
        
        if (!empty($error_file)) {
            $prompt .= "FILE PATH: $error_file\n\n";
        }
        
        $prompt .= "Please provide your analysis in the following format:\n\n";
        $prompt .= "```explanation\nA clear explanation of what the error means in plain English.\n```\n\n";
        $prompt .= "```cause\nThe likely cause of the error.\n```\n\n";
        $prompt .= "```solution\nStep-by-step instructions on how to fix the error.\n```\n\n";
        
        if (!empty($error_file)) {
            $prompt .= "```code_fix\n// Provide the corrected code here if possible\n```\n\n";
            $prompt .= "```can_patch\ntrue or false depending on whether this can be safely patched automatically\n```\n\n";
        }
        
        $prompt .= "Be thorough in your explanation but use plain language that a developer with basic WordPress knowledge would understand. If you can provide a code fix, make sure it follows WordPress coding standards and best practices.";
        
        return $prompt;
    }

    /**
     * Parse error analysis from AI response.
     *
     * @since    1.0.0
     * @param    string    $response    The AI response.
     * @return   array                  The parsed analysis.
     */
    private function parse_error_analysis($response) {
        $analysis = array();
        
        // Match explanation
        if (preg_match('/```explanation\s*(.*?)\s*```/s', $response, $matches)) {
            $analysis['explanation'] = $this->format_html_content($matches[1]);
        }
        
        // Match cause
        if (preg_match('/```cause\s*(.*?)\s*```/s', $response, $matches)) {
            $analysis['cause'] = $this->format_html_content($matches[1]);
        }
        
        // Match solution
        if (preg_match('/```solution\s*(.*?)\s*```/s', $response, $matches)) {
            $analysis['solution'] = $this->format_html_content($matches[1]);
        }
        
        // Match code fix
        if (preg_match('/```code_fix\s*(.*?)\s*```/s', $response, $matches)) {
            $analysis['code_fix'] = trim($matches[1]);
        }
        
        // Match can patch
        if (preg_match('/```can_patch\s*(.*?)\s*```/s', $response, $matches)) {
            $analysis['can_patch'] = (trim(strtolower($matches[1])) === 'true');
        }
        
        return $analysis;
    }

    /**
     * Format content as HTML.
     *
     * @since    1.0.0
     * @param    string    $content    The content to format.
     * @return   string                The formatted HTML content.
     */
    private function format_html_content($content) {
        // Convert line breaks to paragraphs
        $content = '<p>' . str_replace("\n\n", '</p><p>', $content) . '</p>';
        
        // Convert single line breaks to <br>
        $content = str_replace("\n", '<br>', $content);
        
        // Convert code blocks
        $content = preg_replace('/`(.*?)`/', '<code>$1</code>', $content);
        
        return $content;
    }

    /**
     * Patch an error in a file.
     *
     * @since    1.0.0
     * @param    string    $file_path    The file path.
     * @param    string    $code_fix     The code fix.
     * @return   array                   The result of the patching operation.
     */
    public function patch_error($file_path, $code_fix) {
        // Validate file path
        if (empty($file_path) || !file_exists($file_path)) {
            return array(
                'success' => false,
                'message' => 'Invalid file path or file does not exist.',
            );
        }
        
        // Validate code fix
        if (empty($code_fix)) {
            return array(
                'success' => false,
                'message' => 'No code fix provided.',
            );
        }
        
        // Check if file is writable
        if (!is_writable($file_path)) {
            return array(
                'success' => false,
                'message' => 'File is not writable.',
            );
        }
        
        // Create a backup of the file
        $backup_path = $file_path . '.bak.' . time();
        if (!copy($file_path, $backup_path)) {
            return array(
                'success' => false,
                'message' => 'Failed to create backup of the file.',
            );
        }
        
        // Write the fixed code to the file
        if (file_put_contents($file_path, $code_fix) === false) {
            return array(
                'success' => false,
                'message' => 'Failed to write the fixed code to the file.',
            );
        }
        
        return array(
            'success' => true,
            'message' => 'File patched successfully. A backup was created at ' . $backup_path,
            'backup_path' => $backup_path,
        );
    }
}