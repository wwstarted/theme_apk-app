<?php
/**
 * VOYA — search-api.php
 */

if (!defined('ABSPATH'))
    exit;

/* ── Register endpoint ── */
add_action('rest_api_init', 'vy_register_search_endpoint');

function vy_register_search_endpoint()
{
    register_rest_route('voya/v1', '/search', [
        'methods' => 'GET',
        'callback' => 'vy_search_callback',
        'permission_callback' => '__return_true',
        'args' => [
            'q' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function ($val) {
                    return strlen($val) >= 1 && strlen($val) <= 200;
                },
            ],
            'per_page' => [
                'default' => 6,
                'type' => 'integer',
                'sanitize_callback' => 'absint',
            ],
        ],
    ]);
}

/* ── Callback ── */
function vy_search_callback(WP_REST_Request $request)
{
    $query = $request->get_param('q');
    $per_page = min(12, max(1, $request->get_param('per_page')));

    if (strlen($query) < 1) {
        return rest_ensure_response(['products' => [], 'posts' => []]);
    }

    /* ── Search products (WooCommerce) ── */
    $product_query = new WP_Query([
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        's' => $query,
        'no_found_rows' => true,
        'fields' => 'ids',
    ]);

    $products = [];
    foreach ($product_query->posts as $pid) {
        $thumb = get_the_post_thumbnail_url($pid, 'thumbnail')
            ?: get_the_post_thumbnail_url($pid, 'woocommerce_thumbnail');

        // Categories
        $terms = get_the_terms($pid, 'product_cat');
        $cat_name = '';
        if ($terms && !is_wp_error($terms)) {
            $cat_name = implode(', ', array_slice(wp_list_pluck($terms, 'name'), 0, 2));
        }

        $products[] = [
            'id' => $pid,
            'title' => get_the_title($pid),
            'url' => get_permalink($pid),
            'thumbnail' => $thumb ?: '',
            'category' => $cat_name,
            'post_type_label' => 'Game / App',
        ];
    }
    wp_reset_postdata();

    /* ── Search posts (blog) ── */
    $post_query = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        's' => $query,
        'no_found_rows' => true,
        'fields' => 'ids',
    ]);

    $posts = [];
    foreach ($post_query->posts as $pid) {
        $thumb = get_the_post_thumbnail_url($pid, 'thumbnail')
            ?: get_the_post_thumbnail_url($pid, 'medium');

        // Post categories
        $cats = get_the_category($pid);
        $cat_name = '';
        if (!empty($cats)) {
            $cat_name = implode(', ', array_slice(wp_list_pluck($cats, 'name'), 0, 2));
        }

        // Date
        $date = get_the_date('d/m/Y', $pid);

        $posts[] = [
            'id' => $pid,
            'title' => get_the_title($pid),
            'url' => get_permalink($pid),
            'thumbnail' => $thumb ?: '',
            'category' => $cat_name,
            'date' => $date,
            'post_type_label' => 'Blog',
        ];
    }
    wp_reset_postdata();

    return rest_ensure_response([
        'products' => $products,
        'posts' => $posts,
    ]);
}