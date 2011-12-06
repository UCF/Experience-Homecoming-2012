<?php
/**
 * Template Name: Home
 **/
?>
<?php get_header();?>
	<?php $options = get_option(THEME_OPTIONS_NAME);?>
	<?php $page    = get_page_by_title('Home');?>
	<div class="span-24 last page-content" id="home">
		<div class="image span-16">
			<?=wp_get_attachment_image($options['site_image'], 'homepage')?>
		</div>
		
		<div class="description span-8 last">
			<?php $description = $options['site_description'];?>
			<p><?=$description?></p>
		</div>
		
		<div class="bottom span-24 last">
			<div class="content span-15 append-1">
				<?php $content = str_replace(']]>', ']]&gt;', apply_filters('the_content', $page->post_content));?>
				<?=$content?>
			</div>
		
			<div class="span-8 last">
				<?php display_events()?>
			</div>
		</div>
	</div>

<?php get_footer();?>