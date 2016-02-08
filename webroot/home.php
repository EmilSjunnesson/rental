<?php
/**
 * This is a Hera pagecontroller.
 *
 */
// Include the essential config-file which also creates the $anax variable with its defaults.
include(__DIR__ . '/config.php');

$hera['stylesheets'][] = 'css/home.css';
$hera['stylesheets'][] = 'css/blog.css';

$db = new CDatabase($hera['database']);


$movies = CMovie::threeLatest($db);

$blog = CBlog::homeNews($db);

$genres = CMovie::getGenres($db, null, true);
 
// Do it and store it all in variables in the Hera container.
$hera['title'] = "Hem";
  
$hera['main'] = <<<EOD
<div class='clearfix'>
<h1>Senaste filmerna</h1>
<div class='home-movies'>
{$movies}
</div>
<div class='home-blog'>
<h1>Senaste nytt</h1>
{$blog}
</div>
<div class='home-text'>
<h1>Välkommen till RM rental movies</h1>
<p>Vad kul att du hittat hit! Vi här på RM rental movies är specialister på filmuthyrning. I en tid där streaming 
dominerar marknaden, finns det ett behov av att hålla en film i handen. När man håller en film i handen får man 
en nostalgisk känsla inte helt olik känslan av att hålla en älskades hand för första gången. När du hyr film 
från oss får du hem den direkt i brevlådan. Och du får behålla den i en hel månad!</p>
<p>Så vad väntar du på, <a href='register.php'>registrera</a> dig redan nu!</p>
</div>
<div class='home-genres'>
<h1>Tillgängliga genrer</h1>
{$genres}
</div>
</div>
EOD;
  
//Finally, leave it all to the rendering phase of Hera.
include(HERA_THEME_PATH);
  
