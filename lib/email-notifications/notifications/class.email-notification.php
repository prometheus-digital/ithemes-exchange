<?php
/**
 * Single email notification class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Notification
 */
abstract class IT_Exchange_Email_Notification {

	/**
	 * @var string
	 */
	private $slug;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $subject;

	/**
	 * @var string
	 */
	private $body;

	/**
	 * @var bool
	 */
	private $active = true;

	/**
	 * @var IT_Exchange_Email_Template
	 */
	private $template;

	/**
	 * @var string
	 */
	private $description = '';

	/**
	 * @var string
	 */
	private $group;

	/**
	 * @var string
	 */
	private $previous = '';

	/**
	 * @var array
	 */
	private $args = array();

	/**
	 * IT_Exchange_Email_Notification constructor.
	 *
	 * @param string                     $name
	 * @param string                     $slug
	 * @param IT_Exchange_Email_Template $template
	 * @param array                      $args
	 */
	public function __construct( $name, $slug, IT_Exchange_Email_Template $template = null, array $args = array() ) {
		$this->name     = $name;
		$this->slug     = $slug;
		$this->template = $template ? $template : new IT_Exchange_Email_Template( '' );

		$emails = it_exchange_get_option( 'emails' );

		$defaults = isset( $args['defaults'] ) ? $args['defaults'] : array();

		if ( ! is_array( $emails ) || ! isset( $emails[ $this->get_slug() ] ) ) {
			$data = $defaults;
		} else {
			$data = $emails[ $this->get_slug() ];
		}

		$data = ITUtility::merge_defaults( $data, $defaults );

		$this->setup_properties( $data );

		if ( empty( $data['upgraded'] ) && ! empty( $args['previous'] ) ) {
			$this->set_body( $this->convert_to_curly( $args['previous'] ) );

			$emails = it_exchange_get_option( 'emails', true );

			if ( ! is_array( $emails ) ) {
				$emails = array();
			}

			$emails[ $this->get_slug() ] = $this->get_data_to_save();

			$emails[ $this->get_slug() ]['upgraded'] = true;

			it_exchange_save_option( 'emails', $emails, true );
		}

		if ( ! empty( $args['description'] ) ) {
			$this->description = $args['description'];
		}

		if ( ! empty( $args['group'] ) ) {
			$this->group = $args['group'];
		}

		if ( ! empty( $args['previous'] ) ) {
			$this->previous = $args['previous'];
		}

		$this->args = $args;
	}

	/**
	 * Setup this object's properties.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data
	 */
	protected function setup_properties( $data ) {
		$this->set_subject( isset( $data['subject'] ) ? $data['subject'] : '' );
		$this->set_body( isset( $data['body'] ) ? $data['body'] : '' );
		$this->set_active( isset( $data['active'] ) ? (bool) $data['active'] : true );
	}

	/**
	 * Get the email slug.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the email name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the notification type.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	abstract public function get_type( $label = false );

	/**
	 * Get the subject line of the notification.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_subject() {
		return $this->subject;
	}

	/**
	 * Set the subject line of the notification.
	 *
	 * @since 2.0.0
	 *
	 * @param string $subject
	 *
	 * @return self
	 */
	public function set_subject( $subject ) {
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Get the notification body.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * Set the notification body.
	 *
	 * @since 2.0.0
	 *
	 * @param string $body
	 *
	 * @return self
	 */
	public function set_body( $body ) {
		$this->body = $body;

		return $this;
	}

	/**
	 * Check if this notification is active.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function is_active() {
		return $this->active;
	}

	/**
	 * Set the active state of the email notification.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $active
	 *
	 * @return self
	 */
	public function set_active( $active ) {

		if ( ! is_bool( $active ) ) {
			throw new InvalidArgumentException( sprintf( '$active must be a boolean, %s, given', gettype( $active ) ) );
		}

		$this->active = $active;

		return $this;
	}

	/**
	 * Get the template for this notification.
	 *
	 * @since 2.0.0
	 *
	 * @return IT_Exchange_Email_Template
	 */
	public function get_template() {
		return $this->template;
	}

	/**
	 * Get the notification description.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Check if the notification has a description.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function has_description() {
		return trim( $this->get_description() ) !== '';
	}

	/**
	 * Get the previous email contents.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_previous() {
		return $this->previous;
	}

	/**
	 * Check if this notification has a previous value.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function has_previous() {
		return trim( $this->get_previous() ) !== '';
	}

	/**
	 * Get the group this notification belongs to.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_group() {
		return $this->group;
	}

	/**
	 * Check if the notification values are not at their default settings.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_non_default() {

		$defaults = isset( $this->args['defaults'] ) ? $this->args['defaults'] : array();

		if ( ! isset( $defaults['subject'] ) || preg_replace( '/\s+/', '', $this->subject ) !== preg_replace( '/\s+/', '', $defaults['subject'] ) ) {
			return true;
		}

		if ( ! isset( $defaults['body'] ) || preg_replace( '/\s+/', '', $this->body ) !== preg_replace( '/\s+/', '', $defaults['body'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the data to save.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_data_to_save() {
		return array(
			'subject' => $this->get_subject(),
			'body'    => $this->get_body(),
			'active'  => $this->is_active()
		);
	}

	/**
	 * Save the email notification.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function save() {

		$emails = it_exchange_get_option( 'emails', true );

		if ( ! is_array( $emails ) ) {
			$emails = array();
		}

		$existing = isset( $emails[ $this->get_slug() ] ) ? $emails[ $this->get_slug() ] : array();

		$emails[ $this->get_slug() ] = ITUtility::merge_defaults( $this->get_data_to_save(), $existing, true );

		return it_exchange_save_option( 'emails', $emails, true );
	}

	/**
	 * Convert content to curly tags instead of shortcodes.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	protected function convert_to_curly( $content ) {

		$backup = $GLOBALS['shortcode_tags'];

		$GLOBALS['shortcode_tags'] = array();

		add_shortcode( 'it_exchange_email', function ( $atts ) {

			if ( empty( $atts['show'] ) ) {
				return '';
			}

			return '{{' . $atts['show'] . '}}';
		} );

		$converted = do_shortcode( $content );

		$GLOBALS['shortcode_tags'] = $backup;

		return $converted;
	}
}
