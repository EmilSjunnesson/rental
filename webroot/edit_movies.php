<?php
/**
 * This is a Hera pagecontroller.
 *
 */
// Include the essential config-file which also creates the $anax variable with its defaults.
include(__DIR__ . '/config.php');

$hera['javascript_include'][] = 'js/checkbox.js';

// add style for forms
$hera['stylesheets'][] = 'css/forms.css';
$hera['stylesheets'][] = 'css/tables.css';

$hera['title'] = "Hantera filmer";

// Database
$db = new CDatabase($hera['database']);

//login-info
$user = new CUser($db);

//edit class
$edit = new CEditMovies($db);

if($user->IsAuthenticated()) {
  if(isset($_GET['new'])) {  
    $hera['main'] = $edit->printAndPostAdd();     
  } elseif(isset($_GET['delete'])) {
    $edit->getEntryByID($_GET['delete']);
    $hera['main'] = $edit->printAndPostDelete();   
  } elseif(isset($_GET['id'])) {
    $edit->getEntryByID($_GET['id']);
    $hera['main'] = $edit->printAndPostUpdate();
  } else {
    if(isset($_GET['publish'])) {
      $edit->publish($_GET['publish']);
    } 
$list = $edit->getAllAsList();
$new = $user->IsAdmin() ? "<p><a class='as-button' href='?new'>Lägg till en ny film</a></p>" : "<p>För att skapa, radera och ändra filmer behöver man vara inloggad som admin.</p>" ;
$hera['main'] = <<<EOD
<h1>{$hera['title']}</h1>
{$new}
<p>Här är en lista på allt innehåll i film-databasen</p>
{$list}
EOD;
  }
} else {
  $hera['main'] = "<h1>{$hera['title']}</h1>För att visa innehållet behöver du <a href='login.php'>logga in</a>.";      
}
//Finally, leave it all to the rendering phase of Hera.
include(HERA_THEME_PATH);
