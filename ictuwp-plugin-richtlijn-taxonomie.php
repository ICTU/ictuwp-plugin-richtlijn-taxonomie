<?php

/**
 * @link                https://github.com/ICTU/ictuwp-plugin-richtlijn-taxonomie
 * @package             ictuwp-plugin-richtlijn-taxonomie
 *
 * @wordpress-plugin
 * Plugin Name:         ICTU / Gebruiker Centraal / Richtlijn taxonomie
 * Plugin URI:          https://github.com/ICTU/ictuwp-plugin-richtlijn-taxonomie
 * Description:         Plugin voor het aanmaken van de 'richtlijn'-taxonomie en gerelateerde pagina templates.
 * Version:             1.0.0
 * Version description: Initial commit: Richtlijn taxonomy plugin. Basically a copy of Hulpmiddel.
 * Author:              David Hund
 * Author URI:          https://github.com/ICTU/ictuwp-plugin-richtlijn-taxonomie/
 * License:             GPL-3.0+
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:         gctheme
 * Domain Path:         /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//========================================================================================================
defined( 'GC_RICHTLIJN_TAX' ) or define( 'GC_RICHTLIJN_TAX', 'richtlijn' );
defined( 'GC_RICHTLIJN_TAX_OVERVIEW_TEMPLATE' ) or define( 'GC_RICHTLIJN_TAX_OVERVIEW_TEMPLATE', 'template-overview-richtlijnen.php' );
defined( 'GC_RICHTLIJN_TAX_DETAIL_TEMPLATE' ) or define( 'GC_RICHTLIJN_TAX_DETAIL_TEMPLATE', 'template-detail-richtlijnen.php' );
defined( 'GC_RICHTLIJN_TAX_ASSETS_PATH' ) or define( 'GC_RICHTLIJN_TAX_ASSETS_PATH' , '/wp-content/plugins/ictuwp-plugin-richtlijn-taxonomie/assets' );
defined( 'GC_RICHTLIJN_ARCHIVE_TAX' ) or define( 'GC_RICHTLIJN_ARCHIVE_TAX' , 'thema' );
//========================================================================================================
// only this plugin should activate the GC_RICHTLIJN_TAX taxonomy
if ( ! taxonomy_exists( GC_RICHTLIJN_TAX ) ) {
	add_action( 'plugins_loaded', array( 'GC_richtlijn_taxonomy', 'init' ), 10 );
}


//========================================================================================================

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */


if ( ! class_exists( 'GC_richtlijn_taxonomy' ) ) :

	class GC_richtlijn_taxonomy {

		/** ----------------------------------------------------------------------------------------------------
		 * Init
		 */
		public static function init() {

			$newtaxonomy = new self();

		}

		/** ----------------------------------------------------------------------------------------------------
		 * Constructor
		 */
		public function __construct() {

			$this->fn_ictu_richtlijn_setup_actions();

		}

		/** ----------------------------------------------------------------------------------------------------
		 * Hook this plugins functions into WordPress.
		 * Use priority = 20, to ensure that the taxonomy is registered for post types from other plugins,
		 * such as the podcasts plugin (seriously-simple-podcasting)
		 */
		private function fn_ictu_richtlijn_setup_actions() {

			add_action( 'init', array( $this, 'fn_ictu_richtlijn_register_taxonomy' ) );

			// add page templates
			add_filter( 'template_include', array( $this, 'fn_ictu_richtlijn_append_template_locations' ) );

			// filter the breadcrumbs
			add_filter( 'wpseo_breadcrumb_links', array( $this, 'fn_ictu_richtlijn_yoast_filter_breadcrumb' ) );

			// check if the term has detail page attached
			add_action( 'template_redirect', array( $this, 'fn_ictu_richtlijn_check_redirect' ) );

			// Metabox: (40) Berichten tonen
			// Update functionality of `metabox_posts_category` field
			// 1. Change the taxonomy to the GC_RICHTLIJN_ARCHIVE_TAX Taxonomy (Thema) if not 'category'
			// 2. Change the label to 'Bericht <GC_RICHTLIJN_ARCHIVE_TAX>' and `return_format` as `object`
			//
			// @TODO: @NOTE: this might not be needed if we refactor the original ACF metabox
			// to use the THEMA tax instead of category...
			// --------------------------------------------------

			// [1] `metabox_posts_category`: Change the taxonomy of to Thema
			// --------------------------------------------------
			add_filter('acf/fields/taxonomy/query/name=metabox_posts_category', function ( $args, $field, $post_id ) {
				// Only change on Richtlijn Detail page
				if ( ! empty( $post_id ) && 'category' !== GC_RICHTLIJN_ARCHIVE_TAX ) {
					if ( get_post_meta( $post_id, '_wp_page_template', true ) === GC_RICHTLIJN_TAX_DETAIL_TEMPLATE ) {
						$args['taxonomy'] = GC_RICHTLIJN_ARCHIVE_TAX;
					}
				}
				return $args;
			}, 10, 3);

			// [2] `metabox_posts_category`: Change the label to 'Bericht Thema'
			// --------------------------------------------------
			add_filter( 'acf/load_field/name=metabox_posts_category', function ( $field ) {
				global $post;
				if ( ! empty( $post ) && 'category' !== GC_RICHTLIJN_ARCHIVE_TAX ) {
					if ( get_post_meta( $post->ID, '_wp_page_template', true ) === GC_RICHTLIJN_TAX_DETAIL_TEMPLATE ) {
						$field['label'] = 'Filter berichten op ' . ucfirst( GC_RICHTLIJN_ARCHIVE_TAX );
						$field['taxonomy'] = GC_RICHTLIJN_ARCHIVE_TAX;
						$field['return_format'] = 'object';
					}
				}

				return $field;
			}, 10, 1 );

		}


		/** ----------------------------------------------------------------------------------------------------
		 * Do actually register the taxonomy we need
		 *
		 * @return void
		 */
		public function fn_ictu_richtlijn_register_taxonomy() {

			require_once plugin_dir_path( __FILE__ ) . 'includes/richtlijn-taxonomy.php';

			// Require all needed ACF fieldgroup fields
			require_once plugin_dir_path( __FILE__ ) . 'includes/richtlijn-taxonomy-acf-fields.php';

			// Require all needed ACF fieldgroup fields
			require_once plugin_dir_path( __FILE__ ) . 'includes/richtlijn-taxonomy-page-acf-fields.php';

		}


		/**
		 * Checks if the template is assigned to the page
		 *
		 * @in: $template (string)
		 *
		 * @return: $template (string)
		 *
		 */
		public function fn_ictu_richtlijn_append_template_locations( $template ) {

			// For SOME reason, we still get a $post object,
			// even if we are on a taxonomy archive page..
			// This messes with the template selection, so we need to check for that
			// and use the default archive template
			// @TODO: investigate/improve this
			// use is_singular() instead?
			if ( is_search() or is_tax( GC_RICHTLIJN_TAX ) ) {
				// $term = get_queried_object();
				return $template;
			}

			// Not a taxonomy archive page, so continue as before...

			// Get global post
			global $post;
			$file       = '';
			$pluginpath = plugin_dir_path( __FILE__ );

			if ( $post ) {
				// Do we have a post of whatever kind at hand?
				// Get template name; this will only work for pages, obviously
				$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

				if (
					( GC_RICHTLIJN_TAX_OVERVIEW_TEMPLATE === $page_template ) ||
					( GC_RICHTLIJN_TAX_DETAIL_TEMPLATE === $page_template )
				) {
					// these names are added by this plugin, so we return
					// the actual file path for this template
					$file = $pluginpath . $page_template;
				} else {
					// exit with the already set template
					return $template;
				}
			} else {
				// Not a post, return the template
				return $template;
			}

			// Just to be safe, check if the file actually exists
			if ( $file && file_exists( $file ) ) {
				return $file;
			} else {
				// o dear, who deleted the file?
				echo $file;
			}

			// If all else fails, return template
			return $template;
		}


		/**
		 * Filter the Yoast SEO breadcrumb
		 *
		 * @in: $links (array)
		 *
		 * @return: $links (array)
		 *
		 */
		public function fn_ictu_richtlijn_yoast_filter_breadcrumb( $links ) {
			global $post;

			if ( $post && is_page() ) {

				// Currently Querying a Page
				// Try and see if it has the GC_RICHTLIJN_TAX_DETAIL_TEMPLATE template
				// and if so, append the Richtlijn Overview Page to the breadcrumb
				// But only if the current page is not a childpage of the parent...
				if ( $post->post_parent !== 0 ) {
					// page does have a parent, whatever parent it might be, so:
					// do nothing extra for breadcrumb

				} else {
					// page does NOT have a parent, so let's add the overview page to the breadcrumb.
					$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

					if ( $page_template && $page_template === GC_RICHTLIJN_TAX_DETAIL_TEMPLATE ) {
						// current page has template = GC_RICHTLIJN_TAX_DETAIL_TEMPLATE

						// Get the Richtlijn Overview Page to append to our breadcrumb
						$richtlijn_overview_page_id = $this->fn_ictu_richtlijn_get_richtlijn_overview_page();

						if ( $richtlijn_overview_page_id ) {
							// We have a Overview-page ID
							// and it is not the parent of the current page
							// Use this page as GC_RICHTLIJN_TAX term parent in the breadcrumb
							$taxonomy_link = array(
								'url'  => get_permalink( $richtlijn_overview_page_id ),
								'text' => get_the_title( $richtlijn_overview_page_id )
							);
							array_splice( $links, - 1, 0, [ $taxonomy_link ] );
						}

					}

				}


			} elseif ( is_tax( GC_RICHTLIJN_TAX ) ) {

				// NOT currently Querying a Page, but a GC_RICHTLIJN_TAX term
				$term = get_queried_object();
				// Append taxonomy if 1st-level child term only
				// old: Home > Term
				// new: Home > Taxonomy > Term

				if ( ! $term->parent ) {

					$richtlijn_overview_page_id = $this->fn_ictu_richtlijn_get_richtlijn_overview_page();

					if ( $richtlijn_overview_page_id ) {
						// Use this page as GC_RICHTLIJN_TAX term parent in the breadcrumb
						// If not available,
						// - [1] Do not display root
						// - [2] OR fall back to Taxonomy Rewrite

						$taxonomy_link = array(
							'url'  => get_permalink( $richtlijn_overview_page_id ),
							'text' => get_the_title( $richtlijn_overview_page_id )
						);
						array_splice( $links, - 1, 0, array( $taxonomy_link ) );

					} else {
						// [1] .. do nothing...

						// [2] OR .. use Taxonomy Rewrite as root

						// $taxonomy      = get_taxonomy( GC_RICHTLIJN_TAX );
						// $taxonomy_link = [
						// 	'url' => get_home_url() . '/' . $taxonomy->rewrite['slug'],
						// 	'text' => $taxonomy->labels->archives,
						// 	'term_id' => get_queried_object_id(),
						// ];
						// array_splice( $links, -1, 0, [$taxonomy_link] );
					}
				}

			}

			return $links;

		}


		/**
		 * Checks if the richtlijn term is linked to a page and redirect
		 *
		 * @return: {Function|null} wp_safe_redirect when possible
		 *
		 */
		public function fn_ictu_richtlijn_check_redirect() {

			if ( ! function_exists( 'get_field' ) ) {
				// we can't check if ACF is not active
				return;
			}

			if ( is_tax( GC_RICHTLIJN_TAX ) ) {

				// check if the current term has a value for 'richtlijn_taxonomy_page'
				$term    = get_queried_object();
				$page_id = get_field( 'richtlijn_taxonomy_page', $term );
				if ( ! empty( $page_id ) ) {
					$page = get_post( $page_id );
				}

				// A page is selected for this term
				// But is the page published?
				if ( $page && 'publish' === $page->post_status ) {
					// good: it is published
					// let's redirect to that page
					wp_safe_redirect( get_permalink( $page->ID ) );
					exit;

				} else {
					// bad: we only want published pages
					$aargh = _x( 'Er hangt geen gepubliceerde pagina aan dit richtlijn.', 'Warning for redirect error', 'gctheme' );
					if ( current_user_can( 'editor' ) ) {
						$editlink = get_edit_term_link( get_queried_object()->term_id, get_queried_object()->taxonomy );
						$aargh .= '<br>' . sprintf( _x( '<a href="%s">Voeg een gepubliceerde pagina toe, alsjeblieft.</a>', 'Warning for redirect error', 'gctheme' ), $editlink );
					}
					die( $aargh );
				}
			}

		}


		/**
		 * Retrieve a page that is the GC_RICHTLIJN_TAX overview page. This
		 * page shows all available GC_RICHTLIJN_TAX terms
		 *
		 * @in: $args (array)
		 *
		 * @return: $overview_page_id (integer)
		 *
		 */

		private function fn_ictu_richtlijn_get_richtlijn_overview_page( $args = array() ) {

			$return = 0;

			// TODO: discuss if we need to make this page a site setting
			// there is no technical way to limit the number of pages with
			// template GC_RICHTLIJN_TAX_OVERVIEW_TEMPLATE, so the page we find may not be
			// the exact desired page for in the breadcrumb.
			//
			// Try and find 1 Page
			// with the GC_RICHTLIJN_TAX_OVERVIEW_TEMPLATE template...
			$page_template_query_args = array(
				'number'      => 1,
				'sort_column' => 'post_date',
				'sort_order'  => 'DESC',
				'meta_key'    => '_wp_page_template',
				'meta_value'  => GC_RICHTLIJN_TAX_OVERVIEW_TEMPLATE
			);
			$overview_page = get_pages( $page_template_query_args );

			if ( $overview_page && isset( $overview_page[0]->ID ) ) {
				$return = $overview_page[0]->ID;
			}

			return $return;

		}


	}

endif;


//========================================================================================================

/**
 * Load plugin textdomain.
 * only load translations if we can safely assume the taxonomy is active
 */
add_action( 'init', 'fn_ictu_richtlijn_load_plugin_textdomain' );

function fn_ictu_richtlijn_load_plugin_textdomain() {

	load_plugin_textdomain( 'gctheme', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}

//========================================================================================================

/**
 * Returns array of allowed page templates
 *
 * @return array with extra templates
 */
function fn_ictu_richtlijn_add_templates() {

	$return_array = array(
		GC_RICHTLIJN_TAX_OVERVIEW_TEMPLATE      => _x( '[Richtlijn] overzicht', 'label page template', 'gctheme' ),
		GC_RICHTLIJN_TAX_DETAIL_TEMPLATE        => _x( '[Richtlijn] detailpagina', 'label page template', 'gctheme' ),
	);

	return $return_array;

}
