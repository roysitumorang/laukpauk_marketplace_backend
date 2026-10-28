<?php

namespace Application\Library\Session\Adapter;

use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Db\Column;
use Phalcon\Db\Enum;
use SessionHandlerInterface;

/**
 * Database session handler for Phalcon\Session\Manager.
 *
 * Port of the Phalcon 3 incubator adapter (Phalcon\Session\Adapter\Database)
 * that this application used. It keeps the same table layout
 * (id, user_id, ip_address, user_agent, data, last_activity) so existing
 * session rows stay valid.
 */
class Database implements SessionHandlerInterface {
	protected AdapterInterface $connection;
	protected string $table;
	protected array $columns = [];

	function __construct(AdapterInterface $connection, string $table, array $columns = []) {
		$this->connection = $connection;
		$this->table      = $table;
		foreach (['id', 'user_id', 'ip_address', 'user_agent', 'data', 'last_activity'] as $column) {
			$this->columns[$column] = isset($columns[$column]) && is_string($columns[$column]) && $columns[$column] !== '' ? $columns[$column] : $column;
		}
	}

	function open(string $path, string $name): bool {
		return true;
	}

	function close(): bool {
		return true;
	}

	function read(string $id): string|false {
		$max_lifetime = (int) ini_get('session.gc_maxlifetime');
		$row = $this->connection->fetchOne(
			sprintf(
				'SELECT %s FROM %s WHERE %s = ? AND %s + %d >= ?',
				$this->_column('data'),
				$this->_table(),
				$this->_column('id'),
				$this->_column('last_activity'),
				$max_lifetime
			),
			Enum::FETCH_NUM,
			[$id, time()],
			[Column::BIND_PARAM_STR, Column::BIND_PARAM_INT]
		);
		return $row ? (string) $row[0] : '';
	}

	function write(string $id, string $data): bool {
		$user_id    = $_SESSION['user_id'] ?? null;
		$ip_address = $this->_server('REMOTE_ADDR');
		$user_agent = $this->_server('HTTP_USER_AGENT');
		$exists     = $this->connection->fetchColumn(
			sprintf('SELECT COUNT(1) FROM %s WHERE %s = ?', $this->_table(), $this->_column('id')),
			[$id]
		);
		if ($exists) {
			return $this->connection->execute(
				sprintf(
					'UPDATE %s SET %s = ?, %s = ?, %s = ?, %s = ?, %s = ? WHERE %s = ?',
					$this->_table(),
					$this->_column('user_id'),
					$this->_column('ip_address'),
					$this->_column('user_agent'),
					$this->_column('data'),
					$this->_column('last_activity'),
					$this->_column('id')
				),
				[$user_id, $ip_address, $user_agent, $data, time(), $id]
			);
		}
		return $this->connection->execute(
			sprintf(
				'INSERT INTO %s (%s, %s, %s, %s, %s, %s) VALUES (?, ?, ?, ?, ?, ?)',
				$this->_table(),
				$this->_column('id'),
				$this->_column('user_id'),
				$this->_column('ip_address'),
				$this->_column('user_agent'),
				$this->_column('data'),
				$this->_column('last_activity')
			),
			[$id, $user_id, $ip_address, $user_agent, $data, time()]
		);
	}

	function destroy(string $id): bool {
		return $this->connection->execute(
			sprintf('DELETE FROM %s WHERE %s = ?', $this->_table(), $this->_column('id')),
			[$id]
		);
	}

	function gc(int $max_lifetime): int|false {
		$result = $this->connection->execute(
			sprintf('DELETE FROM %s WHERE %s + %d < ?', $this->_table(), $this->_column('last_activity'), $max_lifetime),
			[time()],
			[Column::BIND_PARAM_INT]
		);
		return $result ? $this->connection->affectedRows() : false;
	}

	private function _table(): string {
		return $this->connection->escapeIdentifier($this->table);
	}

	private function _column(string $name): string {
		return $this->connection->escapeIdentifier($this->columns[$name]);
	}

	private function _server(string $key): ?string {
		$value = $_SERVER[$key] ?? null;
		return is_string($value) ? $value : null;
	}
}
