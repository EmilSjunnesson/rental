<?php
/**
 * This is a Hera pagecontroller.
 *
 */
// Include the essential config-file which also creates the $anax variable with its defaults.
include(__DIR__ . '/config.php');

$hera['stylesheets'][] = 'css/forms.css';

$db = new CDatabase($hera['database']);
$user = new CUser($db);

//metod som returerar användar object $profile 

//flytta allt till CUser
// $html = $user->printProfile();

if($user->IsAuthenticated()) {
	if(isset($_GET['id'])) {
	  $user->getEntryByID($_GET['id']);
  	  $html = $user->printAndPostUpdate();
	} else {
	  $html = $user->printProfile($user->GetAcronym());
	}
} else {
	$html = "För att visa din profil behöver du <a href='login.php'>logga in</a>.";
}

// Do it and store it all in variables in the Hera container.
$hera['title'] = "Min sida";
  
$hera['main'] = <<<EOD
<h1>{$hera['title']}</h1>
{$html}
EOD;
  
//Finally, leave it all to the rendering phase of Hera.
include(HERA_THEME_PATH);
  
