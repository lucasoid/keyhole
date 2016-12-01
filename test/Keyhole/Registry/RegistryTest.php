<?php

class RegistryTest extends PHPUnit_Framework_TestCase {
	
	private $conn;
	private $registry;
	
	public function setUp() {
		
		$config = new \Doctrine\DBAL\Configuration();
			
		$connectionParams = array(
			'url'=> 'sqlite:///test/test.db'
		);

		$this->conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
		
		$this->registry = new \Keyhole\Registry\Registry($this->conn, 'test_');
		
	}
	
	public function testNormalizeName() {
		
		$name = 'MyTableWithCaps';
		$this->assertEquals('MyTableWithCaps', $this->registry->normalizeName($name));
		
		$name = 'My Table With Spaces';
		$this->assertEquals('MyTableWithSpaces', $this->registry->normalizeName($name));
		
		$name = 'My Table with Speci@l Ch@rs!!';
		$this->assertEquals('MyTablewithSpecilChrs', $this->registry->normalizeName($name));
		
		$name = 'My Table with Numb3rs';
		$this->assertEquals('MyTablewithNumb3rs', $this->registry->normalizeName($name));
		
		$name = 'My Table with Unicode Char ' . json_decode('"\u01A9\"');
		$this->assertEquals('MyTablewithUnicodeChar', $this->registry->normalizeName($name));
		
	}
		
}
?>	