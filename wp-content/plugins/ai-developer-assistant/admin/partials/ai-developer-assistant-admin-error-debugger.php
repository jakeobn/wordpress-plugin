<?php
/**
 * Admin error debugger template
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
?>

<div class="wrap">
    <div class="ai-developer-assistant-container">
        <div class="ai-developer-assistant-header">
            <h1><?php echo esc_html(get_admin_page_title()); ?> - <?php _e('Error Debugger', 'ai-developer-assistant'); ?></h1>
        </div>
        
        <div class="ai-developer-assistant-error-debugger">
            <p class="ai-developer-assistant-intro">
                <?php _e('Paste a WordPress or PHP error message below, and AI will analyze it, explain what\'s wrong, and suggest fixes.', 'ai-developer-assistant'); ?>
            </p>
            
            <form id="ai-developer-assistant-error-form">
                <?php wp_nonce_field('ai_developer_assistant_debug_error', 'ai_developer_assistant_error_nonce'); ?>
                
                <div class="ai-developer-assistant-form-field">
                    <label for="ai-developer-assistant-error-message"><?php _e('Error Message or Stack Trace:', 'ai-developer-assistant'); ?></label>
                    <textarea id="ai-developer-assistant-error-message" name="error_message" placeholder="<?php _e('Paste your error message or stack trace here...', 'ai-developer-assistant'); ?>" rows="10" required></textarea>
                </div>
                
                <div class="ai-developer-assistant-form-field">
                    <label for="ai-developer-assistant-error-context"><?php _e('Additional Context (Optional):', 'ai-developer-assistant'); ?></label>
                    <textarea id="ai-developer-assistant-error-context" name="error_context" placeholder="<?php _e('Provide any additional context about what you were doing when the error occurred...', 'ai-developer-assistant'); ?>" rows="5"></textarea>
                </div>
                
                <div class="ai-developer-assistant-form-field">
                    <label for="ai-developer-assistant-error-file"><?php _e('File Path (Optional):', 'ai-developer-assistant'); ?></label>
                    <input type="text" id="ai-developer-assistant-error-file" name="error_file" placeholder="<?php _e('/path/to/your/file.php', 'ai-developer-assistant'); ?>">
                    <p class="description"><?php _e('If you know which file contains the error, provide its path for more specific suggestions.', 'ai-developer-assistant'); ?></p>
                </div>
                
                <div class="ai-developer-assistant-form-actions">
                    <button type="submit" id="ai-developer-assistant-analyze-error-btn" class="button button-primary"><?php _e('Analyze Error', 'ai-developer-assistant'); ?></button>
                </div>
            </form>
            
            <div class="ai-developer-assistant-loading" style="display: none;">
                <p><?php _e('Analyzing error...', 'ai-developer-assistant'); ?></p>
            </div>
            
            <div class="ai-developer-assistant-error-result" style="display: none;">
                <h3><?php _e('Error Analysis', 'ai-developer-assistant'); ?></h3>
                
                <div class="ai-developer-assistant-error-explanation">
                    <h4><?php _e('What Happened:', 'ai-developer-assistant'); ?></h4>
                    <div id="ai-developer-assistant-error-explanation-content"></div>
                </div>
                
                <div class="ai-developer-assistant-error-cause">
                    <h4><?php _e('Likely Cause:', 'ai-developer-assistant'); ?></h4>
                    <div id="ai-developer-assistant-error-cause-content"></div>
                </div>
                
                <div class="ai-developer-assistant-error-solution">
                    <h4><?php _e('Suggested Fix:', 'ai-developer-assistant'); ?></h4>
                    <div id="ai-developer-assistant-error-solution-content"></div>
                </div>
                
                <div class="ai-developer-assistant-error-code" style="display: none;">
                    <h4><?php _e('Code Fix:', 'ai-developer-assistant'); ?></h4>
                    <div id="ai-developer-assistant-error-code-container"></div>
                    
                    <div class="ai-developer-assistant-error-actions">
                        <button type="button" id="ai-developer-assistant-patch-error" class="button button-primary"><?php _e('Patch It', 'ai-developer-assistant'); ?></button>
                        <button type="button" id="ai-developer-assistant-copy-fix" class="button"><?php _e('Copy Fix', 'ai-developer-assistant'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>