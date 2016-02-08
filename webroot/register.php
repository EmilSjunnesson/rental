<?php
/**
 * This is a Hera pagecontroller.
 *
 */
// Include the essential config-file which also creates the $anax variable with its defaults.
include(__DIR__ . '/config.php');

$hera['javascript_include'][] = 'js/register.js';

$hera['stylesheets'][] = 'css/forms.css';

$db = new CDatabase($hera['database']);
$user = new CUser($db);

// Check if user and password is okey
if(isset($_POST['send'])) {
	if($user->Register($_POST['acronym'], $_POST['name'], $_POST['password'], $_POST['confirm_password'])) {
	  header('Location: register.php?succes');
	} else {
	  header('Location: register.php?fail&error=' . $user->getError());
	}
}

$output = null;
if(isset($_GET['succes'])) {
  $output = "<br><output class='success'>Din profil har skapats! <a href='login.php'>Logga in?</a></output><br><br>";	
}
 
if(isset($_GET['fail'])) {
  $output = "<br><output class='error'>Skapandet av profil misslyckades{$_GET['error']}</output><br><br>";	
}

// Do it and store it all in variables in the Hera container.
$hera['title'] = "Bli medlem";
  
$hera['main'] = <<<EOD
<h1>Bli medlem</h1>
<form method=post>
<fieldset>
<legend>Registrera dig</legend>
<p><label>Användarnamn:<br><input type=text name='acronym' autocomplete='off' required></label></p>
<p><label>Namn:<br><input type=text name='name' autocomplete='off' required></label></p>
<p><label>Lösenord:<br><input type=password name='password' id='password' required></label></p>
<p><label>Bekräfta lösenord:<br><input type=password name='confirm_password' id='confirm_password' required> <span id='message'></span></label></p>
<p><input type='checkbox' name='accept' required> Jag accepterar villkoren</p>
<p><input type='submit' value='Skicka' name='send'/> <input type='reset' value='Återställ'/></p>
{$output}		
</fieldset>
</form>
EOD;
  
//Finally, leave it all to the rendering phase of Hera.
include(HERA_THEME_PATH);
  
