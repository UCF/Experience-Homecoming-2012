<?php get_header(); ?>
<?php
	if (is_user_logged_in() && current_user_can('edit_post')) {
		$post_id = is_numeric($_GET['id']) ? $_GET['id'] : '';
		$post = get_post($post_id);
		if ($post_id !== '' && (empty($post) == false)) {
			$post_updates = array();
			$post_updates['post_status'] = 'publish';
			print 'success';
			wp_update_post($post_updates);
		}
	}
	else { print 'No.'; }
?>
<?php get_footer(); ?>