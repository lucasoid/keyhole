<?php 

namespace Keyhole\Migration;

class MigrationManager {
	
	/**
	 * 
	 * @var \Doctrine\DBAL\Connection
	 */
	private $conn;
		
	/**	 
	 * 
	 * @var \Doctrine\DBAL\AbstractSchemaManager
	 */
	private $sm;
	
	/**	 
	 * 
	 * @var \Doctrine\DBAL\Schema\Schema
	 */
	private $existingSchema;
	
	/**	 
	 * 
	 * @var \Doctrine\DBAL\Schema\Schema
	 */
	private $newSchema;
	
	/**
	 * 
	 * @var array	SQL statements used to execute migrations
	 */
	private $migrations;
	
	public function __construct($conn, $tablename, array $columns) {
		
		$this->conn = $conn;
		$this->sm = $conn->getSchemaManager();
		
		$this->existingSchema = $this->sm->createSchema();
		$this->newSchema = clone $this->existingSchema;
		
		$this->tablename = $tablename;
		$this->columns = $columns;
	}
	
	/**
	 * Updates the schema and determines if any migrations need to be applied.
	 * 
	 * @return boolean
	 */
	public function migrationsNeeded() {
		$this->updateSchema($this->tablename, $this->columns);
		$this->setMigrations();
		return !empty($this->migrations);
	}
	
	/**
	 * Performs any needed migrations, after they have been set.
	 *  
	 * @return boolean	returns true if successful, false on failure
	 */
	public function doMigrations() {
		
		if(empty($this->migrations)) {
			$this->migrationsNeeded();
		}
		
		if(empty($this->migrations)) {
			return false;
		}
		
		try {
			
			foreach($this->migrations as $migration) {
				$stmt = $this->conn->prepare($migration);
				$stmt->execute();
			}
			
			$this->migrations = array();
			$this->existingSchema = $this->newSchema;
			
			return true;
		}
		catch(\Exception $e) {
			//echo $e->getMessage();
			return false;
		}
	}
	
	private function updateSchema($tablename, $columns) {
		if(!$this->newSchema->hasTable($tablename)) {
			$table = $this->newSchema->createTable($tablename);
		}
		else {
			$table = $this->newSchema->getTable($tablename);
		}
		
		foreach($columns as $requiredColumn) {
			if(!$table->hasColumn($requiredColumn['name'])) {
				$options = !empty($requiredColumn['options']) ? $requiredColumn['options'] : array();
				$table->addColumn($requiredColumn['name'], $requiredColumn['type'], $options);
				if(isset($requiredColumn['primarykey']) && $requiredColumn['primarykey'] == true) {
					$table->setPrimaryKey(array($requiredColumn['name']));
				}
				if(isset($requiredColumn['foreignkey'])) {
					$fk = $requiredColumn['foreignkey'];
					$foreignTable = isset($fk[0]) ? $fk[0] : '';
					$foreignColumns = isset($fk[1]) && is_array($fk[1]) ? $fk[1] : array();
					$opts = $fk[2] ? $fk[2] : array();
					$table->addForeignKeyConstraint($foreignTable, array($requiredColumn['name']), $foreignColumns, $opts);
				}
			}
		}
	}
	
	private function setMigrations() {
		$this->migrations = $this->existingSchema->getMigrateToSql($this->newSchema, $this->conn->getDatabasePlatform());
	}
		
	
	
}

?>