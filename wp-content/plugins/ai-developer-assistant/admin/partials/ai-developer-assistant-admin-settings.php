<?php
/**
 * Admin settings template
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
$api_key = isset($options['api_key']) ? $options['api_key'] : '';
$github_access_token = isset($options['github_access_token']) ? $options['github_access_token'] : '';
$enable_php_injection = isset($options['enable_php_injection']) ? $options['enable_php_injection'] : false;
$default_language = isset($options['default_language']) ? $options['default_language'] : 'php';
$enabled_languages = isset($options['enabled_languages']) ? $options['enabled_languages'] : array('php', 'javascript', 'python', 'nodejs');
$max_history_items = isset($options['max_history_items']) ? intval($options['max_history_items']) : 50;
?>

<div class="wrap">
    <div class="ai-developer-assistant-container">
        <div class="ai-developer-assistant-header">
            <h1><?php echo esc_html(get_admin_page_title()); ?> - <?php _e('Settings', 'ai-developer-assistant'); ?></h1>
        </div>
        
        <div class="ai-developer-assistant-settings">
            <form id="ai-developer-assistant-settings-form" class="ai-developer-assistant-settings-form">
                <div class="ai-developer-assistant-settings-section">
                    <h2><?php _e('API Configuration', 'ai-developer-assistant'); ?></h2>
                    
                    <div class="ai-developer-assistant-form-field">
                        <label for="ai-developer-assistant-api-key"><?php _e('Anthropic Claude API Key:', 'ai-developer-assistant'); ?></label>
                        <input type="password" id="ai-developer-assistant-api-key" name="api_key" value="<?php echo esc_attr($api_key); ?>" autocomplete="off">
                        <p class="description"><?php _e('Enter your Anthropic Claude API key. You can get one from <a href="https://console.anthropic.com/" target="_blank">Anthropic Console</a>.', 'ai-developer-assistant'); ?></p>
                    </div>
                    
                    <button type="button" id="ai-developer-assistant-test-api" class="button"><?php _e('Test Connection', 'ai-developer-assistant'); ?></button>
                </div>
                
                <div class="ai-developer-assistant-settings-section">
                    <h2><?php _e('Code Generation Settings', 'ai-developer-assistant'); ?></h2>
                    
                    <div class="ai-developer-assistant-form-field">
                        <label for="ai-developer-assistant-default-language"><?php _e('Default Language:', 'ai-developer-assistant'); ?></label>
                        <select id="ai-developer-assistant-default-language" name="default_language">
                            <option value="php" <?php selected($default_language, 'php'); ?>><?php _e('PHP', 'ai-developer-assistant'); ?></option>
                            <option value="javascript" <?php selected($default_language, 'javascript'); ?>><?php _e('JavaScript', 'ai-developer-assistant'); ?></option>
                            <option value="python" <?php selected($default_language, 'python'); ?>><?php _e('Python', 'ai-developer-assistant'); ?></option>
                            <option value="nodejs" <?php selected($default_language, 'nodejs'); ?>><?php _e('Node.js', 'ai-developer-assistant'); ?></option>
                        </select>
                    </div>
                    
                    <div class="ai-developer-assistant-form-field">
                        <label><?php _e('Enabled Languages:', 'ai-developer-assistant'); ?></label>
                        
                        <div class="ai-developer-assistant-checkbox-field">
                            <input type="checkbox" id="ai-developer-assistant-language-php" class="ai-developer-assistant-language-checkbox" name="enabled_languages[]" value="php" <?php checked(in_array('php', $enabled_languages)); ?>>
                            <label for="ai-developer-assistant-language-php"><?php _e('PHP', 'ai-developer-assistant'); ?></label>
                        </div>
                        
                        <div class="ai-developer-assistant-checkbox-field">
                            <input type="checkbox" id="ai-developer-assistant-language-javascript" class="ai-developer-assistant-language-checkbox" name="enabled_languages[]" value="javascript" <?php checked(in_array('javascript', $enabled_languages)); ?>>
                            <label for="ai-developer-assistant-language-javascript"><?php _e('JavaScript', 'ai-developer-assistant'); ?></label>
                        </div>
                        
                        <div class="ai-developer-assistant-checkbox-field">
                            <input type="checkbox" id="ai-developer-assistant-language-python" class="ai-developer-assistant-language-checkbox" name="enabled_languages[]" value="python" <?php checked(in_array('python', $enabled_languages)); ?>>
                            <label for="ai-developer-assistant-language-python"><?php _e('Python', 'ai-developer-assistant'); ?></label>
                        </div>
                        
                        <div class="ai-developer-assistant-checkbox-field">
                            <input type="checkbox" id="ai-developer-assistant-language-nodejs" class="ai-developer-assistant-language-checkbox" name="enabled_languages[]" value="nodejs" <?php checked(in_array('nodejs', $enabled_languages)); ?>>
                            <label for="ai-developer-assistant-language-nodejs"><?php _e('Node.js', 'ai-developer-assistant'); ?></label>
                        </div>
                    </div>
                    
                    <div class="ai-developer-assistant-form-field">
                        <label for="ai-developer-assistant-max-history"><?php _e('Maximum History Items:', 'ai-developer-assistant'); ?></label>
                        <input type="number" id="ai-developer-assistant-max-history" name="max_history_items" value="<?php echo esc_attr($max_history_items); ?>" min="10" max="500">
                        <p class="description"><?php _e('Maximum number of history items to store in the database.', 'ai-developer-assistant'); ?></p>
                    </div>
                </div>
                
                <div class="ai-developer-assistant-settings-section">
                    <h2><?php _e('GitHub Integration', 'ai-developer-assistant'); ?></h2>
                    
                    <div class="ai-developer-assistant-form-field">
                        <label for="ai-developer-assistant-github-token"><?php _e('GitHub Personal Access Token:', 'ai-developer-assistant'); ?></label>
                        <input type="password" id="ai-developer-assistant-github-token" name="github_access_token" value="<?php echo esc_attr($github_access_token); ?>" autocomplete="off">
                        <p class="description">
                            <?php _e('Enter your GitHub Personal Access Token with <code>repo</code> and <code>gist</code> scopes. You can create one at <a href="https://github.com/settings/tokens/new" target="_blank">GitHub Settings</a>.', 'ai-developer-assistant'); ?>
                        </p>
                    </div>
                    
                    <p class="description">
                        <?php _e('For more GitHub integration options, visit the <a href="?page=ai-developer-assistant-github">GitHub Integration</a> page.', 'ai-developer-assistant'); ?>
                    </p>
                </div>
                
                <div class="ai-developer-assistant-settings-section">
                    <h2><?php _e('Security Settings', 'ai-developer-assistant'); ?></h2>
                    
                    <div class="ai-developer-assistant-checkbox-field">
                        <input type="checkbox" id="ai-developer-assistant-enable-php-injection" name="enable_php_injection" <?php checked($enable_php_injection); ?>>
                        <label for="ai-developer-assistant-enable-php-injection"><?php _e('Enable PHP Code Injection', 'ai-developer-assistant'); ?></label>
                    </div>
                    
                    <div class="ai-developer-assistant-warning">
                        <p><strong><?php _e('Warning:', 'ai-developer-assistant'); ?></strong> <?php _e('Enabling PHP code injection allows the plugin to modify your theme\'s functions.php file or create new plugin files. This is potentially dangerous if the generated code contains errors. Always review the code before injecting it.', 'ai-developer-assistant'); ?></p>
                    </div>
                </div>
                
                <button type="submit" id="ai-developer-assistant-save-settings" class="button button-primary"><?php _e('Save Settings', 'ai-developer-assistant'); ?></button>
            </form>
        </div>
    </div>
</div>