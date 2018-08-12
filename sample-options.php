<?php
// Mega Menu Options
function mmenu_mega_menu_admin_options($fields) {
	$fields = array(
		'mmenu-active' => array(
			'label' => esc_html__('Enable Megamenu', 'mmenu'),
			'type' => 'checkbox',
		),
		'mmenu-item' => array(
			'label' => esc_html__('Megamenu Item', 'mmenu'),
			'type' => 'checkbox',
		),
		'mmenu-desc' => array(
			'label' => esc_html__('Description', 'mmenu'),
			'type' => 'textarea',
		),
		'mmenu-image' => array(
			'label' => esc_html__('Image', 'mmenu'),
			'type' => 'upload',
		),
		'mmenu-more' => array(
			'label' => esc_html__('More Text', 'mmenu'),
			'type' => 'text',
		),
	);
	return $fields;
}
add_filter( 'm_menu', 'mmenu_mega_menu_admin_options' );