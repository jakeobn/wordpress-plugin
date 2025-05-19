<?php
/**
 * GitHub API Handler
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes/utils
 */

/**
 * GitHub API Handler
 *
 * This class handles interactions with the GitHub API.
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/includes/utils
 * @author     OpenHands
 */
class AI_Developer_Assistant_GitHub_API {

    /**
     * The GitHub API base URL.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_base_url    The GitHub API base URL.
     */
    private $api_base_url = 'https://api.github.com';

    /**
     * The GitHub Personal Access Token.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $access_token    The GitHub Personal Access Token.
     */
    private $access_token;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $options = get_option('ai_developer_assistant_options', array());
        $this->access_token = isset($options['github_access_token']) ? $options['github_access_token'] : '';
    }

    /**
     * Test the GitHub API connection.
     *
     * @since    1.0.0
     * @return   array    The result of the test.
     */
    public function test_connection() {
        if (empty($this->access_token)) {
            return array(
                'success' => false,
                'message' => 'GitHub Personal Access Token is not set.',
            );
        }

        $response = $this->make_request('GET', '/user');

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            $message = isset($body['message']) ? $body['message'] : 'Unknown error';
            return array(
                'success' => false,
                'message' => "GitHub API Error: $message",
            );
        }

        return array(
            'success' => true,
            'message' => 'Successfully connected to GitHub API.',
            'user' => $body,
        );
    }

    /**
     * Get user repositories.
     *
     * @since    1.0.0
     * @return   array    The user repositories.
     */
    public function get_repositories() {
        if (empty($this->access_token)) {
            return array(
                'success' => false,
                'message' => 'GitHub Personal Access Token is not set.',
            );
        }

        $response = $this->make_request('GET', '/user/repos', array(
            'sort' => 'updated',
            'per_page' => 100,
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            $message = isset($body['message']) ? $body['message'] : 'Unknown error';
            return array(
                'success' => false,
                'message' => "GitHub API Error: $message",
            );
        }

        return array(
            'success' => true,
            'repositories' => $body,
        );
    }

    /**
     * Create a GitHub Gist.
     *
     * @since    1.0.0
     * @param    string    $filename     The filename.
     * @param    string    $content      The content.
     * @param    string    $description  The description.
     * @param    bool      $public       Whether the gist is public.
     * @return   array                   The result of the operation.
     */
    public function create_gist($filename, $content, $description = '', $public = false) {
        if (empty($this->access_token)) {
            return array(
                'success' => false,
                'message' => 'GitHub Personal Access Token is not set.',
            );
        }

        $files = array(
            $filename => array(
                'content' => $content,
            ),
        );

        $data = array(
            'description' => $description,
            'public' => $public,
            'files' => $files,
        );

        $response = $this->make_request('POST', '/gists', $data);

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 201) {
            $message = isset($body['message']) ? $body['message'] : 'Unknown error';
            return array(
                'success' => false,
                'message' => "GitHub API Error: $message",
            );
        }

        return array(
            'success' => true,
            'message' => 'Gist created successfully.',
            'gist' => $body,
            'html_url' => $body['html_url'],
        );
    }

    /**
     * Get user gists.
     *
     * @since    1.0.0
     * @return   array    The user gists.
     */
    public function get_gists() {
        if (empty($this->access_token)) {
            return array(
                'success' => false,
                'message' => 'GitHub Personal Access Token is not set.',
            );
        }

        $response = $this->make_request('GET', '/gists');

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            $message = isset($body['message']) ? $body['message'] : 'Unknown error';
            return array(
                'success' => false,
                'message' => "GitHub API Error: $message",
            );
        }

        return array(
            'success' => true,
            'gists' => $body,
        );
    }

    /**
     * Get a specific gist.
     *
     * @since    1.0.0
     * @param    string    $gist_id    The gist ID.
     * @return   array                 The gist.
     */
    public function get_gist($gist_id) {
        if (empty($this->access_token)) {
            return array(
                'success' => false,
                'message' => 'GitHub Personal Access Token is not set.',
            );
        }

        $response = $this->make_request('GET', "/gists/$gist_id");

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            $message = isset($body['message']) ? $body['message'] : 'Unknown error';
            return array(
                'success' => false,
                'message' => "GitHub API Error: $message",
            );
        }

        return array(
            'success' => true,
            'gist' => $body,
        );
    }

    /**
     * Create a file in a repository.
     *
     * @since    1.0.0
     * @param    string    $repo_full_name    The repository full name (owner/repo).
     * @param    string    $path              The file path.
     * @param    string    $content           The file content.
     * @param    string    $commit_message    The commit message.
     * @param    string    $branch            The branch name.
     * @return   array                        The result of the operation.
     */
    public function create_file($repo_full_name, $path, $content, $commit_message, $branch = 'main') {
        if (empty($this->access_token)) {
            return array(
                'success' => false,
                'message' => 'GitHub Personal Access Token is not set.',
            );
        }

        $data = array(
            'message' => $commit_message,
            'content' => base64_encode($content),
            'branch' => $branch,
        );

        $response = $this->make_request('PUT', "/repos/$repo_full_name/contents/$path", $data);

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 201) {
            $message = isset($body['message']) ? $body['message'] : 'Unknown error';
            return array(
                'success' => false,
                'message' => "GitHub API Error: $message",
            );
        }

        return array(
            'success' => true,
            'message' => 'File created successfully.',
            'content' => $body['content'],
            'commit' => $body['commit'],
        );
    }

    /**
     * Get file content from a repository.
     *
     * @since    1.0.0
     * @param    string    $repo_full_name    The repository full name (owner/repo).
     * @param    string    $path              The file path.
     * @param    string    $branch            The branch name.
     * @return   array                        The file content.
     */
    public function get_file_content($repo_full_name, $path, $branch = 'main') {
        if (empty($this->access_token)) {
            return array(
                'success' => false,
                'message' => 'GitHub Personal Access Token is not set.',
            );
        }

        $response = $this->make_request('GET', "/repos/$repo_full_name/contents/$path", array(
            'ref' => $branch,
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            $message = isset($body['message']) ? $body['message'] : 'Unknown error';
            return array(
                'success' => false,
                'message' => "GitHub API Error: $message",
            );
        }

        // Decode content from base64
        $content = base64_decode($body['content']);

        return array(
            'success' => true,
            'content' => $content,
            'sha' => $body['sha'],
            'size' => $body['size'],
            'name' => $body['name'],
            'path' => $body['path'],
            'url' => $body['html_url'],
        );
    }

    /**
     * Get commit history for a repository.
     *
     * @since    1.0.0
     * @param    string    $repo_full_name    The repository full name (owner/repo).
     * @param    string    $path              The file path (optional).
     * @param    string    $branch            The branch name.
     * @return   array                        The commit history.
     */
    public function get_commit_history($repo_full_name, $path = '', $branch = 'main') {
        if (empty($this->access_token)) {
            return array(
                'success' => false,
                'message' => 'GitHub Personal Access Token is not set.',
            );
        }

        $params = array('sha' => $branch);
        if (!empty($path)) {
            $params['path'] = $path;
        }

        $response = $this->make_request('GET', "/repos/$repo_full_name/commits", $params);

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            $message = isset($body['message']) ? $body['message'] : 'Unknown error';
            return array(
                'success' => false,
                'message' => "GitHub API Error: $message",
            );
        }

        return array(
            'success' => true,
            'commits' => $body,
        );
    }

    /**
     * Make a request to the GitHub API.
     *
     * @since    1.0.0
     * @access   private
     * @param    string    $method     The HTTP method.
     * @param    string    $endpoint   The API endpoint.
     * @param    array     $data       The request data.
     * @return   array|WP_Error        The response or WP_Error on failure.
     */
    private function make_request($method, $endpoint, $data = array()) {
        $url = $this->api_base_url . $endpoint;

        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'token ' . $this->access_token,
                'User-Agent' => 'AI-Developer-Assistant-WordPress-Plugin',
                'Accept' => 'application/vnd.github.v3+json',
            ),
            'timeout' => 30,
        );

        if ($method === 'GET' && !empty($data)) {
            $url = add_query_arg($data, $url);
        } elseif (in_array($method, array('POST', 'PUT', 'PATCH')) && !empty($data)) {
            $args['headers']['Content-Type'] = 'application/json';
            $args['body'] = json_encode($data);
        }

        return wp_remote_request($url, $args);
    }
}