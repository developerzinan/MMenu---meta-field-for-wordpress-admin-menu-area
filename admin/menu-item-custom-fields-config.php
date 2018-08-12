<?php
/**
 * Menu item custom fields config
 *
 * @package Menu_Item_Custom_Fields_Config
 * @version 1.0.0
 * @author Zinan Nadeem <zinan09@gmail.com>
 * Get value get_post_meta($item->ID, 'menu-item-inputname', true);
 */

class Menu_Item_Custom_Fields_Config {

	/**
	 * Holds our custom fields
	 *
	 * @var    array
	 * @access protected
	 */
	protected static $fields = array();
	protected static $icon_class = '';

	/**
	 * Initialize plugin
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, '_options' ) );
		add_action( 'wp_nav_menu_item_custom_fields', array( __CLASS__, '_fields' ), 10, 4 );
		add_action( 'wp_update_nav_menu_item', array( __CLASS__, '_save' ), 10, 3 );
		add_filter( 'manage_nav-menus_columns', array( __CLASS__, '_columns' ), 99 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, '_scripts' ) );
		add_action( 'admin_head', array(  __CLASS__, '_custom_scripts' ) );
	}

	/**
	 * Initialize Options
	 */
	public static function _options() {
		self::$fields = apply_filters( 'm_menu',  $default_fields = array() );
	}

	/**
	 * Initialize Scripts
	 */
	public static function _scripts() {
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}

	/**
	 * Save custom field value
	 *
	 * @wp_hook action wp_update_nav_menu_item
	 *
	 * @param int   $menu_id         Nav menu ID
	 * @param int   $menu_item_db_id Menu item ID
	 * @param array $menu_item_args  Menu item data
	 */
	public static function _save( $menu_id, $menu_item_db_id, $menu_item_args ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );

		foreach ( self::$fields as $_key => $label ) {
			$key = sprintf( 'menu-item-%s', $_key );

			// Sanitize
			if ( ! empty( $_POST[ $key ][ $menu_item_db_id ] ) ) {
				// Do some checks here...
				$value = $_POST[ $key ][ $menu_item_db_id ];
			} else {
				$value = null;
			}

			// Update
			if ( ! is_null( $value ) ) {
				update_post_meta( $menu_item_db_id, $key, $value );
			} else {
				delete_post_meta( $menu_item_db_id, $key );
			}
		}
	}

	/**
	 * Print field
	 *
	 * @param object $item  Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args  Menu item args.
	 * @param int    $id    Nav menu ID.
	 *
	 * @return string Form fields
	 */
	public static function _fields( $id, $item, $depth, $args ) {

		foreach ( self::$fields as $_key => $option ) :
			$key   = sprintf( 'menu-item-%s', $_key );
			$id    = sprintf( 'edit-%s-%s', $key, $item->ID );
			$name  = sprintf( '%s[%s]', $key, $item->ID );
			$value = get_post_meta( $item->ID, $key, true );
			$class = sprintf( 'field-%s', $_key );
			$label = ( !empty($option['label'] ) ) ? $option['label'] : '';
			$desc  = ( !empty($option['desc'] ) ) ? $option['desc'] : '';
			$wrap_class  = ( !empty($option['wrap_class'] ) ) ? $option['wrap_class'] : 'wide';
			$field_class = ( !empty($option['field_class'] ) ) ? $option['field_class'] : '';
			$button_text = ( !empty($option['button_text'] ) ) ? $option['button_text'] : esc_html__( 'Upload Image', 'mmenu' );
			?>
				<div class="et-menu-panel description <?php echo esc_attr('description-'.$wrap_class.' '.$class ) ?>">
				<?php
					if ($option['type'] !== 'checkbox') {
						echo '<label for="'.esc_attr( $id ).'"><span class="item-label">'.esc_attr($label).'</span>';
					}
					switch ($option['type']) {
						case 'color':
							echo '<input type="text" id="'.esc_attr( $id ).'" class="widefat et-color '.esc_attr( $field_class ).'" name="'.esc_attr( $name ).'" value="'.esc_attr( $value ).'">';
							break;

						case 'checkbox':
							$checked = ($value) ? 'checked=checked': '';
							echo '<input type="checkbox" id="'.esc_attr( $id ).'" class="'.esc_attr( $field_class ).'" name="'.esc_attr( $name ).'" value="1" '.esc_attr( $checked ).'><label for="'.esc_attr( $id ).'">'.esc_attr( $label ).'</label>';
							break;

						case 'textarea':
							echo '<textarea id="'.esc_attr( $id ).'" class="widefat '.esc_attr( $field_class ).'" name="'.esc_attr( $name ).'"rows="3" cols="20">'.esc_attr( $value ).'</textarea>';
							break;

						case 'upload':
							echo '<img src="'.esc_attr( $value ).'" alt="" class="widefat et-img"/>';
							echo '<input type="hidden" id="'. esc_attr( $id ) .'" class="widefat et-url '. esc_attr( $field_class ) .'" name="'. esc_attr( $name ) .'" value="'. esc_attr( $value ) .'">';
							echo '<input type="button" class="button button-primary et-browse '. esc_attr( $field_class ) .'" value="'. esc_attr( $button_text ) .'" />';
							echo '<button class="button et-img-remove">'. esc_html__( 'Remove', 'mmenu' ) .'</button>';
							break;

						case 'select':
							echo '<select id="'.esc_attr( $id ).'" class="widefat '.esc_attr( $field_class ).'" name="'.esc_attr( $name ).'">';
							echo '<option value="">'.esc_html__( 'Select an option', 'mmenu' ).'</option>';
							foreach ($option['options'] as $option_item_key => $option_item) {
								$selected = ($option_item_key === $value) ? 'selected=selected': '';
								echo '<option value="'.esc_attr( $option_item_key ).'" '.esc_attr( $selected ).'>'.esc_attr( $option_item ).'</option>';
							}
							echo '</select>';
							break;

						default:
							echo '<input type="text" id="'.esc_attr( $id ).'" class="widefat '.esc_attr( $field_class ).'" name="'.esc_attr( $name ).'" value="'.esc_attr( $value ).'">';
							break;
					}

					if ($option['type'] !== 'checkbox') {
						echo '</label>';
					}

					if (!empty($desc)) {
						echo '<span class="description">'.esc_html( $desc ).'</span>';
					}

				?>
				</div>
			<?php
		endforeach;
	}

	/**
	 * Custom scripts and styles
	 */
    public static function _custom_scripts() {
        ?>
        <style type="text/css">
        	.et-menu-panel.description{margin-top: 4px; margin-bottom: 6px; font-style: italic; color: #666; line-height: 1.5;}
        	.et-menu-panel .item-label{display: block;}
            .et-menu-panel .et-img[src=""] {display: none;}
            .et-menu-panel .et-img {max-width: 100%; width: inherit; display: block; margin-bottom: 10px; border: 1px solid #eee; padding: 10px}
            .et-menu-panel .button.et-browse {margin-right: 5px;}
            .et-menu-panel .button.et-img-remove {color: #a00;}
            .et-menu-panel .icon-picker-button span, .remove-icon span{line-height: 27px;}
            .et-menu-panel .wp-picker-container, .wp-picker-container:active {display: block;}
            .et-menu-panel .wp-picker-holder{position: relative;}
            .et-menu-panel .wp-picker-container .iris-picker{position: absolute;left: 0;top: 0;}
        </style>

        <script>
        	(function($){
				'use strict';
	        	function et_menu_function(){
	                //color picker
	                $('.et-color').wpColorPicker();

	                // Image Upload
	                $('.et-img[src!=""]').next().next().hide();
	                $('.et-img[src=""]').next().next().next().hide();

	                $('.et-img-remove').on('click', function (event) {
	                    event.preventDefault();
	                    var self = $(this);
	                    self.hide();
	                    self.prev('.et-browse').show();
	                    self.prev('.et-browse').prev('.et-url').val('');
	                    self.prev('.et-browse').prev('.et-url').prev('.et-img').hide();
	                });

	                $('.et-browse').on('click', function (event) {
	                    event.preventDefault();
	                    var self = $(this);
	                    // Create the media frame.
	                    var file_frame = wp.media.frames.file_frame = wp.media({
	                        title: self.data('uploader_title'),
	                        button: {
	                            text: self.data('uploader_button_text'),
	                        },
	                        multiple: false
	                    });
	                    file_frame.on('select', function () {
	                        var attachment = file_frame.state().get('selection').first().toJSON();
	                        self.hide();
	                        self.next('.et-img-remove').show();
	                        self.prev('.et-url').val(attachment.url).change();
	                        self.prev('.et-url').prev('.et-img').attr('src', attachment.url);
	                        self.prev('.et-url').prev('.et-img').show();
	                    });
	                    // Finally, open the modal
	                    file_frame.open();
	                });
	        	}

	        	// Initialize
	            $(function(){
					if (typeof et_menu_function == 'function'){
						et_menu_function();
					}
	            	$('#menu-management ul.menu.ui-sortable').on('ajaxComplete', function(){
	            		et_menu_function();
	            	});
	        	});
        	})(jQuery);
        </script>
        <?php
    }

	/**
	 * Add our fields to the screen options toggle
	 *
	 * @param array $columns Menu item columns
	 * @return array
	 */
	public static function _columns( $columns ) {
		$all_fields = self::$fields;
		$column_items = array();

		foreach ($all_fields as $key => $value) {
			$column_items[$key] = $value['label'];
		}

		$columns = array_merge( $columns, $column_items );
		return $columns;
	}
}
Menu_Item_Custom_Fields_Config::init();