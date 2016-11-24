<?php

class MigrationManagerTest extends PHPUnit_Framework_TestCase {
	
	private $conn;
	private $setup;
	
	private $mgr;
	
	public function setUp() {
		$config = new \Doctrine\DBAL\Configuration();
			
		$connectionParams = array(
			'url'=> 'sqlite:///test/test.db'
		);

		$this->conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
		
		$tbl = 'test_migration_manager';
		$cols = array(
			array('name'=>'id', 'type'=>'integer', 'options'=>array()),
			array('name'=>'col2', 'type'=>'string', 'options'=>array())
		);
		
		$sql = 'DROP TABLE IF EXISTS ' . $tbl;
		$stmt = $this->conn->prepare($sql);
		$stmt->execute();
		
		$this->mgr = new \Keyhole\Migration\MigrationManager($this->conn, $tbl, $cols);
		
	}
	
	public function testMigrationsNeededOnStartup() {
		$this->assertTrue($this->mgr->migrationsNeeded()); //when the db is empty, the schema needs to be built
	}
	
	public function testDoMigrations() {
		if($this->mgr->migrationsNeeded()) {
			$this->assertTrue($this->mgr->doMigrations()); //returns true if migrations are successful
		}
	}
	
	public function testDoMigrationsMultipleCalls() {
		if($this->mgr->migrationsNeeded()) {
			$this->assertTrue($this->mgr->doMigrations()); //returns true if migrations are successful the first time
		}
		$this->assertFalse($this->mgr->doMigrations()); //should return false now that the migrations have been processed
		
	}
	public function testMigrationsNeededAfterMigrating() {
		if($this->mgr->migrationsNeeded()) {
			$this->mgr->doMigrations();
		}
		$this->assertFalse($this->mgr->migrationsNeeded()); //after migration, no migrations should be needed
	}
	
}

?>