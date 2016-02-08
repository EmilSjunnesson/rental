<?php
/**
 * This is a Hera pagecontroller.
 *
 */
// Include the essential config-file which also creates the $anax variable with its defaults.
include (__DIR__ . '/config.php');

// Do it and store it all in variables in the Hera container.
$hera ['title'] = "Om oss";

$hera ['main'] = <<<EOD
<h1>Om oss</h1>
<h2>Företaget</h2>
RM rental movies startades upp av ett kompisgäng i februari 2014. Vi såg ett behov av att kunna hyra filmer som 
du kan hålla i handen. Det var därför vi beslöt oss att starta företaget. Det har gått bra sen starten, och vi 
fortsätter växa så det knakar. Vi är just nu inne i en expanderingsfas och det känns otroligt spännande.
<h2>Våra tjänster</h2>
<p>Hos oss kan du hyra filmer som du får hemskickade direkt i brevlådan. Du kan behålla filmerna upp till en 
månad innan du behöver skicka tillbaka dem. Vi har ett stort utbud av både DVD och blu-ray skivor i många genrer. 
Har du svårt att bestämma dig? Då kan du vara lugn, vår filmdatabas innehåller en mängd information om våra filmer. 
Du kan bland annat se filmernas speltid, vem som regiserat dem och vilket språk filmerna har. Det finns även 
länkar till IMDb om du vill ha mer info. Dessutom kan du titta på trailers, direkt på siten.</p>
<h2>Kontakt</h2>
<p>Email: info@rm.se</p>
<p>Telefon(dagtid): 0340-2073053</p>
<p>Telefon(övrig tid): 0650-5006890</p>
EOD;

// Finally, leave it all to the rendering phase of Hera.
include (HERA_THEME_PATH);
  
