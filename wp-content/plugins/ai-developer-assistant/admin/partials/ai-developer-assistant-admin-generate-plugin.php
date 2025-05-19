<?php
/**
 * Admin generate plugin template
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
            <h1><?php echo esc_html(get_admin_page_title()); ?> - <?php _e('Generate Plugin', 'ai-developer-assistant'); ?></h1>
        </div>
        
        <div class="ai-developer-assistant-generate-plugin">
            <p class="ai-developer-assistant-intro">
                <?php _e('Generate a complete WordPress plugin from a single prompt. Describe what you want the plugin to do, and AI will scaffold a fully functional plugin structure with all necessary files.', 'ai-developer-assistant'); ?>
            </p>
            
            <form id="ai-developer-assistant-generate-plugin-form">
                <?php wp_nonce_field('ai_developer_assistant_generate_plugin', 'ai_developer_assistant_generate_plugin_nonce'); ?>
                
                <div class="ai-developer-assistant-form-field">
                    <label for="ai-developer-assistant-plugin-name"><?php _e('Plugin Name:', 'ai-developer-assistant'); ?></label>
                    <input type="text" id="ai-developer-assistant-plugin-name" name="plugin_name" placeholder="<?php _e('My Awesome Plugin', 'ai-developer-assistant'); ?>" required>
                    <p class="description"><?php _e('A human-readable name for your plugin.', 'ai-developer-assistant'); ?></p>
                </div>
                
                <div class="ai-developer-assistant-form-field">
                    <label for="ai-developer-assistant-plugin-slug"><?php _e('Plugin Slug:', 'ai-developer-assistant'); ?></label>
                    <input type="text" id="ai-developer-assistant-plugin-slug" name="plugin_slug" placeholder="<?php _e('my-awesome-plugin', 'ai-developer-assistant'); ?>" required>
                    <p class="description"><?php _e('Used for file names and function prefixes. Lowercase letters, numbers, and hyphens only.', 'ai-developer-assistant'); ?></p>
                </div>
                
                <div class="ai-developer-assistant-form-field">
                    <label for="ai-developer-assistant-plugin-description"><?php _e('Plugin Description:', 'ai-developer-assistant'); ?></label>
                    <textarea id="ai-developer-assistant-plugin-description" name="plugin_description" placeholder="<?php _e('Describe what your plugin does...', 'ai-developer-assistant'); ?>" rows="3"></textarea>
                </div>
                
                <div class="ai-developer-assistant-form-field">
                    <label for="ai-developer-assistant-plugin-author"><?php _e('Author:', 'ai-developer-assistant'); ?></label>
                    <input type="text" id="ai-developer-assistant-plugin-author" name="plugin_author" value="<?php echo esc_attr(wp_get_current_user()->display_name); ?>">
                </div>
                
                <div class="ai-developer-assistant-form-field">
                    <label for="ai-developer-assistant-plugin-prompt"><?php _e('Plugin Requirements:', 'ai-developer-assistant'); ?></label>
                    <textarea id="ai-developer-assistant-plugin-prompt" name="plugin_prompt" placeholder="<?php _e('Describe in detail what you want your plugin to do. Be specific about features, settings, shortcodes, etc.', 'ai-developer-assistant'); ?>" rows="10" required></textarea>
                </div>
                
                <div class="ai-developer-assistant-form-field">
                    <label><?php _e('Plugin Components:', 'ai-developer-assistant'); ?></label>
                    
                    <div class="ai-developer-assistant-checkbox-field">
                        <input type="checkbox" id="ai-developer-assistant-component-settings" name="plugin_components[]" value="settings" checked>
                        <label for="ai-developer-assistant-component-settings"><?php _e('Settings Page', 'ai-developer-assistant'); ?></label>
                    </div>
                    
                    <div class="ai-developer-assistant-checkbox-field">
                        <input type="checkbox" id="ai-developer-assistant-component-shortcode" name="plugin_components[]" value="shortcode" checked>
                        <label for="ai-developer-assistant-component-shortcode"><?php _e('Shortcode', 'ai-developer-assistant'); ?></label>
                    </div>
                    
                    <div class="ai-developer-assistant-checkbox-field">
                        <input type="checkbox" id="ai-developer-assistant-component-block" name="plugin_components[]" value="block">
                        <label for="ai-developer-assistant-component-block"><?php _e('Gutenberg Block', 'ai-developer-assistant'); ?></label>
                    </div>
                    
                    <div class="ai-developer-assistant-checkbox-field">
                        <input type="checkbox" id="ai-developer-assistant-component-cpt" name="plugin_components[]" value="cpt">
                        <label for="ai-developer-assistant-component-cpt"><?php _e('Custom Post Type', 'ai-developer-assistant'); ?></label>
                    </div>
                </div>
                
                <div class="ai-developer-assistant-form-actions">
                    <button type="submit" id="ai-developer-assistant-generate-plugin-btn" class="button button-primary"><?php _e('Generate Plugin', 'ai-developer-assistant'); ?></button>
                </div>
            </form>
            
            <div class="ai-developer-assistant-generation-result" style="display: none;">
                <h3><?php _e('Plugin Generated Successfully!', 'ai-developer-assistant'); ?></h3>
                
                <div class="ai-developer-assistant-result-actions">
                    <a href="#" id="ai-developer-assistant-download-plugin" class="button button-primary"><?php _e('Download Plugin ZIP', 'ai-developer-assistant'); ?></a>
                    <a href="#" id="ai-developer-assistant-view-files" class="button"><?php _e('View Plugin Files', 'ai-developer-assistant'); ?></a>
                </div>
                
                <div class="ai-developer-assistant-plugin-files" style="display: none;">
                    <h4><?php _e('Plugin Files:', 'ai-developer-assistant'); ?></h4>
                    <div id="ai-developer-assistant-file-tree"></div>
                </div>
            </div>
        </div>
    </div>
</div>