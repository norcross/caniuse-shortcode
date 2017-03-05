<?php
/**
 * Can I Use? Shortcode - Front Module
 *
 * Contains front-end functionality.
 *
 * @package Can I Use Shortcode
 */

/**
 * Lets start the engine.
 */
class CIU_Shortcode_Front
{

	/**
	 * Load our hooks and filters.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts',                   array( $this, 'front_styles'                ),  10      );
		add_shortcode( 'caniuse',                           array( $this, 'caniuse_display_setup'       )           );
	}

	/**
	 * Load the front CSS and JS files for the shortcode.
	 *
	 * @return void
	 */
	public function front_styles() {

		// Bail if not singular.
		if ( ! is_singular() ) {
			return;
		}

		// Check for our CSS bypass.
		if ( false !== $css = apply_filters( 'ciu_disable_css', false ) ) {
			return;
		}

		// Call the global post object.
		global $post;

		// Check if we are on a post and using the shortcode.
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'caniuse' ) ) {

			// Set a version for the files based on debug mode or not.
			$vers   = defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : CIU_SHORTCODE_VER;
			$sffx   = defined( 'WP_DEBUG' ) && WP_DEBUG ? '.css' : '.min.css';

			// Load the file.
			wp_enqueue_style( 'ciu-display', plugins_url( '/css/caniuse' . $sffx, __FILE__ ), array(), $vers, 'all' );
		}
	}

	/**
	 * Display the "Can I Use" info.
	 *
	 * @param  array  $atts     The shortcode attributes.
	 * @param  string $content  The content inside.
	 *
	 * @return HTML
	 */
	public function caniuse_display_setup( $atts, $content = null ) {

		// Grab my args.
		$args   = shortcode_atts( array(
			'feature'  => '',
		), $atts );

		// Bail with no feature declared, or no return.
		if ( empty( $args['feature'] ) ) {
			return;
		}

		// Get my data.
		$data   = CIU_Shortcode_Helper::get_support_data( esc_attr( $args['feature'] ) );

		// Bail if an error return.
		if ( ! empty( $data['error'] ) ) {
			return;
		}

		// Set an empty build.
		$build  = '';

		// Do the div wrapper.
		$build .= '<div class="caniuse">';

			// Display the layout header.
			$build .= CIU_Shortcode_Display::get_display_header( $data );

			// Display the desktop support row.
			$build .= CIU_Shortcode_Display::get_display_row( $data['stats'], 'desktop', __( 'Desktop', 'caniuse-shortcode' ) );

			// Display the mobile support row.
			$build .= CIU_Shortcode_Display::get_display_row( $data['stats'], 'mobile', __( 'Mobile / Tablet', 'caniuse-shortcode' ) );

			// Display the legend row.
			$build .= CIU_Shortcode_Display::get_display_legend( $args['feature'] );

		// Close the wrapper.
		$build .= '</div>';

		// Return it.
		return $build;
	}

	// End class.
}

// Instantiate our class.
$CIU_Shortcode_Front = new CIU_Shortcode_Front();
$CIU_Shortcode_Front->init();
