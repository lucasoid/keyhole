<?php 

namespace Keyhole\Registry;

/**
 * This class checks for the existence and integrity of field_registry and table_registry tables.
 * If not found, they are created.
 */

class Setup {
	
	/**
	 * 
	 * @var \Doctrine\DBAL\Connection
	 */
	private $conn;
	
	/**
	 * 
	 * @var string	Prefixes the tables used in the application
	 */
	private $prefix;
		
	/**
	 * 
	 * @var \Keyhole\Registry\RegisteredTableMapper
	 */
	private $tableMapper;
	
	/**
	 * 
	 * @var \Keyhole\Registry\RegisteredFieldMapper
	 */
	private $fieldMapper;
	
	/**
	 * 
	 * @var \Keyhole\Registry\RegisteredAccessMapper
	 */
	private $accessMapper;
	
	/**
	 * 
	 * @var \Keyhole\Migration\MigrationManager
	 */
	private $tableManager;
	
	/**
	 * 
	 * @var \Keyhole\Migration\MigrationManager
	 */
	private $fieldManager;
	
	/**
	 * 
	 * @var \Keyhole\Migration\MigrationManager
	 */
	private $accessManager;
	
	/**
	 * 
	 * @param \Doctrine\DBAL\Connection $conn
	 * @param string $prefix	optional string to prefix registry tables
	 */
	public function __construct($conn, $prefix = '', $fieldMapper = null, $tableMapper = null, $accessMapper = null) {
		
		$this->conn = $conn;
		$this->prefix = $prefix;
		$this->fieldMapper = $fieldMapper != null ? $fieldMapper : new RegisteredFieldMapper($conn, $prefix);
		$this->tableMapper = $tableMapper != null ? $tableMapper : new RegisteredTableMapper($conn, $prefix);
		$this->accessMapper = $accessMapper != null ? $accessMapper : new RegisteredAccessMapper($conn, $prefix);
		
		$this->tableManager = new \Keyhole\Migration\MigrationManager(
			$conn, 
			$this->tableMapper->getTableName(), 
			$this->tableMapper->getColumnDefinitions()
		);
		
		$this->fieldManager = new \Keyhole\Migration\MigrationManager(
			$conn,
			$this->fieldMapper->getTableName(),
			$this->fieldMapper->getColumnDefinitions()
		);
		
		$this->accessManager = new \Keyhole\Migration\MigrationManager(
			$conn,
			$this->accessMapper->getTableName(),
			$this->accessMapper->getColumnDefinitions()
		);
	}
	
	/**
	 * Wrapper for MigrationManager methods.
	 * 
	 * @return boolean
	 */
	public function migrationsNeeded() {
		return $this->tableManager->migrationsNeeded() || $this->fieldManager->migrationsNeeded() || $this->accessManager->migrationsNeeded();
	}
	
	/**
	 * Wrapper for MigrationManager methods.
	 *  
	 * @return boolean
	 */
	public function doMigrations() {
		$tbl = $this->tableManager->doMigrations();
		$fld = $this->fieldManager->doMigrations();
		$axs = $this->accessManager->doMigrations();
		return $tbl && $fld && $axs;
	}
	
}

?>