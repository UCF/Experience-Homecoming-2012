<?php get_header(); ?>
<?php
	if (is_user_logged_in() && current_user_can('edit_post')) {
		$post_id = is_numeric($_GET['id']) ? $_GET['id'] : '';
		$post_status = $_GET['status'] ? $_GET['status'] : '';
		$post = get_post($post_id);
		if ($post_id !== '' && $post_status !== '' && (empty($post) == false)) {
			$post_updates = array();
			switch ($post_status) {
				case 't':
					$post_updates['post_status'] = 'trash';
					break;
				case 'p':
					$post_updates['post_status'] = 'publish';
					break;
				default:
					break;
			}
			print 'success';
			wp_update_post($post_updates);
		}
	}
	else { print 'No.'; }
?>
<?php get_footer(); ?>