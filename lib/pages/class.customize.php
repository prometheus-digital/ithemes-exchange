<?php
/**
 * Add ghost pages to the customizer.
 *
 * @since   1.33
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Menu_Customizer
 */
class IT_Exchange_Menu_Customizer {

	/**
	 * IT_Exchange_Menu_Customizer constructor.
	 */
	public function __construct() {
		add_filter( 'customize_nav_menu_available_item_types', array( $this, 'register_item_type' ) );
		add_filter( 'customize_nav_menu_available_items', array( $this, 'items_ajax' ), 10, 4 );
	}

	/**
	 * Register our item type.
	 *
	 * @since 1.33
	 *
	 * @param array $item_types
	 *
	 * @return array
	 */
	public function register_item_type( $item_types ) {

		$item_types[] = array(
			'title'  => 'iThemes Exchange',
			'type'   => 'exchange-casper',
			'object' => 'exchange-page'
		);

		return $item_types;
	}

	/**
	 * Add our exchange casper items to the ajax response.
	 *
	 * @since 1.33
	 *
	 * @param array $items
	 * @param string $type
	 * @param string $object
	 * @param int $page
	 *
	 * @return array
	 */
	public function items_ajax( $items, $type, $object, $page ) {

		if ( 'exchange-casper' === $type ) {

			$pages = it_exchange_get_pages( true, array( 'type' => 'exchange' ) );
			unset( $pages['product'] );
			unset( $pages['transaction'] );
			$pages = array_slice( $pages, 10 * $page, 10 );

			foreach ( $pages as $page ) {
				$items[] = array(
					'id'         => "casper-{$page['slug']}",
					'title'      => $page['name'],
					'type'       => 'custom',
					'type_label' => 'Custom Link',
					'object'     => (object) $page,
					'object_id'  => $page['slug'],
					'url'        => it_exchange_get_page_url( $page['slug'] )
				);
			}
		}

		return $items;
	}
}

new IT_Exchange_Menu_Customizer();