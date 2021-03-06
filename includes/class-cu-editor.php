<?php

/**
 * Further customization of the Wordpress editor.
 *
 * @author Lumen Learning
 * @license MIT
 */

namespace Candela\Utility\Editor;


class Candela_Utility_Editor {

	/**
	 * Constructor: Called when the plugin is initialized.
	 */
	function __construct() {

		if ( is_admin() ) {
			add_action( 'admin_head', array( &$this, 'configure_editor' ) );
			add_action( 'admin_head', array( &$this, 'globalize_images_url' ) );
			add_action( 'init', array( &$this, 'add_editor_stylesheets' ) );
		}

		add_shortcode( 'reveal-answer', array( &$this, 'reveal_answer_shortcode' ) );
		add_shortcode( 'hidden-answer', array( &$this, 'hidden_answer_shortcode' ) );
		add_shortcode( 'practice-area', array( &$this, 'practice_area_shortcode' ) );
		add_shortcode( 'glossary-page', array( &$this, 'glossary_page_shortcode' ) );
		add_shortcode( 'glossary-term', array( &$this, 'glossary_term_shortcode' ) );
		add_shortcode( 'glossary-definition', array( &$this, 'glossary_definition_shortcode' ) );

		add_action( 'admin_print_footer_scripts', array( &$this, 'add_custom_quicktags' ) );
		add_filter( 'tiny_mce_before_init', array( &$this, 'unstrip_mathml' ) );
	}

	/**
	 * Called by Constructor: Check if the current user can edit Posts or Pages, and is
	 * using the Visual Editor. If so, add filters so we can register plugin.
	 */
	function configure_editor() {

		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		if ( get_user_option( 'rich_editing' ) !== 'true' ) {
			return;
		}

		add_filter( 'mce_external_plugins', array( &$this, 'add_editor_scripts' ) );
		add_filter( 'mce_buttons', array( &$this, 'add_first_row_buttons' ) );
		add_filter( 'mce_buttons_2', array( &$this, 'add_second_row_buttons' ) );

	}

	/**
	 * Called by Constructor: Add the icon url to the admin head as a variable
	 * so that it is accessible to editor-hide-answer.js
	 */
	function globalize_images_url() {

		?>
		<script type="text/javascript">
			var icon_url = "<?php echo CU_PLUGIN_URL . 'assets/images/'; ?>"
		</script>
		<?php

	}

	/**
	 * Called by Constructor: Adds tryit textbox styles for admin and pages
	 */
	function add_editor_stylesheets() {

		add_editor_style( CU_PLUGIN_URL . 'assets/css/editor-tryit.css' );

	}

	/**
	 * Adds the Plugin JS file to the Visual Editor instance.
	 *
	 * @param array $plugin_array Array of registered TinyMCE Plugins
	 * @return array Modified array of registered TinyMCE Plugins
	 */
	function add_editor_scripts( $plugin_array ) {

		$plugin_array['tryit'] = CU_PLUGIN_URL . 'assets/js/editor-tryit.js';
		$plugin_array['hide_answer'] = CU_PLUGIN_URL . 'assets/js/editor-hide-answer.js';
		$plugin_array['ohm_question'] = CU_PLUGIN_URL . 'assets/js/editor-ohm-question.js';
		$plugin_array['glossary_page'] = CU_PLUGIN_URL . 'assets/js/editor-glossary-page.js';
		$plugin_array['glossary_entry'] = CU_PLUGIN_URL . 'assets/js/editor-glossary-entry.js';

		return $plugin_array;

	}

	/**
	 * Adds buttons to the first row  of buttons in the editor.
	 *
	 * @param array $buttons Array of registered editor buttons
	 * @return array $buttons Modified array of registered editor buttons
	 */
	function add_first_row_buttons( $buttons ) {

		array_push( $buttons, 'hide_answer' );
		array_push( $buttons, 'ohm_question' );
		array_push( $buttons, 'glossary_page' );
		array_push( $buttons, 'glossary_entry' );

		return $buttons;

	}

	/**
	 * Adds a button to the second row of buttons in the editor.
	 *
	 * @param array $buttons Array of registered editor buttons
	 * @return array $buttons Modified array of registered editor buttons
	 */
	function add_second_row_buttons( $buttons ) {

		$p = array_search( 'textboxes', $buttons );
		array_splice( $buttons, $p + 1, 0, 'tryit' );

		return $buttons;

	}


	// -----------------------------------------------------------------------------
	// SHORTCODES
	// -----------------------------------------------------------------------------

	/**
	 * Shortcode that wraps around text that, when clicked, will reveal the hidden answer.
	 * Ex: [reveal-answer q="1"]Show Answer[/reveal-answer].
	 */
	function reveal_answer_shortcode( $atts, $content = null ) {

		$wrapper_style = 'display: block';
		$show_answer_style = 'cursor: pointer';

		$atts = shortcode_atts(
			array(
				'q' => 'default 1',
			), $atts
		);

		return '<div class="qa-wrapper" style="' . $wrapper_style . '"><span class="show-answer collapsed" style="' . $show_answer_style . '" data-target="q' . $atts['q'] . '">' . do_shortcode( $content ) . '</span>';
	}

	/**
	 * Shortcode that wraps around text that hides the answer.
	 * Ex: [hidden-answer a="1"]Show Answer[/hidden-answer].
	 */
	function hidden_answer_shortcode( $atts, $content = null ) {

		$hidden_answer_style = 'display: none';

		$atts = shortcode_atts(
			array(
				'a' => 'default 1',
			), $atts
		);

		return '<div id="q' . $atts['a'] . '" class="hidden-answer" style="' . $hidden_answer_style . '">' . do_shortcode( $content ) . '</div></div>';

	}

	/**
	 * Shortcode that adds a textarea element to the page.
	 * Ex: [practice-area rows="8"][/practice-area].
	 */
	function practice_area_shortcode( $atts ) {
		$atts = shortcode_atts(array(
			"rows" => 'default 8'
		), $atts);

		return '<textarea rows="' . $atts['rows'] . '"></textarea>';
	}

	/**
	 * Shortcode that adds a glossary page wrapper element.
	 * Ex: [glossary-page][/glossary-page].
	 */
	function glossary_page_shortcode( $atts, $content = null ) {
		return '<div class="titlepage"><dl>' . do_shortcode( $content ) . '</dl></div>';
	}

	/**
	 * Shortcode that adds a glossary term (used by 'glossary-entry' shortcode)
	 * Ex: [glossary-term]Batman[/glossary-term].
	 */
	function glossary_term_shortcode( $atts, $content = null ) {
		return '<dt>' . do_shortcode( $content ) . '</dt>';
	}

	/**
	 * Shortcode that adds a glossary definition (used by 'glossary-entry' shortcode)
	 * Ex: [glossary-definition]I am the night[/glossary-definition].
	 */
	function glossary_definition_shortcode( $atts, $content = null ) {
		return '<dd>' . do_shortcode( $content ) . '</dd>';
	}

	/**
	 * Adds several custom quicktags to the text view in the editor
	 * Ex: QTags.addButton( id, display, arg1, arg2, access_key, title, priority, instance );
	 */
	function add_custom_quicktags() {
		if ( wp_script_is( 'quicktags' ) ) { ?>

		<script type="text/javascript">
			QTags.addButton( 'ol-decimal', 'ol 1', '<ol style="list-style-type: decimal;">\n', '</ol>\n', '.', 'Decimal', 91 );
			QTags.addButton( 'ol-decimal-leading-zero', 'ol 01', '<ol style="list-style-type: decimal-leading-zero;">\n', '</ol>\n', '0', 'Leading Zero', 92 );
			QTags.addButton( 'ol-upper-alpha', 'ol A', '<ol style="list-style-type: upper-alpha;">\n', '</ol>\n', 'A', 'Upper Alpha', 93 );
			QTags.addButton( 'ol-lower-alpha', 'ol a', '<ol style="list-style-type: lower-alpha;">\n', '</ol>\n', 'a', 'Lower Alpha', 94 );
			QTags.addButton( 'ol-upper-roman', 'ol I', '<ol style="list-style-type: upper-roman;">\n', '</ol>\n', 'I', 'Upper Roman', 95 );
			QTags.addButton( 'ol-lower-roman', 'ol i', '<ol style="list-style-type: lower-roman;">\n', '</ol>\n', 'i', 'Lower Roman', 96 );
			QTags.addButton( 'ul-disc', 'ul disc', '<ul style="list-style-type: disc;">\n', '</ul>\n', 'd', 'Disc', 80 );
			QTags.addButton( 'ul-circle', 'ul circle', '<ul style="list-style-type: circle;">\n', '</ul>\n', 'c', 'Circle', 81 );
			QTags.addButton( 'ul-square', 'ul square', '<ul style="list-style-type: square;">\n', '</ul>\n', 's', 'Square', 82 );
		</script>

	<?php }
	}

	/**
	 * Stop removal of MathML elements when switching between visual and text modes
	 * in the text editor.
	 */
	function unstrip_mathml( $init ) {
		$init['extended_valid_elements'] = 'math[class|id|xmlns|altimg|alttext|display|overflow],semantics[encoding|definitionURL],annotation[encoding|definitionURL|cd|name|src],annotation-xml[cd|name|encoding|definitionURL|src],merror,mtext,mspace,mover[accent|align],munder,munderover,mstack,mrow[dir],msrow,mfenced[open|close|separators],menclose[notation],mphantom,msup,msub,msubsup,mmultiscripts,mi,mn,mo[fence],ms,mtable,mtr,mtd,mlabeledtr,mfrac[linethickness|bevelled|numalign|denomalign],mfraction,msline,msqrt,mroot,mscarries,mscarry';

		return $init;
	}
}

$candela_utility_editor = new Candela_Utility_Editor;
