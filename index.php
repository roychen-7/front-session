<?php

require_once './Session.class.php';

$session = Session::get_instance();

if (!$session->init())
{
	print_r('refresh to see session');
	$session->set('a', 1);
}
else
{
	echo '<pre>';
	print_r($session->all());
}

if ($_GET['clear'])
{
	$session->destroy();
}


?>
