<?php
/**
 * PC Builder Example
 * 
 * This file contains example code for a custom PC builder feature that can be generated
 * using the AI Developer Assistant plugin. This is a demonstration of the plugin's
 * capabilities and can be used as a reference for creating similar features.
 */

/**
 * Example prompt for generating a PC Builder feature:
 * 
 * "Create a custom PC builder page for WordPress with the following features:
 * 1. Allow users to select components (CPU, GPU, RAM, Motherboard, Storage, Power Supply, Case)
 * 2. Check compatibility between components (e.g., CPU socket matches motherboard)
 * 3. Calculate total price and power consumption
 * 4. Show a summary of the selected components
 * 5. Allow users to save their build or share it via a unique URL
 * 
 * Include both the PHP code for the WordPress page template and the JavaScript for the
 * interactive functionality. Also include a data structure for storing component information."
 */

/**
 * PHP Code: PC Builder Shortcode
 */
function pc_builder_shortcode() {
    // Enqueue required scripts and styles
    wp_enqueue_script('pc-builder-js', plugin_dir_url(__FILE__) . 'js/pc-builder.js', array('jquery'), '1.0.0', true);
    wp_enqueue_style('pc-builder-css', plugin_dir_url(__FILE__) . 'css/pc-builder.css', array(), '1.0.0');
    
    // Pass component data to JavaScript
    wp_localize_script('pc-builder-js', 'pcBuilderData', array(
        'components' => get_pc_components(),
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pc_builder_nonce')
    ));
    
    // Start output buffering
    ob_start();
    ?>
    <div class="pc-builder-container">
        <h2>Custom PC Builder</h2>
        
        <div class="pc-builder-sections">
            <!-- Component Selection Tabs -->
            <div class="pc-builder-tabs">
                <button class="pc-builder-tab active" data-tab="cpu">CPU</button>
                <button class="pc-builder-tab" data-tab="motherboard">Motherboard</button>
                <button class="pc-builder-tab" data-tab="gpu">Graphics Card</button>
                <button class="pc-builder-tab" data-tab="ram">RAM</button>
                <button class="pc-builder-tab" data-tab="storage">Storage</button>
                <button class="pc-builder-tab" data-tab="psu">Power Supply</button>
                <button class="pc-builder-tab" data-tab="case">Case</button>
            </div>
            
            <!-- Component Selection Area -->
            <div class="pc-builder-component-area">
                <div class="pc-builder-filters">
                    <input type="text" id="pc-builder-search" placeholder="Search components...">
                    <select id="pc-builder-sort">
                        <option value="price-asc">Price: Low to High</option>
                        <option value="price-desc">Price: High to Low</option>
                        <option value="name-asc">Name: A to Z</option>
                        <option value="name-desc">Name: Z to A</option>
                    </select>
                </div>
                
                <div class="pc-builder-component-list" id="pc-builder-component-list">
                    <!-- Components will be loaded here via JavaScript -->
                    <div class="pc-builder-loading">Loading components...</div>
                </div>
            </div>
            
            <!-- Selected Components Summary -->
            <div class="pc-builder-summary">
                <h3>Your PC Build</h3>
                
                <div class="pc-builder-selected-components">
                    <div class="pc-builder-selected-item" id="selected-cpu">
                        <div class="pc-builder-component-label">CPU</div>
                        <div class="pc-builder-component-value">Not selected</div>
                        <button class="pc-builder-remove-btn" data-type="cpu" style="display: none;">Remove</button>
                    </div>
                    
                    <div class="pc-builder-selected-item" id="selected-motherboard">
                        <div class="pc-builder-component-label">Motherboard</div>
                        <div class="pc-builder-component-value">Not selected</div>
                        <button class="pc-builder-remove-btn" data-type="motherboard" style="display: none;">Remove</button>
                    </div>
                    
                    <div class="pc-builder-selected-item" id="selected-gpu">
                        <div class="pc-builder-component-label">Graphics Card</div>
                        <div class="pc-builder-component-value">Not selected</div>
                        <button class="pc-builder-remove-btn" data-type="gpu" style="display: none;">Remove</button>
                    </div>
                    
                    <div class="pc-builder-selected-item" id="selected-ram">
                        <div class="pc-builder-component-label">RAM</div>
                        <div class="pc-builder-component-value">Not selected</div>
                        <button class="pc-builder-remove-btn" data-type="ram" style="display: none;">Remove</button>
                    </div>
                    
                    <div class="pc-builder-selected-item" id="selected-storage">
                        <div class="pc-builder-component-label">Storage</div>
                        <div class="pc-builder-component-value">Not selected</div>
                        <button class="pc-builder-remove-btn" data-type="storage" style="display: none;">Remove</button>
                    </div>
                    
                    <div class="pc-builder-selected-item" id="selected-psu">
                        <div class="pc-builder-component-label">Power Supply</div>
                        <div class="pc-builder-component-value">Not selected</div>
                        <button class="pc-builder-remove-btn" data-type="psu" style="display: none;">Remove</button>
                    </div>
                    
                    <div class="pc-builder-selected-item" id="selected-case">
                        <div class="pc-builder-component-label">Case</div>
                        <div class="pc-builder-component-value">Not selected</div>
                        <button class="pc-builder-remove-btn" data-type="case" style="display: none;">Remove</button>
                    </div>
                </div>
                
                <div class="pc-builder-totals">
                    <div class="pc-builder-total-item">
                        <div class="pc-builder-total-label">Estimated Power:</div>
                        <div class="pc-builder-total-value" id="total-power">0 W</div>
                    </div>
                    
                    <div class="pc-builder-total-item">
                        <div class="pc-builder-total-label">Total Price:</div>
                        <div class="pc-builder-total-value" id="total-price">$0.00</div>
                    </div>
                </div>
                
                <div class="pc-builder-compatibility" id="compatibility-warnings">
                    <!-- Compatibility warnings will be shown here -->
                </div>
                
                <div class="pc-builder-actions">
                    <button id="pc-builder-save" class="pc-builder-action-btn">Save Build</button>
                    <button id="pc-builder-share" class="pc-builder-action-btn">Share Build</button>
                    <button id="pc-builder-reset" class="pc-builder-action-btn">Reset Build</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('pc_builder', 'pc_builder_shortcode');

/**
 * Get PC components data
 * 
 * @return array Array of PC components
 */
function get_pc_components() {
    // In a real implementation, this data would come from a database
    return array(
        'cpu' => array(
            array(
                'id' => 'cpu1',
                'name' => 'Intel Core i9-13900K',
                'price' => 589.99,
                'image' => 'intel-i9-13900k.jpg',
                'specs' => array(
                    'socket' => 'LGA1700',
                    'cores' => 24,
                    'threads' => 32,
                    'base_clock' => 3.0,
                    'boost_clock' => 5.8,
                    'tdp' => 125
                )
            ),
            array(
                'id' => 'cpu2',
                'name' => 'AMD Ryzen 9 7950X',
                'price' => 549.99,
                'image' => 'amd-ryzen-7950x.jpg',
                'specs' => array(
                    'socket' => 'AM5',
                    'cores' => 16,
                    'threads' => 32,
                    'base_clock' => 4.5,
                    'boost_clock' => 5.7,
                    'tdp' => 170
                )
            ),
            array(
                'id' => 'cpu3',
                'name' => 'Intel Core i5-13600K',
                'price' => 319.99,
                'image' => 'intel-i5-13600k.jpg',
                'specs' => array(
                    'socket' => 'LGA1700',
                    'cores' => 14,
                    'threads' => 20,
                    'base_clock' => 3.5,
                    'boost_clock' => 5.1,
                    'tdp' => 125
                )
            ),
            array(
                'id' => 'cpu4',
                'name' => 'AMD Ryzen 7 7700X',
                'price' => 349.99,
                'image' => 'amd-ryzen-7700x.jpg',
                'specs' => array(
                    'socket' => 'AM5',
                    'cores' => 8,
                    'threads' => 16,
                    'base_clock' => 4.5,
                    'boost_clock' => 5.4,
                    'tdp' => 105
                )
            ),
        ),
        'motherboard' => array(
            array(
                'id' => 'mb1',
                'name' => 'ASUS ROG Maximus Z790 Hero',
                'price' => 629.99,
                'image' => 'asus-rog-maximus-z790.jpg',
                'specs' => array(
                    'socket' => 'LGA1700',
                    'chipset' => 'Z790',
                    'form_factor' => 'ATX',
                    'memory_slots' => 4,
                    'max_memory' => 128,
                    'pcie_slots' => 3
                )
            ),
            array(
                'id' => 'mb2',
                'name' => 'MSI MPG X670E Carbon WiFi',
                'price' => 479.99,
                'image' => 'msi-mpg-x670e.jpg',
                'specs' => array(
                    'socket' => 'AM5',
                    'chipset' => 'X670E',
                    'form_factor' => 'ATX',
                    'memory_slots' => 4,
                    'max_memory' => 128,
                    'pcie_slots' => 3
                )
            ),
            array(
                'id' => 'mb3',
                'name' => 'Gigabyte B760 AORUS Elite AX',
                'price' => 219.99,
                'image' => 'gigabyte-b760-aorus.jpg',
                'specs' => array(
                    'socket' => 'LGA1700',
                    'chipset' => 'B760',
                    'form_factor' => 'ATX',
                    'memory_slots' => 4,
                    'max_memory' => 128,
                    'pcie_slots' => 2
                )
            ),
            array(
                'id' => 'mb4',
                'name' => 'ASRock B650M PG Riptide WiFi',
                'price' => 169.99,
                'image' => 'asrock-b650m.jpg',
                'specs' => array(
                    'socket' => 'AM5',
                    'chipset' => 'B650',
                    'form_factor' => 'Micro-ATX',
                    'memory_slots' => 4,
                    'max_memory' => 128,
                    'pcie_slots' => 2
                )
            ),
        ),
        'gpu' => array(
            array(
                'id' => 'gpu1',
                'name' => 'NVIDIA GeForce RTX 4090',
                'price' => 1599.99,
                'image' => 'nvidia-rtx-4090.jpg',
                'specs' => array(
                    'memory' => 24,
                    'memory_type' => 'GDDR6X',
                    'tdp' => 450,
                    'length' => 304,
                    'recommended_psu' => 850
                )
            ),
            array(
                'id' => 'gpu2',
                'name' => 'AMD Radeon RX 7900 XTX',
                'price' => 999.99,
                'image' => 'amd-rx-7900xtx.jpg',
                'specs' => array(
                    'memory' => 24,
                    'memory_type' => 'GDDR6',
                    'tdp' => 355,
                    'length' => 287,
                    'recommended_psu' => 800
                )
            ),
            array(
                'id' => 'gpu3',
                'name' => 'NVIDIA GeForce RTX 4070 Ti',
                'price' => 799.99,
                'image' => 'nvidia-rtx-4070ti.jpg',
                'specs' => array(
                    'memory' => 12,
                    'memory_type' => 'GDDR6X',
                    'tdp' => 285,
                    'length' => 267,
                    'recommended_psu' => 700
                )
            ),
            array(
                'id' => 'gpu4',
                'name' => 'AMD Radeon RX 7800 XT',
                'price' => 499.99,
                'image' => 'amd-rx-7800xt.jpg',
                'specs' => array(
                    'memory' => 16,
                    'memory_type' => 'GDDR6',
                    'tdp' => 263,
                    'length' => 267,
                    'recommended_psu' => 650
                )
            ),
        ),
        // Additional components would be defined here
    );
}

/**
 * AJAX handler for saving PC builds
 */
function save_pc_build() {
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pc_builder_nonce')) {
        wp_send_json_error('Invalid security token');
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to save a build');
    }
    
    // Get the build data
    $build_data = isset($_POST['build_data']) ? $_POST['build_data'] : array();
    
    if (empty($build_data)) {
        wp_send_json_error('No build data provided');
    }
    
    // Generate a unique build ID
    $build_id = uniqid('pcbuild_');
    
    // Get current user ID
    $user_id = get_current_user_id();
    
    // Save the build to user meta
    $saved_builds = get_user_meta($user_id, 'pc_builder_saved_builds', true);
    
    if (!is_array($saved_builds)) {
        $saved_builds = array();
    }
    
    // Add the new build
    $saved_builds[$build_id] = array(
        'build_data' => $build_data,
        'date_created' => current_time('mysql'),
        'build_name' => isset($_POST['build_name']) ? sanitize_text_field($_POST['build_name']) : 'My PC Build'
    );
    
    // Update user meta
    update_user_meta($user_id, 'pc_builder_saved_builds', $saved_builds);
    
    // Return success
    wp_send_json_success(array(
        'build_id' => $build_id,
        'message' => 'Build saved successfully',
        'share_url' => add_query_arg('pc_build', $build_id, get_permalink())
    ));
}
add_action('wp_ajax_save_pc_build', 'save_pc_build');