<?php 

namespace Keyhole\Registry;

class RegistryMapperMock extends RegistryMapper {
	
	/**
	 * 
	 * @var string
	 */
	const TABLE_NAME = 'mock_registry';
	
	public function setMappedClass() {
		$this->mappedClass = '\Keyhole\Registry\Mock';
	}
	
	public function setTableName() {
		$this->fullTableName = $this->tablePrefix . self::TABLE_NAME;
	}
	
	public function setColumnDefinitions() {
		
		$tableMapper = new RegisteredTableMapper($this->conn, $this->tablePrefix);
		$tableRegistryName = $tableMapper->getTableName();
		
		$this->columnDefinitions = array(
			array('name'=>'id', 'type'=>'integer', 'options'=>array('options'=>array('autoincrement'=>true), 'primarykey'=>true)),
			array('name'=>'name', 'type'=>'string', 'options'=>array()),
			array('name'=>'updated_at', 'type'=>'datetimetz', 'options'=>array()),
		);
		
	}

	public function setMap() {
		$this->map = array(
			array('column'=>'id', 'property'=>'id'),
			array('column'=>'name', 'property'=>'name'),
			array('column'=>'updated_at', 'property'=>'updatedAt'),
			array('column'=>'created_by', 'property'=>'createdBy')
		);
	}
	
	public function overrideColumnDefinitions($x) {
		$this->columnDefinitions = $x;
	}
	
}