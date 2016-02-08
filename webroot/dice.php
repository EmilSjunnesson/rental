<?php
/**
 * This is a Hera pagecontroller.
 *
 */
// Include the essential config-file which also creates the $anax variable with its defaults.
include(__DIR__ . '/config.php');

// add style for cssource
$hera['stylesheets'][] = 'css/dice.css';

$db = new CDatabase($hera['database']);
$game = new CDiceDisplay($_GET, $db);

// Do it and store it all in variables in the Hera container.
$hera['title'] = "Tävling";

$hera['main'] = <<<EOD
<h1>Tävling: tärninsspelet 100</h1>
{$game->PrintHTML()}
EOD;
  
//Finally, leave it all to the rendering phase of Hera.
include(HERA_THEME_PATH);
  
