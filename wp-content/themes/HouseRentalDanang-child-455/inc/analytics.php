<?php
/**
 * Google Analytics 4 bootstrap.
 */

function hrd_print_google_tag() {
	?>
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-HHJ3X825EG"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', 'G-HHJ3X825EG');
	</script>
	<?php
}
add_action( 'wp_head', 'hrd_print_google_tag', 2 );
