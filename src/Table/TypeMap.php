<?php

namespace Keyhole\Table;

class TypeMap {
	
	public static function typeIsSupported($type) {
		$map = \Doctrine\DBAL\Types\Type::getTypesMap();
		foreach($map as $key=>$class) {
			if($key == $type) {
				return true;
			}
		}
		return false;
	}
		
}
	
?>