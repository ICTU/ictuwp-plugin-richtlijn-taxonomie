<?php
/**
 * Template Name: [Richtlijn] overzicht
 *
 * @package    WordPress
 * @subpackage Timber v2
 */

$context                       = Timber::context();
$context['post']               = Timber::get_post();
$context['modifier']            = 'richtlijn-overview';
$context['item_type']          = 'richtlijn-overview'; // Pass item_type to grid section (adds ID to grid)
$context['has_centered_intro'] = false;

// TODO: implement Query Filters ($context['query_filters']) (like template-instumenten-tt.php)?

/**
 * Add richtlijnen (terms in Richtlijn taxonomy)
 */
if (
	function_exists( 'fn_ictu_richtlijn_get_richtlijn_terms' ) &&
	function_exists( 'prepare_richtlijn_card_content' )
) {
	$richtlijn_items = [];

	// Fill items (cards) for overview template

	// NOTE [1]:
	// fn_ictu_richtlijn_get_richtlijn_terms() returns
	// an array of WP_Term objects, ordered by `name`.
	//
	// We want to order the Richtlijnen alphabetically
	// based on their name. But we use the linked Page Title
	// for the actual display. So really, we expect to order
	// based on Term -> Page -> Title
	// This is why we need to re-order the array
	// here, after we've retrieved the page title
	// in the loop below.

	$select_args = array(
		'taxonomy'   => GC_RICHTLIJN_TAX,
		// Also Terms with NO linked content (could be external link)
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	);

	foreach ( fn_ictu_richtlijn_get_richtlijn_terms( null, $select_args ) as $richtlijn ) {
		$richtlijn_items[] = prepare_richtlijn_card_content( $richtlijn );
	}

	$context['items'] = $richtlijn_items;
}

// Enqueue page-specific JS (checkbox filters)
// ------------------------------------------
// add_action( 'wp_enqueue_scripts', 'gc_enqueue_checkbox_filters_scripts' );
// function gc_enqueue_checkbox_filters_scripts() {
// 	// handle, source, deps, version, footer
// 	wp_enqueue_script( 'gc-checkbox-filters', get_template_directory_uri() . '/assets/js/gc-checkbox-filters.min.js', [], '1.2.0', true );
// }

// Use a special Overview template with filters
$templates = [ 'overview-with-filters.twig' ];

// Overload Theme page.php
require_once get_stylesheet_directory() . '/page.php';