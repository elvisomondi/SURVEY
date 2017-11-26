<?php

 // Set the correct temp path for PclZip
if (!defined('PCLZIP_TEMPORARY_DIR')) {
    define( 'PCLZIP_TEMPORARY_DIR', Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR );
  }

# include PclZip class
require_once(APPPATH.'/third_party/pclzip/pclzip.lib.php');
