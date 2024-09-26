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
		"show_in_quick_edit" => false,
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
 * Richtlijn is a custom Taxonomy (WP_Term)
 * It has some *extra* ACF fields:
 * - richtlijn_taxonomy_page: 'Richtlijnpagina' landingspage
 * - richtlijn_taxonomy_link: link to [external] richtlijn (URL) [optional]
 * Plus we add a custom `richtlijn_slug` property to the Term object
 * to be able to order the Terms based on the linked Page slug.
 * @param WP_Term|Number $term or Term ID
 * @return WP_Term
 */
function add_richtlijn_fields( $term ) {
	// If we have a Term ID, fetch the Term object
	if ( is_numeric( $term ) ) {
		$term = get_term( $term );
	}

	if ( ! $term || is_wp_error( $term ) || ! $term instanceof WP_Term ) {
		return $term;
	}

	// Add a custom `richtlijn_slug` property to the Term object
	// which is the Term slug by default, but is changed
	// to the Linked Page slug if we have a linked Page.
	$term->richtlijn_slug = $term->slug;
	// Add ACF fields to the Term object
	$term_fields = get_fields( $term );
	if ( is_array( $term_fields ) ) {
		foreach ( $term_fields as $key => $val ) {

			// Add extra `url` property to Term if we have a linked Page
			// Change `richtlijn_slug` property to the linked Page slug.
			// $val is the ID of the linked Page
			if ( $key == 'richtlijn_taxonomy_page' && ! empty( $val ) ) {
				$term->url            = get_permalink( $val );
				$term->richtlijn_slug = get_post_field( 'post_name', $val );
			}

			// Add extra `direct` property to Term if we have a Link
			// and we want to skip the Page (if set)
			if ( $key == 'richtlijn_taxonomy_link' && ! empty( $val ) ) {
				if (
					/* If passed `direct` param... */
					$skip_landingspage_when_linked ||
					/* .. OR we have a Link but no Page */
					empty( $term_fields['richtlijn_taxonomy_page'] )
				) {
					$term->direct = true;
				}
			}

			$term->$key = $val;
		}
	}
	return $term;
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
			// Add a custom ACF fields to Terms
			$richtlijn_terms[] = add_richtlijn_fields( $richtlijn_term );
		}
	}

	// NOTE:
	// We want to order the Richtlijnen alphabetically
	// based on their name. But we use the linked Page Title
	// for the actual display. So really, we expect to order
	// based on Term -> Page -> Title
	// This is why we need to re-order the array based on our `richtlijn_slug`
	// which is the linked Page slug when available.
	return order_by_richtlijn_slug( $richtlijn_terms );
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


/**
 * order_by_richtlijn_slug()
 *
 * This function re-orders the Richtlijnen array
 * based on their `richtlijn_slug` if available (Term slug or link Page page_name).
 *
 * @param Array $items Array of Richtlijn items, could be plain Arrays or WP_Term objects
 * @return Array re-ordered Richtlijn items
 */
function order_by_richtlijn_slug( $items ) {
	if ( ! empty( $items ) ) {
		usort( $items, function( $a, $b ) {
			// NOTE:
			// We compare on `richtlijn_slug` if available
			// but fall back to `slug` if not available.
			$p = 'richtlijn_slug';
			// Also:
			// $items could be an Array of WP_Term objects
			// or an Array of plain Arrays
			// if it's an Array of WP_Term objects, we need to
			// access properties differently than if it's an Array of plain Arrays
			// ( $item->richtlijn_slug vs $item['richtlijn_slug'] )
			// But we can convert the items to an Array with `get_object_vars()`
			// so we can continue to use the same syntax for both types of items...
			if ( $a instanceof WP_Term ) {
				$a = get_object_vars($a);
			}
			if ( $b instanceof WP_Term ) {
				$b = get_object_vars($b);
			}
			// From this point on we should be able to use `$x['prop']` syntax
			// Start by checking if we should fallback to `slug`
			if ( ! isset( $a[$p] ) || ! isset( $b[$p] ) ) {
				$p = 'slug';
			}
			if ( isset( $a[$p] ) && isset( $b[$p] ) ) {
				return strcmp( strtoupper( $a[$p] ), strtoupper( $b[$p] ) );
			}
		} );
	}

	return $items;
}


/*
 * This function takes a Richtlijn WP_Term objects and
 * distills all relevant fields from it to be used in a twig card (or other contexts)
 *
 * $richtlijn is a WP_Term object
 * `term_id`                        {Integer} ID of Term
 * `name`                           {String}  Name of Term
 * `slug`                           {String}  Slug of Term
 * `description`                    {String}  Description of Term
 *
 * .. etc..
 *
 * ..BUT also with added ACF fields as properties!
 * (These could be empty)
 *
 * `richtlijn_taxonomy_link`           {Array}   Link object (title, url, target)
 * `richtlijn_taxonomy_page`           {Integer} ID of linked page
 *
 * @param WP_Post $richtlijn the Richtlijn WP_Term with extra ACF fields
 *
 * @return array for use with a card
 *
 */
function prepare_richtlijn_card_content( $richtlijn ) {

	if ( ! $richtlijn || is_wp_error( $richtlijn ) || ! $richtlijn instanceof WP_Term ) {
		return $richtlijn;
	}

	// Fill custom Term ACF fields when not already present
	// We know there _should_ be a `richtlijn_taxonomy_page` property
	// (could be empty)
	// so we check for its presence.
	if ( ! isset( $richtlijn->richtlijn_taxonomy_page ) ) {
		$richtlijn = add_richtlijn_fields( $richtlijn );
	}

	$item = array(
		// type, translates to card-type--{type}
		'type'  => 'richtlijn',
		// Title default: term name
		'title' => $richtlijn->name,
		'slug'  => $richtlijn->slug,
		// Descr default: term description
		'descr' => $richtlijn->description,
	);

	// Setup properties to use for Card
	$item_url = null;
	$item_img = null;

	// Linked Landingpage
	if ( empty( $richtlijn->richtlijn_taxonomy_page ) ) {
		if ( empty( $richtlijn->richtlijn_taxonomy_link ) ) {
			// We do NOT have a linked Page NOR a Link URL.
			// Abort *unless* we are viewing the page logged-in
			// (in that case we show a warning)
			if ( ! is_user_logged_in() ) {
				// We do not show the card
			} else {
				// we show the card, but with a warning
				$item['type']  = $item['type'] . ' card--has-warning';
				$item['descr'] = sprintf(
					'<a style="color:red" href="%s">%s</a>',
					get_edit_term_link( $richtlijn->term_id, GC_RICHTLIJN_TAX ),
					_x('Deze card is verborgen totdat een pagina wordt gekoppeld aan deze Richtlijn!', 'Richtlijn overview: card warning', 'gctheme'),
				);
			}
		}
	} else {
		// Fetch page from ID
		$item_page = get_post( $richtlijn->richtlijn_taxonomy_page );
		// If no ID or invalid, get_post _could_ return the *current* page..
		if ( $item_page instanceof WP_Post ) {
			$item_page_id = $item_page->ID;
			// .. so check if it has the correct template
			$item_page_template    = get_post_meta( $item_page_id, '_wp_page_template', true );
			// .. and if page is published
			$item_page_status      = get_post_status( $item_page_id );
			$item_page_status_ok   = 'publish' === $item_page_status;

			// Only continue if page has the correct template and is published
			if ( $item_page_status_ok ) {
				// Set Card image from page thumbnail
				$item_img      = get_the_post_thumbnail_url( $item_page, 'image-16x9' ) ?: $item_img;
				// Override: card Title from page link
				$item['title'] = get_the_title( $item_page );
				// Set Card url from page link
				$item_url      = get_page_link( $item_page );
				// the sort order is based on the slug of the page (which is *probably* based on the page title)
				$item['slug'] = $item_page->post_name;

				// Override: card Description from:
				// - the page excerpt (if set)
				// - else: the page 00 - intro (if set)
				// - else: the term descr
				if ( $item_page->post_excerpt ) {
					// only use the excerpt if the field is actually filled
					$item['descr'] = get_the_excerpt( $item_page );
				} elseif ( get_field( 'post_inleiding', $item_page ) ) {
					$item['descr'] = get_field( 'post_inleiding', $item_page );
				}
				// No else needed for Term description as this was the default already..

				// Fetch Thema's for this Richtlijn page
				if ( function_exists( 'fn_ictu_thema_get_post_thema_terms' ) ) {
					// - All by default (2nd parameter = nullish)
					$themas         = fn_ictu_thema_get_post_thema_terms( $item_page_id );
					$item['themas'] = $themas['items'];
				}

				// Fetch Community's for this Richtlijn page
				if ( function_exists( 'fn_ictu_community_get_post_community_terms' ) ) {
					$communities         = fn_ictu_community_get_post_community_terms( $item_page_id, 10 );
					$item['communities'] = $communities['items'];
				}

			} else {
				// Not a proper page?
				// Show warning *when* we are viewing the page logged-in
				if ( ! is_user_logged_in() ) {
					// We do not show the card
				} else {
					// we show the card, but with a warning
					$item['type']  = $item['type'] . ' card--has-warning';
					$item['descr'] = sprintf(
						'<a style="color:red" href="%s">%s</a>',
						get_edit_post_link( $item_page, 'edit' ),
						_x('Deze card is verborgen totdat de pagina gepubliceerd wordt!', 'Richtlijn overview: card warning', 'gctheme'),
					);
				}
			}
		}
	}

	// Link: link to subsite?
	if ( $richtlijn->richtlijn_taxonomy_link ) {
		// Store the `link` in the Card item
		// NOTE: this is NOT the Card URL.
		// We do not link to a subsite directly,
		// but _always_ refer to the landing page (URL) first..
		$item['link'] = $richtlijn->richtlijn_taxonomy_link;

		// SETTING: Link directly to the subsite?
		// only if `direct` is set to true, we skip the landing page...
		if ( isset( $richtlijn->direct ) && $richtlijn->direct ) {
			// Override URL. Skip page and link straight to Link URL.
			$item_url = $item['link']['url'];
		}
	}

	// Use preferred image for card
	if ( $item_img ) {
		$item['img'] = '<img src="' . $item_img . '" alt=""/>';
	}

	// Use preferred URL for card
	if ( $item_url ) {
		$item['url'] = $item_url;
	}

	return $item;
}
