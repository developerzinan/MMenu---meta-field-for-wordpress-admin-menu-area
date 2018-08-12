<?php
/* Check if Class Exists. */
if ( ! class_exists( 'mmenu_Navwalker' ) ) {
	/**
	 * mmenu_Navwalker class.
	 *
	 * @extends Walker_Nav_Menu
	 */
	class mmenu_Navwalker extends Walker_Nav_Menu {

		public function start_lvl( &$output, $depth = 0, $args = array() ) {
			if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
				$t = '';
				$n = '';
			} else {
				$t = "\t";
				$n = "\n";
			}
			$indent = str_repeat( $t, $depth );

			// Default class.
			$classes = array( 'sub-menu' );

			$class_names = join( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

			$output .= "{$n}{$indent}<ul $class_names>{$n}";

		}

		public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
			if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
				$t = '';
				$n = '';
			} else {
				$t = "\t";
				$n = "\n";
			}
			$indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

			$classes = empty( $item->classes ) ? array() : (array) $item->classes;
			$classes[] = 'menu-item-' . $item->ID;

			/**
			 * Filters the arguments for a single nav menu item.
			 *
			 * @since 4.4.0
			 *
			 * @param stdClass $args  An object of wp_nav_menu() arguments.
			 * @param WP_Post  $item  Menu item data object.
			 * @param int      $depth Depth of menu item. Used for padding.
			 */
			$args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

			/**
			 * Filters the CSS class(es) applied to a menu item's list item element.
			 *
			 * @since 3.0.0
			 * @since 4.1.0 The `$depth` parameter was added.
			 *
			 * @param array    $classes The CSS classes that are applied to the menu item's `<li>` element.
			 * @param WP_Post  $item    The current menu item.
			 * @param stdClass $args    An object of wp_nav_menu() arguments.
			 * @param int      $depth   Depth of menu item. Used for padding.
			 */
			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );

			$mmenu_active = get_post_meta($item->ID, 'menu-item-mmenu-active', true);
			$mmenu_active_class = $mmenu_active ? ' megamenu-item ' : '';
			$class_names = $class_names ? ' class="'. esc_attr( $class_names . $mmenu_active_class ) . '"' : '';

			/**
			 * Filters the ID applied to a menu item's list item element.
			 *
			 * @since 3.0.1
			 * @since 4.1.0 The `$depth` parameter was added.
			 *
			 * @param string   $menu_id The ID that is applied to the menu item's `<li>` element.
			 * @param WP_Post  $item    The current menu item.
			 * @param stdClass $args    An object of wp_nav_menu() arguments.
			 * @param int      $depth   Depth of menu item. Used for padding.
			 */
			$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
			$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

			$output .= $indent . '<li' . $id . $class_names .'>';

			$atts = array();
			$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
			$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
			$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
			$atts['href']   = ! empty( $item->url )        ? $item->url        : '';

			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}

			/** This filter is documented in wp-includes/post-template.php */
			$title = apply_filters( 'the_title', $item->title, $item->ID );

			$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

			$item_output = '';
			$mmenu_item_active = get_post_meta($item->ID, 'menu-item-mmenu-item', true);
			$mmenu_item_img = get_post_meta($item->ID, 'menu-item-mmenu-image', true);
			$mmenu_desc = get_post_meta($item->ID, 'menu-item-mmenu-desc', true);
			$mmenu_desc = ($mmenu_desc) ? '<p>'.$mmenu_desc.'</p>' : '';
			$mmenu_more = get_post_meta($item->ID, 'menu-item-mmenu-more', true);
			$mmenu_more = ($mmenu_more) ? $mmenu_more : esc_html__('Learn more', 'mmenu');

			if($mmenu_item_active && $depth === 1){
				if(class_exists('Aq_Resize')) {
					$m_img = aq_resize( $mmenu_item_img, '45', '45', true );
					$m_img = ( $m_img ) ? $m_img : $mmenu_item_img;
				} else {$m_img = $mmenu_item_img;}

	          	$actual_image = $mmenu_item_img ? '<div class="ziph-megm_icon"><img src="'. $m_img .'" alt="icon"></div>' : '';

				$item_output .= $actual_image;
                $item_output .= '<h4>'.$item->title.'</h4>' . $mmenu_desc ;
                $item_output .= '<a class="ziph-megmRead_btn" href="'.$item->url.'">'.$mmenu_more.'</a>';
			} else {
				$item_output .= $args->before;
				$item_output .= '<a'. $attributes .'>';
				$item_output .= $args->link_before . $title . $args->link_after;
				$item_output .= '</a>';
				$item_output .= $args->after;
			}

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );

		}

	}

}