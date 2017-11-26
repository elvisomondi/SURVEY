<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

return array(
    'components' => array(
        'db' => array(
            'connectionString' => 'mysql:host=localhost;port=8080;dbname=test;',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'tablePrefix' => 'lime_',
        ),

		

        'urlManager' => array(
			'urlFormat' => 'get',
			'rules' => array(
				// You can add your own rules here
			),
            'showScriptName' => true,
        ),

	),
    // Use the following config variable to set modified optional settings copied from config-defaults.php
    'config'=>array(
        'debug'=>1,
		'debugsql'=>0, // Set this to 1 to enanble sql logging, only active when debug = 2
		
    )
);
/* End of file config.php */
/* Location: ./application/config/config.php */