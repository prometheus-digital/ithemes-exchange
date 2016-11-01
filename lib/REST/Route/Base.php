<?php
/**
 * Base Route class.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route;

use iThemes\Exchange\REST\Manager;
use iThemes\Exchange\REST\Route;

/**
 * Class Base
 * @package iThemes\Exchange\REST\Route
 */
abstract class Base implements Route {

	/** @var Route */
	private $parent;

	/** @var Manager */
	private $manager;

	/**
	 * @inheritDoc
	 */
	public function has_parent() {
		return (bool) $this->parent;
	}

	/**
	 * @inheritDoc
	 */
	public function get_parent() {
		if ( ! $this->parent ) {
			throw new \UnexpectedValueException( "No parent exists for {$this->get_path()}" );
		}

		return $this->parent;
	}

	/**
	 * Set the parent route.
	 *
	 * @since 1.36.0
	 *
	 * @param \iThemes\Exchange\REST\Route $route
	 *
	 * @return $this
	 */
	public function set_parent( Route $route ) {
		$this->parent = $route;

		return $this;
	}

	/**
	 * Get the REST manager.
	 *
	 * @since 1.36.0
	 *
	 * @return \iThemes\Exchange\REST\Manager
	 */
	public function get_manager() {
		return $this->manager;
	}

	/**
	 * Set the REST manager.
	 *
	 * @since 1.36.0
	 *
	 * @param \iThemes\Exchange\REST\Manager $manager
	 *
	 * @return $this
	 */
	public function set_manager( $manager ) {
		$this->manager = $manager;

		return $this;
	}
}