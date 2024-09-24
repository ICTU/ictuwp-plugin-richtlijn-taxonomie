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
if ( function_exists( 'fn_ictu_richtlijn_get_richtlijn_terms' ) ) {
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

		/**
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
		 */

		$item = array(
			// type, translates to card-type--{type}
			'type'  => 'richtlijn',
			// Title default: term name
			'title' => $richtlijn->name,
			'slug' => $richtlijn->slug,
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
					continue;
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

				} else {
					// Not a proper page?
					// Show warning *when* we are viewing the page logged-in
					if ( ! is_user_logged_in() ) {
						// We do not show the card
						continue;
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

		// Fill all items with new item
		$richtlijn_items[] = $item;

	}

	// NOTE [1]:
	// Re-order the Richtlijnen alphabetically
	// based on their linked slug (this is oftentimes derived from the Page Title).
	// They were originally ordered by Term name
	usort( $richtlijn_items, function( $a, $b ) {
		return strcmp( strtoupper( $a['slug'] ), strtoupper( $b['slug'] ) );
	} );

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