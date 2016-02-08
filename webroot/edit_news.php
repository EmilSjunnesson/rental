<?php
/**
 * This is a Hera pagecontroller.
 *
 */
// Include the essential config-file which also creates the $anax variable with its defaults.
include(__DIR__ . '/config.php');

// add style for forms
$hera['stylesheets'][] = 'css/forms.css';

$hera['title'] = "Hantera nyheter";

// Database
$db = new CDatabase($hera['database']);

//login-info
$user = new CUser($db);

//content handler
$content = new CContent($db);

if($user->IsAuthenticated()) {
  if(isset($_GET['new'])) {  
    $hera['main'] = $content->printAndPostAdd();     
  } elseif(isset($_GET['delete'])) {
    $content->getEntryById($_GET['delete']);
    $hera['main'] = $content->printAndPostDelete();     
  } elseif(isset($_GET['id'])) {
    $content->getEntryById($_GET['id']);
    $hera['main'] = $content->printAndPostUpdate();
  } else {
    if(isset($_GET['publish'])) {
      $content->publish($_GET['publish']); 
    }
$list = $content->getAllAsList();  
$new = $user->IsAdmin() ? "<a href='?new' class='as-button'>Skapa ett nytt inlägg</a>" : "<p>För att skapa, radera och ändra inlägg behöver man vara inloggad som admin.</p>" ;
$hera['main'] = <<<EOD
<h1>{$hera['title']}</h1>
<p>Här är en lista på allt innehåll i nyhets-databasen</p>
{$list}
{$new}
EOD;
  }
} else {
  $hera['main'] = "<h1>{$hera['title']}</h1>För att visa innehållet behöver du <a href='login.php'>logga in</a>.";      
}
//Finally, leave it all to the rendering phase of Hera.
include(HERA_THEME_PATH);
