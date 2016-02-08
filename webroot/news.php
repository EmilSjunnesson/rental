<?php
/**
 * This is a Hera pagecontroller.
 *
 */
// Include the essential config-file which also creates the $anax variable with its defaults.
include(__DIR__ . '/config.php');

// add style for database tabels
$hera['stylesheets'][] = 'css/blog.css';
$hera['stylesheets'][] = 'css/breadcrumb.css';

$slug = isset($_GET['slug']) ? $_GET['slug'] : null;
$cat = isset($_GET['cat']) ? $_GET['cat'] : null;

// Database
$db = new CDatabase($hera['database']);

// Blog handler
$blog = new CBlog($db);
$blog->getPostsFromSlug($slug, $cat);

// Do it and store it all in variables in the Hera container.
$hera['title'] = "Nyheter";
$hera['main'] = null;

if($blog->postsExists()) {
  $hera['main'] = $blog->printPosts();
} else if($slug) {
  $hera['main'] = "<h1>{$hera['title']}</h1><p>Det fanns inte en sådan bloggpost.</p><p><a href='blog.php'>Visa alla</a></p>";
} else {
  $hera['main'] = "<h1>{$hera['title']}</h1>Det fanns inga bloggposter.</p>";
}
  
//Finally, leave it all to the rendering phase of Hera.
include(HERA_THEME_PATH);

