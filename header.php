<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header id="header" class="header has-sticky sticky-jump">
        <div class="header-wrapper">
            <div id="masthead" class="header-main">
                <div class="header-inner flex-row container logo-left medium-logo-center" role="navigation">

                    <!-- Logo (Left) -->
                    <div id="logo" class="flex-col logo">
                        <?php
                        if (has_custom_logo()) {
                            the_custom_logo();
                        } else {
                            echo '<a href="' . esc_url(home_url('/')) . '" class="logo-link">' .
                                '<img src="' . esc_url(get_template_directory_uri()) . '/images/logo.png" alt="' . esc_attr(get_bloginfo('name')) . '">' .
                                '</a>';
                        }
                        ?>
                    </div>

                    <div class="flex-col show-for-medium flex-left">
                        <ul class="mobile-nav nav nav-left">
                            <li class="nav-icon has-icon">
                                <a href="#" id="vy-menu-toggle" data-open="#main-menu"
                                    aria-label="<?php esc_attr_e('Menu', 'voya'); ?>" aria-controls="main-menu"
                                    aria-expanded="false" class="is-small">
                                    <i class="fa-solid fa-bars"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Desktop Center Elements (Desktop Search) -->
                    <div class="flex-col hide-for-medium flex-grow search-center">
                        <ul class="header-nav header-nav-main nav nav-center nav-uppercase">
                            <li class="header-search-form search-form html relative has-icon">
                                <div class="header-search-form-wrapper">
                                    <div class="searchform-wrapper ux-search-box relative form-flat is-normal">
                                        <form method="get" class="searchform"
                                            action="<?php echo esc_url(home_url('/')); ?>" role="search">
                                            <div class="flex-row relative">
                                                <div class="flex-col flex-grow">
                                                    <input type="search" class="search-field mb-0" name="s"
                                                        placeholder="<?php echo esc_attr(get_theme_mod('vy_search_placeholder', 'Tìm kiếm')); ?>"
                                                        value="<?php echo esc_attr(get_search_query()); ?>"
                                                        autocomplete="off">
                                                </div>
                                                <div class="flex-col">
                                                    <button type="submit"
                                                        class="ux-search-submit submit-button secondary icon mb-0"
                                                        aria-label="<?php esc_attr_e('Search', 'voya'); ?>">
                                                        <i class="fa-solid fa-magnifying-glass"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Desktop Right Elements (Desktop Navigation) -->
                    <div class="flex-col hide-for-medium flex-right">
                        <ul class="header-nav header-nav-main nav nav-right nav-uppercase">
                            <?php
                            wp_nav_menu([
                                'theme_location' => 'primary',
                                'container' => false,
                                'menu_class' => '',
                                'items_wrap' => '%3$s',
                                'fallback_cb' => 'vy_nav_fallback',
                                'depth' => 1,
                            ]);
                            ?>
                        </ul>
                    </div>

                    <!-- Mobile Right Elements (Mobile Search Icon) -->
                    <div class="flex-col show-for-medium flex-right">
                        <ul class="mobile-nav nav nav-right">
                            <li
                                class="header-search header-search-dropdown has-icon has-dropdown menu-item-has-children">
                                <a href="#" id="vy-search-toggle-mobile"
                                    aria-label="<?php esc_attr_e('Search', 'voya'); ?>" class="is-small">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>

                <div class="container">
                    <div class="top-divider full-width"></div>
                </div>
            </div>

            <div class="header-bg-container fill">
                <div class="header-bg-image fill"></div>
                <div class="header-bg-color fill"></div>
            </div>
        </div>
    </header>

    <!-- Mobile Menu Sidebar (Drawer) -->
    <div class="vy-sidebar-overlay" id="vy-overlay"></div>
    <div class="mfp-content">
        <div id="main-menu" class="mobile-sidebar no-scrollbar">
            <div class="sidebar-menu no-scrollbar">

                <!-- Mobile Search Form -->
                <ul class="nav nav-sidebar nav-vertical nav-uppercase" data-tab="1">
                    <li class="header-search-form search-form html relative has-icon">
                        <div class="header-search-form-wrapper">
                            <div class="searchform-wrapper ux-search-box relative form-flat is-normal">
                                <form method="get" class="searchform" action="<?php echo esc_url(home_url('/')); ?>"
                                    role="search">
                                    <div class="flex-row relative">
                                        <div class="flex-col flex-grow">
                                            <input type="search" class="search-field mb-0" name="s"
                                                placeholder="<?php echo esc_attr(get_theme_mod('vy_search_placeholder', 'Tìm kiếm')); ?>"
                                                autocomplete="off">
                                        </div>
                                        <div class="flex-col">

                                            <button type="submit"
                                                class="ux-search-submit submit-button secondary icon mb-0"
                                                aria-label="<?php esc_attr_e('Search', 'voya'); ?>">
                                                <i class="fa-solid fa-magnifying-glass"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </li>

                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'container' => false,
                        'menu_class' => '',
                        'items_wrap' => '%3$s',
                        'fallback_cb' => 'vy_nav_fallback',
                        'depth' => 1,
                    ]);
                    ?>
                </ul>
            </div>
        </div>
    </div>