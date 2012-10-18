<?php disallow_direct_load('sidebar.php');?>

<?php if(!function_exists('dynamic_sidebar') or !dynamic_sidebar('Sidebar')):?>
	<?=wp_nav_menu(array(
		'theme_location' => 'main-menu', 
		'container' => 'false', 
		'menu_class' => 'menu '.get_header_styles(), 
		'menu_id' => 'header-menu', 
		'walker' => new Bootstrap_Walker_Nav_Menu()
		));
	?>
<?php endif;?>