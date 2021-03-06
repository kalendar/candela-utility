<?php

/* ------------------------------------------------------------------------ *
 * Google Webfonts
 * ------------------------------------------------------------------------ */

function fitzgerald_enqueue_styles() {
	wp_enqueue_style( 'fitzgerald-fonts', 'https://fonts.googleapis.com/css?family=Crimson+Text:400,400italic,700|Roboto+Condensed:400,300,300italic,400italic' );
}
add_action( 'wp_print_styles', 'fitzgerald_enqueue_styles' );

function fitzgerald_theme_scripts() {
	wp_enqueue_script( 'embedded_audio', get_stylesheet_directory_uri() . '/js/audio_behavior.js', array( 'jquery' ), '', true );
}
add_action( 'wp_enqueue_scripts', 'fitzgerald_theme_scripts' );

function fitzgerald_enqueue_lt_ie9() {
	global $is_IE;

	if ( ! $is_IE ) {
		return;
	}

	if ( ! function_exists( 'wp_check_browser_version' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/dashboard.php' );
	}

	$response = wp_check_browser_version();

	if ( 0 > version_compare( intval( $response['version'] ), 9 ) ) {
		wp_enqueue_script( 'fitzgerald-html5shiv', 'http://html5shim.googlecode.com/svn/trunk/html5.js', array(), 'pre3.6', false );
	}
}
add_action( 'wp_enqueue_scripts', 'fitzgerald_enqueue_lt_ie9' );

/**
 * Returns an html blog of meta elements
 *
 * @return string $html metadata
 */
function pbt_get_seo_meta_elements() {
	// map items that are already captured
	$meta_mapping = array(
		'author' => 'pb_author',
		'description' => 'pb_about_50',
		'keywords' => 'pb_keywords_tags',
		'publisher' => 'pb_publisher',
	);

	$html = "<meta name='application-name' content='PressBooks'>\n";
	$metadata = \PressBooks\Book::getBookInformation();

	// create meta elements
	foreach ( $meta_mapping as $name => $content ) {
		if ( array_key_exists( $content, $metadata ) ) {
			$html .= "<meta name='" . $name . "' content='" . $metadata[ $content ] . "'>\n";
		}
	}

	return $html;
}

function pbt_get_microdata_meta_elements() {
	// map items that are already captured
	$html = '';
	$micro_mapping = array(
		'about' => 'pb_bisac_subject',
		'alternativeHeadline' => 'pb_subtitle',
		'author' => 'pb_author',
		'contributor' => 'pb_contributing_authors',
		'copyrightHolder' => 'pb_copyright_holder',
		'copyrightYear' => 'pb_copyright_year',
		'datePublished' => 'pb_publication_date',
		'description' => 'pb_about_50',
		'editor' => 'pb_editor',
		'image' => 'pb_cover_image',
		'inLanguage' => 'pb_language',
		'keywords' => 'pb_keywords_tags',
		'publisher' => 'pb_publisher',
	);
	$metadata = \PressBooks\Book::getBookInformation();

	// create microdata elements
	foreach ( $micro_mapping as $itemprop => $content ) {
		if ( array_key_exists( $content, $metadata ) ) {
			if ( 'pb_publication_date' == $content ) {
				$content = date( 'Y-m-d', $metadata[ $content ] );
			} else {
				$content = $metadata[ $content ];
			}
			$html .= "<meta itemprop='" . $itemprop . "' content='" . $content . "' id='" . $itemprop . "'>\n";
		}
	}

	return $html;
}

/**
 * Sends a Window.postMessage to resize the iframe
 * (Only works in Canvas for now)
 */
function add_iframe_resize_message() {
	printf(
		'<script>
			// get rid of double iframe scrollbars
			var default_height = Math.max(
				document.body.scrollHeight, document.body.offsetHeight,
				document.documentElement.clientHeight, document.documentElement.scrollHeight,
				document.documentElement.offsetHeight);
				parent.postMessage(JSON.stringify({
				subject: "lti.frameResize",
				height: default_height
			}), "*");
		</script>'
	);
}

// allow iframe tag within posts
function allow_post_tags( $allowedposttags ) {
	$allowedposttags['iframe'] = array(
		'align' => true,
		'allowFullScreen' => true,
		'class' => true,
		'frameborder' => true,
		'height' => true,
		'id' => true,
		'longdesc' => true,
		'marginheight' => true,
		'marginwidth' => true,
		'mozallowfullscreen' => true,
		'name' => true,
		'sandbox' => true,
		'seamless' => true,
		'scrolling' => true,
		'src' => true,
		'srcdoc' => true,
		'style' => true,
		'width' => true,
		'webkitAllowFullScreen' => true,
	);

	return $allowedposttags;
}
add_filter( 'wp_kses_allowed_html', 'allow_post_tags', 1 );
