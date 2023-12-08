<?php

/**
 * Header file for the Twenty Twenty WordPress default theme.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */

?>
<!DOCTYPE html>

<html class="no-js" <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php
	wp_body_open();
	?>

	<header id="site-header" class="header-footer-group sticky-top">

		<div id="custom-navigation-bar">
			<div class="custom-navigation-container">
				<a href="http://localhost/planty">
					<img class="custom-logo" src="http://localhost/planty/wp-content/uploads/2023/09/Logo-mini-size-planty-1.png" alt="Logo">
				</a>
				<nav class="custom-primary-menu" aria-label="<?php echo esc_attr_x('Horizontal', 'menu', 'twentytwenty'); ?>">
					<ul class="custom-navigation-links">
						<?php
						if (has_nav_menu('primary')) {
							wp_nav_menu(
								array(
									'container'  => '',
									'items_wrap' => '%3$s',
									'theme_location' => 'primary',
								)
							);
						} elseif (!has_nav_menu('expanded')) {
							wp_list_pages(
								array(
									'match_menu_classes' => true,
									'show_sub_menu_icons' => true,
									'title_li' => false,
									'walker'   => new TwentyTwenty_Walker_Page(),
								)
							);
						}
						?>
					</ul>
				</nav><!-- .primary-menu-wrapper -->
			</div>
		</div>

	</header><!-- #site-header -->