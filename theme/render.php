<?php
/**
 * Render content to theme.
 *
 */
 
// Extract the data array to variables for easier acces in the template files.
extract($hera);

// Include the template functions.
include(__DIR__ . '/functions.php');

// Include the template file.
include(__DIR__ . '/index.tpl.php');
