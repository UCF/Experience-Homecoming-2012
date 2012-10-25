<?php
$_SERVER = Array();
// Needs to match current host
$_SERVER['HTTP_HOST'] = 'experience.wp3qa.smca.ucf.edu';
require('../../../../wp-load.php');
print create_feedsubmissions();
?>