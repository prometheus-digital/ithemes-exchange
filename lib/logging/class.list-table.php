<?php
/**
 * Log List Table.
 *
 * @since   2.0.0
 * @license GPLv2
 */
use Doctrine\Common\Collections\Expr\Comparison;

/**
 * Class ITE_Log_List_Table
 */
class ITE_Log_List_Table extends WP_List_Table {

	/** @var ITE_Queryable_Logger|ITE_Retrievable_Logger */
	private $logger;

	/**
	 * ITE_Log_List_Table constructor.
	 *
	 * @param \Psr\Log\LoggerInterface $logger
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( \Psr\Log\LoggerInterface $logger ) {

		if ( ! $logger instanceof ITE_Queryable_Logger && ! $logger instanceof ITE_Retrievable_Logger ) {
			throw new InvalidArgumentException( 'Logger must either be queryable or retrievable.' );
		}

		$this->logger = $logger;
		parent::__construct( array(
			'plural'   => 'logs',
			'singular' => 'log',
			'screen'   => get_current_screen(),
		) );
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {

		$columns = array(
			'message' => __( 'Message', 'it-l10n-ithemes-exchange' ),
			'level'   => __( 'Level', 'it-l10n-ithemes-exchange' ),
			'time'    => __( 'Time', 'it-l10n-ithemes-exchange' ),
			'ip'      => __( 'IP', 'it-l10n-ithemes-exchange' ),
			'user'    => __( 'User', 'it-l10n-ithemes-exchange' ),
			'group'   => __( 'Group', 'it-l10n-ithemes-exchange' ),
		);

		if ( isset( $this->items[0] ) ) {
			/** @var ITE_Log_Item $item */
			$item = $this->items[0];

			if ( ! $item->has_user() ) {
				unset( $columns['user'] );
			}

			if ( ! $item->has_group() ) {
				unset( $columns['group'] );
			}

			if ( ! $item->has_ip() ) {
				unset( $columns['ip'] );
			}
		}

		return $columns;
	}

	/**
	 * @inheritDoc
	 */
	protected function get_sortable_columns() {
		if ( $this->logger instanceof ITE_Queryable_Logger ) {
			return array(
				'time'  => array( 'time', true ),
				'level' => array( 'level', true ),
			);
		}

		return array();
	}

	/**
	 * @inheritDoc
	 */
	protected function get_primary_column_name() {
		return 'message';
	}

	/**
	 * @inheritDoc
	 */
	protected function column_default( $item, $column_name ) {
		if ( method_exists( $item, "get_{$column_name}" ) ) {
			echo call_user_func( array( $item, "get_{$column_name}" ) ) ?: '-';
		} else {
			echo '-';
		}
	}

	/**
	 * Print the log level as a translated string.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Log_Item $item
	 *
	 * @return string
	 */
	public function column_level( ITE_Log_Item $item ) {

		if ( ! $item->get_level() ) {
			return '-';
		}

		$levels = ITE_Log_Levels::get_levels();

		if ( ! isset( $levels[ $item->get_level() ] ) ) {
			return $item->get_level();
		}

		return $levels[ $item->get_level() ];
	}

	/**
	 * Render the user column.
	 *
	 * @since 1.0
	 *
	 * @param ITE_Log_Item $item
	 */
	public function column_user( ITE_Log_Item $item ) {

		$user = $item->get_user();

		if ( $user ) {
			echo $user->user_email;
		} elseif ( $item->get_user_id() ) {
			echo "#{$item->get_user_id()}";
		} else {
			echo '-';
		}
	}

	/**
	 * Render the time column.
	 *
	 * @since 1.0
	 *
	 * @param ITE_Log_Item $item
	 */
	public function column_time( ITE_Log_Item $item ) {

		$time = $item->get_time();

		if ( $time ) {
			echo $time->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
		} else {
			echo '-';
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function extra_tablenav( $which ) {

		if ( $which === 'top' ) {
			$this->render_top_tablenav();
		} else {
			$this->render_bottom_tablenav();
		}
	}

	/**
	 * Render the top tablenav.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	protected function render_top_tablenav() {

		if ( ! $this->logger instanceof ITE_Queryable_Logger ) {
			return;
		}

		$filters = $this->logger->get_supported_filters();

		$selected_level = isset( $_GET['level'] ) ? $_GET['level'] : '';
		$group          = isset( $_GET['group'] ) ? $_GET['group'] : '';
		?>

		<?php if ( isset( $filters['level'] ) ) : ?>
            <label for="filter-by-level" class="screen-reader-text">
				<?php _e( 'Filter by Level', 'it-l10n-ithemes-exchange' ); ?>
            </label>
            <select id="filter-by-level" name="level">
                <option value=""><?php _e( 'Any Level', 'it-l10n-ithemes-exchange' ); ?></option>
				<?php foreach ( ITE_Log_Levels::get_levels() as $level => $label ) : ?>
                    <option value="<?php echo esc_attr( $level ); ?>" <?php selected( $selected_level, $level ); ?>>
						<?php echo esc_html( $label ); ?>
                    </option>
				<?php endforeach; ?>
            </select>
		<?php endif; ?>

		<?php if ( isset( $filters['group'] ) ): ?>
            <label for="filter-by-group" class="screen-reader-text">
				<?php _e( 'Filter by Group', 'it-l10n-ithemes-exchange' ) ?>
            </label>
            <input type="text" id="filter-by-group" name="group" value="<?php echo esc_attr( $group ); ?>"
                   placeholder="<?php echo esc_attr( __( 'Group', 'it-l10n-ithemes-exchange' ) ); ?>">

		<?php endif;

		if ( $filters ) {
			submit_button( __( 'Filter', 'it-l10n-ithemes-exchange' ), 'button', 'filter', false );
		}
	}

	/**
	 * Render the purge log buttons.
	 *
	 * @since   2.0.0
	 * @license GPLv2
	 */
	protected function render_bottom_tablenav() {

		if ( ! $this->logger instanceof ITE_Purgeable_Logger ) {
			return;
		}
		?>

		<?php if ( $this->logger instanceof ITE_Date_Purgeable_Logger ): ?>
            <input type="number" min="1" name="logs_older_than" placeholder="<?php echo esc_attr( '15 days' ); ?>">
			<?php submit_button(
				__( 'Purge Old Logs', 'it-l10n-ithemes-exchange' ),
				'button button-delete',
				'it_exchange_delete_old_logs',
				false
			) ?>
		<?php endif; ?>

		<?php submit_button(
			__( 'Purge Logs', 'it-l10n-ithemes-exchange' ),
			'button button-delete',
			'it_exchange_delete_logs',
			false
		); ?>

		<?php

        wp_nonce_field( 'it_exchange_delete_logs', 'it_exchange_delete_logs_nonce' );
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_items() {
		$page     = $this->get_pagenum();
		$per_page = $this->get_items_per_page( 'exchange_page_it_exchange_tools_logs_per_page' );

		if ( $this->logger instanceof ITE_Retrievable_Logger ) {
			$this->items = $this->logger->get_log_items( $page, $per_page );
		} elseif ( $this->logger instanceof ITE_Queryable_Logger ) {
			$order_by = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'time';
			$order    = isset( $_GET['order'] ) ? strtoupper( $_GET['order'] ) : 'DESC';
			$level    = isset( $_GET['level'] ) ? $_GET['level'] : '';
			$message  = isset( $_GET['s'] ) ? $_GET['s'] : '';
			$group    = isset( $_GET['group'] ) ? $_GET['group'] : '';

			$filters = $this->logger->get_supported_filters();

			$criteria = \Doctrine\Common\Collections\Criteria::create();
			$criteria->setFirstResult( $per_page * ( $page - 1 ) );
			$criteria->setMaxResults( $per_page );
			$criteria->orderBy( array( $order_by => $order ) );

			if ( $level && isset( $filters['level'] ) ) {
				$criteria->andWhere( new Comparison( $filters['level'], '=', $level ) );
			}

			if ( $message && isset( $filters['message'] ) ) {
				$criteria->andWhere( new Comparison( $filters['message'], Comparison::CONTAINS, $message ) );
			}

			if ( $group && isset( $filters['group'] ) ) {
				$criteria->andWhere( new Comparison( $filters['group'], '=', $group ) );
			}

			$this->items = $this->logger->query( $criteria );
		}

		if ( $this->items ) {
			$this->set_pagination_args( array(
				'per_page'    => $per_page,
				'total_items' => $per_page * 9999,
				'total_pages' => 9999
			) );
		} else {
			$this->set_pagination_args( array(
				'per_page' => $per_page
			) );
		}
	}
}
