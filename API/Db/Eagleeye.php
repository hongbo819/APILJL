<?php
/**
* eagleeye DB
*/
class API_Db_Eagleeye extends API_Db_Abstract_Pdo
{
	protected $servers   = array(
		'username' => 'puser',
		'password' => '',
		'master' => array(
			'host'     => '127.0.0.1',
			'database' => 'ljl_eagleeye',
		 ),
		 'slave' => array(
			'host'     => '127.0.0.1',
			'database' => 'ljl_eagleeye',
		 ),
	);    	
}
