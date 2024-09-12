<?php
/**
 * GC ACF Fields for: Richtlijn Taxonomy
 *
 * ACF fields for `richtlijn` taxonomy
 *
 * @see https://www.advancedcustomfields.com/resources/register-fields-via-php/
 *
 * - It is important to remember that each field group’s key and each field’s key must be unique.
 * The key is a reference for ACF to find, save and load data. If 2 fields or 2 groups are added using
 * the same key, the later will override the original.
 *
 * - Field Groups and Fields registered via code will NOT be visible/editable via
 * the “Edit Field Groups” admin page.
 *
 * Initialize with eg:
 * add_action('acf/init', 'my_acf_add_local_field_groups');
 *
 */

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

// Add the field group

acf_add_local_field_group( array(
	'key' => 'group_66e068c0ad5ae',
	'title' => 'GC - Richtlijn taxonomy',
	'fields' => array(
		array(
			'key' => 'field_66e068c1d80ae',
			'label' => 'Richtlijnpagina',
			'name' => 'richtlijn_taxonomy_page',
			'aria-label' => '',
			'type' => 'post_object',
			'instructions' => 'Deze pagina zal worden getoond als een overzichtspagina met alle informatie over het richtlijn.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'post_type' => array(
				0 => 'page',
			),
			'post_status' => '',
			'taxonomy' => '',
			'return_format' => 'id',
			'allow_null' => 0,
			'multiple' => 0,
			'bidirectional' => 1,
			'bidirectional_target' => array(
				0 => 'field_66e06943087ed',
			),
			'ui' => 1,
		),
		array(
			'key' => 'field_66e0690ad80af',
			'label' => 'Link',
			'name' => 'richtlijn_taxonomy_link',
			'aria-label' => '',
			'type' => 'link',
			'instructions' => '(Optionele) link naar bijvoorbeeld een subsite',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'array',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'taxonomy',
				'operator' => '==',
				'value' => GC_RICHTLIJN_TAX,
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
