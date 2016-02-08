<?php
/**
 * This is a Hera pagecontroller.
 *
 */
// Include the essential config-file which also creates the $anax variable with its defaults.
include(__DIR__ . '/config.php');

$hera['stylesheets'][] = 'css/forms.css';

// Connect to a MySQL database using PHP PDO
$db = new CDatabase($hera['database']);
$user = new CUser($db);

if($user->IsAuthenticated()) {
  $output = "Du är inloggad som: {$user->GetAcronym()} ({$user->GetName()})";
}
else {
  $output = "Du är INTE inloggad. / <a href='login.php'>Logga in?</a>";
}

// Logout the user
if(isset($_POST['logout'])) {
  $user->Logout();
  header('Location: logout.php');
}

// Do it and store it all in variables in the Hera container.
$hera['title'] = "Logout";
  
$hera['main'] = <<<EOD
<h1>{$hera['title']}</h1>
<form method=post>
<fieldset>
<legend>Logga ut</legend>
<p><input type='submit' value='Logga ut' name='logout'></p>
<p>{$output}</p>
</fieldset>
</form>
EOD;
  
//Finally, leave it all to the rendering phase of Hera.
include(HERA_THEME_PATH);
  
