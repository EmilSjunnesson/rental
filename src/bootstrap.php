<?php
/**
 * Bootstrapping functions, essential and needed for Hera to work together with some common helpers. 
 *
 */
 
/**
 * Default exception handler.
 *
 */
function myExceptionHandler($exception) {
  echo "Hera: Uncaught exception: <p>" . $exception->getMessage() . "</p><pre>" . $exception->getTraceAsString(), "</pre>";
}
set_exception_handler('myExceptionHandler');
 
 
/**
 * Autoloader for classes.
 *
 */
function myAutoloader($class) {
  $path = HERA_INSTALL_PATH . "/src/{$class}/{$class}.php";
  if(is_file($path)) {
    include($path);
  }
  else {
    throw new Exception("Classfile '{$class}' does not exists.");
  }
}
spl_autoload_register('myAutoloader');


/**
 * Prints out an array.
 *
 */
function dump($array) {
  echo "<pre>" . htmlentities(print_r($array, 1)) . "</pre>";
}


/**
 * Gets current URL as permalink.
 *
 */
function getCurrentUrl() {
  $url = "http";
  $url .= (@$_SERVER["HTTPS"] == "on") ? 's' : '';
  $url .= "://";
  $serverPort = ($_SERVER["SERVER_PORT"] == "80") ? '' :
    (($_SERVER["SERVER_PORT"] == 443 && @$_SERVER["HTTPS"] == "on") ? '' : ":{$_SERVER['SERVER_PORT']}");
  $url .= $_SERVER["SERVER_NAME"] . $serverPort . htmlspecialchars($_SERVER["REQUEST_URI"]);
  return $url;
}


/**
 * Gets the current querystring with the option to modify it
 *
 */
function getQueryString($options, $prepend='?') {
  // parse query string into array
  $query = array();
  parse_str($_SERVER['QUERY_STRING'], $query);
 
  // Modify the existing query string with new options
  $query = array_merge($query, $options);
 
  // Return the modified querystring
  return $prepend . htmlentities(http_build_query($query));
}


/**
 * Shortens the text and adds a link to show more
 *
 */
function shortenText($text, $link, $max = 100) {
  if(strlen($text) > $max) {
    $text = substr($text, 0, strpos($text, ' ', $max)); 
    return $text . "... <a href='{$link}' style='color: inherit;'>Läs mer »</a>";    
  } else {
    return $text;     
  }
}


/**
 * Checks if image can be displayed else displays placeholder image
 *
 */
function validateImage($img, $placeholder) {
	if(!empty($img) && file_exists($img)) {
		strtolower($img);
		$img = str_replace('img/','', $img);
		if(!(preg_match('([^\s]+(\.(?i)(jpg|png|gif|bmp))$)', $img))) {
			$img = $placeholder;
		}
	} else {
		$img = $placeholder;
	}
	return $img;
}

