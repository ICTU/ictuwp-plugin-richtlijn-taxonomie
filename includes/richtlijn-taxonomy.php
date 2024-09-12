<?php
/**
 * Custom Taxonomy: Richtlijn
 * -  hierarchical (like 'category')
 *
 * @package GebruikerCentraalTheme
 *
 * @see https://developer.wordpress.org/reference/functions/register_taxonomy/
 * @see https://developer.wordpress.org/reference/functions/get_taxonomy_labels/
 *
 * CONTENTS:
 * - Set GC_RICHTLIJN_TAX taxonomy labels
 * - Set GC_RICHTLIJN_TAX taxonomy arguments
 * - Register GC_RICHTLIJN_TAX taxonomy
 * - public function fn_ictu_richtlijn_get_post_richtlijn_terms() - Retreive Richtlijn terms with custom field data for Post
 * ----------------------------------------------------- */


if ( ! taxonomy_exists( GC_RICHTLIJN_TAX ) ) {

	// [1] Richtlijn Taxonomy Labels
	$richtlijn_tax_labels = [
		'name'                       => _x( 'Richtlijn', 'Custom taxonomy labels definition', 'gctheme' ),
		'singular_name'              => _x( 'Richtlijn', 'Custom taxonomy labels definition', 'gctheme' ),
		'search_items'               => _x( 'Zoek richtlijnen', 'Custom taxonomy labels definition', 'gctheme' ),
		'popular_items'              => _x( 'Populaire richtlijnen', 'Custom taxonomy labels definition', 'gctheme' ),
		'all_items'                  => _x( 'Alle richtlijnen', 'Custom taxonomy labels definition', 'gctheme' ),
		'edit_item'                  => _x( 'Bewerk richtlijn', 'Custom taxonomy labels definition', 'gctheme' ),
		'view_item'                  => _x( 'Bekijk richtlijn', 'Custom taxonomy labels definition', 'gctheme' ),
		'update_item'                => _x( 'Richtlijn bijwerken', 'Custom taxonomy labels definition', 'gctheme' ),
		'add_new_item'               => _x( 'Voeg nieuw richtlijn toe', 'Custom taxonomy labels definition', 'gctheme' ),
		'new_item_name'              => _x( 'Nieuwe richtlijn', 'Custom taxonomy labels definition', 'gctheme' ),
		'separate_items_with_commas' => _x( 'Kommagescheiden richtlijnen', 'Custom taxonomy labels definition', 'gctheme' ),
		'add_or_remove_items'        => _x( 'Richtlijnen toevoegen of verwijderen', 'Custom taxonomy labels definition', 'gctheme' ),
		'choose_from_most_used'      => _x( 'Kies uit de meest-gekozen richtlijnen', 'Custom taxonomy labels definition', 'gctheme' ),
		'not_found'                  => _x( 'Geen richtlijnen gevonden', 'Custom taxonomy labels definition', 'gctheme' ),
		'no_terms'                   => _x( 'Geen richtlijnen gevonden', 'Custom taxonomy labels definition', 'gctheme' ),
		'items_list_navigation'      => _x( 'Navigatie door richtlijnenlijst', 'Custom taxonomy labels definition', 'gctheme' ),
		'items_list'                 => _x( 'Richtlijnenlijst', 'Custom taxonomy labels definition', 'gctheme' ),
		'item_link'                  => _x( 'Richtlijn Link', 'Custom taxonomy labels definition', 'gctheme' ),
		'item_link_description'      => _x( 'Een link naar een Richtlijn', 'Custom taxonomy labels definition', 'gctheme' ),
		'menu_name'                  => _x( 'Richtlijnen', 'Custom taxonomy labels definition', 'gctheme' ),
		'back_to_items'              => _x( 'Terug naar richtlijnen', 'Custom taxonomy labels definition', 'gctheme' ),
		'not_found_in_trash'         => _x( 'Geen richtlijnen gevonden in de prullenbak', 'Custom taxonomy labels definition', 'gctheme' ),
		'featured_image'             => _x( 'Uitgelichte afbeelding', 'Custom taxonomy labels definition', 'gctheme' ),
		'archives'                   => _x( 'Richtlijn overzicht', 'Custom taxonomy labels definition', 'gctheme' ),
	];

	// [2] Richtlijn Taxonomy Arguments
	$richtlijn_slug = GC_RICHTLIJN_TAX;
	// TODO: discuss if slug should be set to a page with the overview template
	// like so:
	// $richtlijn_slug = fn_ictu_richtlijn_get_richtlijn_overview_page();

	$richtlijn_tax_args = [
		"labels"             => $richtlijn_tax_labels,
		"label"              => _x( 'Richtlijnen', 'Custom taxonomy arguments definition', 'gctheme' ),
		"description"        => _x( 'Richtlijnen op het gebied van een gebruikersvriendelijke overheid', 'Custom taxonomy arguments definition', 'gctheme' ),
		"hierarchical"       => true,
		"public"             => true,
		"show_ui"            => true,
		"show_in_menu"       => true,
		"show_in_nav_menus"  => false,
		"query_var"          => false,
		// Needed for tax to appear in Gutenberg editor.
		'show_in_rest'       => true,
		"show_admin_column"  => true,
		// Needed for tax to appear in Gutenberg editor.
		"rewrite"            => [
			'slug'       => $richtlijn_slug,
			'with_front' => true,
		],
		"show_in_quick_edit" => true,
	];

	// register the taxonomy with these post types
	// 'post',
	// 'page',
	// 'podcast',
	// 'session',
	// 'keynote',
	// 'speaker',
	// 'event',
	// 'video_page',
	$post_types_with_richtlijn = array(
		'page',
	);

	// Commented: not needed with only `page` as post type
	// check if the post types exist
	// $post_types_with_richtlijn = array_filter( $post_types_with_richtlijn, 'post_type_exists' );

	// [3] Register our Custom Taxonomy
	register_taxonomy( GC_RICHTLIJN_TAX, $post_types_with_richtlijn, $richtlijn_tax_args );

}


/**
 * fn_ictu_richtlijn_get_richtlijn_terms()
 *
 * 'Richtlijn' is a custom taxonomy (category)
 * It has some extra ACF fields:
 * - richtlijn_taxonomy_page: landingspage
 * - richtlijn_taxonomy_link: link to [external] richtlijn (URL) [optional]
 *
 * This function fills an array of all
 * terms, with their extra fields...
 *
 * If one $richtlijn_name is passed it returns only that
 * If $term_args is passed it uses that for the query
 *
 * @see https://developer.wordpress.org/reference/functions/get_terms/
 * @see https://www.advancedcustomfields.com/resources/adding-fields-taxonomy-term/
 * @see https://developer.wordpress.org/reference/classes/wp_term_query/__construct/
 *
 * @param String $richtlijn_name Specific term name/slug to query
 * @param Array $richtlijn_args Specific term query Arguments to use
 * @param Boolean $skip_landingspage_when_linked Go straight to Link and bypass Page, even when set?
 */


function fn_ictu_richtlijn_get_richtlijn_terms( $richtlijn_name = null, $term_args = null, $skip_landingspage_when_linked = false ) {

	// TODO: I foresee that editors will want to have a custom order to the taxonomy terms
	// but for now the terms are ordered alphabetically
	$richtlijn_taxonomy = GC_RICHTLIJN_TAX;
	$richtlijn_terms    = array();
	$richtlijn_query    = is_array( $term_args ) ? $term_args : array(
		'taxonomy'   => $richtlijn_taxonomy,
		// We also want Terms with NO linked content, in this case
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	);

	// NOTE:
	// We want to order the Richtlijnen alphabetically
	// based on their name. But we use the linked Page Title
	// for the actual display. So really, we expect to order
	// based on Term -> Page -> Title
	// This is why we need to re-order the array
	// in template-overview-richtlijnen.php

	// Query specific term name
	if ( ! empty( $richtlijn_name ) ) {
		// If we find a Space, or an Uppercase letter, we assume `name`
		// otherwise we use `slug`
		$RE_disqualify_slug                  = "/[\sA-Z]/";
		$query_prop_type                     = preg_match( $RE_disqualify_slug, $richtlijn_name ) ? 'name' : 'slug';
		$richtlijn_query[ $query_prop_type ] = $richtlijn_name;
	}

	$found_richtlijn_terms = get_terms( $richtlijn_query );

	if ( is_array( $found_richtlijn_terms ) && ! empty( $found_richtlijn_terms ) ) {
		// Add our custom Fields to each found WP_Term instance
		// And add to $richtlijn_terms[]
		foreach ( $found_richtlijn_terms as $richtlijn_term ) {
			$richtlijn_term_fields = get_fields( $richtlijn_term );
			if ( is_array( $richtlijn_term_fields ) ) {
				foreach ( $richtlijn_term_fields as $key => $val ) {

					// Add extra `url` property to Term if we have a linked Page
					if ( $key == 'richtlijn_taxonomy_page' && ! empty( $val ) ) {
						$richtlijn_term->url = get_permalink( $val );
					}

					// Add extra `direct` property to Term if we have a Link
					// and we want to skip the Page (if set)
					if ( $key == 'richtlijn_taxonomy_link' && ! empty( $val ) ) {
						if (
							/* If passed `direct` param... */
							$skip_landingspage_when_linked ||
							/* .. OR we have a Link but no Page */
							empty( $richtlijn_term_fields['richtlijn_taxonomy_page'] )
						) {
							$richtlijn_term->direct = true;
						}
					}

					$richtlijn_term->$key = $val;
				}
			}
			$richtlijn_terms[] = $richtlijn_term;
		}
	}

	return $richtlijn_terms;
}

/**
 * fn_ictu_richtlijn_get_post_richtlijn_terms()
 *
 * This function fills an array of all
 * terms, with their extra fields _for a specific Post_...
 *
 * - Only top-lever Terms
 * - 1 by default
 *
 * used in [themes]/ictuwp-theme-gc2020/includes/gc-fill-context-with-acf-fields.php
 *
 * @param String|Number $post_id Post to retrieve linked terms for
 *
 * @return Array        Array of WPTerm Objects with extra ACF fields
 */
function fn_ictu_richtlijn_get_post_richtlijn_terms( $post_id = null, $term_number = 1 ) {
	$return_terms = array();
	if ( ! $post_id ) {
		return $return_terms;
	}

	$post_richtlijn_terms = wp_get_post_terms( $post_id, GC_RICHTLIJN_TAX, [
		'taxonomy'   => GC_RICHTLIJN_TAX,
		'number'     => $term_number, // Return max $term_number Terms
		'hide_empty' => true,
		'parent'     => 0,
		'fields'     => 'names' // Only return names (to use in `fn_ictu_richtlijn_get_richtlijn_terms()`)
	] );
	if ( ! empty( $post_richtlijn_terms ) && ! is_wp_error( $post_richtlijn_terms ) ) {

		$return_terms['title'] = _n( 'Hoort bij het richtlijn', 'Hoort bij de richtlijnen', count( $post_richtlijn_terms ), 'gctheme' ) ;
		$return_terms['items'] = array();

		foreach ( $post_richtlijn_terms as $_term ) {
			$full_post_richtlijn_term = fn_ictu_richtlijn_get_richtlijn_terms( $_term );
			if ( ! empty( $full_post_richtlijn_term ) ) {
				$return_terms['items'][] = $full_post_richtlijn_term[0];
			}
		}

	}

	return $return_terms;
}
