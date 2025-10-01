<?php
/*
Plugin Name: WP Prompts Lib
Description: A library database for AI prompts
Version: 1.0
Author: John Simmons HyperText
Author URI: https://johnsimmonshypertext.com
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register Custom Post Type
function wppl_register_prompts_post_type() {
    // Register the custom taxonomy for categories first
    $cat_labels = array(
        'name'              => 'Prompt Categories',
        'singular_name'     => 'Prompt Category',
        'search_items'      => 'Search Prompt Categories',
        'all_items'        => 'All Prompt Categories',
        'parent_item'       => 'Parent Prompt Category',
        'parent_item_colon' => 'Parent Prompt Category:',
        'edit_item'        => 'Edit Prompt Category',
        'update_item'      => 'Update Prompt Category',
        'add_new_item'     => 'Add New Prompt Category',
        'new_item_name'    => 'New Prompt Category Name',
        'menu_name'        => 'Categories'
    );

    $cat_args = array(
        'hierarchical'      => true,
        'labels'           => $cat_labels,
        'show_ui'          => true,
        'show_admin_column' => true,
        'query_var'        => true,
        'rewrite'          => array('slug' => 'prompt-category'),
    );

    register_taxonomy('prompt_category', 'prompts', $cat_args);

    // Register the post type
    $labels = array(
        'name'               => 'Prompts',
        'singular_name'      => 'Prompt',
        'menu_name'          => 'WP Prompts',
        'add_new'           => 'Add New Prompt',
        'add_new_item'      => 'Add New Prompt',
        'edit_item'         => 'Edit Prompt',
        'view_item'         => 'View Prompt',
        'search_items'      => 'Search Prompts',
        'not_found'         => 'No prompts found',
        'not_found_in_trash'=> 'No prompts found in trash'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'supports'           => array('title', 'editor'),
        'menu_icon'          => 'dashicons-format-chat',
        'taxonomies'         => array('prompt_category', 'post_tag'),
        'rewrite'            => array('slug' => 'prompts'),
        'capability_type'    => 'post',
        'menu_position'      => 5
    );

    register_post_type('prompts', $args);
}

// Activation Hook
function wppl_activate_plugin() {
    wppl_register_prompts_post_type();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wppl_activate_plugin');

// Deactivation Hook
function wppl_deactivate_plugin() {
    unregister_post_type('prompts');
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'wppl_deactivate_plugin');

// Register post type on init
add_action('init', 'wppl_register_prompts_post_type');

// Add Meta Box
function wppl_add_prompt_meta_box() {
    add_meta_box(
        'prompt_details',
        'Prompt Details',
        'wppl_render_prompt_meta_box',
        'prompts',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'wppl_add_prompt_meta_box');

// Render Meta Box
function wppl_render_prompt_meta_box($post) {
    wp_nonce_field('wppl_save_prompt_meta', 'wppl_prompt_meta_nonce');

    $output_format = get_post_meta($post->ID, '_output_format', true);
    $prompt_text = get_post_meta($post->ID, '_prompt_text', true);
    $contexts = get_post_meta($post->ID, '_contexts', true);
    $reasoning_chain = get_post_meta($post->ID, '_reasoning_chain', true);
    $best_llm = get_post_meta($post->ID, '_best_llm', true);
    ?>
    <div class="prompt-meta-box">
        <p>
            <label for="output_format">Output/Format:</label><br>
            <input type="text" id="output_format" name="output_format" value="<?php echo esc_attr($output_format); ?>" class="widefat">
        </p>
        <p>
            <label for="prompt_text">Prompt:</label><br>
            <textarea id="prompt_text" name="prompt_text" class="widefat" rows="5"><?php echo esc_textarea($prompt_text); ?></textarea>
        </p>
        <p>
            <label for="contexts">Contexts (optional):</label><br>
            <textarea id="contexts" name="contexts" class="widefat" rows="3"><?php echo esc_textarea($contexts); ?></textarea>
        </p>
        <p>
            <label for="reasoning_chain">Reasoning Chain (optional):</label><br>
            <textarea id="reasoning_chain" name="reasoning_chain" class="widefat" rows="3"><?php echo esc_textarea($reasoning_chain); ?></textarea>
        </p>
        <p>
            <label for="best_llm">Best LLM for Prompt:</label><br>
            <input type="text" id="best_llm" name="best_llm" value="<?php echo esc_attr($best_llm); ?>" class="widefat">
        </p>
    </div>
    <?php
}

// Save Meta Box Data
function wppl_save_prompt_meta($post_id) {
    if (!isset($_POST['wppl_prompt_meta_nonce']) || 
        !wp_verify_nonce($_POST['wppl_prompt_meta_nonce'], 'wppl_save_prompt_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = array(
        'output_format',
        'prompt_text',
        'contexts',
        'reasoning_chain',
        'best_llm'
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, wp_kses_post($_POST[$field]));
        }
    }
}
add_action('save_post_prompts', 'wppl_save_prompt_meta');

// Customize Single Prompt Display
function wppl_single_prompt_content($content) {
    if (is_singular('prompts') && is_main_query()) {
        $post_id = get_the_ID();
        
        // Get the field values
        $fields = array(
            'Output/Format' => get_post_meta($post_id, '_output_format', true),
            'Prompt' => get_post_meta($post_id, '_prompt_text', true),
            'Contexts' => get_post_meta($post_id, '_contexts', true),
            'Reasoning Chain' => get_post_meta($post_id, '_reasoning_chain', true),
            'Best LLM' => get_post_meta($post_id, '_best_llm', true)
        );

        // Create the display output
        $output = '<div class="prompt-content">';
        $output .= '<h1>' . get_the_title() . '</h1>';

        // Create hidden div for copyable content
        $copyable_content = array();
        if (!empty($fields['Contexts'])) {
            $copyable_content[] = $fields['Contexts'];
        }
        if (!empty($fields['Prompt'])) {
            $copyable_content[] = $fields['Prompt'];
        }
        if (!empty($fields['Reasoning Chain'])) {
            $copyable_content[] = $fields['Reasoning Chain'];
        }
        
        $output .= '<div id="copyable-content" style="display: none;">' . 
                   esc_html(implode("\n\n", $copyable_content)) . 
                   '</div>';

        // Display all fields with headers
        foreach ($fields as $label => $value) {
            if (!empty($value)) {
                $output .= '<div class="prompt-field">';
                $output .= '<h3>' . $label . '</h3>';
                $output .= '<div class="field-content">' . nl2br(esc_html($value)) . '</div>';
                $output .= '</div>';
            }
        }

        $output .= '<button id="copy-prompt" class="button">Copy to Clipboard</button>';
        $output .= '</div>';

        // Add JavaScript for copy functionality
        $output .= "
        <script>
        document.getElementById('copy-prompt').addEventListener('click', function() {
            var promptContent = document.getElementById('copyable-content').innerText;
            navigator.clipboard.writeText(promptContent).then(function() {
                alert('Prompt copied to clipboard!');
            }).catch(function(err) {
                console.error('Failed to copy text: ', err);
                alert('Failed to copy to clipboard');
            });
        });
        </script>
        ";

        // Add styling
        $output .= "
        <style>
        .prompt-content {
            max-width: 800px;
            margin: 2em auto;
            padding: 20px;
        }
        .prompt-field {
            margin-bottom: 20px;
        }
        .prompt-field h3 {
            margin-bottom: 10px;
            color: #333;
        }
        .field-content {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
        }
        #copy-prompt {
            background: #0073aa;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        #copy-prompt:hover {
            background: #005177;
        }
        </style>
        ";

        return $output;
    }
    return $content;
}
add_filter('the_content', 'wppl_single_prompt_content');

// Display Prompts List Shortcode
function wppl_display_prompts_shortcode($atts) {
    $atts = shortcode_atts(array(
        'posts_per_page' => 20,
        'category' => '',
        'orderby' => 'date',
        'order' => 'DESC'
    ), $atts);

    ob_start();
    ?>
    <div class="prompts-list-container">
        <div class="prompts-filters">
            <div class="search-box">
                <input type="text" id="prompts-search" placeholder="Search prompts...">
            </div>
            <div class="filter-controls">
                <select id="category-filter">
                    <option value="">All Categories</option>
                    <?php
                    $categories = get_terms(array(
                        'taxonomy' => 'prompt_category',
                        'hide_empty' => true
                    ));
                    if (!is_wp_error($categories)) {
                        foreach ($categories as $category) {
                            echo sprintf(
                                '<option value="%s">%s</option>',
                                esc_attr($category->slug),
                                esc_html($category->name)
                            );
                        }
                    }
                    ?>
                </select>
                <select id="sort-filter">
                    <option value="date-desc">Newest First</option>
                    <option value="date-asc">Oldest First</option>
                    <option value="title-asc">Title A-Z</option>
                    <option value="title-desc">Title Z-A</option>
                </select>
                <?php 
                if (is_user_logged_in()) {
                    echo '<button id="export-prompts" class="button export-button">Export to CSV</button>';
                }
                ?>
            </div>
        </div>

        <div class="prompts-grid" id="prompts-grid">
            <?php
            $args = array(
                'post_type' => 'prompts',
                'posts_per_page' => $atts['posts_per_page'],
                'orderby' => $atts['orderby'],
                'order' => $atts['order'],
                'post_status' => 'publish'
            );

            if (!empty($atts['category'])) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'prompt_category',
                        'field' => 'slug',
                        'terms' => $atts['category']
                    )
                );
            }

            $prompts_query = new WP_Query($args);

            if ($prompts_query->have_posts()) {
                while ($prompts_query->have_posts()) {
                    $prompts_query->the_post();
                    $best_llm = get_post_meta(get_the_ID(), '_best_llm', true);
                    ?>
                    <div class="prompt-card">
                        <h3 class="prompt-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <div class="prompt-meta">
                            <span class="prompt-llm"><?php echo esc_html($best_llm); ?></span>
                            <span class="prompt-date"><?php echo get_the_date(); ?></span>
                        </div>
                        <?php
                        $categories = get_the_terms(get_the_ID(), 'prompt_category');
                        if ($categories && !is_wp_error($categories)) {
                            echo '<div class="prompt-categories">';
                            foreach ($categories as $category) {
                                echo '<span class="prompt-category">' . esc_html($category->name) . '</span>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <?php
                }
            } else {
                echo '<p>No prompts found.</p>';
            }
            wp_reset_postdata();
            ?>
        </div>
    </div>

    <style>
    .prompts-list-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    .prompts-filters {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }
    .search-box input,
    .filter-controls select {
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        min-width: 200px;
    }
    .prompts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .prompt-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .prompt-title {
        margin: 0 0 10px 0;
        font-size: 1.2em;
    }
    .prompt-title a {
        color: #333;
        text-decoration: none;
    }
    .prompt-meta {
        font-size: 0.9em;
        color: #666;
        margin-bottom: 10px;
    }
    .prompt-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }
    .prompt-category {
        background: #f0f0f0;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 0.8em;
        color: #555;
    }
    .export-button {
        background: #2271b1;
        color: white;
        padding: 8px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin-left: 10px;
    }
    .export-button:hover {
        background: #135e96;
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Search functionality
        $('#prompts-search').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            $('.prompt-card').each(function() {
                var text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(searchTerm) > -1);
            });
        });

        // Category and sort filters
        $('#category-filter, #sort-filter').on('change', function() {
            var category = $('#category-filter').val();
            var sort = $('#sort-filter').val();
            
            // Add your filter logic here
            // You might want to make an AJAX call to refresh the grid
        });

        // Export functionality
        $('#export-prompts').on('click', function() {
            var button = $(this);
            button.prop('disabled', true).text('Exporting...');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'export_prompts_csv',
                    nonce: '<?php echo wp_create_nonce('export_prompts_csv_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        // Create and trigger download
                        var blob = new Blob([response.data], { type: 'text/csv' });
                        var url = window.URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = 'prompts-export-' + new Date().toISOString().slice(0,10) + '.csv';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    } else {
                        alert('Export failed: ' + response.data);
                    }
                },
                error: function() {
                    alert('Export failed. Please try again.');
                },
                complete: function() {
                    button.prop('disabled', false).text('Export to CSV');
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('display_prompts', 'wppl_display_prompts_shortcode');

// Export functionality
function wppl_export_prompts_csv() {
    // Verify nonce and user permissions
    if (!check_ajax_referer('export_prompts_csv_nonce', 'nonce', false) || !is_user_logged_in()) {
        wp_send_json_error('Unauthorized access');
        return;
    }

    // Get all published prompts
    $args = array(
        'post_type' => 'prompts',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );

    $prompts = get_posts($args);

    if (empty($prompts)) {
        wp_send_json_error('No prompts found to export');
        return;
    }

    // Prepare CSV headers
    $headers = array(
        'Title',
        'Output/Format',
        'Prompt',
        'Contexts',
        'Reasoning Chain',
        'Best LLM',
        'Categories',
        'Tags',
        'Date Created'
    );

    // Start CSV content
    $csv_content = implode(',', $headers) . "\n";

    foreach ($prompts as $prompt) {
        // Get meta values
        $output_format = get_post_meta($prompt->ID, '_output_format', true);
        $prompt_text = get_post_meta($prompt->ID, '_prompt_text', true);
        $contexts = get_post_meta($prompt->ID, '_contexts', true);
        $reasoning_chain = get_post_meta($prompt->ID, '_reasoning_chain', true);
        $best_llm = get_post_meta($prompt->ID, '_best_llm', true);

        // Get categories and tags
        $categories = wp_get_post_terms($prompt->ID, 'prompt_category', array('fields' => 'names'));
        $categories_str = is_wp_error($categories) ? '' : implode('; ', $categories);
        
        $tags = wp_get_post_tags($prompt->ID, array('fields' => 'names'));
        $tags_str = is_wp_error($tags) ? '' : implode('; ', $tags);

        // Prepare row data
        $row = array(
            wppl_csv_escape($prompt->post_title),
            wppl_csv_escape($output_format),
            wppl_csv_escape($prompt_text),
            wppl_csv_escape($contexts),
            wppl_csv_escape($reasoning_chain),
            wppl_csv_escape($best_llm),
            wppl_csv_escape($categories_str),
            wppl_csv_escape($tags_str),
            get_the_date('Y-m-d', $prompt)
        );

        $csv_content .= implode(',', $row) . "\n";
    }

    wp_send_json_success($csv_content);
}
add_action('wp_ajax_export_prompts_csv', 'wppl_export_prompts_csv');

// Helper function for CSV escaping
function wppl_csv_escape($value) {
    if (empty($value)) return '';
    
    // Remove any existing quotes and escape special characters
    $value = str_replace('"', '""', $value);
    
    // Wrap in quotes if contains special characters
    if (preg_match('/[,\r\n"]/', $value)) {
        $value = '"' . $value . '"';
    }
    
    return $value;
}

// AJAX handler for filtering prompts
function wppl_filter_prompts() {
    check_ajax_referer('filter_prompts_nonce', 'nonce');

    $category = sanitize_text_field($_POST['category']);
    $sort = sanitize_text_field($_POST['sort']);
    $search = sanitize_text_field($_POST['search']);

    $args = array(
        'post_type' => 'prompts',
        'posts_per_page' => 20,
        'post_status' => 'publish'
    );

    // Add search if provided
    if (!empty($search)) {
        $args['s'] = $search;
    }

    // Add category filter if provided
    if (!empty($category)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'prompt_category',
                'field' => 'slug',
                'terms' => $category
            )
        );
    }

    // Add sorting
    list($orderby, $order) = explode('-', $sort);
    $args['orderby'] = $orderby;
    $args['order'] = strtoupper($order);

    $query = new WP_Query($args);
    
    ob_start();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $best_llm = get_post_meta(get_the_ID(), '_best_llm', true);
            ?>
            <div class="prompt-card">
                <h3 class="prompt-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>
                <div class="prompt-meta">
                    <span class="prompt-llm"><?php echo esc_html($best_llm); ?></span>
                    <span class="prompt-date"><?php echo get_the_date(); ?></span>
                </div>
                <?php
                $categories = get_the_terms(get_the_ID(), 'prompt_category');
                if ($categories && !is_wp_error($categories)) {
                    echo '<div class="prompt-categories">';
                    foreach ($categories as $category) {
                        echo '<span class="prompt-category">' . esc_html($category->name) . '</span>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
            <?php
        }
    } else {
        echo '<p>No prompts found.</p>';
    }
    
    wp_reset_postdata();
    
    $html = ob_get_clean();
    wp_send_json_success($html);
}
add_action('wp_ajax_filter_prompts', 'wppl_filter_prompts');
add_action('wp_ajax_nopriv_filter_prompts', 'wppl_filter_prompts');

// Update the JavaScript in the shortcode to handle filtering
function wppl_add_filter_script() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        function filterPrompts() {
            var category = $('#category-filter').val();
            var sort = $('#sort-filter').val();
            var search = $('#prompts-search').val();

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'filter_prompts',
                    category: category,
                    sort: sort,
                    search: search,
                    nonce: '<?php echo wp_create_nonce('filter_prompts_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#prompts-grid').html(response.data);
                    }
                }
            });
        }

        var filterTimer;
        $('#prompts-search').on('input', function() {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(filterPrompts, 500);
        });

        $('#category-filter, #sort-filter').on('change', filterPrompts);
    });
    </script>
    <?php
}
add_action('wp_footer', 'wppl_add_filter_script');

// Add necessary admin styles
function wppl_admin_styles() {
    if (get_post_type() === 'prompts') {
        ?>
        <style>
        .prompt-meta-box {
            padding: 12px;
        }
        .prompt-meta-box p {
            margin: 1em 0;
        }
        .prompt-meta-box label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .prompt-meta-box textarea {
            width: 100%;
            margin-top: 5px;
        }
        </style>
        <?php
    }
}
add_action('admin_head', 'wppl_admin_styles');


