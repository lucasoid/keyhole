<?php

class RegistryMapperTest extends PHPUnit_Framework_TestCase {
	
	private $conn;
	private $mapper;
	
	public function setUp() {
		
		include_once('RegistryMapperMock.php');
		include_once('Mock.php');
		
		$config = new \Doctrine\DBAL\Configuration();
			
		$connectionParams = array(
			'url'=> 'sqlite:///test/test.db'
		);

		$this->conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
		
		$this->mapper = new \Keyhole\Registry\RegistryMapperMock($this->conn, 'test_');
		
		$setup = new \Keyhole\Registry\Setup($this->conn, 'test_', $this->mapper, null);
		$setup->doMigrations();
		
		$this->deleteSeeds();
		
		$this->seed(1, 'field1', '2016-01-01');
		$this->seed(2, 'field2', '2016-01-02');
		
	}
	
	private function deleteSeeds() {
		$qb = $this->conn->createQueryBuilder();
		$qb->delete('test_mock_registry');
		$stmt = $qb->execute();
	}
	
	private function seed($id, $name, $updated_at) {
		$qb = $this->conn->createQueryBuilder();
		$qb
			->insert('test_mock_registry')
			->setValue('id', '?')
			->setValue('name', '?')
			->setValue('updated_at', '?')
			->setParameter(0, $id)
			->setParameter(1, $name)
			->setParameter(2, $updated_at)
		;
		$stmt = $qb->execute();
	}
	
	public function testGetColumnNamesInvalid() {
		$this->mapper->overrideColumnDefinitions(array('single', 'level', 'array'));
		$this->assertEmpty($this->mapper->getColumnNames());
		
		$this->mapper->overrideColumnDefinitions(array('multilevel'=>'array', 'with'=>'badparams'));
		$this->assertEmpty($this->mapper->getColumnNames());
	}
	
	public function testGetColumnNamesValid() {
		$names = array('id', 'name', 'updated_at');
		$this->assertEquals($names, $this->mapper->getColumnNames());
	}
	
	public function testGetColumnDefinitions() {
		
		//getting a mock to access the spy method
		$mock = $this->getMock('\Keyhole\Registry\RegistryMapperMock', array('setColumnDefinitions'), array($this->conn, 'test_'));
		$mock->overrideColumnDefinitions(null);
		$mock->expects($spy = $this->any())->method('setColumnDefinitions');
		$cols = $mock->getColumnDefinitions();
		$invoked = $spy->getInvocations();
		$this->assertEquals(1, count($invoked));
		
	}
	
	public function testFindById() {
		$obj = $this->mapper->findById(1);
		$this->assertEquals($obj->getName(), 'field1');
		
		$obj = $this->mapper->findById(22);
		$this->assertNull($obj);
		
	}
	
	public function testCamelCaseConversion() {
		$obj = $this->mapper->findById(1);
		$this->assertNotNull($obj->getUpdatedAt());
	}
	
	
	public function testSelectAll() {
		$objects = $this->mapper->selectAll();
		$this->assertEquals(2, count($objects));
	}

	public function testSelectAllWithFields() {
		$conditions = array('select'=>array('name'));
		$objects = $this->mapper->selectAll($conditions);
		$object = $objects[0];
		$this->assertNotNull($object->getName());
		$this->assertNull($object->getUpdatedAt());
	}
	
	public function testSelectAllWithWhere() {
		$conditions = array('where'=>array(array('field'=>'name', 'operator'=>'=', 'value'=>'?')), 'params'=>array('field1'));
		$objects = $this->mapper->selectAll($conditions);
		$this->assertEquals(1, count($objects));
	}
	
	public function testSelectAllWithJoin() {
		$sql = array();
		$sql[] = 'CREATE TABLE IF NOT EXISTS test_mock_join (fkid INTEGER)';
		$sql[] = 'DELETE FROM test_mock_join';
		$sql[] = 'INSERT INTO test_mock_join (fkid) VALUES (1)';
		foreach($sql as $qry) {
			$stmt = $this->conn->prepare($qry);
			$stmt->execute();
		}
		
		$conditions = array(
			'join'=>array(array('table'=>'test_mock_join', 'on'=>'test_mock_join.fkid = test_mock_registry.id')),
			'where'=>array(array('field'=>'fkid', 'operator'=>'IS NOT NULL', 'value'=>''))
		);
		
		$objects = $this->mapper->selectAll($conditions);
		$this->assertEquals(1, count($objects));
	}
	
	public function testSelectAllWithRange() {
		$conditions = array('maxResults'=>1);
		$objects = $this->mapper->selectAll($conditions);
		$this->assertEquals(1, count($objects));
		
		$conditions = array('maxResults'=>3);
		$objects = $this->mapper->selectAll($conditions);
		$this->assertEquals(2, count($objects));
		
		$conditions = array('firstResult'=>0);
		$objects = $this->mapper->selectAll($conditions);
		$this->assertEquals(2, count($objects));
		
		$conditions = array('firstResult'=>1);
		$objects = $this->mapper->selectAll($conditions);
		$this->assertEquals(1, count($objects));
		
		$conditions = array('firstResult'=>2);
		$objects = $this->mapper->selectAll($conditions);
		$this->assertEquals(0, count($objects));
		
		
	}
	
	public function testSave() {
		$mock = $this->mapper->findById(1);
		$mock->observe('name', 'updatedName');
		$mock = $this->mapper->save($mock);
		
		$mock = $this->mapper->findById(1);
		$this->assertEquals('updatedName', $mock->getName());
	}
	
	/*
	//Doctrine has a bug with sqlite autoincrement right now.
	public function testInsert() {
		$mock = new \Keyhole\Registry\Mock;
		$mock->observe('name', 'newName');
		$mock = $this->mapper->save($mock);
		$this->assertNotNull($mock);
		$this->assertEquals(3, count($this->mapper->selectAll()));
	}
	*/
	
	public function testDelete() {
		$mock = $this->mapper->findById(1);
		$deleted = $this->mapper->delete($mock);
		$this->assertTrue($deleted);
		
		$list = $this->mapper->selectAll();
		$this->assertEquals(1, count($list));
	}
	
	
	
}
?>	