<?php
/**
 * ACF fields for the Richtlijn Taxonomy Detail page template
 */
if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

// Remove default Richtlijn Taxonomy metabox from side
// of Richtlijn Taxonomy Detail pages
// (remove_meta_box() does not work in GB) so we need JS:
add_action( 'admin_enqueue_scripts', 'fn_ictu_richtlijn_admin_scripts' );

function fn_ictu_richtlijn_admin_scripts() {
	global $post;
	if ( $post ) {
		// Do we have a post of whatever kind at hand?
		// Get template name; this will only work for pages, obviously
		$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

		if ( ( GC_RICHTLIJN_TAX_OVERVIEW_TEMPLATE === $page_template ) || ( GC_RICHTLIJN_TAX_DETAIL_TEMPLATE === $page_template ) ) {
			// Enqueue GB JS that hides the Richtlijn Taxonomy side panel
			wp_enqueue_script( 'gc-richtlijn-editor', GC_RICHTLIJN_TAX_ASSETS_PATH . '/scripts/gc-richtlijn-editor.js' );
		}
	}
}

// Add Custom ACF MetaBox for coupling a Richtlijn Term to a Page

acf_add_local_field_group( array(
	'key' => 'group_66e06942b8c3b',
	'title' => 'Metabox: selecteer richtlijn',
	'fields' => array(
		array(
			'key' => 'field_66e06943087ed',
			'label' => 'Selecteer de richtlijn voor deze pagina',
			'name' => 'richtlijn_detail_select_richtlijn_term',
			'aria-label' => '',
			'type' => 'taxonomy',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'taxonomy' => GC_RICHTLIJN_TAX,
			'add_term' => 0,
			'save_terms' => 0,
			'load_terms' => 0,
			'return_format' => 'id',
			'field_type' => 'select',
			'allow_null' => 0,
			'multiple' => 0,
			'bidirectional' => 1,
			'bidirectional_target' => array(
				0 => 'field_66e068c1d80ae',
			),
			'ui' => 1,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'template-detail-richtlijnen.php',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 0,
) );
