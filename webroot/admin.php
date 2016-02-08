<?php
/**
 * This is a Hera pagecontroller.
 *
 */
// Include the essential config-file which also creates the $anax variable with its defaults.
include(__DIR__ . '/config.php');

// Get user functions
$db = new CDatabase($hera['database']);
$user = new CUser($db);

if($user->IsAdmin()) {
  $html = "<p>Som admin har du tillgång till många funktioner som andra användare inte har.</p>\n";
  $html .= "<p><a class='as-button' href='edit_movies.php'>Hantera filmer</a> - Skapa, updatera eller radera filmer. Du kan även publicera och opublicera dem.</p>\n"; 
  $html .= "<p><a class='as-button' href='edit_news.php'>Hantera nyheter</a> - Skapa, updatera eller radera nyhets-inlägg. Du kan även publicera och opublicera dem.</p>\n";
  $html .= "<p><a class='as-button' href='edit_users.php'>Hantera användare</a> - Skapa, updatera eller radera användare. Befodra en vanlig användare till admin, samt återställa deras lösenord.</p>\n";
  $html .= "<br><p><a class='as-button' href='logout.php'>Logga ut</a></p>\n";
} else {
  $html = "För att komma åt adimn-funktionerna behöver du <a href='login.php'>logga in som admin</a>.";     
}

// Do it and store it all in variables in the Hera container.
$hera['title'] = "Admin";
  
$hera['main'] = <<<EOD
<h1>{$hera['title']}</h1>
{$html}
EOD;
  
//Finally, leave it all to the rendering phase of Hera.
include(HERA_THEME_PATH);
