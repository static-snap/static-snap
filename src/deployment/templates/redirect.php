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
	<meta http-equiv="refresh" content="0;url=<?php echo esc_url( $redirect_url ); ?>">
</head>
<body>
<body>
	<?php
	$redirect_anchor_link = '<a href = "' . esc_html( $escaped_url ) . '">' . esc_html( $escaped_url ) . '</a>';
	?>

		<p>
		<?php
		// Translators: %s is the URL to which the user will be redirected.
		sprintf( __( 'If you are not redirected automatically, follow this: %s ', 'static-snap' ), $redirect_anchor_link );
		?>
		.</p>
</body>

</html>
