<?php
/**
 * This is a Hera pagecontroller.
 *
 */
// Include the essential config-file which also creates the $anax variable with its defaults.
include(__DIR__ . '/config.php');

// add style for database tabels
$hera['stylesheets'][] = 'css/tables.css';
$hera['stylesheets'][] = 'css/gallery.css';
$hera['stylesheets'][] = 'css/movies.css';
$hera['stylesheets'][] = 'css/forms.css';
$hera['stylesheets'][] = 'css/breadcrumb.css';

// Connect to a MySQL database using PHP PDO
$db = new CDatabase($hera['database']);
$movie = new CMovie($db);

// Do it and store it all in variables in the Hera container.
$hera['title'] = "Filmer";

$hera['main'] = $movie->printMovies();

// Finally, leave it all to the rendering phase of Hera.
include(HERA_THEME_PATH);
