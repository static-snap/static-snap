<?php
/**
 * Redirect template
 *
 * @package StaticSnap
 */

?>
<!DOCTYPE html>
<html>
<head>
	<title>Redirecting...</title>
	<meta http-equiv="refresh" content="0;url=<?php echo $redirect_url; //phpcs:ignore ?>">
</head>
<body>
<script>
	window.location.replace( '<?php echo $redirect_url; //phpcs:ignore ?>' );
</script>

</html>
