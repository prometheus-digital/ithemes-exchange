<?php
/**
 * Transaction Field Config.
 *
 * @since   2.0.0
 * @license GPLv2
 */

use Doctrine\Common\Collections\Criteria;
use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Query\Tag\Where;
use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Fields\AggregateField;
use iThemes\Exchange\REST\Fields\CallableField;
use iThemes\Exchange\REST\Fields\CallableQueryArg;
use iThemes\Exchange\REST\Fields\IDField;
use iThemes\Exchange\REST\Fields\PageQueryArg;
use iThemes\Exchange\REST\Fields\PerPageQueryArg;

return array(
	'fields' => array(
		new IDField(),
		new CallableField( 'order_number', array(
			'description' => __( 'The order number.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'string',
			'readonly'    => true,
		), array( 'view', 'edit', 'embed' ),
			function ( $txn ) { return it_exchange_get_transaction_order_number( $txn ); }
		),
		new AggregateField( 'customer', array(
			new CallableField( 'id', array(
				'description' => __( 'The id of the customer for this transaction.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'readonly'    => true,
			), array( 'view', 'edit', 'embed' ), 'it_exchange_get_transaction_customer_id' ),
			new CallableField( 'email', array(
				'description' => __( 'The email address of the customer for this transaction.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'string',
				'format'      => 'email',
				'readonly'    => true,
			), array( 'view', 'edit', 'embed' ), 'it_exchange_get_transaction_customer_email' ),
			new CallableField( 'name', array(
				'description' => __( 'The display name of the customer for this transaction.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'string',
				'readonly'    => true,
			), array( 'view', 'edit', 'embed' ), 'it_exchange_get_transaction_customer_display_name' ),
			new CallableField( 'avatar', array(
				'description' => __( 'The display name of the customer for this transaction.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'string',
				'readonly'    => true,
			), array( 'view', 'edit', 'embed' ), function ( IT_Exchange_Transaction $txn ) {
				return get_avatar_url( $txn->get_customer_email(), array( 'size' => 96 ) );
			} ),
		) ),
		new AggregateField( 'method', array(
			new CallableField( 'slug', array(
				'description' => __( 'The transaction method slug.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'string',
				'readonly'    => true,
			), array( 'edit' ), 'it_exchange_get_transaction_method' ),
			new CallableField( 'label', array(
				'description' => __( 'The transaction method name.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'string',
				'readonly'    => true,
			), array( 'view', 'edit' ), 'it_exchange_get_transaction_method_name' ),
		) ),
		new CallableField( 'method_id', array(
			'description' => __( 'The method id used for this transaction.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'string',
			'readonly'    => true,
		), array( 'edit' ), 'it_exchange_get_transaction_method_id' ),
		new CallableField( 'status', array(
			'description' => __( 'The transaction status.', 'it-l10n-ithemes-exchange' ),
			'required'    => true,
			'oneOf'       => array(
				array(
					'type'       => 'object',
					'properties' => array(
						'slug'  => array(
							'description' => __( 'The transaction status slug.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
							'required'    => true,
						),
						'label' => array(
							'description' => __( 'The transaction status label.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' )
						),
					),
				),
				array(
					'type'        => 'string',
					'description' => __( 'The transaction status slug.', 'it-l10n-ithemes-exchange' ),
				),
			),
		), array( 'view', 'edit', 'embed' ), function ( IT_Exchange_Transaction $transaction ) {
			return array(
				'slug'  => $transaction->get_status(),
				'label' => $transaction->get_status( true ),
			);
		}, function ( IT_Exchange_Transaction $transaction, $value ) {
			if ( is_string( $value ) ) {
				return $transaction->update_status( $value );
			} elseif ( is_array( $value ) && isset( $value['slug'] ) ) {
				return $transaction->update_status( $value['slug'] );
			} else {
				return false;
			}
		} ),
		new CallableField( 'cleared_for_delivery', array(
			'description' => __( 'The cleared for delivery status of the transaction.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'boolean',
			'readonly'    => true,
		), array( 'view', 'edit', 'embed' ), 'it_exchange_transaction_is_cleared_for_delivery' ),
		new CallableField( 'order_date', array(
			'description' => __( 'The date the transaction was placed, as GMT.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'string',
			'format'      => 'date-time',
			'readonly'    => true,
		), array( 'view', 'edit', 'embed' ), function ( IT_Exchange_Transaction $transaction ) {
			return \iThemes\Exchange\REST\format_rfc339( $transaction->get_date( true ) );
		} ),
		new AggregateField( 'payment', array(
			new CallableField( 'label', array(
				'description' => __( 'Human readable label of the payment method used.' ),
				'type'        => 'string',
				'readonly'    => true,
			), array( 'view', 'edit', 'embed' ), function ( IT_Exchange_Transaction $transaction ) {
				return ( $source = $transaction->get_payment_source() ) ? $source->get_label() : '';
			} ),
			new CallableField( 'card', array(
				'oneOf'    => array(
					array( '$ref' => \iThemes\Exchange\REST\url_for_schema( 'card' ) ),
					array( 'type' => 'null' ),
				),
				'readonly' => true,
			), array( 'view', 'edit' ), function ( IT_Exchange_Transaction $transaction ) {
				$card = $transaction->get_card();

				if ( $card ) {
					return array(
						'number' => $card->get_redacted_number(),
						'year'   => $card->get_expiration_year(),
						'month'  => $card->get_expiration_month(),
						'name'   => $card->get_holder_name(),
					);
				}

				return null;
			} ),
			new CallableField( 'token', array(
				'description' => __( 'The payment token used.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'readonly'    => true,
			), array( 'view', 'edit' ), function ( IT_Exchange_Transaction $transaction ) {
				return $transaction->get_raw_attribute( 'payment_token' );
			} ),
		) ),
		new CallableField( 'purchase_mode', array(
			'description' => __( 'The mode the transaction was created in.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'string',
			'enum'        => array( \ITE_Const::P_MODE_SANDBOX, \ITE_Const::P_MODE_LIVE, '' ),
			'readonly'    => true,
		), array( 'view', 'edit', 'embed' ), function ( IT_Exchange_Transaction $transaction ) {
			return $transaction->purchase_mode;
		} ),
		new CallableField( 'parent', array(
			'description' => __( 'The id of the parent transaction.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'integer',
			'readonly'    => true,
		), array( 'view', 'edit' ), function ( IT_Exchange_Transaction $transaction ) {
			return $transaction->get_raw_attribute( 'parent' );
		} ),
		new CallableField( 'subtotal', array(
			'description' => __( 'The transaction subtotal.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'number',
			'readonly'    => true,
		), array( 'view', 'edit', 'embed' ), function ( IT_Exchange_Transaction $transaction ) {
			return $transaction->get_subtotal();
		} ),
		new CallableField( 'total', array(
			'description' => __( 'The transaction total.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'number',
			'readonly'    => true,
		), array( 'view', 'edit', 'embed' ), function ( IT_Exchange_Transaction $transaction ) {
			return $transaction->get_total();
		} ),
		new CallableField( 'total_before_refunds', array(
			'description' => __( 'The transaction total before refunds have been applied.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'number',
			'readonly'    => true,
		), array( 'view', 'edit' ), function ( IT_Exchange_Transaction $transaction ) {
			return $transaction->get_total( false );
		} ),
		new CallableField( 'open_for_refund', array(
			'description' => __( 'Is the transaction open for refunds.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'boolean',
			'readonly'    => true,
		), array( 'view', 'edit', 'embed' ), 'it_exchange_transaction_can_be_refunded' ),
		new CallableField( 'currency', array(
			'description' => __( 'The transaction currency.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'string',
			'readonly'    => true,
		), array( 'view', 'edit', 'embed' ), 'it_exchange_get_transaction_currency' ),
		new CallableField( 'description', array(
			'description' => __( 'The transaction description.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'string',
			'readonly'    => true,
		), array( 'view', 'edit', 'embed' ), 'it_exchange_get_transaction_description' ),
		new CallableField( 'billing_address', array(
			'description' => __( 'The billing address for this transaction.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'object',
			'$ref'        => \iThemes\Exchange\REST\url_for_schema( 'address' ),
			'readonly'    => true,
		), array( 'view', 'edit' ), function ( IT_Exchange_Transaction $transaction ) {
			return $transaction->get_billing_address() ? $transaction->get_billing_address()->to_array() : array();
		} ),
		new CallableField( 'shipping_address', array(
			'description' => __( 'The shipping address for this transaction.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'object',
			'$ref'        => \iThemes\Exchange\REST\url_for_schema( 'address' ),
			'readonly'    => true,
		), array( 'view', 'edit' ), function ( IT_Exchange_Transaction $transaction ) {
			return $transaction->get_shipping_address() ? $transaction->get_shipping_address()->to_array() : array();
		} ),
	),
	'query'  => array(
		new PerPageQueryArg(),
		new PageQueryArg(),
		new CallableQueryArg( 'customer', array(
			'description' => __( 'The customer whose transactions should be retrieved.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'integer',
			'default'     => 0,
		),
			function ( $scope, $id ) { return $id ? $scope->can( 'edit_user', $id ) : $scope->can( 'list_it_transactions' ); },
			'customer_id',
			function ( $id ) { return $id ? (bool) get_userdata( $id ) : true; }
		),
		new CallableQueryArg( 'method_id', array(
			'description' => __( 'Filter by method id.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'string',
		),
			function ( $scope ) { return $scope->can( 'list_it_transactions' ); }
		),
		new CallableQueryArg( 'cleared_for_delivery', array(
			'description' => __( 'Only return transactions that have been cleared for delivery.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'boolean',
			'default'     => null,
		), null, 'cleared' ),
		new CallableQueryArg( 'method', array(
			'description' => __( 'Filter by transaction method.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'string',
			'enum'        => array_map( function ( $gateway ) { return $gateway->get_slug(); }, \ITE_Gateways::all() )
		) ),
		new CallableQueryArg( 'method_id', array(
			'description' => __( 'Filter by method id.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'string',
		), function ( $scope ) { return $scope->can( 'edit_others_it_transactions' ); } ),
		new CallableQueryArg( 'parent', array(
			'description' => __( 'Retrieve child transactions of a given parent.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'integer',
		), function ( AuthScope $scope, $id ) { return $scope->can( 'edit_it_transaction', $id ); },
			null, function ( $value ) { return (bool) it_exchange_get_transaction( $value ); } ),
		new CallableQueryArg( 'search', array(
			'description' => __( 'Limit results to those matching a string.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'string',
			'minLength'   => 3,
			'maxLength'   => 300,
		), null, function ( Criteria $criteria, $s ) {

			add_action( 'it_exchange_get_transaction_objects', function ( FluentQuery $query, $_criteria ) use ( $criteria, $s ) {
				if ( ! $_criteria !== $criteria ) {
					return;
				}

				$s = $GLOBALS['wpdb']->esc_like( $s );

				$aliases = array();

				$query->join( new \IronBound\DB\WP\Users(), 'customer_id', 'ID', '=',
					function ( FluentQuery $query ) use ( &$aliases ) {
						$aliases['users'] = $query->get_alias();
					} );

				$query->join( new ITE_Transaction_Line_Item_Table(), 'ID', 'transaction', '=',
					function ( FluentQuery $query ) use ( &$aliases ) {
						$aliases['items'] = $query->get_alias();
					} );

				$where = new Where( "{$aliases['users']}.display_name", 'LIKE', "%{$s}%" );
				$where->qOr( new Where( "{$aliases['items']}.name", 'LIKE', "%{$s}%" ) );
				$where->qOr( new Where( "{$aliases['items']}.description", 'LIKE', "%{$s}%" ) );

				$query->and_where( $where );
			}, 10, 2 );
		} )
	),
);