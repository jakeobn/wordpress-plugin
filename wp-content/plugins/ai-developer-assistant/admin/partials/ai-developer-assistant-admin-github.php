<?php
/**
 * Admin GitHub integration template
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get plugin options
$options = get_option('ai_developer_assistant_options', array());
$github_token = isset($options['github_access_token']) ? $options['github_access_token'] : '';
$github_connected = !empty($github_token);

// Initialize GitHub API
$github_api = new AI_Developer_Assistant_GitHub_API();
$connection_status = $github_connected ? $github_api->test_connection() : array('success' => false);
$user_info = isset($connection_status['user']) ? $connection_status['user'] : null;
?>

<div class="wrap">
    <div class="ai-developer-assistant-container">
        <div class="ai-developer-assistant-header">
            <h1><?php echo esc_html(get_admin_page_title()); ?> - <?php _e('GitHub Integration', 'ai-developer-assistant'); ?></h1>
        </div>
        
        <div class="ai-developer-assistant-github-integration">
            <p class="ai-developer-assistant-intro">
                <?php _e('Connect your GitHub account to push code snippets as gists, create files in repositories, and fetch code from your repositories.', 'ai-developer-assistant'); ?>
            </p>
            
            <div class="ai-developer-assistant-settings-section">
                <h2><?php _e('GitHub Connection', 'ai-developer-assistant'); ?></h2>
                
                <?php if ($github_connected && $connection_status['success']) : ?>
                    <div class="ai-developer-assistant-connection-status connected">
                        <p>
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php _e('Connected to GitHub as', 'ai-developer-assistant'); ?>
                            <strong><?php echo esc_html($user_info['login']); ?></strong>
                            <?php if (!empty($user_info['name'])) : ?>
                                (<?php echo esc_html($user_info['name']); ?>)
                            <?php endif; ?>
                        </p>
                        <p>
                            <a href="<?php echo esc_url($user_info['html_url']); ?>" target="_blank" class="button">
                                <?php _e('View Profile', 'ai-developer-assistant'); ?>
                            </a>
                            <button type="button" id="ai-developer-assistant-disconnect-github" class="button">
                                <?php _e('Disconnect', 'ai-developer-assistant'); ?>
                            </button>
                        </p>
                    </div>
                <?php else : ?>
                    <div class="ai-developer-assistant-connection-status not-connected">
                        <p>
                            <span class="dashicons dashicons-no"></span>
                            <?php _e('Not connected to GitHub.', 'ai-developer-assistant'); ?>
                            <?php if ($github_connected && !$connection_status['success']) : ?>
                                <span class="error"><?php echo esc_html($connection_status['message']); ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <form id="ai-developer-assistant-github-connect-form">
                        <?php wp_nonce_field('ai_developer_assistant_github_connect', 'ai_developer_assistant_github_nonce'); ?>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-github-token"><?php _e('GitHub Personal Access Token:', 'ai-developer-assistant'); ?></label>
                            <input type="password" id="ai-developer-assistant-github-token" name="github_token" value="<?php echo esc_attr($github_token); ?>" class="regular-text">
                            <p class="description">
                                <?php _e('Create a Personal Access Token with the following scopes: <code>repo</code>, <code>gist</code>.', 'ai-developer-assistant'); ?>
                                <a href="https://github.com/settings/tokens/new" target="_blank"><?php _e('Create Token', 'ai-developer-assistant'); ?></a>
                            </p>
                        </div>
                        
                        <div class="ai-developer-assistant-form-actions">
                            <button type="submit" id="ai-developer-assistant-connect-github" class="button button-primary">
                                <?php _e('Connect to GitHub', 'ai-developer-assistant'); ?>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            
            <?php if ($github_connected && $connection_status['success']) : ?>
                <div class="ai-developer-assistant-settings-section">
                    <h2><?php _e('Your Repositories', 'ai-developer-assistant'); ?></h2>
                    
                    <div class="ai-developer-assistant-loading" style="display: none;">
                        <p><?php _e('Loading repositories...', 'ai-developer-assistant'); ?></p>
                    </div>
                    
                    <div id="ai-developer-assistant-repositories-list"></div>
                    
                    <button type="button" id="ai-developer-assistant-load-repositories" class="button">
                        <?php _e('Load Repositories', 'ai-developer-assistant'); ?>
                    </button>
                </div>
                
                <div class="ai-developer-assistant-settings-section">
                    <h2><?php _e('Your Gists', 'ai-developer-assistant'); ?></h2>
                    
                    <div class="ai-developer-assistant-loading" style="display: none;">
                        <p><?php _e('Loading gists...', 'ai-developer-assistant'); ?></p>
                    </div>
                    
                    <div id="ai-developer-assistant-gists-list"></div>
                    
                    <button type="button" id="ai-developer-assistant-load-gists" class="button">
                        <?php _e('Load Gists', 'ai-developer-assistant'); ?>
                    </button>
                </div>
                
                <div class="ai-developer-assistant-settings-section">
                    <h2><?php _e('Create Gist', 'ai-developer-assistant'); ?></h2>
                    
                    <form id="ai-developer-assistant-create-gist-form">
                        <?php wp_nonce_field('ai_developer_assistant_create_gist', 'ai_developer_assistant_gist_nonce'); ?>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-gist-filename"><?php _e('Filename:', 'ai-developer-assistant'); ?></label>
                            <input type="text" id="ai-developer-assistant-gist-filename" name="gist_filename" class="regular-text" required>
                        </div>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-gist-description"><?php _e('Description:', 'ai-developer-assistant'); ?></label>
                            <input type="text" id="ai-developer-assistant-gist-description" name="gist_description" class="regular-text">
                        </div>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-gist-content"><?php _e('Content:', 'ai-developer-assistant'); ?></label>
                            <textarea id="ai-developer-assistant-gist-content" name="gist_content" rows="10" class="large-text" required></textarea>
                        </div>
                        
                        <div class="ai-developer-assistant-checkbox-field">
                            <input type="checkbox" id="ai-developer-assistant-gist-public" name="gist_public" value="1">
                            <label for="ai-developer-assistant-gist-public"><?php _e('Make this gist public', 'ai-developer-assistant'); ?></label>
                        </div>
                        
                        <div class="ai-developer-assistant-form-actions">
                            <button type="submit" id="ai-developer-assistant-submit-gist" class="button button-primary">
                                <?php _e('Create Gist', 'ai-developer-assistant'); ?>
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="ai-developer-assistant-settings-section">
                    <h2><?php _e('Create File in Repository', 'ai-developer-assistant'); ?></h2>
                    
                    <form id="ai-developer-assistant-create-file-form">
                        <?php wp_nonce_field('ai_developer_assistant_create_file', 'ai_developer_assistant_file_nonce'); ?>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-repo-select"><?php _e('Repository:', 'ai-developer-assistant'); ?></label>
                            <select id="ai-developer-assistant-repo-select" name="repo_full_name" class="regular-text" required>
                                <option value=""><?php _e('Select a repository...', 'ai-developer-assistant'); ?></option>
                            </select>
                            <button type="button" id="ai-developer-assistant-load-repos-for-select" class="button">
                                <?php _e('Load Repositories', 'ai-developer-assistant'); ?>
                            </button>
                        </div>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-file-path"><?php _e('File Path:', 'ai-developer-assistant'); ?></label>
                            <input type="text" id="ai-developer-assistant-file-path" name="file_path" class="regular-text" required>
                            <p class="description"><?php _e('Example: folder/filename.php', 'ai-developer-assistant'); ?></p>
                        </div>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-branch-name"><?php _e('Branch:', 'ai-developer-assistant'); ?></label>
                            <input type="text" id="ai-developer-assistant-branch-name" name="branch_name" class="regular-text" value="main">
                        </div>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-commit-message"><?php _e('Commit Message:', 'ai-developer-assistant'); ?></label>
                            <input type="text" id="ai-developer-assistant-commit-message" name="commit_message" class="regular-text" required>
                        </div>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-file-content"><?php _e('Content:', 'ai-developer-assistant'); ?></label>
                            <textarea id="ai-developer-assistant-file-content" name="file_content" rows="10" class="large-text" required></textarea>
                        </div>
                        
                        <div class="ai-developer-assistant-form-actions">
                            <button type="submit" id="ai-developer-assistant-submit-file" class="button button-primary">
                                <?php _e('Create File', 'ai-developer-assistant'); ?>
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="ai-developer-assistant-settings-section">
                    <h2><?php _e('Fetch File from Repository', 'ai-developer-assistant'); ?></h2>
                    
                    <form id="ai-developer-assistant-fetch-file-form">
                        <?php wp_nonce_field('ai_developer_assistant_fetch_file', 'ai_developer_assistant_fetch_nonce'); ?>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-fetch-repo-select"><?php _e('Repository:', 'ai-developer-assistant'); ?></label>
                            <select id="ai-developer-assistant-fetch-repo-select" name="fetch_repo_full_name" class="regular-text" required>
                                <option value=""><?php _e('Select a repository...', 'ai-developer-assistant'); ?></option>
                            </select>
                        </div>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-fetch-file-path"><?php _e('File Path:', 'ai-developer-assistant'); ?></label>
                            <input type="text" id="ai-developer-assistant-fetch-file-path" name="fetch_file_path" class="regular-text" required>
                            <p class="description"><?php _e('Example: folder/filename.php', 'ai-developer-assistant'); ?></p>
                        </div>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-fetch-branch-name"><?php _e('Branch:', 'ai-developer-assistant'); ?></label>
                            <input type="text" id="ai-developer-assistant-fetch-branch-name" name="fetch_branch_name" class="regular-text" value="main">
                        </div>
                        
                        <div class="ai-developer-assistant-form-actions">
                            <button type="submit" id="ai-developer-assistant-fetch-file" class="button button-primary">
                                <?php _e('Fetch File', 'ai-developer-assistant'); ?>
                            </button>
                        </div>
                    </form>
                    
                    <div id="ai-developer-assistant-fetched-file-container" style="display: none;">
                        <h3><?php _e('Fetched File', 'ai-developer-assistant'); ?></h3>
                        <div class="ai-developer-assistant-fetched-file-info"></div>
                        <div class="ai-developer-assistant-code-container">
                            <div class="ai-developer-assistant-code-header">
                                <span id="ai-developer-assistant-fetched-filename"></span>
                            </div>
                            <div id="ai-developer-assistant-fetched-file-content"></div>
                        </div>
                        <div class="ai-developer-assistant-code-actions">
                            <button type="button" id="ai-developer-assistant-copy-fetched-file" class="button">
                                <?php _e('Copy to Clipboard', 'ai-developer-assistant'); ?>
                            </button>
                            <button type="button" id="ai-developer-assistant-use-in-chat" class="button">
                                <?php _e('Use in Chat', 'ai-developer-assistant'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="ai-developer-assistant-settings-section">
                    <h2><?php _e('View Commit History', 'ai-developer-assistant'); ?></h2>
                    
                    <form id="ai-developer-assistant-commit-history-form">
                        <?php wp_nonce_field('ai_developer_assistant_commit_history', 'ai_developer_assistant_history_nonce'); ?>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-history-repo-select"><?php _e('Repository:', 'ai-developer-assistant'); ?></label>
                            <select id="ai-developer-assistant-history-repo-select" name="history_repo_full_name" class="regular-text" required>
                                <option value=""><?php _e('Select a repository...', 'ai-developer-assistant'); ?></option>
                            </select>
                        </div>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-history-file-path"><?php _e('File Path (Optional):', 'ai-developer-assistant'); ?></label>
                            <input type="text" id="ai-developer-assistant-history-file-path" name="history_file_path" class="regular-text">
                            <p class="description"><?php _e('Leave empty to view commits for the entire repository.', 'ai-developer-assistant'); ?></p>
                        </div>
                        
                        <div class="ai-developer-assistant-form-field">
                            <label for="ai-developer-assistant-history-branch-name"><?php _e('Branch:', 'ai-developer-assistant'); ?></label>
                            <input type="text" id="ai-developer-assistant-history-branch-name" name="history_branch_name" class="regular-text" value="main">
                        </div>
                        
                        <div class="ai-developer-assistant-form-actions">
                            <button type="submit" id="ai-developer-assistant-view-history" class="button button-primary">
                                <?php _e('View Commit History', 'ai-developer-assistant'); ?>
                            </button>
                        </div>
                    </form>
                    
                    <div id="ai-developer-assistant-commit-history-container" style="display: none;">
                        <h3><?php _e('Commit History', 'ai-developer-assistant'); ?></h3>
                        <div id="ai-developer-assistant-commit-history-list"></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>