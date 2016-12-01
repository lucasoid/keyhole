<?php

class TableManagerTest extends PHPUnit_Framework_TestCase {
	
	private $conn;
	private $registry;
	private $table;
	
	public function setUp() {
		
		$config = new \Doctrine\DBAL\Configuration();
			
		$connectionParams = array(
			'url'=> 'sqlite:///test/test.db'
		);
		
		$this->conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
		
		$this->registry = new \Keyhole\Registry\Registry($this->conn, 'test_table_');
		
		$this->register();
		
		$this->table = new \Keyhole\Table\TableManager($this->conn, 1, $this->registry);
		
		if($this->table->migrationsNeeded()) {
			$this->table->doMigrations();
		}
		
		$this->seed();
	}
	
	private function register() {
		$queries = array();
		$queries[] = 'DELETE FROM test_table_table_registry';
		$queries[] = 'DELETE FROM test_table_field_registry';
		$queries[] = 'INSERT INTO test_table_table_registry (id, name, label, updated_at) 
				VALUES (1, "MyTable", "My Table", "2016-01-01")';
		$queries[] = 'INSERT INTO test_table_field_registry (id, name, label, options, updated_at, table_id, fieldtype) 
				VALUES (1, "MyField", "My Field", "", "2016-01-01", 1, "string")';
		
		foreach($queries as $qry) {
			$stmt = $this->conn->prepare($qry);
			$stmt->execute();
		}
		
	}
	
	private function seed() {
		$queries = array();
		$queries[] = 'DELETE FROM test_table_MyTable';
		$queries[] = 'INSERT INTO test_table_MyTable (id, MyField) values (1, "first")';
		$queries[] = 'INSERT INTO test_table_MyTable (id, MyField) values (2, "second")';
		foreach($queries as $qry) {
			$stmt = $this->conn->prepare($qry);
			$stmt->execute();
		}
	}
	
	public function testGetColumnNames() {
		$names = array('id', 'MyField');
		$this->assertEquals($names, $this->table->getColumnNames());
	}
	
	public function testGetTableName() {
		$this->assertEquals('test_table_MyTable', $this->table->getTableName());
	}
	
	public function testSelect() {
		$objects = $this->table->select();
		$this->assertEquals(2, count($objects));
	}

	public function testSelectWithFields() {
		$conditions = array('select'=>array('id'));
		$objects = $this->table->select($conditions);
		$object = $objects[0];
		$this->assertNotNull($object->getId());
	}
	
	public function testSelectWithWhere() {
		$conditions = array('where'=>array(array('field'=>'MyField', 'operator'=>'=', 'value'=>'?')), 'params'=>array('first'));
		$objects = $this->table->select($conditions);
		$this->assertEquals(1, count($objects));
	}
	
	public function testSelectWithRange() {
		
		$conditions = array('maxResults'=>1);
		$objects = $this->table->select($conditions);
		$this->assertEquals(1, count($objects));
		
		$conditions = array('maxResults'=>3);
		$objects = $this->table->select($conditions);
		$this->assertEquals(2, count($objects));
		
		$conditions = array('firstResult'=>0);
		$objects = $this->table->select($conditions);
		$this->assertEquals(2, count($objects));
		
		$conditions = array('firstResult'=>1);
		$objects = $this->table->select($conditions);
		$this->assertEquals(1, count($objects));
		
		$conditions = array('firstResult'=>2);
		$objects = $this->table->select($conditions);
		$this->assertEquals(0, count($objects));
		
	}
	
	public function testSave() {
		
		$rows = $this->table->select();
		$row = $rows[0];
		$data = $row->getData();
		$data['MyField'] = $data['MyField'] . ' * changed';
		$row->setData($data);
		$saved = $this->table->save($row);
		$this->assertNotNull($saved);
		
		$conditions = array('where'=>array(array('field'=>'MyField', 'operator'=>' LIKE ', 'value'=>'?')), 'params'=>array('%* changed%'));
		$rows = $this->table->select($conditions);
		$this->assertEquals(1, count($rows));
		
	}
		
	public function testDelete() {
		$rows = $this->table->select();
		$row = $rows[0];
		$deleted = $this->table->delete($row);
		$this->assertTrue($deleted);
		
		$rows = $this->table->select();
		$this->assertEquals(1, count($rows));
		
	}
	
}
?>	