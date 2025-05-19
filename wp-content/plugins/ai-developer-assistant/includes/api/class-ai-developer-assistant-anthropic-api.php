<?php
/**
 * Handles API requests to Anthropic Claude 3.7
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes/api
 */

/**
 * Handles API requests to Anthropic Claude 3.7.
 *
 * This class defines all code necessary to communicate with the Anthropic Claude API.
 *
 * @since      1.0.0
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes/api
 * @author     OpenHands
 */
class AI_Developer_Assistant_Anthropic_API {

    /**
     * The API key for Anthropic Claude.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_key    The API key for Anthropic Claude.
     */
    private $api_key;

    /**
     * The API endpoint for Anthropic Claude.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_endpoint    The API endpoint for Anthropic Claude.
     */
    private $api_endpoint = 'https://api.anthropic.com/v1/messages';

    /**
     * The model to use for Anthropic Claude.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $model    The model to use for Anthropic Claude.
     */
    private $model = 'claude-3-7-sonnet-20240620';

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $options = get_option('ai_developer_assistant_options');
        $this->api_key = isset($options['api_key']) ? $options['api_key'] : '';
    }

    /**
     * Test the API connection.
     *
     * @since    1.0.0
     * @return   array    The response from the API.
     */
    public function test_connection() {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => 'API key is not set. Please configure your Anthropic Claude API key in the settings.',
            );
        }

        $test_prompt = 'Respond with "Connection successful" if you can read this message.';
        $response = $this->generate_code($test_prompt, 'text', array());

        if (isset($response['success']) && $response['success'] === false) {
            return $response;
        }

        return array(
            'success' => true,
            'message' => 'Connection to Anthropic Claude API successful.',
            'response' => $response,
        );
    }

    /**
     * Generate code using Anthropic Claude.
     *
     * @since    1.0.0
     * @param    string    $prompt       The prompt to send to the API.
     * @param    string    $language     The programming language to generate code for.
     * @param    array     $history      Optional. The conversation history. Default empty array.
     * @return   array     The response from the API.
     */
    public function generate_code($prompt, $language, $history = array()) {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => 'API key is not set. Please configure your Anthropic Claude API key in the settings.',
            );
        }

        // Construct the system prompt based on the language
        $system_prompt = $this->get_system_prompt($language);
        
        // Prepare the messages array
        $messages = array();
        
        // Add history messages if provided
        if (!empty($history)) {
            foreach ($history as $entry) {
                $messages[] = array(
                    'role' => $entry['role'],
                    'content' => $entry['content']
                );
            }
        }
        
        // Add the current user message
        $messages[] = array(
            'role' => 'user',
            'content' => $prompt
        );

        // Prepare the request body
        $body = array(
            'model' => $this->model,
            'messages' => $messages,
            'system' => $system_prompt,
            'max_tokens' => 4000,
            'temperature' => 0.2, // Lower temperature for more deterministic code generation
        );

        // Make the API request
        $response = wp_remote_post(
            $this->api_endpoint,
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->api_key,
                    'anthropic-version' => '2023-06-01',
                ),
                'body' => json_encode($body),
                'timeout' => 60, // Increase timeout for longer responses
            )
        );

        // Check for errors
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        // Parse the response
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code !== 200) {
            return array(
                'success' => false,
                'message' => isset($response_body['error']['message']) ? $response_body['error']['message'] : 'Unknown error occurred.',
                'code' => $response_code,
            );
        }

        // Extract the code from the response
        $content = $response_body['content'][0]['text'];
        
        // Save to history if it's a code generation request
        if ($language !== 'text') {
            $this->save_to_history($prompt, $content, $language);
        }

        return array(
            'success' => true,
            'content' => $content,
            'language' => $language,
        );
    }

    /**
     * Get the system prompt based on the language.
     *
     * @since    1.0.0
     * @param    string    $language    The programming language.
     * @return   string    The system prompt.
     */
    private function get_system_prompt($language) {
        $base_prompt = "You are an expert developer assistant specializing in generating high-quality, well-documented code. ";
        
        switch ($language) {
            case 'php':
                return $base_prompt . "Generate only PHP code with clear comments. Follow WordPress best practices and coding standards. Do not include explanations outside of code comments. Ensure the code is secure, efficient, and properly documented with PHPDoc comments where appropriate.";
            
            case 'javascript':
                return $base_prompt . "Generate only JavaScript code with clear comments. Follow modern ES6+ best practices. Do not include explanations outside of code comments. Ensure the code is secure, efficient, and properly documented with JSDoc comments where appropriate.";
            
            case 'python':
                return $base_prompt . "Generate only Python code with clear comments. Follow PEP 8 style guidelines. Do not include explanations outside of code comments. Ensure the code is secure, efficient, and properly documented with docstrings where appropriate.";
            
            case 'nodejs':
                return $base_prompt . "Generate only Node.js code with clear comments. Follow modern ES6+ best practices. Do not include explanations outside of code comments. Ensure the code is secure, efficient, and properly documented with JSDoc comments where appropriate.";
            
            case 'text':
                return "You are a helpful AI assistant that provides clear and concise responses.";
            
            default:
                return $base_prompt . "Generate only code with clear comments. Do not include explanations outside of code comments. Ensure the code is secure, efficient, and properly documented.";
        }
    }

    /**
     * Save the prompt and response to history.
     *
     * @since    1.0.0
     * @param    string    $prompt      The prompt sent to the API.
     * @param    string    $response    The response from the API.
     * @param    string    $language    The programming language.
     */
    private function save_to_history($prompt, $response, $language) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_developer_assistant_history';
        
        $wpdb->insert(
            $table_name,
            array(
                'prompt' => $prompt,
                'response' => $response,
                'language' => $language,
                'created_at' => current_time('mysql'),
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );
        
        // Get the options
        $options = get_option('ai_developer_assistant_options');
        $max_history_items = isset($options['max_history_items']) ? intval($options['max_history_items']) : 50;
        
        // Delete old history items if we exceed the maximum
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($count > $max_history_items) {
            $to_delete = $count - $max_history_items;
            $wpdb->query("DELETE FROM $table_name ORDER BY created_at ASC LIMIT $to_delete");
        }
    }
}