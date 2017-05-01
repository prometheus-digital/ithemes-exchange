<?php
/**
 * Load the Logging component.
 *
 * @since   2.0.0
 * @license GPLv2
 */

require_once __DIR__ . '/abstract.levels.php';
require_once __DIR__ . '/class.db-table.php';
require_once __DIR__ . '/class.item.php';
require_once __DIR__ . '/interface.purgeable.php';
require_once __DIR__ . '/interface.date-purgeable.php';
require_once __DIR__ . '/interface.queryable.php';
require_once __DIR__ . '/interface.retrievable.php';
require_once __DIR__ . '/class.db-logger.php';
require_once __DIR__ . '/class.file-logger.php';

require_once __DIR__ . '/class.list-table.php';
IronBound\DB\Manager::register( new ITE_Log_DB_Table( 'ite-logs' ) );