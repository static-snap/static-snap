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
<body>
	<?php
	$redirect_anchor_link = "<a href = \"$redirect_url\" >$redirect_url</a>";
	?>

		<p>
		<?php
		// Translators: %s is the URL to which the user will be redirected.
		sprintf( __( 'If you are not redirected automatically, follow this: %s ', 'static-snap' ), $redirect_anchor_link );
		?>
		.</p>
</body>

</html>
