<?php
/* 
실 오픈시 발급 
도메인 + 사이트유니크키
*/


class Lisence
{
	static $keys = [];

	static function add($newLisence = '')
	{
		if (isset($newLisence) && !empty($newLisence)) {
			self::$keys[] = $newLisence;
		}
	}

	static function getAll()
	{
		return self::$keys;
	}
}

Lisence::add('d3dcfdecd0510387cb3e282901e4c323cfeae482f4a6029fa4b823eb40401e8f'); // 유니크키 개발
Lisence::add('937e8d5fbb48bd4949536cd65b8d35c426b80d2f830c5c308e2cdec422ae2244'); // 유니크키 실서버
Lisence::add('23240606effb1a7f566d47ec5fc2cf504dd0035fd31aacfdd9f96fe093b20945'); // 유니크키 개발

Lisence::add('3583c5a9a7c033f5f8446cddb5d60281129b6b48152d66f99ec3a08bb258bd25'); // 유니크키 개발
Lisence::add('64100f7481113570bdfea0232a4d519719c6e92ab7592ec31c3664fa9705a42d'); // 유니크키 개발

Lisence::add('a3e39226528afb78600631479253bdd36d9ff1e345d59a7e524b588963af4bd7'); // 유니크키 개발
Lisence::add('6022578cd9e395cc588b85e5aac568604a23e8c6f8b07bb6679e7657c9cf88be'); // 유니크키 개발
Lisence::add('72f50c09000d8ba201aa786ccd79a731717e2d048c0ef9a72c27fd215678c577'); // localhost:8618
Lisence::add('b31ae8deb216d906cb641faede58fca14366077ef9c1db2d21746acb275573f3'); // localhost
Lisence::add('5bad4d9c3534e6220ee416f6368cbb1959e5ed8944e3c632b988b1cc7abe7c80'); // 127.0.0.1:8618
Lisence::add('c47d0ed9321bb8f33511dca030c8bc1e3756e071c8e9d39877e7a4dddac989e7'); // gate.local:8618
Lisence::add('9b2f8db4b7a42e6c647104968af577673a587739c16083459f52510151e99863'); // gate.local

