<?php


class Logger
{
	private static $tableName;

	public function __construct()
	{

	}

	private static function getTableName(){
		global $wpdb;
		return $wpdb->prefix . "currency_exchange_log";
	}

	function create_storage()
	{
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$tableName = Logger::getTableName();
		$sql = "CREATE TABLE  IF NOT EXISTS $tableName (
		id int(11) NOT NULL AUTO_INCREMENT,
		remote_addr varchar(255) NOT NULL DEFAULT '',
		log_date timestamp NOT NULL DEFAULT NOW(),
		message text NOT NULL,
		UNIQUE KEY id (id) ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

	}

	public static function log($message)
	{
		global $wpdb;
		$remote_addr = $_SERVER['REMOTE_ADDR'];
		if (empty($remote_addr)) {        // Get IP address
			$remote_addr = "REMOTE_ADDR_UNKNOWN";
		}
		$message = esc_sql($message);
		$remote_addr = esc_sql($remote_addr);

		$tableName = Logger::getTableName();
		$wpdb->insert($tableName, array(
			'remote_addr' => $remote_addr,
			'message' => $message
		));
		logShrink();
	}

	public static function logShrink()
	{
		//todo Sometimes delete old records. For performance, not every call
	}



	function read($limit=10)
	{
		global $wpdb;
		$tableName = Logger::getTableName();
		$sql = "SELECT * FROM $tableName  WHERE 1=1 ORDER BY log_date DESC LIMIT $limit";
		return $wpdb->get_results($sql);
	}

	function debugPrint()
	{
		$logger = new Logger();
		echo "<br>Log:";
		foreach ( $logger->read(20) as $row ) {
			echo "<br> $row->log_date  $row->remote_addr $row->message";
		}
	}



}
