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
					<img src="http://localhost/planty/wp-content/uploads/2023/11/Logo-mini-size-planty-2.png" alt="Logo">
				</a>
				<nav class="custom-navigation-links">
					<ul>
						<li><a href="http://localhost/planty/nous-rencontrer">Nous rencontrer</a></li>
						<li><a href="http://localhost/planty/admin">Admin</a></li>
						<li><a href="http://localhost/planty/commander">Commander</a></li>
					</ul>
				</nav>
			</div>
		</div>
		
		

	</header><!-- #site-header -->