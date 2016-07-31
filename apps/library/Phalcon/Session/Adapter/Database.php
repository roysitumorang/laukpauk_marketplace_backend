<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  |          Nikita Vershinin <endeveit@gmail.com>                         |
  +------------------------------------------------------------------------+
*/
namespace Phalcon\Session\Adapter;

use Phalcon\Session\Adapter;
use Phalcon\Session\AdapterInterface;
use Phalcon\Session\Exception;
use Phalcon\Db\AdapterInterface as DbAdapter;
use Phalcon\Http\Request;

/**
 * Phalcon\Session\Adapter\Database
 * Database adapter for Phalcon\Session
 */
class Database extends Adapter implements AdapterInterface {
	/**
	 * @var DbAdapter
	 */
	protected $connection;
	/**
	 * {@inheritdoc}
	 *
	 * @param  array $options
	 * @throws Exception
	 */
	function __construct(array $options = []) {
		if (!isset($options['db']) || !$options['db'] instanceof DbAdapter) {
			throw new Exception(
				'Parameter "db" is required and it must be an instance of Phalcon\Acl\AdapterInterface'
			);
		}
		$this->connection = $options['db'];
		unset($options['db']);
		if (!isset($options['table']) || empty($options['table']) || !is_string($options['table'])) {
			throw new Exception('Parameter "table" is required and it must be a non empty string');
		}
		$columns = ['id', 'user_id', 'ip_address', 'user_agent', 'data', 'last_activity'];
		foreach ($columns as $column) {
			$oColumn = "column_$column";
			if (!isset($options[$oColumn]) || !is_string($options[$oColumn]) || empty($options[$oColumn])) {
				$options[$oColumn] = $column;
			}
		}
		parent::__construct($options);
		session_set_save_handler(
			[$this, 'open'],
			[$this, 'close'],
			[$this, 'read'],
			[$this, 'write'],
			[$this, 'destroy'],
			[$this, 'gc']
		);
	}
	/**
	 * {@inheritdoc}
	 *
	 * @return boolean
	 */
	function open() { return true; }
	/**
	 * {@inheritdoc}
	 *
	 * @return boolean
	 */
	function close() { return false; }
	/**
	 * {@inheritdoc}
	 *
	 * @param  string $sessionId
	 * @return string
	 */
	function read($sessionId) {
		$maxLifetime = (int) ini_get('session.gc_maxlifetime');
		$options = $this->getOptions();
		return $this->connection->fetchColumn(
			sprintf(
				'SELECT %s FROM %s WHERE %s = ? AND %s + %d >= ?',
				$this->connection->escapeIdentifier($options['column_data']),
				$this->connection->escapeIdentifier($options['table']),
				$this->connection->escapeIdentifier($options['column_id']),
				$this->connection->escapeIdentifier($options['column_last_activity']),
				$maxLifetime
			),
			[$sessionId, time()]
		);
	}
	/**
	 * {@inheritdoc}
	 *
	 * @param  string $sessionId
	 * @param  string $data
	 * @return boolean
	 */
	function write($sessionId, $data) {
		$options = $this->getOptions();
		$request = new Request;
		$row = $this->connection->fetchColumn(
			sprintf(
				'SELECT COUNT(*) FROM %s WHERE %s = ?',
				$this->connection->escapeIdentifier($options['table']),
				$this->connection->escapeIdentifier($options['column_id'])
			),
			[$sessionId]
		);
		if (intval($row[0]) > 0) {
			return $this->connection->execute(
				sprintf(
					'UPDATE %s SET %s = ?, %s = ?, %s = ?, %s = ?, %s = ? WHERE %s = ?',
					$this->connection->escapeIdentifier($options['table']),
					$this->connection->escapeIdentifier($options['column_user_id']),
					$this->connection->escapeIdentifier($options['column_ip_address']),
					$this->connection->escapeIdentifier($options['column_user_agent']),
					$this->connection->escapeIdentifier($options['column_data']),
					$this->connection->escapeIdentifier($options['column_last_activity']),
					$this->connection->escapeIdentifier($options['column_id'])
				),
				[$this->get('user_id'), $request->getClientAddress(), $request->getUserAgent(), $data, time(), $sessionId]
			);
		}
		return $this->connection->execute(
			sprintf(
				'INSERT INTO %s (%s, %s, %s, %s, %s, %s) VALUES (?, ?, ?, ?, ?, ?)',
				$this->connection->escapeIdentifier($options['table']),
				$this->connection->escapeIdentifier($options['column_id']),
				$this->connection->escapeIdentifier($options['column_user_id']),
				$this->connection->escapeIdentifier($options['column_ip_address']),
				$this->connection->escapeIdentifier($options['column_user_agent']),
				$this->connection->escapeIdentifier($options['column_data']),
				$this->connection->escapeIdentifier($options['column_last_activity'])
			),
			[$sessionId, $this->get('user_id'), $request->getClientAddress(), $request->getUserAgent(), $data, time()]
		);
	}
	/**
	 * {@inheritdoc}
	 *
	 * @return boolean
	 */
	function destroy($id = null) {
		if (!$this->isStarted()) {
			return true;
		}
		if (is_null($id)) {
			$id = $this->getId();
		}
		$this->_started = false;
		$options = $this->getOptions();
		$result = $this->connection->execute(
			sprintf(
				'DELETE FROM %s WHERE %s = ?',
				$this->connection->escapeIdentifier($options['table']),
				$this->connection->escapeIdentifier($options['column_id'])
			),
			[$id]
		);
		return $result && session_destroy();
	}
	/**
	 * {@inheritdoc}
	 * @param  integer $maxlifetime
	 *
	 * @return boolean
	 */
	function gc($maxlifetime) {
		$options = $this->getOptions();
		return $this->connection->execute(
			sprintf(
				'DELETE FROM %s WHERE %s + %d < ?',
				$this->connection->escapeIdentifier($options['table']),
				$this->connection->escapeIdentifier($options['column_last_activity']),
				$maxlifetime
			),
			[time()]
		);
	}
}
