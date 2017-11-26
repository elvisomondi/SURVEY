<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    /**
    * TCPDF configuration file
    */

    $tcpdf['base_directory'] = APPPATH.'third_party'.DIRECTORY_SEPARATOR.'tcpdf'.DIRECTORY_SEPARATOR;


    $tcpdf['base_url'] = 'dummy'; // If empty and debug === 2, "empty needle" occurs


    $tcpdf['fonts_directory'] = $tcpdf['base_directory'].'fonts'.DIRECTORY_SEPARATOR;


    $tcpdf['enable_disk_cache'] = FALSE;
    $tcpdf['cache_directory'] = Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR;


    $tcpdf['image_directory'] = Yii::app()->getConfig('rootdir').DIRECTORY_SEPARATOR.'styles'.DIRECTORY_SEPARATOR.Yii::app()->getConfig('admintheme').DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;


    $tcpdf['blank_image'] = $tcpdf['image_directory'].'_blank.png';



    $tcpdf['page_format'] = 'A4';

    $tcpdf['page_orientation'] = 'P';


    $tcpdf['page_unit'] = 'mm';


    $tcpdf['page_break_auto'] = TRUE;


    $tcpdf['unicode'] = TRUE;
    $tcpdf['encoding'] = 'UTF-8';


    $tcpdf['creator'] = 'TCPDF';
    $tcpdf['author'] = 'TCPDF';

    $tcpdf['margin_top']    = 27;
    $tcpdf['margin_bottom'] = 27;
    $tcpdf['margin_left']   = 15;
    $tcpdf['margin_right']  = 15;

    $tcpdf['page_font'] = 'freesans';
    $tcpdf['page_font_size'] = 9;
    $tcpdf['data_font'] = 'freesans';
    $tcpdf['data_font_size'] = 8;
    $tcpdf['mono_font'] = 'freemono';

    $tcpdf['small_font_ratio'] = 2/3;

    $tcpdf['header_on'] = TRUE;
    $tcpdf['header_font'] = $tcpdf['page_font'];
    $tcpdf['header_font_size'] = 10;
    $tcpdf['header_margin'] = 5;
    //$tcpdf['header_title'] = 'TCPDF Example';
    //$tcpdf['header_string'] = "by Nicola Asuni - Tecnick.com\nwww.tcpdf.org";
    $tcpdf['header_title'] = '';
    $tcpdf['header_string'] = "";
    //$tcpdf['header_logo'] = 'tcpdf_logo.jpg';
    $tcpdf['header_logo'] = '';
    $tcpdf['header_logo_width'] = 30;


    $tcpdf['footer_on'] = TRUE;
    $tcpdf['footer_font'] = $tcpdf['page_font'];
    $tcpdf['footer_font_size'] = 8;
    $tcpdf['footer_margin'] = 10;

    $tcpdf['image_scale'] = 4;


    $tcpdf['cell_height_ratio'] = 1.25;
    $tcpdf['cell_padding'] = 0;

    return $tcpdf;

