# WooCommerce Integration - Quick Start Guide

## Overview

This theme now supports WooCommerce products with custom features:

- ✅ Download URL field per product
- ✅ Star rating (0-5) per product
- ✅ "Add to Cart" button removed
- ✅ Responsive grid layout (HappyMod.cx style)
- ✅ Easy shortcode or PHP integration

---

## Step 1: Setup in WordPress Admin

### 1.1 Install WooCommerce

- Go to Plugins > Add New
- Search: "WooCommerce"
- Click Install and Activate

### 1.2 Create Product Categories

1. Go to **Products > Categories**
2. Add New:
   - Name: **Game**
   - Slug: game
   - Description: (optional)
   - Featured Image: (optional)
3. Repeat for **App** category

### 1.3 Add Some Products

1. Go to **Products > Add New**
2. Fill in:
   - **Title**: "Extreme Car Driving Simulator Hack"
   - **Description**: Game details
   - **Featured Image**: Product thumbnail
   - **Category**: Select "Game"
3. Scroll down to **Download URL** metabox:
   - Paste download link: `https://example.com/download/game.apk`
   - Button text: "Tải xuống" (default)
4. Scroll down to **Product Rating** metabox:
   - Enter: `4.5` (or any 0-5 value)
5. Click **Publish**

Repeat for more products.

---

## Step 2: Display Products on Homepage

### Option A: Use Shortcode (Easiest)

If you're using a page builder or editing front-page.php, add this shortcode:

```
[vy_products_grid
  title="GAME Mới Cập Nhật"
  category="game"
  per_page="12"
  orderby="date"
  order="DESC"
  view_more_text="Xem thêm"
  view_more_url="/shop/game/"
]
```

**Shortcode Parameters:**

- `title`: Section heading
- `category`: Product category (game, app, etc.)
- `per_page`: How many products to show
- `orderby`: Sort by (date, title, price, popularity, rating)
- `order`: Ascending (ASC) or Descending (DESC)
- `view_more_text`: Text for "View More" button
- `view_more_url`: Link for "View More" button

**Multiple Categories Example:**

```
[vy_products_grid
  category="game,app"
  per_page="20"
]
```

### Option B: Use PHP in front-page.php

Add this code directly in front-page.php where you want products to display:

```php
<!-- GAME SECTION -->
<section class="vy-products-section">
    <div class="vy-container">
        <h2><?php _e('GAME Mới Cập Nhật', 'vy-theme'); ?></h2>

        <?php
        $game_args = [
            'post_type'      => 'product',
            'posts_per_page' => 12,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => 'game',
                ]
            ]
        ];

        $game_query = new WP_Query($game_args);
        ?>

        <?php if ($game_query->have_posts()): ?>
            <div class="apk-grid">
                <?php while ($game_query->have_posts()): $game_query->the_post(); ?>
                    <div class="apk-item">
                        <!-- Thumbnail -->
                        <div class="apk-thumbnail">
                            <?php
                            $thumb_id = get_post_thumbnail_id();
                            if ($thumb_id) {
                                echo wp_get_attachment_image($thumb_id, 'woocommerce_thumbnail', false, [
                                    'alt' => esc_attr(get_the_title()),
                                    'loading' => 'lazy'
                                ]);
                            } else {
                                echo wc_placeholder_img();
                            }
                            ?>
                        </div>

                        <!-- Info -->
                        <div class="apk-info">
                            <h3>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h3>

                            <!-- Categories -->
                            <?php echo vy_get_product_categories_html(get_the_ID(), 3); ?>

                            <!-- Box Bottom: Rating + Button -->
                            <div class="box-bottom">
                                <!-- Stars -->
                                <?php echo vy_get_product_star_rating(get_the_ID()); ?>

                                <!-- Download Button -->
                                <?php echo vy_get_product_download_button(get_the_ID()); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <div class="apk-view-more">
                <a href="<?php echo esc_url('/shop/game/'); ?>" class="btn">
                    <?php _e('Xem thêm', 'vy-theme'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- APP SECTION (same logic, change category to "app") -->
<section class="vy-products-section">
    <div class="vy-container">
        <h2><?php _e('APP Mới Cập Nhật', 'vy-theme'); ?></h2>

        <?php
        $app_args = [
            'post_type'      => 'product',
            'posts_per_page' => 12,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => 'app',
                ]
            ]
        ];

        $app_query = new WP_Query($app_args);
        ?>

        <?php if ($app_query->have_posts()): ?>
            <div class="apk-grid">
                <?php while ($app_query->have_posts()): $app_query->the_post(); ?>
                    <div class="apk-item">
                        <div class="apk-thumbnail">
                            <?php
                            $thumb_id = get_post_thumbnail_id();
                            if ($thumb_id) {
                                echo wp_get_attachment_image($thumb_id, 'woocommerce_thumbnail', false, [
                                    'alt' => esc_attr(get_the_title()),
                                    'loading' => 'lazy'
                                ]);
                            } else {
                                echo wc_placeholder_img();
                            }
                            ?>
                        </div>

                        <div class="apk-info">
                            <h3>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h3>

                            <?php echo vy_get_product_categories_html(get_the_ID(), 3); ?>

                            <div class="box-bottom">
                                <?php echo vy_get_product_star_rating(get_the_ID()); ?>
                                <?php echo vy_get_product_download_button(get_the_ID()); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <div class="apk-view-more">
                <a href="<?php echo esc_url('/shop/app/'); ?>" class="btn">
                    <?php _e('Xem thêm', 'vy-theme'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
```

### Option C: Native WooCommerce Shortcodes

```
[products category="game" limit="12" columns="4" orderby="date" order="DESC"]
```

---

## Step 3: Customize Styling (Optional)

Edit `css/woocommerce-products.css` to adjust:

- Colors (--apk-btn-bg, --apk-title-color, etc.)
- Grid spacing (--apk-gap)
- Responsive breakpoints
- Button styling

---

## Responsive Grid Layout

**Desktop** (> 768px):

- Auto-fit columns, ~280px each
- Hover effect: scale + shadow

**Tablet** (481px - 768px):

- 2-3 columns
- Touch-friendly spacing

**Mobile** (≤ 480px):

- 1 column, full width
- Optimized button size

---

## Star Rating Icons (Important!)

The rating uses images. You need to upload:

1. `images/star.png` - Filled star icon
2. `images/star-empty.png` - Empty star icon

Size: 16x16px or 32x32px (will be resized by CSS)

**Alternative:** Use FontAwesome stars by modifying `vy_get_product_star_rating()` in `inc/woocommerce-setup.php`

---

## Troubleshooting

**Q: Products not showing?**

- ✓ WooCommerce installed & activated?
- ✓ Products published (not draft)?
- ✓ Categories assigned?
- ✓ Check browser console for errors

**Q: Download button missing?**

- ✓ Edit product > scroll to "Download URL"
- ✓ Enter valid URL
- ✓ Save product

**Q: Stars not showing?**

- ✓ Upload star icons to /images/
- ✓ OR edit vy_get_product_star_rating() for FontAwesome

**Q: Products from all categories showing?**

- ✓ Make sure `tax_query` is set with correct category slug
- ✓ Category slug should match project (e.g., "game", "app")

---

## API Reference

### Custom Functions

**`vy_get_product_download_button($product_id)`**
Returns HTML for download button. Example:

```php
<?php echo vy_get_product_download_button(123); ?>
```

**`vy_get_product_star_rating($product_id)`**
Returns HTML for star rating. Example:

```php
<?php echo vy_get_product_star_rating(123); ?>
```

**`vy_get_product_categories_html($product_id, $limit)`**
Returns category links. Example:

```php
<?php echo vy_get_product_categories_html(123, 3); ?>
```

### WooCommerce Functions

**`wc_get_product($product_id)`** - Get product object
**`wc_placeholder_img()` - Get placeholder image HTML
**`wp_get_attachment_image($thumb_id, $size)` - Get image HTML

---

## Next Steps

For questions or issues, check `inc/WOOCOMMERCE_GUIDE.php` for detailed examples!
