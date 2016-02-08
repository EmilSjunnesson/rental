<?php 
/**
 * This is a Anax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $anax variable with its defaults.
include(__DIR__.'/config.php'); 

$hera['stylesheets'][] = 'css/forms.css';

$error = null;
if (isset($_GET['error'])) {
  $error = $_GET['error'];
}

// Do it and store it all in variables in the Anax container.
$hera['title'] = "404";
$hera['main'] = "<h1>This is a Hera 404</h1><p>The page you where looking for does not exsist.</p><p>Go back to the <a href='home.php'>home</a> page?</p><br>" . ($error ? "<output class='error'>{$error}</output>" : null);
 
// Send the 404 header 
header("HTTP/1.0 404 Not Found");
 
 
// Finally, leave it all to the rendering phase of Anax.
include(HERA_THEME_PATH);
