<?php
class CImage {
        
// Members     
private $maxWidth = null;
private $maxHight = null;
private $src = null;
private $verbose = null;
private $saveAs = null;
private $quality = null;
private $ignoreCache = null;
private $newWidth = null;
private $newHeight = null;
private $cropToFit = null;
private $sharpen = null;
private $grayscale = null;
private $sepia = null;
private $pathToImage = null;
private $fileExtension = null;

public function __construct($maxWidth, $maxHeight) {
  //
  // Get the incoming arguments
  //
  $src        = isset($_GET['src'])     ? $_GET['src']      : null;
  $verbose    = isset($_GET['verbose']) ? true              : null;
  $saveAs     = isset($_GET['save-as']) ? $_GET['save-as']  : null;
  $quality    = isset($_GET['quality']) ? $_GET['quality']  : 60;
  $ignoreCache = isset($_GET['no-cache']) ? true           : null;
  $newWidth   = isset($_GET['width'])   ? $_GET['width']    : null;
  $newHeight  = isset($_GET['height'])  ? $_GET['height']   : null;
  $cropToFit  = isset($_GET['crop-to-fit']) ? true : null;
  $sharpen    = isset($_GET['sharpen']) ? true : null;
  $grayscale    = isset($_GET['grayscale']) ? true : null;
  $sepia    = isset($_GET['sepia']) ? true : null;

  $pathToImage = realpath(IMG_PATH . $src);
  
  //
  // Validate incoming arguments
  //
  is_dir(IMG_PATH) or $this->errorMessage('The image dir is not a valid directory.');
  is_writable(CACHE_PATH) or $this->errorMessage('The cache dir is not a writable directory.');
  isset($src) or $this->errorMessage('Must set src-attribute.');
  preg_match('#^[a-z0-9A-Z-_\.\/]+$#', $src) or $this->errorMessage('Filename contains invalid characters.');
  substr_compare(IMG_PATH, $pathToImage, 0, strlen(IMG_PATH)) == 0 or $this->errorMessage('Security constraint: Source image is not directly below the directory IMG_PATH.');
  is_null($saveAs) or in_array($saveAs, array('png', 'jpg', 'jpeg', 'gif')) or $this->errorMessage('Not a valid extension to save image as');
  is_null($quality) or (is_numeric($quality) and $quality > 0 and $quality <= 100) or $this->errorMessage('Quality out of range');
  is_null($newWidth) or (is_numeric($newWidth) and $newWidth > 0 and $newWidth <= $maxWidth) or $this->errorMessage('Width out of range');
  is_null($newHeight) or (is_numeric($newHeight) and $newHeight > 0 and $newHeight <= $maxHeight) or $this->errorMessage('Height out of range');
  is_null($cropToFit) or ($cropToFit and $newWidth and $newHeight) or errorMessage('Crop to fit needs both width and height to work');

  //
  // Get the incoming arguments
  //
  $this->maxWidth   = $maxWidth;
  $this->maxHeight   = $maxHeight;
  $this->src        = $src;
  $this->verbose    = $verbose;
  $this->saveAs     = $saveAs;
  $this->quality    = $quality;
  $this->ignoreCache = $ignoreCache;
  $this->newWidth   = $newWidth;
  $this->newHeight  = $newHeight;
  $this->cropToFit  = $cropToFit;
  $this->sharpen    = $sharpen;
  $this->grayscale    = $grayscale;
  $this->sepia    = $sepia;
  $this->pathToImage = $pathToImage;
  $this->fileExtension = pathinfo($pathToImage)['extension'];
}

public function displayImage() {
  !isset($this->verbose) or $this->startVerbose();
  $this->getImageInfo();
  $image = $this->getOrginalImage();
  if (isset($this->cropToFit) || isset($this->newHeight) || isset($this->newWidth)) {
    $image = $this->resizeImage($this->width, $this->height, $image);
  }
  $cacheFileName = $this->createCache();
  $this->checkForCache($cacheFileName);
  $image = $this->applyFilters($image);
  $this->saveImage($cacheFileName, $image);
  $this->outputImage($cacheFileName, $this->verbose);
}

private function applyFilters($image) {
  if($this->sharpen) {
    $image = $this->sharpenImage($image);
  }
  if($this->grayscale) {
    imagefilter($image, IMG_FILTER_GRAYSCALE);
  }
  if($this->sepia) {
    $image = $this->sepiaImage($image);      
  }
  return $image;      
}

private function startVerbose() {
  $query = array();
  parse_str($_SERVER['QUERY_STRING'], $query);
  unset($query['verbose']);
  $url = '?' . http_build_query($query);
  
  $html = "<html lang='en'>\n";
  $html .= "<meta charset='UTF-8'/>\n";
  $html .= "<title>img.php verbose mode</title>\n";
  $html .= "<h1>Verbose mode</h1>\n";
  $html .= "<p><a href=$url><code>$url</code></a><br>\n";
  $html .= "<img src='{$url}' /></p>\n";
  
  echo $html;
}

private function getImageInfo() {
  $pathToImage = $this->pathToImage;      
        
  $imgInfo = list($this->width, $this->height, $type, $attr) = getimagesize($pathToImage);
  !empty($imgInfo) or $this->errorMessage("The file doesn't seem to be an image.");
  $mime = $imgInfo['mime'];

  if($this->verbose) {
    $filesize = filesize($pathToImage);
    $this->verbose("Image file: {$pathToImage}");
    $this->verbose("Image information: " . print_r($imgInfo, true));
    $this->verbose("Image width x height (type): {$this->width} x {$this->height} ({$type}).");
    $this->verbose("Image file size: {$filesize} bytes.");
    $this->verbose("Image mime type: {$mime}.");
  }      
}

private function getOrginalImage() {
  $verbose = $this->verbose;
  $pathToImage = $this->pathToImage;
  $fileExtension = $this->fileExtension;
  
  if($verbose) { $this->verbose("File extension is: {$fileExtension}"); }

  switch($fileExtension) {  
    case 'jpg':
    case 'jpeg': 
      $image = imagecreatefromjpeg($pathToImage);
      if($verbose) { $this->verbose("Opened the image as a JPEG image."); }
      break;  
  
    case 'png':  
      $image = imagecreatefrompng($pathToImage); 
      if($verbose) { $this->verbose("Opened the image as a PNG image."); }
      break;
      
    case 'gif':  
      $image = imagecreatefromgif($pathToImage); 
      if($verbose) { $this->verbose("Opened the image as a GIF image."); }
      break;

    default: $this->errorMessage('No support for this file extension.');
  }
  return $image;
}

private function resizeImage($width, $height, $image) {
  $verbose = $this->verbose;
  $newWidth = $this->newWidth;
  $newHeight = $this->newHeight;
        
  $aspectRatio = $width / $height;

  if($this->cropToFit && $newWidth && $newHeight) {
    $targetRatio = $newWidth / $newHeight;
    $cropWidth   = $targetRatio > $aspectRatio ? $width : round($height * $targetRatio);
    $cropHeight  = $targetRatio > $aspectRatio ? round($width  / $targetRatio) : $height;
    if($verbose) { $this->verbose("Crop to fit into box of {$newWidth}x{$newHeight}. Cropping dimensions: {$cropWidth}x{$cropHeight}."); }
  } else if($newWidth && !$newHeight) {
    $newHeight = round($newWidth / $aspectRatio);
    if($verbose) { $this->verbose("New width is known {$newWidth}, height is calculated to {$newHeight}."); }
  } else if(!$newWidth && $newHeight) {
    $newWidth = round($newHeight * $aspectRatio);
    if($verbose) { $this->verbose("New height is known {$newHeight}, width is calculated to {$newWidth}."); }
  } else if($newWidth && $newHeight) {
    $ratioWidth  = $width  / $newWidth;
    $ratioHeight = $height / $newHeight;
    $ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
    $newWidth  = round($width  / $ratio);
    $newHeight = round($height / $ratio);
    if($verbose) { $this->verbose("New width & height is requested, keeping aspect ratio results in {$newWidth}x{$newHeight}."); }
  } else {
    $newWidth = $width;
    $newHeight = $height;
   if($verbose) { $this->verbose("Keeping original width & heigth."); }
  }
  
  if($this->cropToFit) {
    if($verbose) { $this->verbose("Resizing, crop to fit."); }
    $cropX = round(($width - $cropWidth) / 2);  
    $cropY = round(($height - $cropHeight) / 2);    
    $imageResized = $this->createImageKeepTransparency($newWidth, $newHeight);
    imagecopyresampled($imageResized, $image, 0, 0, $cropX, $cropY, $newWidth, $newHeight, $cropWidth, $cropHeight);
    $image = $imageResized;
    $width = $newWidth;
    $height = $newHeight;
  } else if(!($newWidth == $width && $newHeight == $height)) {
    if($verbose) { $this->verbose("Resizing, new height and/or width."); }
    $imageResized = $this->createImageKeepTransparency($newWidth, $newHeight);
    imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    $image  = $imageResized;
    $width  = $newWidth;
    $height = $newHeight;
  }
  return $image;
}

private function createCache() {
  $parts          = pathinfo($this->pathToImage);
  $this->saveAs   = is_null($this->saveAs) ? $this->fileExtension : $this->saveAs;
  $quality_       = is_null($this->quality) ? null : "_q{$this->quality}";
  $cropToFit_     = is_null($this->cropToFit) ? null : "_cf";
  $sharpen_       = is_null($this->sharpen) ? null : "_s";
  $grayscale_       = is_null($this->grayscale) ? null : "_g";
  $sepia_       = is_null($this->sepia) ? null : "_o";
  $dirName        = preg_replace('/\//', '-', dirname($this->src));
  $cacheFileName = CACHE_PATH . "-{$dirName}-{$parts['filename']}_{$this->newWidth}_{$this->newHeight}{$quality_}{$cropToFit_}{$sharpen_}{$grayscale_}{$sepia_}.{$this->saveAs}";
  $cacheFileName = preg_replace('/^a-zA-Z0-9\.-_/', '', $cacheFileName);

  if($this->verbose) { $this->verbose("Cache file is: {$cacheFileName}"); }
  return $cacheFileName;      
}

private function checkForCache($cacheFileName) {
  $verbose = $this->verbose;      
        
  $imageModifiedTime = filemtime($this->pathToImage);
  $cacheModifiedTime = is_file($cacheFileName) ? filemtime($cacheFileName) : null;

  // If cached image is valid, output it.
  if(!$this->ignoreCache && is_file($cacheFileName) && $imageModifiedTime < $cacheModifiedTime) {
    if($verbose) { $this->verbose("Cache file is valid, output it."); }
    $this->outputImage($cacheFileName, $verbose);
  }

  if($verbose) { $this->verbose("Cache is not valid, process image and create a cached version of it."); }        
}

private function saveImage($cacheFileName, $image) {
  $verbose = $this->verbose;
  $saveAs = $this->saveAs;
   
  switch($saveAs) {
    case 'jpeg':
    case 'jpg':
    if($verbose) { $this->verbose("Saving image as JPEG to cache using quality = {$this->quality}."); }
    imagejpeg($image, $cacheFileName, $this->quality);
    break;  

    case 'png':  
    if($verbose) { $this->verbose("Saving image as PNG to cache."); }
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagepng($image, $cacheFileName);  
    break;
    
    case 'gif':  
    if($verbose) { $this->verbose("Saving image as GIF to cache."); }
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagegif($image, $cacheFileName);  
    break;

    default:
    $this->errorMessage('No support to save as this file extension.');
    break;
  }      
}


/**
 * Display error message.
 *
 * @param string $message the error message to display.
 */
function errorMessage($message) {
  header("Status: 404 Not Found");
  die('img.php says 404 - ' . htmlentities($message));
}



/**
 * Display log message.
 *
 * @param string $message the log message to display.
 */
function verbose($message) {
  echo "<p>" . htmlentities($message) . "</p>";
}



/**
 * Output an image together with last modified header.
 *
 * @param string $file as path to the image.
 * @param boolean $verbose if verbose mode is on or off.
 */
function outputImage($file, $verbose) {
  $info = getimagesize($file);
  !empty($info) or $this->errorMessage("The file doesn't seem to be an image.");
  $mime   = $info['mime'];

  $lastModified = filemtime($file);  
  $gmdate = gmdate("D, d M Y H:i:s", $lastModified);

  if($verbose) {
    $this->verbose("Memory peak: " . round(memory_get_peak_usage() /1024/1024) . "M");
    $this->verbose("Memory limit: " . ini_get('memory_limit'));
    $this->verbose("Time is {$gmdate} GMT.");
  }

  if(!$verbose) header('Last-Modified: ' . $gmdate . ' GMT');
  if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified){
    if($verbose) { $this->verbose("Would send header 304 Not Modified, but its verbose mode."); exit; }
    header('HTTP/1.0 304 Not Modified');
  } else {  
    if($verbose) { $this->verbose("Would send header to deliver image with modified time: {$gmdate} GMT, but its verbose mode."); exit; }
    header('Content-type: ' . $mime);  
    readfile($file);
  }
  exit;
}


/**
 * Create new image and keep transparency
 *
 * @param resource $image the image to apply this filter on.
 * @return resource $image as the processed image.
 */
function createImageKeepTransparency($width, $height) {
    $img = imagecreatetruecolor($width, $height);
    imagealphablending($img, false);
    imagesavealpha($img, true);  
    return $img;
}



/**
 * Sharpen image as http://php.net/manual/en/ref.image.php#56144
 * http://loriweb.pair.com/8udf-sharpen.html
 *
 * @param resource $image the image to apply this filter on.
 * @return resource $image as the processed image.
 */
function sharpenImage($image) {
  $matrix = array(
    array(-1,-1,-1,),
    array(-1,16,-1,),
    array(-1,-1,-1,)
  );
  $divisor = 8;
  $offset = 0;
  imageconvolution($image, $matrix, $divisor, $offset);
  return $image;
}


/**
 * Apply sepia filter
 *
 * @param resource $image the image to apply this filter on.
 * @return resource $image as the processed image.
 */
function sepiaImage($image) {
  imagefilter($image, IMG_FILTER_GRAYSCALE);
  imagefilter($image, IMG_FILTER_BRIGHTNESS, -10);
  imagefilter($image, IMG_FILTER_CONTRAST, -20);
  imagefilter($image, IMG_FILTER_COLORIZE, 120, 60, 0, 0);
  return $image;
}
}
