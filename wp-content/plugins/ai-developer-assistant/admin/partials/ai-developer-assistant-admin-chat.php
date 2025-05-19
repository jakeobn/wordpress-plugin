<?php
/**
 * Admin chat interface template
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
$default_language = isset($options['default_language']) ? $options['default_language'] : 'php';
$enabled_languages = isset($options['enabled_languages']) ? $options['enabled_languages'] : array('php', 'javascript', 'python', 'nodejs');
?>

<div class="wrap">
    <div class="ai-developer-assistant-container">
        <div class="ai-developer-assistant-header">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        </div>
        
        <div class="ai-developer-assistant-chat">
            <div class="ai-developer-assistant-input-area">
                <form id="ai-developer-assistant-form">
                    <div class="ai-developer-assistant-prompt-container">
                        <label for="ai-developer-assistant-prompt"><?php _e('Enter your prompt:', 'ai-developer-assistant'); ?></label>
                        <textarea id="ai-developer-assistant-prompt" name="prompt" placeholder="<?php _e('Describe what you want to build, e.g., "Create a WordPress shortcode that displays a responsive image gallery with lightbox"', 'ai-developer-assistant'); ?>"></textarea>
                    </div>
                    
                    <div class="ai-developer-assistant-controls">
                        <div class="ai-developer-assistant-language-selector">
                            <label for="ai-developer-assistant-language"><?php _e('Language:', 'ai-developer-assistant'); ?></label>
                            <select id="ai-developer-assistant-language" name="language">
                                <?php if (in_array('php', $enabled_languages)): ?>
                                <option value="php" <?php selected($default_language, 'php'); ?>><?php _e('PHP', 'ai-developer-assistant'); ?></option>
                                <?php endif; ?>
                                
                                <?php if (in_array('javascript', $enabled_languages)): ?>
                                <option value="javascript" <?php selected($default_language, 'javascript'); ?>><?php _e('JavaScript', 'ai-developer-assistant'); ?></option>
                                <?php endif; ?>
                                
                                <?php if (in_array('python', $enabled_languages)): ?>
                                <option value="python" <?php selected($default_language, 'python'); ?>><?php _e('Python', 'ai-developer-assistant'); ?></option>
                                <?php endif; ?>
                                
                                <?php if (in_array('nodejs', $enabled_languages)): ?>
                                <option value="nodejs" <?php selected($default_language, 'nodejs'); ?>><?php _e('Node.js', 'ai-developer-assistant'); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <button type="submit" id="ai-developer-assistant-submit" class="ai-developer-assistant-submit-btn"><?php _e('Generate Code', 'ai-developer-assistant'); ?></button>
                    </div>
                </form>
            </div>
            
            <div class="ai-developer-assistant-output-area">
                <div class="ai-developer-assistant-output-header">
                    <h3><?php _e('Generated Code', 'ai-developer-assistant'); ?></h3>
                    
                    <div class="ai-developer-assistant-output-actions">
                        <button type="button" id="ai-developer-assistant-inject-btn" class="button" disabled><?php _e('Inject PHP Code', 'ai-developer-assistant'); ?></button>
                        <button type="button" id="ai-developer-assistant-save-btn" class="button" disabled><?php _e('Save Snippet', 'ai-developer-assistant'); ?></button>
                        <button type="button" id="ai-developer-assistant-export-btn" class="button" disabled><?php _e('Export Code', 'ai-developer-assistant'); ?></button>
                    </div>
                </div>
                
                <div class="ai-developer-assistant-code-container">
                    <div id="ai-developer-assistant-code"></div>
                </div>
            </div>
        </div>
    </div>
</div>