<?php disallow_direct_load('single.php');?>
<?php get_header(); the_post();?>
	
	<div class="row-fluid page-content" id="<?=$post->post_type?>-<?=$post->post_name?>">
		<div class="span3" id="sidebar">
			<?=get_sidebar();?>
		</div>
		<div class="span9" id="content-col">
			<? if(!is_front_page())	{ ?>
					<h1><?php the_title();?></h1>
			<? } ?>
			<?php the_content();?>
		</div>
	</div>

<?php get_footer();?>