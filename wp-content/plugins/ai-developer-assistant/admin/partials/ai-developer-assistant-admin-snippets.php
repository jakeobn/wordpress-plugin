<?php
/**
 * Admin snippets template
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
            <h1><?php echo esc_html(get_admin_page_title()); ?> - <?php _e('Saved Snippets', 'ai-developer-assistant'); ?></h1>
        </div>
        
        <div class="ai-developer-assistant-snippets">
            <p><?php _e('Below are your saved code snippets. You can copy, export, or delete them as needed.', 'ai-developer-assistant'); ?></p>
            
            <div class="ai-developer-assistant-snippets-list">
                <!-- Snippets will be loaded here via JavaScript -->
                <div class="ai-developer-assistant-loading">
                    <div class="ai-developer-assistant-loading-spinner"></div>
                </div>
            </div>
        </div>
    </div>
</div>