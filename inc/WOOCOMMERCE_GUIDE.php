<?php
/**
 * WooCommerce Products Display Examples
 * 
 * This file shows different ways to display WooCommerce products
 * on your front-page.php or via shortcodes
 */

// ============================================================
// USAGE EXAMPLES
// ============================================================

/**
 * OPTION 1: Using Shortcode in Page Editor
 * 
 * [vy_products_grid 
 *   title="GAME Mới Cập Nhật"
 *   per_page="12"
 *   category="game"
 *   orderby="date"
 *   order="DESC"
 *   view_more_text="Xem thêm"
 *   view_more_url="https://yoursite.com/shop/game/"
 * ]
 * 
 * ATTRIBUTES:
 * - title: Section title (optional)
 * - per_page: Number of products to display (default: 12)
 * - category: Product category slug(s), comma-separated (optional)
 * - orderby: Sort by 'date', 'title', 'price', etc. (default: 'date')
 * - order: 'DESC' or 'ASC' (default: 'DESC')
 * - view_more_text: Text for "View More" button (optional)
 * - view_more_url: URL for "View More" button (optional)
 */

// ============================================================
// OPTION 2: Using WooCommerce Native Shortcodes
// ============================================================

/**
 * Display products by category:
 * [products category="game" limit="12" columns="4" orderby="date" order="DESC"]
 * 
 * Display specific products:
 * [products ids="123,456,789"]
 * 
 * Display best sellers:
 * [best_selling_products limit="12" columns="4"]
 */

// ============================================================
// OPTION 3: Using PHP directly in front-page.php
// ============================================================

/**
 * Example code to add to front-page.php:
 * 
 * <?php
 * // Display Game Products
 * $game_args = [
 *     'post_type'      => 'product',
 *     'posts_per_page' => 12,
 *     'orderby'        => 'date',
 *     'order'          => 'DESC',
 *     'tax_query'      => [
 *         [
 *             'taxonomy' => 'product_cat',
 *             'field'    => 'slug',
 *             'terms'    => 'game',
 *         ]
 *     ]
 * ];
 * 
 * $game_query = new WP_Query($game_args);
 * ?>
 *
 * <section class="vy-products-section">
 * <div class="vy-container">
 * <h2><?php _e('GAME Mới Cập Nhật', 'vy-theme'); ?></h2>
 *
 * <?php if ($game_query->have_posts()): ?>
 * <div class="apk-grid">
 * <?php while ($game_query->have_posts()): $game_query->the_post(); ?>
 * <div class="apk-item">
 * <div class="apk-thumbnail">
 * <?php
 *                             $thumb_id = get_post_thumbnail_id();
 *                             if ($thumb_id) {
 *                                 echo wp_get_attachment_image($thumb_id, 'woocommerce_thumbnail');
 *                             } else {
 *                                 echo wc_placeholder_img();
 *                             }
 *                             ?>
 * </div>
 *
 * <div class="apk-info">
 * <h3>
 * <a href="<?php the_permalink(); ?>">
 * <?php the_title(); ?>
 * </a>
 * </h3>
 *
 * <?php echo vy_get_product_categories_html(get_the_ID(), 3); ?>
 *
 * <div class="box-bottom">
 * <?php echo vy_get_product_star_rating(get_the_ID()); ?>
 * <?php echo vy_get_product_download_button(get_the_ID()); ?>
 * </div>
 * </div>
 * </div>
 * <?php endwhile; wp_reset_postdata(); ?>
 * </div>
 * <?php endif; ?>
 * </div>
 * </section>
 */

// ============================================================
// SETUP INSTRUCTIONS
// ============================================================

/**
 * STEP 1: Enable WooCommerce Support
 * - Install and activate WooCommerce plugin
 * - Go to WooCommerce > Settings to configure
 *
 * STEP 2: Create Product Categories (if not exists)
 * - Go to Products > Categories
 * - Create "Game", "App" categories
 * - Optionally set featured image for categories
 *
 * STEP 3: Add Products
 * - Go to Products > Add New
 * - Set Title, Description (content)
 * - Set Category (Game/App)
 * - Set Featured Image (product thumbnail)
 * - In "Download URL" metabox, enter the download link
 * - In "Product Rating" metabox, enter rating (0-5)
 * - Publish
 *
 * STEP 4: Display on Homepage
 * Option A: Use Page Editor
 * - Edit your front page
 * - Add the shortcode: [vy_products_grid title="GAME Mới Cập Nhật" category="game"]
 *
 * Option B: Edit front-page.php
 * - Add PHP code directly in the template
 * - See OPTION 3 example above
 *
 * STEP 5: Customize Styling
 * - Edit css/woocommerce-products.css for grid layout
 * - Modify colors, spacing, responsive breakpoints
 */

// ============================================================
// AVAILABLE HELPER FUNCTIONS
// ============================================================

/**
 * Get product download button HTML
 *
 * Usage: vy_get_product_download_button($product_id)
 *
 * Returns: <a class="apk-download-btn" href="...">Download Text</a>
 */

/**
 * Get product star rating HTML
 *
 * Usage: vy_get_product_star_rating($product_id)
 *
 * Returns: <div class="rating">★★★★☆</div>
 *
 * Note: You need to create/upload star.png and star-empty.png
 * in the /images/ folder
 */

/**
 * Get product categories HTML
 *
 * Usage: vy_get_product_categories_html($product_id, $limit = 3)
 *
 * Returns: <p class="apk-category"><a href="...">Category 1</a>, ...</p>
 */

// ============================================================
// SHORTCODE PARAMETERS REFERENCE
// ============================================================

/**
 * Full Shortcode Example with all parameters:
 *
 * [vy_products_grid
 * title="Latest Games"
 * category="game,casual"
 * per_page="20"
 * orderby="date"
 * order="DESC"
 * view_more_text="View All Games"
 * view_more_url="https://example.com/shop/game/"
 * ]
 *
 * Parameters:
 * - title (string): Section heading. Default: empty
 * - category (string): Product category slug(s). Comma-separated for multiple. Default: empty (all)
 * - per_page (int): Number of products per page. Default: 12. Range: 1-100
 * - orderby (string): Sort by. Options: 'date', 'title', 'price', 'popularity', 'rating'. Default: 'date'
 * - order (string): Sort order. Options: 'DESC', 'ASC'. Default: 'DESC'
 * - view_more_text (string): "View More" link text. Default: 'Xem thêm'
 * - view_more_url (string): "View More" link URL. Leave empty to hide button. Default: empty
 */

// ============================================================
// TROUBLESHOOTING
// ============================================================

/**
 * Issue: Products not showing
 * Solution:
 * 1. Make sure WooCommerce is installed and activated
 * 2. Check that products are published (not draft)
 * 3. Verify product category assignments
 * 4. Check browser console for JavaScript errors
 *
 * Issue: Download button not showing
 * Solution:
 * 1. Edit each product
 * 2. Scroll to "Download URL" metabox
 * 3. Enter a valid URL
 * 4. Save the product
 *
 * Issue: Rating stars not showing correctly
 * Solution:
 * 1. Upload star.png and star-empty.png to /images/ folder
 * 2. OR modify the vy_get_product_star_rating() function
 * to use CSS-based stars (FontAwesome, etc.)
 *
 * Issue: Grid layout not responsive
 * Solution:
 * 1. Check css/woocommerce-products.css @media queries
 * 2. Ensure WooCommerce CSS is loaded
 * 3. Check for conflicting CSS from other plugins
 */