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
  $output = "Du är inloggad som: {$user->GetAcronym()} ({$user->GetName()}) / <a href='logout.php'>Logga ut?</a>";
}
else {
  $output = "Du är INTE inloggad.";
}

// Check if user and password is okey
if(isset($_POST['Login'])) {         
  $user->Login($_POST['acronym'], $_POST['password']);
  header('Location: login.php');
}

// Do it and store it all in variables in the Hera container.
$hera['title'] = "Login";
  
$hera['main'] = <<<EOD
<h1>{$hera['title']}</h1>
<form method=post>
<fieldset>
<legend>Logga in</legend>
<p><em>Du kan logga in med emsf14:emsf14 för att logga in som vanlig användare eller admin:admin för att logga in som administratör.</em></p>
<div class='box'>Inte medlem än? Klicka <a href='register.php'>här</a> för att registrera dig.</div>
<p><label>Användare:<br><input type=text name='acronym' value=''></label></p>
<p><label>Lösenord:<br><input type=password name='password' value=''></label></p>
<p><input type='submit' value='Logga in' name='Login'></p>
<p>{$output}</p>
</fieldset>
</form>
EOD;
  
//Finally, leave it all to the rendering phase of Hera.
include(HERA_THEME_PATH);
  
