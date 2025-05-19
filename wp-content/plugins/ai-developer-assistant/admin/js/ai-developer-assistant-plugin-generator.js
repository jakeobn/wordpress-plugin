/**
 * Plugin Generator JavaScript for AI Developer Assistant
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Only initialize if we're on the generate plugin page
        if (!$('#ai-developer-assistant-generate-plugin-form').length) {
            return;
        }
        
        // Set up event listeners for the plugin generator
        $('#ai-developer-assistant-generate-plugin-form').on('submit', function(e) {
            e.preventDefault();
            generatePlugin();
        });
        
        // Auto-generate slug from plugin name
        $('#ai-developer-assistant-plugin-name').on('blur', function() {
            const pluginName = $(this).val();
            const pluginSlug = $('#ai-developer-assistant-plugin-slug');
            
            if (pluginName && !pluginSlug.val()) {
                pluginSlug.val(pluginName.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''));
            }
        });
        
        // View files toggle
        $('#ai-developer-assistant-view-files').on('click', function(e) {
            e.preventDefault();
            $('.ai-developer-assistant-plugin-files').toggle();
        });
    });
    
    /**
     * Generate a WordPress plugin
     */
    function generatePlugin() {
        // Get form data
        const pluginName = $('#ai-developer-assistant-plugin-name').val();
        const pluginSlug = $('#ai-developer-assistant-plugin-slug').val();
        const pluginDescription = $('#ai-developer-assistant-plugin-description').val();
        const pluginAuthor = $('#ai-developer-assistant-plugin-author').val();
        const pluginPrompt = $('#ai-developer-assistant-plugin-prompt').val();
        const pluginComponents = [];
        
        // Get selected components
        $('input[name="plugin_components[]"]:checked').each(function() {
            pluginComponents.push($(this).val());
        });
        
        // Validate required fields
        if (!pluginName || !pluginSlug || !pluginPrompt) {
            alert('Please fill in all required fields');
            return;
        }
        
        // Validate plugin slug format
        if (!/^[a-z0-9-]+$/.test(pluginSlug)) {
            alert('Plugin slug must contain only lowercase letters, numbers, and hyphens');
            return;
        }
        
        // Show loading state
        $('#ai-developer-assistant-generate-plugin-btn').prop('disabled', true).text('Generating Plugin...');
        
        // Hide previous results
        $('.ai-developer-assistant-generation-result').hide();
        
        // Make API request
        $.ajax({
            url: aiDevAssistant.rest_url + '/generate-plugin',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', aiDevAssistant.nonce);
            },
            data: {
                nonce: $('#ai_developer_assistant_generate_plugin_nonce').val(),
                plugin_name: pluginName,
                plugin_slug: pluginSlug,
                plugin_description: pluginDescription,
                plugin_author: pluginAuthor,
                plugin_prompt: pluginPrompt,
                plugin_components: pluginComponents
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    alert('Plugin generated successfully!');
                    
                    // Show result section
                    $('.ai-developer-assistant-generation-result').show();
                    
                    // Set download link
                    $('#ai-developer-assistant-download-plugin').attr('href', response.zip_url);
                    
                    // Build file tree
                    let fileTree = '<ul class="ai-developer-assistant-file-list">';
                    response.files.forEach(function(file) {
                        fileTree += '<li>' + file + '</li>';
                    });
                    fileTree += '</ul>';
                    
                    $('#ai-developer-assistant-file-tree').html(fileTree);
                    
                    // Scroll to result
                    $('html, body').animate({
                        scrollTop: $('.ai-developer-assistant-generation-result').offset().top - 50
                    }, 500);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while generating the plugin';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert('Error: ' + errorMessage);
            },
            complete: function() {
                // Reset button state
                $('#ai-developer-assistant-generate-plugin-btn').prop('disabled', false).text('Generate Plugin');
            }
        });
    }

})(jQuery);