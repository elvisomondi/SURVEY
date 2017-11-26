<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

return array(
	'components' => array(
		'db' => array(
			'connectionString' => 'mysql:host=localhost;port=3306;dbname=survey;',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8mb4',
			'tablePrefix' => '',
		),
		
		
		
		'urlManager' => array(
			'urlFormat' => 'path',
			'rules' => array(
				// You can add your own rules here
			),
			'showScriptName' => true,
		),
	
	),
	'config'=>array(
		'debug'=>1,
		'debugsql'=>0, 
	)
);
/* End of file config.php */
/* Location: ./application/config/config.php */