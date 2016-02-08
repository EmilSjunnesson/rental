<?php
/**
 * Config-file for Hera. Change settings here to affect installation.
 *
 */
 
/**
 * Set the error reporting.
 *
 */
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors 
ini_set('output_buffering', 0);   // Do not buffer outputs, write directly
 
 
/**
 * Define Hera paths.
 *
 */
define('HERA_INSTALL_PATH', __DIR__ . '/..');
define('HERA_THEME_PATH', HERA_INSTALL_PATH . '/theme/render.php');
 
 
/**
 * Include bootstrapping functions.
 *
 */
include(HERA_INSTALL_PATH . '/src/bootstrap.php');
 
 
/**
 * Start the session.
 *
 */
session_name(preg_replace('/[^a-z\d]/i', '', __DIR__));
session_start();
 
 
/**
 * Create the Hera variable.
 *
 */
$hera = array();
 
 
/**
 * Site wide settings.
 *
 */
$hera['lang']         = 'sv';
$hera['title_append'] = ' | RM';


/**
 * Theme related settings.
 *
 */
$hera['stylesheets'] = array('css/style.css');
$hera['favicon']    = 'favicon.ico';

$hera['header'] = <<<EOD
<img class='sitelogo' src='img/logo.png' alt='oophp Logo'/>
<span class='sitetitle'>rental movies</span>
<span class='siteslogan'>Filmer du kan hålla i handen</span>
EOD;

$hera['footer'] = <<<EOD
<footer><span class='sitefooter'>Copyright &copy; RM rental movies (info@rm.se) | <a href='http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance'>Unicorn</a> | <a href='source.php'>Källkod</a></span></footer>
EOD;

/**
 * Settings for the database.
 *
 */
$hera['database']['dsn']            = 'mysql:host=blu-ray.student.bth.se;dbname=emsf14;';
$hera['database']['username']       = 'emsf14';
$hera['database']['password']       = 'p,$75}jH';
$hera['database']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'");


/**
 * The navbar
 *
 */
$hera['navbar'] = array(
  'class' => 'nb-plain',
  'items' => array(
    'hem'         => array('text'=>'Hem',         'url'=>'home.php',          'title' => 'Startsidan'),
    'filmer' => array('text'=>'Filmer', 'url'=>'movies.php', 'title' => 'Hyr-filmer'),
    'nyheter' => array('text'=>'Nyheter', 'url'=>'news.php', 'title' => 'Senaste nytt'),
  	'tarning' => array('text'=>'Tävling', 'url'=>'dice.php', 'title' => 'Tävlingen: tärningspelet 100'),
    'om' => array('text'=>'Om oss', 'url'=>'about.php', 'title' => 'Information om oss'),
    'login' => CUser::userNav(),
  ),
  'callback' => function($url) {
    if(basename($_SERVER['SCRIPT_FILENAME']) == $url) {
      return true;
    }
  }
);
;
 
/**
 * Settings for JavaScript.
 *
 */
$hera['modernizr'] = 'js/modernizr.js';
$hera['scroll'] = 'js/scroll.js';
$hera['jquery'] = '//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js';
//$hera['jquery'] = null; // To disable jQuery
$hera['javascript_include'] = array();
//$hera['javascript_include'] = array('js/main.js'); // To add extra javascript files


/**
 * Google analytics.
 *
 */
$hera['google_analytics'] = 'UA-22093351-1'; // Set to null to disable google analytics
