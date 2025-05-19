/**
 * GitHub Integration JavaScript
 *
 * @package    AI_Developer_Assistant
 * @subpackage AI_Developer_Assistant/admin/js
 * @author     OpenHands
 */

(function($) {
    'use strict';

    // Initialize CodeMirror instances for code editors
    let gistEditor = null;
    let fileEditor = null;
    let fetchedFileEditor = null;

    $(document).ready(function() {
        // Initialize CodeMirror editors if the elements exist
        if ($('#ai-developer-assistant-gist-content').length) {
            gistEditor = CodeMirror.fromTextArea(document.getElementById('ai-developer-assistant-gist-content'), {
                lineNumbers: true,
                mode: 'javascript',
                theme: 'default',
                indentUnit: 4,
                smartIndent: true,
                lineWrapping: true,
                extraKeys: {"Ctrl-Space": "autocomplete"}
            });
        }

        if ($('#ai-developer-assistant-file-content').length) {
            fileEditor = CodeMirror.fromTextArea(document.getElementById('ai-developer-assistant-file-content'), {
                lineNumbers: true,
                mode: 'javascript',
                theme: 'default',
                indentUnit: 4,
                smartIndent: true,
                lineWrapping: true,
                extraKeys: {"Ctrl-Space": "autocomplete"}
            });
        }

        if ($('#ai-developer-assistant-fetched-file-content').length) {
            fetchedFileEditor = CodeMirror(document.getElementById('ai-developer-assistant-fetched-file-content'), {
                lineNumbers: true,
                mode: 'javascript',
                theme: 'default',
                indentUnit: 4,
                smartIndent: true,
                lineWrapping: true,
                readOnly: true
            });
        }

        // Connect to GitHub
        $('#ai-developer-assistant-github-connect-form').on('submit', function(e) {
            e.preventDefault();
            
            const githubToken = $('#ai-developer-assistant-github-token').val();
            const nonce = $('#ai_developer_assistant_github_nonce').val();
            
            if (!githubToken) {
                showNotification('Please enter a GitHub Personal Access Token.', 'error');
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_developer_assistant_github_connect',
                    github_token: githubToken,
                    nonce: nonce
                },
                beforeSend: function() {
                    showLoading('Connecting to GitHub...');
                },
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        showNotification(response.data.message, 'success');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    showNotification('An error occurred while connecting to GitHub.', 'error');
                }
            });
        });

        // Disconnect from GitHub
        $('#ai-developer-assistant-disconnect-github').on('click', function() {
            const nonce = $('#ai_developer_assistant_github_nonce').val();
            
            if (confirm('Are you sure you want to disconnect from GitHub?')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ai_developer_assistant_github_disconnect',
                        nonce: nonce
                    },
                    beforeSend: function() {
                        showLoading('Disconnecting from GitHub...');
                    },
                    success: function(response) {
                        hideLoading();
                        
                        if (response.success) {
                            showNotification(response.data.message, 'success');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showNotification(response.data.message, 'error');
                        }
                    },
                    error: function() {
                        hideLoading();
                        showNotification('An error occurred while disconnecting from GitHub.', 'error');
                    }
                });
            }
        });

        // Load repositories
        $('#ai-developer-assistant-load-repositories, #ai-developer-assistant-load-repos-for-select').on('click', function() {
            loadRepositories();
        });

        // Load gists
        $('#ai-developer-assistant-load-gists').on('click', function() {
            loadGists();
        });

        // Create gist
        $('#ai-developer-assistant-create-gist-form').on('submit', function(e) {
            e.preventDefault();
            
            // Get values from form
            const filename = $('#ai-developer-assistant-gist-filename').val();
            const description = $('#ai-developer-assistant-gist-description').val();
            const content = gistEditor.getValue();
            const isPublic = $('#ai-developer-assistant-gist-public').is(':checked');
            const nonce = $('#ai_developer_assistant_gist_nonce').val();
            
            if (!filename || !content) {
                showNotification('Please fill in all required fields.', 'error');
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_developer_assistant_create_gist',
                    filename: filename,
                    description: description,
                    content: content,
                    public: isPublic,
                    nonce: nonce
                },
                beforeSend: function() {
                    showLoading('Creating gist...');
                },
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        showNotification(response.data.message, 'success');
                        
                        // Clear form
                        $('#ai-developer-assistant-gist-filename').val('');
                        $('#ai-developer-assistant-gist-description').val('');
                        gistEditor.setValue('');
                        $('#ai-developer-assistant-gist-public').prop('checked', false);
                        
                        // Show link to gist
                        if (response.data.html_url) {
                            showNotification(
                                'Gist created successfully. <a href="' + response.data.html_url + '" target="_blank">View Gist</a>',
                                'success'
                            );
                        }
                        
                        // Reload gists
                        loadGists();
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    showNotification('An error occurred while creating the gist.', 'error');
                }
            });
        });

        // Create file in repository
        $('#ai-developer-assistant-create-file-form').on('submit', function(e) {
            e.preventDefault();
            
            // Get values from form
            const repoFullName = $('#ai-developer-assistant-repo-select').val();
            const filePath = $('#ai-developer-assistant-file-path').val();
            const branchName = $('#ai-developer-assistant-branch-name').val();
            const commitMessage = $('#ai-developer-assistant-commit-message').val();
            const content = fileEditor.getValue();
            const nonce = $('#ai_developer_assistant_file_nonce').val();
            
            if (!repoFullName || !filePath || !branchName || !commitMessage || !content) {
                showNotification('Please fill in all required fields.', 'error');
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_developer_assistant_create_file',
                    repo_full_name: repoFullName,
                    file_path: filePath,
                    branch_name: branchName,
                    commit_message: commitMessage,
                    content: content,
                    nonce: nonce
                },
                beforeSend: function() {
                    showLoading('Creating file...');
                },
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        showNotification(response.data.message, 'success');
                        
                        // Clear form
                        $('#ai-developer-assistant-file-path').val('');
                        $('#ai-developer-assistant-commit-message').val('');
                        fileEditor.setValue('');
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    showNotification('An error occurred while creating the file.', 'error');
                }
            });
        });

        // Fetch file from repository
        $('#ai-developer-assistant-fetch-file-form').on('submit', function(e) {
            e.preventDefault();
            
            // Get values from form
            const repoFullName = $('#ai-developer-assistant-fetch-repo-select').val();
            const filePath = $('#ai-developer-assistant-fetch-file-path').val();
            const branchName = $('#ai-developer-assistant-fetch-branch-name').val();
            const nonce = $('#ai_developer_assistant_fetch_nonce').val();
            
            if (!repoFullName || !filePath || !branchName) {
                showNotification('Please fill in all required fields.', 'error');
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_developer_assistant_get_file_content',
                    repo_full_name: repoFullName,
                    file_path: filePath,
                    branch_name: branchName,
                    nonce: nonce
                },
                beforeSend: function() {
                    showLoading('Fetching file...');
                },
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        // Display fetched file
                        $('#ai-developer-assistant-fetched-file-container').show();
                        $('#ai-developer-assistant-fetched-filename').text(response.data.name);
                        
                        // Set content in CodeMirror
                        fetchedFileEditor.setValue(response.data.content);
                        
                        // Set mode based on file extension
                        const fileExtension = response.data.name.split('.').pop().toLowerCase();
                        setCodeMirrorMode(fetchedFileEditor, fileExtension);
                        
                        // Display file info
                        const fileInfo = `
                            <p><strong>Path:</strong> ${response.data.path}</p>
                            <p><strong>Size:</strong> ${formatBytes(response.data.size)}</p>
                            <p><strong>URL:</strong> <a href="${response.data.url}" target="_blank">View on GitHub</a></p>
                        `;
                        $('.ai-developer-assistant-fetched-file-info').html(fileInfo);
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    showNotification('An error occurred while fetching the file.', 'error');
                }
            });
        });

        // Copy fetched file to clipboard
        $('#ai-developer-assistant-copy-fetched-file').on('click', function() {
            const content = fetchedFileEditor.getValue();
            copyToClipboard(content);
            showNotification('File content copied to clipboard.', 'success');
        });

        // Use fetched file in chat
        $('#ai-developer-assistant-use-in-chat').on('click', function() {
            const content = fetchedFileEditor.getValue();
            const filename = $('#ai-developer-assistant-fetched-filename').text();
            
            // Store in localStorage to be used in chat page
            localStorage.setItem('ai_developer_assistant_code', content);
            localStorage.setItem('ai_developer_assistant_filename', filename);
            
            // Redirect to chat page
            window.location.href = 'admin.php?page=ai-developer-assistant';
        });

        // View commit history
        $('#ai-developer-assistant-commit-history-form').on('submit', function(e) {
            e.preventDefault();
            
            // Get values from form
            const repoFullName = $('#ai-developer-assistant-history-repo-select').val();
            const filePath = $('#ai-developer-assistant-history-file-path').val();
            const branchName = $('#ai-developer-assistant-history-branch-name').val();
            const nonce = $('#ai_developer_assistant_history_nonce').val();
            
            if (!repoFullName || !branchName) {
                showNotification('Please fill in all required fields.', 'error');
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_developer_assistant_get_commit_history',
                    repo_full_name: repoFullName,
                    file_path: filePath,
                    branch_name: branchName,
                    nonce: nonce
                },
                beforeSend: function() {
                    showLoading('Fetching commit history...');
                },
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        // Display commit history
                        $('#ai-developer-assistant-commit-history-container').show();
                        
                        if (response.data.commits.length === 0) {
                            $('#ai-developer-assistant-commit-history-list').html('<p>No commits found.</p>');
                            return;
                        }
                        
                        let html = '<ul class="ai-developer-assistant-commits-list">';
                        
                        response.data.commits.forEach(function(commit) {
                            const date = new Date(commit.commit.author.date);
                            const formattedDate = date.toLocaleString();
                            
                            html += `
                                <li class="ai-developer-assistant-commit-item">
                                    <div class="ai-developer-assistant-commit-header">
                                        <strong>${commit.commit.message}</strong>
                                    </div>
                                    <div class="ai-developer-assistant-commit-details">
                                        <span>Author: ${commit.commit.author.name}</span>
                                        <span>Date: ${formattedDate}</span>
                                        <a href="${commit.html_url}" target="_blank">View on GitHub</a>
                                    </div>
                                </li>
                            `;
                        });
                        
                        html += '</ul>';
                        $('#ai-developer-assistant-commit-history-list').html(html);
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    showNotification('An error occurred while fetching commit history.', 'error');
                }
            });
        });
    });

    /**
     * Load repositories from GitHub
     */
    function loadRepositories() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_developer_assistant_get_repositories',
                nonce: $('#ai_developer_assistant_github_nonce').val()
            },
            beforeSend: function() {
                $('.ai-developer-assistant-loading').show();
            },
            success: function(response) {
                $('.ai-developer-assistant-loading').hide();
                
                if (response.success) {
                    // Display repositories
                    if (response.data.repositories.length === 0) {
                        $('#ai-developer-assistant-repositories-list').html('<p>No repositories found.</p>');
                        return;
                    }
                    
                    let html = '<ul class="ai-developer-assistant-repos-list">';
                    let selectOptions = '<option value="">Select a repository...</option>';
                    
                    response.data.repositories.forEach(function(repo) {
                        html += `
                            <li class="ai-developer-assistant-repo-item">
                                <div class="ai-developer-assistant-repo-header">
                                    <strong>${repo.name}</strong>
                                    ${repo.private ? '<span class="ai-developer-assistant-private-badge">Private</span>' : ''}
                                </div>
                                <div class="ai-developer-assistant-repo-description">
                                    ${repo.description ? repo.description : 'No description'}
                                </div>
                                <div class="ai-developer-assistant-repo-details">
                                    <span>Language: ${repo.language ? repo.language : 'Not specified'}</span>
                                    <span>Stars: ${repo.stargazers_count}</span>
                                    <span>Forks: ${repo.forks_count}</span>
                                    <a href="${repo.html_url}" target="_blank">View on GitHub</a>
                                </div>
                            </li>
                        `;
                        
                        selectOptions += `<option value="${repo.full_name}">${repo.full_name}</option>`;
                    });
                    
                    html += '</ul>';
                    $('#ai-developer-assistant-repositories-list').html(html);
                    
                    // Update select dropdowns
                    $('#ai-developer-assistant-repo-select').html(selectOptions);
                    $('#ai-developer-assistant-fetch-repo-select').html(selectOptions);
                    $('#ai-developer-assistant-history-repo-select').html(selectOptions);
                } else {
                    showNotification(response.data.message, 'error');
                }
            },
            error: function() {
                $('.ai-developer-assistant-loading').hide();
                showNotification('An error occurred while loading repositories.', 'error');
            }
        });
    }

    /**
     * Load gists from GitHub
     */
    function loadGists() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_developer_assistant_get_gists',
                nonce: $('#ai_developer_assistant_github_nonce').val()
            },
            beforeSend: function() {
                $('.ai-developer-assistant-loading').show();
            },
            success: function(response) {
                $('.ai-developer-assistant-loading').hide();
                
                if (response.success) {
                    // Display gists
                    if (response.data.gists.length === 0) {
                        $('#ai-developer-assistant-gists-list').html('<p>No gists found.</p>');
                        return;
                    }
                    
                    let html = '<ul class="ai-developer-assistant-gists-list">';
                    
                    response.data.gists.forEach(function(gist) {
                        const files = Object.keys(gist.files);
                        const date = new Date(gist.created_at);
                        const formattedDate = date.toLocaleString();
                        
                        html += `
                            <li class="ai-developer-assistant-gist-item">
                                <div class="ai-developer-assistant-gist-header">
                                    <strong>${gist.description ? gist.description : 'Untitled'}</strong>
                                    ${gist.public ? '' : '<span class="ai-developer-assistant-private-badge">Secret</span>'}
                                </div>
                                <div class="ai-developer-assistant-gist-files">
                                    <strong>Files:</strong>
                                    <ul>
                        `;
                        
                        files.forEach(function(file) {
                            html += `<li>${gist.files[file].filename}</li>`;
                        });
                        
                        html += `
                                    </ul>
                                </div>
                                <div class="ai-developer-assistant-gist-details">
                                    <span>Created: ${formattedDate}</span>
                                    <a href="${gist.html_url}" target="_blank">View on GitHub</a>
                                    <button type="button" class="button ai-developer-assistant-load-gist" data-gist-id="${gist.id}">Load Content</button>
                                </div>
                            </li>
                        `;
                    });
                    
                    html += '</ul>';
                    $('#ai-developer-assistant-gists-list').html(html);
                    
                    // Add event listener for loading gist content
                    $('.ai-developer-assistant-load-gist').on('click', function() {
                        const gistId = $(this).data('gist-id');
                        loadGistContent(gistId);
                    });
                } else {
                    showNotification(response.data.message, 'error');
                }
            },
            error: function() {
                $('.ai-developer-assistant-loading').hide();
                showNotification('An error occurred while loading gists.', 'error');
            }
        });
    }

    /**
     * Load gist content
     * 
     * @param {string} gistId The gist ID
     */
    function loadGistContent(gistId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_developer_assistant_get_gist',
                gist_id: gistId,
                nonce: $('#ai_developer_assistant_github_nonce').val()
            },
            beforeSend: function() {
                showLoading('Loading gist content...');
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    const gist = response.data.gist;
                    const files = Object.keys(gist.files);
                    
                    if (files.length === 0) {
                        showNotification('This gist has no files.', 'error');
                        return;
                    }
                    
                    // Get the first file
                    const file = gist.files[files[0]];
                    
                    // Set values in the create gist form
                    $('#ai-developer-assistant-gist-filename').val(file.filename);
                    $('#ai-developer-assistant-gist-description').val(gist.description);
                    gistEditor.setValue(file.content);
                    $('#ai-developer-assistant-gist-public').prop('checked', gist.public);
                    
                    // Set mode based on file extension
                    const fileExtension = file.filename.split('.').pop().toLowerCase();
                    setCodeMirrorMode(gistEditor, fileExtension);
                    
                    showNotification('Gist content loaded successfully.', 'success');
                } else {
                    showNotification(response.data.message, 'error');
                }
            },
            error: function() {
                hideLoading();
                showNotification('An error occurred while loading gist content.', 'error');
            }
        });
    }

    /**
     * Set CodeMirror mode based on file extension
     * 
     * @param {CodeMirror} editor The CodeMirror editor
     * @param {string} extension The file extension
     */
    function setCodeMirrorMode(editor, extension) {
        let mode = 'text/plain';
        
        switch (extension) {
            case 'js':
                mode = 'javascript';
                break;
            case 'php':
                mode = 'php';
                break;
            case 'py':
                mode = 'python';
                break;
            case 'html':
                mode = 'htmlmixed';
                break;
            case 'css':
                mode = 'css';
                break;
            case 'json':
                mode = 'application/json';
                break;
            case 'md':
                mode = 'markdown';
                break;
            case 'xml':
                mode = 'xml';
                break;
            case 'sql':
                mode = 'sql';
                break;
            case 'sh':
            case 'bash':
                mode = 'shell';
                break;
            default:
                mode = 'text/plain';
        }
        
        editor.setOption('mode', mode);
    }

    /**
     * Copy text to clipboard
     * 
     * @param {string} text The text to copy
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
     * Format bytes to human-readable format
     * 
     * @param {number} bytes The bytes to format
     * @param {number} decimals The number of decimals
     * @return {string} The formatted size
     */
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    /**
     * Show loading message
     * 
     * @param {string} message The loading message
     */
    function showLoading(message) {
        if ($('#ai-developer-assistant-loading-overlay').length === 0) {
            $('body').append(`
                <div id="ai-developer-assistant-loading-overlay">
                    <div class="ai-developer-assistant-loading-content">
                        <div class="ai-developer-assistant-spinner"></div>
                        <p id="ai-developer-assistant-loading-message">${message}</p>
                    </div>
                </div>
            `);
        } else {
            $('#ai-developer-assistant-loading-message').text(message);
            $('#ai-developer-assistant-loading-overlay').show();
        }
    }

    /**
     * Hide loading message
     */
    function hideLoading() {
        $('#ai-developer-assistant-loading-overlay').hide();
    }

    /**
     * Show notification
     * 
     * @param {string} message The notification message
     * @param {string} type The notification type (success, error)
     */
    function showNotification(message, type) {
        const notificationId = 'ai-developer-assistant-notification-' + Date.now();
        
        $('body').append(`
            <div id="${notificationId}" class="ai-developer-assistant-notification ${type}">
                <div class="ai-developer-assistant-notification-content">
                    <p>${message}</p>
                </div>
                <button type="button" class="ai-developer-assistant-notification-close">×</button>
            </div>
        `);
        
        const $notification = $('#' + notificationId);
        
        // Show notification
        setTimeout(function() {
            $notification.addClass('show');
        }, 10);
        
        // Hide notification after 5 seconds
        setTimeout(function() {
            $notification.removeClass('show');
            
            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 5000);
        
        // Close notification on click
        $notification.find('.ai-developer-assistant-notification-close').on('click', function() {
            $notification.removeClass('show');
            
            setTimeout(function() {
                $notification.remove();
            }, 300);
        });
    }
})(jQuery);