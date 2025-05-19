/**
 * Error Debugger JavaScript for AI Developer Assistant
 */
(function($) {
    'use strict';

    // CodeMirror editor instance for code fix
    let codeFixEditor = null;

    $(document).ready(function() {
        // Only initialize if we're on the error debugger page
        if (!$('#ai-developer-assistant-error-form').length) {
            return;
        }
        
        // Initialize CodeMirror for code fix
        initializeCodeFixEditor();
        
        // Set up event listeners
        setupEventListeners();
    });
    
    /**
     * Initialize the CodeMirror editor for code fix
     */
    function initializeCodeFixEditor() {
        const codeContainer = document.getElementById('ai-developer-assistant-error-code-container');
        
        if (codeContainer) {
            codeFixEditor = CodeMirror(codeContainer, {
                mode: 'application/x-httpd-php',
                theme: 'dracula',
                lineNumbers: true,
                indentUnit: 4,
                smartIndent: true,
                indentWithTabs: true,
                lineWrapping: true,
                readOnly: false
            });
            
            // Set initial value
            codeFixEditor.setValue('// Code fix will appear here');
        }
    }
    
    /**
     * Set up event listeners
     */
    function setupEventListeners() {
        // Error form submission
        $('#ai-developer-assistant-error-form').on('submit', function(e) {
            e.preventDefault();
            analyzeError();
        });
        
        // Patch error button
        $('#ai-developer-assistant-patch-error').on('click', function() {
            patchError();
        });
        
        // Copy fix button
        $('#ai-developer-assistant-copy-fix').on('click', function() {
            copyFix();
        });
    }
    
    /**
     * Analyze the error using AI
     */
    function analyzeError() {
        const errorMessage = $('#ai-developer-assistant-error-message').val();
        const errorContext = $('#ai-developer-assistant-error-context').val();
        const errorFile = $('#ai-developer-assistant-error-file').val();
        
        if (!errorMessage) {
            alert('Please enter an error message to analyze.');
            return;
        }
        
        // Show loading indicator
        $('.ai-developer-assistant-loading').show();
        $('#ai-developer-assistant-analyze-error-btn').prop('disabled', true).text('Analyzing...');
        
        // Hide previous results
        $('.ai-developer-assistant-error-result').hide();
        
        // Make API request
        $.ajax({
            url: aiDevAssistant.rest_url + '/debug-error',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', aiDevAssistant.nonce);
            },
            data: {
                nonce: $('#ai_developer_assistant_error_nonce').val(),
                error_message: errorMessage,
                error_context: errorContext,
                error_file: errorFile
            },
            success: function(response) {
                if (response.success) {
                    // Update the result sections
                    $('#ai-developer-assistant-error-explanation-content').html(response.explanation);
                    $('#ai-developer-assistant-error-cause-content').html(response.cause);
                    $('#ai-developer-assistant-error-solution-content').html(response.solution);
                    
                    // Show the result
                    $('.ai-developer-assistant-error-result').show();
                    
                    // If code fix is available, show it
                    if (response.code_fix) {
                        codeFixEditor.setValue(response.code_fix);
                        $('.ai-developer-assistant-error-code').show();
                        
                        // Only enable patch button if file path is provided and it's functions.php
                        const filePath = $('#ai-developer-assistant-error-file').val();
                        if (filePath && (filePath.includes('functions.php') || response.can_patch)) {
                            $('#ai-developer-assistant-patch-error').show();
                        } else {
                            $('#ai-developer-assistant-patch-error').hide();
                        }
                    } else {
                        $('.ai-developer-assistant-error-code').hide();
                    }
                    
                    // Scroll to result
                    $('html, body').animate({
                        scrollTop: $('.ai-developer-assistant-error-result').offset().top - 50
                    }, 500);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while analyzing the error';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert('Error: ' + errorMessage);
            },
            complete: function() {
                // Hide loading indicator
                $('.ai-developer-assistant-loading').hide();
                
                // Reset button state
                $('#ai-developer-assistant-analyze-error-btn').prop('disabled', false).text('Analyze Error');
            }
        });
    }
    
    /**
     * Patch the error in the file
     */
    function patchError() {
        const errorFile = $('#ai-developer-assistant-error-file').val();
        const codeFix = codeFixEditor.getValue();
        
        if (!errorFile) {
            alert('Please provide a file path to patch.');
            return;
        }
        
        if (!codeFix) {
            alert('No code fix available to patch.');
            return;
        }
        
        if (!confirm('Are you sure you want to patch this file? This will modify your code. Make sure you have a backup.')) {
            return;
        }
        
        // Show loading state
        $('#ai-developer-assistant-patch-error').prop('disabled', true).text('Patching...');
        
        // Make API request
        $.ajax({
            url: aiDevAssistant.rest_url + '/patch-error',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', aiDevAssistant.nonce);
            },
            data: {
                nonce: $('#ai_developer_assistant_error_nonce').val(),
                error_file: errorFile,
                code_fix: codeFix
            },
            success: function(response) {
                if (response.success) {
                    alert('File patched successfully!');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while patching the file';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert('Error: ' + errorMessage);
            },
            complete: function() {
                // Reset button state
                $('#ai-developer-assistant-patch-error').prop('disabled', false).text('Patch It');
            }
        });
    }
    
    /**
     * Copy the code fix to clipboard
     */
    function copyFix() {
        const codeFix = codeFixEditor.getValue();
        
        if (!codeFix) {
            alert('No code fix available to copy.');
            return;
        }
        
        // Create a temporary textarea element to copy from
        const textarea = document.createElement('textarea');
        textarea.value = codeFix;
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            // Execute copy command
            document.execCommand('copy');
            alert('Code fix copied to clipboard!');
        } catch (err) {
            alert('Failed to copy code fix: ' + err);
        } finally {
            // Remove the temporary textarea
            document.body.removeChild(textarea);
        }
    }

})(jQuery);