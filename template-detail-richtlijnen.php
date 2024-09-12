<?php
/**
 * Template Name: [Richtlijn] detailpagina
 *
 * @package    WordPress
 * @subpackage Timber v2
 */

$timber_post = Timber::get_post();

$current_richtlijn_taxid = get_current_richtlijn_tax();
$current_richtlijn_term  = get_term_by( 'id', $current_richtlijn_taxid, GC_RICHTLIJN_TAX );

$context                = Timber::context();
$context['post']        = $timber_post;
$context['modifier']    = 'richtlijn-detail';
$context['is_unboxed']  = true;
$context['show_author'] = false;

if ( $current_richtlijn_term && ! is_wp_error( $current_richtlijn_term ) ) {
	// Update body class
	$context['body_class'] = ( $context['body_class'] ?: '' ) . ' single-richtlijn richtlijn--' . $current_richtlijn_term->slug;
}

$templates = [ 'richtlijn-detail.twig', 'page.twig' ];

/**
 * returns the ID for the richtlijn term that is
 * attached to this page in ACF field 'richtlijn_detail_select_richtlijn_term'
 *
 * @return int
 */
function get_current_richtlijn_tax() {
	global $post;

	$term_id = get_field( 'richtlijn_detail_select_richtlijn_term' ) ?: 0;
	if ( ! $term_id ) {
		$aargh = _x( 'Geen richtlijn gekoppeld aan deze pagina. ', 'Richtlijn taxonomy error message', 'gctheme' );
		if ( current_user_can( 'editor' ) ) {
			$editlink = get_edit_post_link( $post );
			$aargh    .= '<a href="' . $editlink . '">' . _x( 'Kies een relevante richtlijn voor deze pagina. ', 'Richtlijn taxonomy error message', 'gctheme' ) . '</a>';
		}
		die( $aargh );
	}

	return $term_id;
}

/**
 * Fill Timber $context with available page/post Blocks/Metaboxes
 * @see /includes/gc-fill-context-with-acf-fields.php
 */
if ( function_exists( 'gc_fill_context_with_acf_fields' ) ) {
	$context = gc_fill_context_with_acf_fields( $context );
}

// We have a valid Richtlijn Term for this page
if ( $current_richtlijn_term && ! is_wp_error( $current_richtlijn_term ) ) {

	// Get custom ACF fields for this WP_Term..
	// filter out 'empty' or nullish values
	$current_richtlijn_term_fields = array_filter(
		get_fields( $current_richtlijn_term ) ?: [],
		function ( $field ) {
			return ! empty( $field );
		}
	);

	if ( $current_richtlijn_term_fields ) {
		// We have some custom ACF fields for this Term
		// If we have an extra Richtlijn Link
		if ( isset( $current_richtlijn_term_fields['richtlijn_taxonomy_link'] ) ) {
			$context['richtlijn_link'] = $current_richtlijn_term_fields['richtlijn_taxonomy_link'];
		}
	}

	// CONTENT
	// -----------------------------

	// text for 'inleiding' is taken from term description
	// -----------------------------
	// $timber_post->post_content = $current_richtlijn_term->description;
	// Use Excerpt instead?! Fall back to Term description if no Excerpt is set?
	if ( empty( $context['intro'] ) ) {
		$context['intro'] = wpautop( $current_richtlijn_term->description );
	}

}


Timber::render( $templates, $context );
