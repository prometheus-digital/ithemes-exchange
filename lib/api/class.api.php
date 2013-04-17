<?php
class IT_Exchange_API_Setup {

	function IT_Exchange_API_Setup() {
		add_action( 'template_redirect', array( $this, 'load_api' ) );
	}

	function load_api() {
		if ( ! is_admin() ) {
			if ( is_singular( 'it_exchange_prod' ) ) {
				global $post;
				$GLOBALS['it_exchange']['product'] = it_exchange_get_product( $post );
			}
		}
	}
}
new IT_Exchange_API_Setup();
