<?php
/* 
실 오픈시 발급 
도메인 + 사이트유니크키
*/


class Lisence {
	static $keys = []; 

	static function add($newLisence = ''){
		if(isset($newLisence) && !empty($newLisence)) {
			self::$keys[] = $newLisence;
		}
	}
	
	static function getAll(){
		return self::$keys; 
	}
}

Lisence::add('937e8d5fbb48bd4949536cd65b8d35c426b80d2f830c5c308e2cdec422ae2244');
Lisence::add('5766bb5e3fa320bdac9ecbb87a7816f1d24fc1b542c27722d265d963cd8f42cf');

