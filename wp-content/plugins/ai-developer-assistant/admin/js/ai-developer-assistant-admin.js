/**
 * Admin JavaScript for AI Developer Assistant
 */
(function($) {
    'use strict';

    // Store conversation history
    let conversationHistory = [];
    
    // CodeMirror editor instance
    let codeEditor = null;
    
    // Current language and code
    let currentLanguage = 'php';
    let currentCode = '';
    
    /**
     * Initialize the plugin admin functionality
     */
    function init() {
        // Initialize on document ready
        $(document).ready(function() {
            setupEventListeners();
            initializeCodeEditor();
            initializeSettings();
            initializeSnippets();
            initializePluginGenerator();
        });
    }
    
    /**
     * Set up event listeners
     */
    function setupEventListeners() {
        // Chat interface form submission
        $('#ai-developer-assistant-form').on('submit', function(e) {
            e.preventDefault();
            generateCode();
        });
        
        // Language selector change
        $('#ai-developer-assistant-language').on('change', function() {
            currentLanguage = $(this).val();
            updateCodeEditorMode(currentLanguage);
        });
        
        // Action buttons
        $('#ai-developer-assistant-inject-btn').on('click', injectPhpCode);
        $('#ai-developer-assistant-save-btn').on('click', openSaveSnippetModal);
        $('#ai-developer-assistant-export-btn').on('click', exportCode);
        
        // Settings form submission
        $('#ai-developer-assistant-settings-form').on('submit', function(e) {
            e.preventDefault();
            saveSettings();
        });
        
        // Test API connection button
        $('#ai-developer-assistant-test-api').on('click', testApiConnection);
        
        // Modal close buttons
        $(document).on('click', '.ai-developer-assistant-modal-close', closeModal);
        
        // Save snippet form submission
        $(document).on('submit', '#ai-developer-assistant-save-snippet-form', function(e) {
            e.preventDefault();
            saveSnippet();
        });
    }
    
    /**
     * Initialize the CodeMirror editor
     */
    function initializeCodeEditor() {
        const codeContainer = document.getElementById('ai-developer-assistant-code');
        
        if (codeContainer) {
            codeEditor = CodeMirror(codeContainer, {
                mode: 'application/x-httpd-php',
                theme: 'dracula',
                lineNumbers: true,
                indentUnit: 4,
                smartIndent: true,
                indentWithTabs: true,
                lineWrapping: true,
                extraKeys: {
                    'Ctrl-Space': 'autocomplete'
                },
                readOnly: false
            });
            
            // Set initial value
            codeEditor.setValue('// Generated code will appear here');
            
            // Set initial language
            currentLanguage = $('#ai-developer-assistant-language').val() || 'php';
            updateCodeEditorMode(currentLanguage);
        }
    }
    
    /**
     * Update the CodeMirror editor mode based on the selected language
     */
    function updateCodeEditorMode(language) {
        if (!codeEditor) return;
        
        let mode;
        switch (language) {
            case 'php':
                mode = 'application/x-httpd-php';
                break;
            case 'javascript':
            case 'nodejs':
                mode = 'text/javascript';
                break;
            case 'python':
                mode = 'text/x-python';
                break;
            default:
                mode = 'text/plain';
        }
        
        codeEditor.setOption('mode', mode);
    }
    
    /**
     * Generate code using the Anthropic Claude API
     */
    function generateCode() {
        const prompt = $('#ai-developer-assistant-prompt').val();
        const language = $('#ai-developer-assistant-language').val();
        
        if (!prompt) {
            showNotification('Please enter a prompt', 'error');
            return;
        }
        
        // Show loading indicator
        showLoading(true);
        
        // Disable submit button
        $('#ai-developer-assistant-submit').prop('disabled', true);
        
        // Add to conversation history
        conversationHistory.push({
            role: 'user',
            content: prompt
        });
        
        // Make API request
        $.ajax({
            url: aiDevAssistant.rest_url + '/generate-code',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', aiDevAssistant.nonce);
            },
            data: {
                prompt: prompt,
                language: language,
                history: conversationHistory
            },
            success: function(response) {
                if (response.success) {
                    // Update the code editor
                    currentCode = response.content;
                    codeEditor.setValue(currentCode);
                    
                    // Add to conversation history
                    conversationHistory.push({
                        role: 'assistant',
                        content: response.content
                    });
                    
                    // Limit history length
                    if (conversationHistory.length > 10) {
                        conversationHistory = conversationHistory.slice(-10);
                    }
                    
                    // Show success notification
                    showNotification('Code generated successfully', 'success');
                    
                    // Enable action buttons
                    enableActionButtons(language);
                } else {
                    showNotification('Error: ' + response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while generating code';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('Error: ' + errorMessage, 'error');
            },
            complete: function() {
                // Hide loading indicator
                showLoading(false);
                
                // Enable submit button
                $('#ai-developer-assistant-submit').prop('disabled', false);
            }
        });
    }
    
    /**
     * Inject PHP code into a file
     */
    function injectPhpCode() {
        // Only allow PHP code injection
        if (currentLanguage !== 'php') {
            showNotification('Only PHP code can be injected', 'error');
            return;
        }
        
        // Get the current code
        const code = codeEditor.getValue();
        
        if (!code) {
            showNotification('No code to inject', 'error');
            return;
        }
        
        // Create modal for injection options
        const modalContent = `
            <div class="ai-developer-assistant-modal-overlay">
                <div class="ai-developer-assistant-modal">
                    <div class="ai-developer-assistant-modal-header">
                        <h3 class="ai-developer-assistant-modal-title">Inject PHP Code</h3>
                        <button type="button" class="ai-developer-assistant-modal-close">&times;</button>
                    </div>
                    <div class="ai-developer-assistant-modal-body">
                        <form id="ai-developer-assistant-inject-form">
                            <div class="ai-developer-assistant-form-field">
                                <label for="ai-developer-assistant-inject-target">Target Location:</label>
                                <select id="ai-developer-assistant-inject-target" required>
                                    <option value="functions">Theme's functions.php</option>
                                    <option value="new_plugin">Create New Plugin</option>
                                    <option value="custom_file">Custom File</option>
                                </select>
                            </div>
                            <div class="ai-developer-assistant-warning">
                                <p><strong>Warning:</strong> Injecting code can potentially break your site if the code contains errors. A backup will be created automatically.</p>
                            </div>
                        </form>
                    </div>
                    <div class="ai-developer-assistant-modal-footer">
                        <button type="button" class="button" id="ai-developer-assistant-inject-cancel">Cancel</button>
                        <button type="button" class="button button-primary" id="ai-developer-assistant-inject-confirm">Inject Code</button>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to the page
        $('body').append(modalContent);
        
        // Set up event listeners for the modal
        $('#ai-developer-assistant-inject-cancel, .ai-developer-assistant-modal-close').on('click', closeModal);
        $('#ai-developer-assistant-inject-confirm').on('click', function() {
            const target = $('#ai-developer-assistant-inject-target').val();
            
            // Show loading indicator
            showLoading(true);
            
            // Make API request
            $.ajax({
                url: aiDevAssistant.rest_url + '/inject-php-code',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', aiDevAssistant.nonce);
                },
                data: {
                    code: code,
                    target: target
                },
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        closeModal();
                    } else {
                        showNotification('Error: ' + response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while injecting code';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification('Error: ' + errorMessage, 'error');
                },
                complete: function() {
                    // Hide loading indicator
                    showLoading(false);
                }
            });
        });
    }
    
    /**
     * Open the save snippet modal
     */
    function openSaveSnippetModal() {
        // Get the current code
        const code = codeEditor.getValue();
        
        if (!code) {
            showNotification('No code to save', 'error');
            return;
        }
        
        // Create modal for saving snippet
        const modalContent = `
            <div class="ai-developer-assistant-modal-overlay">
                <div class="ai-developer-assistant-modal">
                    <div class="ai-developer-assistant-modal-header">
                        <h3 class="ai-developer-assistant-modal-title">Save Code Snippet</h3>
                        <button type="button" class="ai-developer-assistant-modal-close">&times;</button>
                    </div>
                    <div class="ai-developer-assistant-modal-body">
                        <form id="ai-developer-assistant-save-snippet-form">
                            <div class="ai-developer-assistant-form-field">
                                <label for="ai-developer-assistant-snippet-title">Title:</label>
                                <input type="text" id="ai-developer-assistant-snippet-title" required>
                            </div>
                            <div class="ai-developer-assistant-form-field">
                                <label for="ai-developer-assistant-snippet-description">Description (optional):</label>
                                <textarea id="ai-developer-assistant-snippet-description" rows="3"></textarea>
                            </div>
                            <input type="hidden" id="ai-developer-assistant-snippet-language" value="${currentLanguage}">
                            <input type="hidden" id="ai-developer-assistant-snippet-code" value="">
                        </form>
                    </div>
                    <div class="ai-developer-assistant-modal-footer">
                        <button type="button" class="button" id="ai-developer-assistant-save-cancel">Cancel</button>
                        <button type="submit" form="ai-developer-assistant-save-snippet-form" class="button button-primary">Save Snippet</button>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to the page
        $('body').append(modalContent);
        
        // Set the code value (using jQuery val() to handle special characters)
        $('#ai-developer-assistant-snippet-code').val(code);
        
        // Set up event listeners for the modal
        $('#ai-developer-assistant-save-cancel, .ai-developer-assistant-modal-close').on('click', closeModal);
    }
    
    /**
     * Save a code snippet
     */
    function saveSnippet() {
        const title = $('#ai-developer-assistant-snippet-title').val();
        const description = $('#ai-developer-assistant-snippet-description').val();
        const language = $('#ai-developer-assistant-snippet-language').val();
        const code = $('#ai-developer-assistant-snippet-code').val();
        
        if (!title || !code) {
            showNotification('Title and code are required', 'error');
            return;
        }
        
        // Show loading indicator
        showLoading(true);
        
        // Make API request
        $.ajax({
            url: aiDevAssistant.rest_url + '/save-snippet',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', aiDevAssistant.nonce);
            },
            data: {
                title: title,
                description: description,
                language: language,
                code: code
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    closeModal();
                } else {
                    showNotification('Error: ' + response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while saving snippet';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('Error: ' + errorMessage, 'error');
            },
            complete: function() {
                // Hide loading indicator
                showLoading(false);
            }
        });
    }
    
    /**
     * Export code as a file
     */
    function exportCode() {
        // Get the current code
        const code = codeEditor.getValue();
        
        if (!code) {
            showNotification('No code to export', 'error');
            return;
        }
        
        // Create modal for exporting code
        const modalContent = `
            <div class="ai-developer-assistant-modal-overlay">
                <div class="ai-developer-assistant-modal">
                    <div class="ai-developer-assistant-modal-header">
                        <h3 class="ai-developer-assistant-modal-title">Export Code</h3>
                        <button type="button" class="ai-developer-assistant-modal-close">&times;</button>
                    </div>
                    <div class="ai-developer-assistant-modal-body">
                        <form id="ai-developer-assistant-export-form">
                            <div class="ai-developer-assistant-form-field">
                                <label for="ai-developer-assistant-export-filename">Filename:</label>
                                <input type="text" id="ai-developer-assistant-export-filename" required>
                            </div>
                        </form>
                    </div>
                    <div class="ai-developer-assistant-modal-footer">
                        <button type="button" class="button" id="ai-developer-assistant-export-cancel">Cancel</button>
                        <button type="button" class="button button-primary" id="ai-developer-assistant-export-confirm">Export</button>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to the page
        $('body').append(modalContent);
        
        // Set default filename based on language
        let defaultFilename = 'code';
        switch (currentLanguage) {
            case 'php':
                defaultFilename += '.php';
                break;
            case 'javascript':
            case 'nodejs':
                defaultFilename += '.js';
                break;
            case 'python':
                defaultFilename += '.py';
                break;
        }
        $('#ai-developer-assistant-export-filename').val(defaultFilename);
        
        // Set up event listeners for the modal
        $('#ai-developer-assistant-export-cancel, .ai-developer-assistant-modal-close').on('click', closeModal);
        $('#ai-developer-assistant-export-confirm').on('click', function() {
            const filename = $('#ai-developer-assistant-export-filename').val();
            
            if (!filename) {
                showNotification('Filename is required', 'error');
                return;
            }
            
            // Show loading indicator
            showLoading(true);
            
            // Make API request
            $.ajax({
                url: aiDevAssistant.rest_url + '/export-code',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', aiDevAssistant.nonce);
                },
                data: {
                    code: code,
                    language: currentLanguage,
                    filename: filename
                },
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        closeModal();
                        
                        // Create a download link
                        const downloadLink = document.createElement('a');
                        downloadLink.href = response.file_url;
                        downloadLink.download = response.filename;
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                    } else {
                        showNotification('Error: ' + response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while exporting code';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification('Error: ' + errorMessage, 'error');
                },
                complete: function() {
                    // Hide loading indicator
                    showLoading(false);
                }
            });
        });
    }
    
    /**
     * Initialize the settings page
     */
    function initializeSettings() {
        // Test API connection button
        $('#ai-developer-assistant-test-api').on('click', testApiConnection);
    }
    
    /**
     * Test the API connection
     */
    function testApiConnection() {
        const apiKey = $('#ai-developer-assistant-api-key').val();
        
        if (!apiKey) {
            showNotification('API key is required', 'error');
            return;
        }
        
        // Show loading indicator
        $('#ai-developer-assistant-test-api').prop('disabled', true).text('Testing...');
        
        // Make API request
        $.ajax({
            url: aiDevAssistant.rest_url + '/test-api-connection',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', aiDevAssistant.nonce);
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                } else {
                    showNotification('Error: ' + response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while testing API connection';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('Error: ' + errorMessage, 'error');
            },
            complete: function() {
                // Reset button
                $('#ai-developer-assistant-test-api').prop('disabled', false).text('Test Connection');
            }
        });
    }
    
    /**
     * Save settings
     */
    function saveSettings() {
        const apiKey = $('#ai-developer-assistant-api-key').val();
        const githubToken = $('#ai-developer-assistant-github-token').val();
        const enablePhpInjection = $('#ai-developer-assistant-enable-php-injection').is(':checked');
        const defaultLanguage = $('#ai-developer-assistant-default-language').val();
        const maxHistoryItems = $('#ai-developer-assistant-max-history').val();
        
        // Collect enabled languages
        const enabledLanguages = [];
        $('.ai-developer-assistant-language-checkbox:checked').each(function() {
            enabledLanguages.push($(this).val());
        });
        
        // Show loading indicator
        $('#ai-developer-assistant-save-settings').prop('disabled', true).text('Saving...');
        
        // Make API request
        $.ajax({
            url: aiDevAssistant.rest_url + '/update-settings',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', aiDevAssistant.nonce);
            },
            data: {
                settings: {
                    api_key: apiKey,
                    github_access_token: githubToken,
                    enable_php_injection: enablePhpInjection,
                    default_language: defaultLanguage,
                    enabled_languages: enabledLanguages,
                    max_history_items: maxHistoryItems
                }
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                } else {
                    showNotification('Error: ' + response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while saving settings';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('Error: ' + errorMessage, 'error');
            },
            complete: function() {
                // Reset button
                $('#ai-developer-assistant-save-settings').prop('disabled', false).text('Save Settings');
            }
        });
    }
    
    /**
     * Initialize the snippets page
     */
    function initializeSnippets() {
        // Load snippets if we're on the snippets page
        if ($('.ai-developer-assistant-snippets-list').length) {
            loadSnippets();
        }
    }
    
    /**
     * Load saved snippets
     */
    function loadSnippets() {
        // Show loading indicator
        $('.ai-developer-assistant-snippets-list').html('<div class="ai-developer-assistant-loading"><div class="ai-developer-assistant-loading-spinner"></div></div>');
        
        // Make API request
        $.ajax({
            url: aiDevAssistant.rest_url + '/get-snippets',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', aiDevAssistant.nonce);
            },
            success: function(response) {
                if (response.success) {
                    displaySnippets(response.snippets);
                } else {
                    showNotification('Error: ' + response.message, 'error');
                    $('.ai-developer-assistant-snippets-list').html('<p>Failed to load snippets.</p>');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while loading snippets';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('Error: ' + errorMessage, 'error');
                $('.ai-developer-assistant-snippets-list').html('<p>Failed to load snippets.</p>');
            }
        });
    }
    
    /**
     * Display snippets in the UI
     */
    function displaySnippets(snippets) {
        if (!snippets || snippets.length === 0) {
            $('.ai-developer-assistant-snippets-list').html('<p>No snippets found.</p>');
            return;
        }
        
        let html = '';
        
        snippets.forEach(function(snippet) {
            html += `
                <div class="ai-developer-assistant-snippet-item" data-id="${snippet.id}">
                    <div class="ai-developer-assistant-snippet-header">
                        <h3 class="ai-developer-assistant-snippet-title">${snippet.title}</h3>
                        <span class="ai-developer-assistant-snippet-language">${snippet.language}</span>
                    </div>
                    <div class="ai-developer-assistant-snippet-content">
                        <pre><code class="language-${snippet.language}">${escapeHtml(snippet.code)}</code></pre>
                    </div>
                    <div class="ai-developer-assistant-snippet-actions">
                        <button type="button" class="button ai-developer-assistant-snippet-copy">Copy Code</button>
                        <button type="button" class="button ai-developer-assistant-snippet-export">Export</button>
                        <button type="button" class="button ai-developer-assistant-snippet-delete">Delete</button>
                    </div>
                </div>
            `;
        });
        
        $('.ai-developer-assistant-snippets-list').html(html);
        
        // Set up event listeners for snippet actions
        $('.ai-developer-assistant-snippet-copy').on('click', function() {
            const code = $(this).closest('.ai-developer-assistant-snippet-item').find('code').text();
            copyToClipboard(code);
            showNotification('Code copied to clipboard', 'success');
        });
        
        $('.ai-developer-assistant-snippet-export').on('click', function() {
            const snippetId = $(this).closest('.ai-developer-assistant-snippet-item').data('id');
            const snippet = snippets.find(s => s.id == snippetId);
            
            if (snippet) {
                exportSnippet(snippet);
            }
        });
        
        $('.ai-developer-assistant-snippet-delete').on('click', function() {
            const snippetId = $(this).closest('.ai-developer-assistant-snippet-item').data('id');
            deleteSnippet(snippetId);
        });
    }
    
    /**
     * Export a snippet as a file
     */
    function exportSnippet(snippet) {
        // Create modal for exporting snippet
        const modalContent = `
            <div class="ai-developer-assistant-modal-overlay">
                <div class="ai-developer-assistant-modal">
                    <div class="ai-developer-assistant-modal-header">
                        <h3 class="ai-developer-assistant-modal-title">Export Snippet</h3>
                        <button type="button" class="ai-developer-assistant-modal-close">&times;</button>
                    </div>
                    <div class="ai-developer-assistant-modal-body">
                        <form id="ai-developer-assistant-export-snippet-form">
                            <div class="ai-developer-assistant-form-field">
                                <label for="ai-developer-assistant-export-snippet-filename">Filename:</label>
                                <input type="text" id="ai-developer-assistant-export-snippet-filename" required>
                            </div>
                        </form>
                    </div>
                    <div class="ai-developer-assistant-modal-footer">
                        <button type="button" class="button" id="ai-developer-assistant-export-snippet-cancel">Cancel</button>
                        <button type="button" class="button button-primary" id="ai-developer-assistant-export-snippet-confirm">Export</button>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to the page
        $('body').append(modalContent);
        
        // Set default filename based on snippet title and language
        let defaultFilename = snippet.title.toLowerCase().replace(/[^a-z0-9]/g, '-');
        switch (snippet.language) {
            case 'php':
                defaultFilename += '.php';
                break;
            case 'javascript':
            case 'nodejs':
                defaultFilename += '.js';
                break;
            case 'python':
                defaultFilename += '.py';
                break;
            default:
                defaultFilename += '.txt';
        }
        $('#ai-developer-assistant-export-snippet-filename').val(defaultFilename);
        
        // Set up event listeners for the modal
        $('#ai-developer-assistant-export-snippet-cancel, .ai-developer-assistant-modal-close').on('click', closeModal);
        $('#ai-developer-assistant-export-snippet-confirm').on('click', function() {
            const filename = $('#ai-developer-assistant-export-snippet-filename').val();
            
            if (!filename) {
                showNotification('Filename is required', 'error');
                return;
            }
            
            // Show loading indicator
            showLoading(true);
            
            // Make API request
            $.ajax({
                url: aiDevAssistant.rest_url + '/export-code',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', aiDevAssistant.nonce);
                },
                data: {
                    code: snippet.code,
                    language: snippet.language,
                    filename: filename
                },
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        closeModal();
                        
                        // Create a download link
                        const downloadLink = document.createElement('a');
                        downloadLink.href = response.file_url;
                        downloadLink.download = response.filename;
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                    } else {
                        showNotification('Error: ' + response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while exporting snippet';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification('Error: ' + errorMessage, 'error');
                },
                complete: function() {
                    // Hide loading indicator
                    showLoading(false);
                }
            });
        });
    }
    
    /**
     * Delete a snippet
     */
    function deleteSnippet(snippetId) {
        // Confirm deletion
        if (!confirm('Are you sure you want to delete this snippet?')) {
            return;
        }
        
        // Show loading indicator
        showLoading(true);
        
        // Make API request
        $.ajax({
            url: aiDevAssistant.rest_url + '/delete-snippet',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', aiDevAssistant.nonce);
            },
            data: {
                id: snippetId
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    loadSnippets(); // Reload snippets
                } else {
                    showNotification('Error: ' + response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while deleting snippet';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('Error: ' + errorMessage, 'error');
            },
            complete: function() {
                // Hide loading indicator
                showLoading(false);
            }
        });
    }
    
    /**
     * Enable or disable action buttons based on the current language
     */
    function enableActionButtons(language) {
        // Enable all buttons by default
        $('#ai-developer-assistant-inject-btn, #ai-developer-assistant-save-btn, #ai-developer-assistant-export-btn').prop('disabled', false);
        
        // Disable inject button for non-PHP languages
        if (language !== 'php') {
            $('#ai-developer-assistant-inject-btn').prop('disabled', true);
        }
    }
    
    /**
     * Show or hide the loading indicator
     */
    function showLoading(show) {
        if (show) {
            if ($('.ai-developer-assistant-loading').length === 0) {
                $('.ai-developer-assistant-output-area').append('<div class="ai-developer-assistant-loading"><div class="ai-developer-assistant-loading-spinner"></div></div>');
            }
        } else {
            $('.ai-developer-assistant-loading').remove();
        }
    }
    
    /**
     * Show a notification message
     */
    function showNotification(message, type) {
        // Remove any existing notifications
        $('.ai-developer-assistant-notification').remove();
        
        // Create the notification element
        const notification = $('<div class="ai-developer-assistant-notification ' + type + '">' + message + '</div>');
        
        // Add it to the page
        $('body').append(notification);
        
        // Position it at the top of the page
        notification.css({
            'position': 'fixed',
            'top': '20px',
            'right': '20px',
            'padding': '10px 15px',
            'border-radius': '4px',
            'z-index': '999999',
            'box-shadow': '0 2px 5px rgba(0, 0, 0, 0.2)'
        });
        
        // Style based on type
        if (type === 'success') {
            notification.css({
                'background-color': '#dff0d8',
                'color': '#3c763d',
                'border': '1px solid #d6e9c6'
            });
        } else if (type === 'error') {
            notification.css({
                'background-color': '#f2dede',
                'color': '#a94442',
                'border': '1px solid #ebccd1'
            });
        }
        
        // Remove the notification after 3 seconds
        setTimeout(function() {
            notification.fadeOut(300, function() {
                notification.remove();
            });
        }, 3000);
    }
    
    /**
     * Close any open modals
     */
    function closeModal() {
        $('.ai-developer-assistant-modal-overlay').remove();
    }
    
    /**
     * Copy text to clipboard
     */
    function copyToClipboard(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }
    
    /**
     * Escape HTML special characters
     */
    function escapeHtml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
    // Initialize the plugin
    init();
    
})(jQuery);
    /**
     * Initialize the plugin generator functionality
     */
    function initializePluginGenerator() {
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
    }
    
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
            showNotification('Please fill in all required fields', 'error');
            return;
        }
        
        // Validate plugin slug format
        if (!/^[a-z0-9-]+$/.test(pluginSlug)) {
            showNotification('Plugin slug must contain only lowercase letters, numbers, and hyphens', 'error');
            return;
        }
        
        // Show loading state
        showLoading(true);
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
                    showNotification('Plugin generated successfully!', 'success');
                    
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
                    showNotification('Error: ' + response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while generating the plugin';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('Error: ' + errorMessage, 'error');
            },
            complete: function() {
                // Hide loading indicator
                showLoading(false);
                
                // Reset button state
                $('#ai-developer-assistant-generate-plugin-btn').prop('disabled', false).text('Generate Plugin');
            }
        });
    }
