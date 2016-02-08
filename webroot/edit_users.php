<?php
/**
 * This is a Hera pagecontroller.
 *
 */
// Include the essential config-file which also creates the $anax variable with its defaults.
include(__DIR__ . '/config.php');

$hera['javascript_include'][] = 'js/register.js';

// add style for forms
$hera['stylesheets'][] = 'css/forms.css';
$hera['stylesheets'][] = 'css/tables.css';

$hera['title'] = "Hantera användare";

// Database
$db = new CDatabase($hera['database']);

//user class
$user = new CUser($db);

if($user->IsAuthenticated()) {
  if(isset($_GET['new'])) {  
    $hera['main'] = $user->printAndPostAdd();     
  } elseif(isset($_GET['delete'])) {
    $user->getEntryByID($_GET['delete']);
    $user->deleteEntry();   
  } elseif(isset($_GET['id'])) {
  	$user->getEntryByID($_GET['id']);
  	$hera['main'] = $user->printAndPostUpdate();
  } else {
$list = $user->getUserList();
$new = $user->IsAdmin() ? "<p><a class='as-button' href='?new'>Lägg till en ny användare</a></p>" : "<p>För att skapa, radera och ändra användare behöver man vara inloggad som admin.</p>" ;
$hera['main'] = <<<EOD
<h1>{$hera['title']}</h1>
{$new}
<p>Här är en lista på allt innehåll i användar-databasen</p>
{$list}
EOD;
  }
} else {
  $hera['main'] = "<h1>{$hera['title']}</h1>För att visa innehållet behöver du <a href='login.php'>logga in</a>.";      
}
//Finally, leave it all to the rendering phase of Hera.
include(HERA_THEME_PATH);
