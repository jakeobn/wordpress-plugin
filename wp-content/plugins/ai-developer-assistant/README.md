# AI Developer Assistant

A powerful WordPress plugin that integrates with Anthropic Claude 3.7 API to provide multi-language code generation and developer support directly within your WordPress admin dashboard.

## Features

- **Multi-language Code Generation**: Generate code in PHP, JavaScript, Python, Node.js, and more
- **WordPress Integration**: Inject PHP code directly into your active theme or create new plugin files
- **Code Management**: Save, export, and manage code snippets
- **Error Debugger**: Analyze and fix WordPress/PHP errors with AI-powered explanations
- **Plugin Generator**: Create custom WordPress plugins from natural language prompts
- **Syntax Highlighting**: Clean, intuitive interface with syntax highlighting for all supported languages
- **Secure Implementation**: Proper sanitization, nonce verification, and capability checks

## Installation

1. Download the plugin zip file
2. Log in to your WordPress admin dashboard
3. Navigate to Plugins > Add New
4. Click "Upload Plugin" and select the downloaded zip file
5. Click "Install Now"
6. After installation, click "Activate Plugin"

## Configuration

1. Navigate to "AI Developer Assistant" > "Settings" in your WordPress admin menu
2. Enter your Anthropic Claude API key (You can get one from [Anthropic Console](https://console.anthropic.com/))
3. Configure your preferred settings:
   - Default language
   - Enabled languages
   - PHP code injection (disabled by default for security)
   - Maximum history items

## Usage

### Generating Code

1. Navigate to "AI Developer Assistant" > "Chat Interface"
2. Enter a detailed prompt describing what you want to build
3. Select the desired programming language
4. Click "Generate Code"
5. The AI will generate code based on your prompt
6. You can then:
   - Inject PHP code into your theme or create a new plugin
   - Save the snippet for later use
   - Export the code as a file

### Managing Snippets

1. Navigate to "AI Developer Assistant" > "Saved Snippets"
2. View, copy, export, or delete your saved code snippets

### Debugging Errors

1. Navigate to "AI Developer Assistant" > "Error Debugger"
2. Paste your WordPress or PHP error message
3. Optionally provide additional context or the file path
4. Click "Analyze Error"
5. Review the AI's explanation, cause analysis, and suggested fix
6. If a file path was provided, you can use the "Patch It" button to automatically apply the fix

### Generating Plugins

1. Navigate to "AI Developer Assistant" > "Generate Plugin"
2. Enter a name, slug, and description for your plugin
3. Write a detailed prompt describing what you want the plugin to do
4. Select the components you want to include
5. Click "Generate Plugin"
6. Download the generated plugin zip file

## Example Prompts

### WordPress Shortcode

```
Create a WordPress shortcode that displays a responsive image gallery with lightbox functionality. The shortcode should accept parameters for the number of columns, image size, and category.
```

### Custom PC Builder

```
Create a custom PC builder page for WordPress with the following features:
1. Allow users to select components (CPU, GPU, RAM, Motherboard, Storage, Power Supply, Case)
2. Check compatibility between components (e.g., CPU socket matches motherboard)
3. Calculate total price and power consumption
4. Show a summary of the selected components
5. Allow users to save their build or share it via a unique URL
```

### Error Debugging

```
Parse error: syntax error, unexpected '}', expecting end of file in /var/www/html/wp-content/themes/mytheme/functions.php on line 156
```

### Plugin Generation

```
Create a WordPress plugin that adds a custom post type for "Recipes" with custom taxonomies for "Cuisine Type" and "Difficulty Level". Include custom meta fields for ingredients, cooking time, and nutritional information. Add a shortcode to display recipes in a responsive grid with filtering options.
```

## Security Considerations

- The plugin restricts access to users with the `manage_options` capability (administrators)
- PHP code injection is disabled by default and must be explicitly enabled
- All inputs and outputs are properly sanitized
- Nonce verification is used for all form submissions and API requests
- Files are backed up before any modifications

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Anthropic Claude API key

## Support

For support, feature requests, or bug reports, please contact us at support@example.com or open an issue on our GitHub repository.

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
```