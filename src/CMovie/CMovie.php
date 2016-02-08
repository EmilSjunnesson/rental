<?php
/**
 * Class to display database in html
 *
 */
class CMovie extends CDatabaseHandle {
	
  private $db = null;
  
  private $title = null;
  private $genre = null;
  private $hits = null;
  private $page = null;
  private $year1 = null;
  private $year2 = null;
  private $orderby = null;
  private $order = null;
  private $layout = null;
  
  private $sqlOrig = null; 
  private $params = null;
  private $where = null; 
  private $groupby = null;
  private $res = null;

  // Get database and parameters from querystring and validates them
  public function __construct($db) {
	$this->db = $db;
	
	// Get parameters 
	$this->title    = isset($_GET['title']) ? $_GET['title'] : null;
	$this->genre    = isset($_GET['genre']) ? $_GET['genre'] : null;
	$this->hits     = isset($_GET['hits'])  ? $_GET['hits']  : 10;
	$this->page     = isset($_GET['page'])  ? $_GET['page']  : 1;
	$this->year1    = isset($_GET['year1']) && !empty($_GET['year1']) ? $_GET['year1'] : null;
	$this->year2    = isset($_GET['year2']) && !empty($_GET['year2']) ? $_GET['year2'] : null;
	$this->orderby  = isset($_GET['orderby']) ? strtolower($_GET['orderby']) : 'updated';
	$this->order    = isset($_GET['order'])   ? strtolower($_GET['order'])   : 'desc';
	$this->layout   = isset($_GET['layout']) ? strtolower($_GET['layout'])   : 'grid';

	// Check that incoming parameters are valid
	is_numeric($this->hits) or header("Location: 404.php?error=Hits must be numeric.");
	is_numeric($this->page) or header("Location: 404.php?error=Page must be numeric.");
	is_numeric($this->year1) || !isset($year1)  or header("Location: 404.php?error=Year must be numeric or not set.");
	is_numeric($this->year2) || !isset($year2)  or header("Location: 404.php?error=Year must be numeric or not set.");
  
	
	$this->hits     = htmlentities($this->hits, null, 'UTF-8');
	$this->page     = htmlentities($this->page, null, 'UTF-8');
	$this->year1    = htmlentities($this->year1, null, 'UTF-8');
	$this->year2    = htmlentities($this->year2, null, 'UTF-8');
	$this->orderby  = htmlentities($this->orderby, null, 'UTF-8');
	$this->order    = htmlentities($this->order, null, 'UTF-8');
	$this->layout   = htmlentities($this->layout, null, 'UTF-8');
  }
  
  //Html for navbar search
  public static function headerSearch() {
    $html = "<form id='search' action='movies.php#anchor'>\n";
    $html .= "<input type='search' name='title' placeholder='Sök efter film'/><input type='submit' name='submit' value='Sök'/>\n";
    $html .= "</form>\n";      
    return $html;
  }
  
  // output html displaying the  three latest movies
  public static function threeLatest($db, $width=175, $height=260) {
  	 $sql = "SELECT * FROM rm_movie WHERE published <= NOW() ORDER BY IFNULL(updated,published) DESC LIMIT 3; ";
  	 $res = $db->ExecuteSelectQueryAndFetchAll($sql);
  	 $html = null;
  	 foreach($res AS $val) {
  	   $img = validateImage($val->image, 'placeholder.png');
  	   $html .= "<a href='movies.php?id={$val->id}'>\n";
  	   $html .= "<figure class='movie'>\n";
  	   $html .= "<img src='img.php?src={$img}&amp;width={$width}&amp;height={$height}&amp;crop-to-fit' alt='Bild saknas' title='{$val->title}'/>\n";
  	   $html .= "<figcaption>{$val->title}</figcaption>\n";
  	   $html .= "</figure>\n";
  	   $html .= "</a>\n";
  	 }
  	 return $html;
  }
  
  //Prints the entire database or one entry depeinding on querystring
  public function printMovies() {
  	if (isset($_GET['id']) && $this->inDb($_GET['id'])) {
  	  return $this->printSingle($_GET['id']);
  	} else {
  	  return $this->printAll();
  	}
  }
  
  //Prints html for single database-entry
  private function printSingle($id) {
  	$sql = "SELECT * FROM rm_vmovie WHERE id=?";
  	$res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
  	$movie = $res[0];
  	$html = "<div class='movie-single clearfix'>\n";
  	// $html = $breadcrumb;
  	$html .= "<h1>{$movie->title} ({$movie->year})</h1>\n";
  	$html .= CDatabaseHandle::createBreadcrumb('Filmer', 'id', $movie->title);
  	$html .= "<article>\n";
  	$html .= "<div class='trailer'>" .$this->embedYoutube($movie->youtube) . "</div>\n";
  	$html .= "<div class='plot'>" . CTextFilter::doAllFilters($movie->plot) . "</div>\n";
  	$html .= "</article>\n";
  	$html .= "<aside>\n";
  	$html .= $this->printImage($movie->image, 204, 307);
  	$titles = array('Regissör','Genre','Längd','Språk','IMDb','Pris');
  	$data = array($movie->director,preg_replace('/,/',', ',$movie->genre),(!empty($movie->length) ? $movie->length . " min" : null),$movie->language,$this->IMDbLink($movie->imdb),(!empty($movie->price) ? $movie->price . " kr" : null));
  	$html .= $this->printAside($titles, $data) . "\n";
  	$html .= "</aside>\n";
  	$html .= "</div>\n";
  	$html .= "<footer class='movie'>\n";
  	$html .= $this->returnLink('movies.php');
  	$html .= "</footer>\n";
  	
  	return $html;
  }
  
  //Prints out html for an overview of the database
  private function printAll() {
  	$this->prepareQuery();
  	$this->getMaxAndRows($this->sqlOrig, $this->where, $this->groupby);
  	
  	$html = "<h1>Hyfilmer</h1>\n";
  	$html .= $this->printForm();
  	$html .= "<br>\n";
  	$html .= "<div id='anchor' class='movie-nav clearfix'>\n";
  	$html .= $this->orderByHtml();
  	$html .= $this->changeLayout();
  	$html .= "<div style='text-align: right; float:right;'>{$this->rows} träffar. " . $this->getHitsPerPage(array(5,10,20)) . "</div>\n";
  	$html .= "</div>\n";
  	
  	if (empty($this->res)) {
  	  $html .= "<p style='text-align:center;'>Tyvärr så finns det inga filmer som matchar din sökning</p>";
  	} else {
  	  if ($this->layout == 'table') {
  	    $html .= $this->printDatabaseTable($this->res);
  	  } else {
  	    $html .= $this->printGallery($this->res);
  	  }
  	}
  	
  	$html .= "<div style='text-align: center;'>" . $this->getPageNavigation($this->hits, $this->page, $this->max) . "</div>\n";
  	//ändra till galleri
  	return $html;
  }
  
  private function returnLink($default) {
  	if(isset($_SERVER['HTTP_REFERER'])) {
  		$containsM = strpos($_SERVER['HTTP_REFERER'], 'movies.php');
  		$containsE = strpos($_SERVER['HTTP_REFERER'], 'edit.php');
  		if ($containsM !== false || $containsE !== false) {
  			$link = $_SERVER['HTTP_REFERER'];
  		} else {
  			$link = $default;
  		}
  	} else {
  		$link = $default;
  	}
  	return "<a href='{$link}'><div class='arrow-left'></div> Tillbaka</a>\n";
  }
  
  //Checks if url is youtube video, if it is embeds it
  private function embedYoutube($url, $width = 560, $height = 315) {
  	if (!empty($url)) {
  	  preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $url, $id);
  	  if (!empty($id[0])) {
  	  	$embed = "<iframe width='{$width}' height='{$height}' src='//www.youtube.com/embed/{$id[0]}' style='border: 0;' allowfullscreen></iframe>";
  	  } else {
  	    $embed = "<output>Trailern kunde inte hämtas. Är <a href='{$url}'>{$url}</a> en Youtube-video?</output>";
  	  }
  } else {
  	$embed = null;
  }
  	return $embed;
  }
  
  //Prints out image in specified size
  private function printImage($img, $width = 173, $height = 260) {
  	if (!empty($img)) {
    return "<figure><img src='img.php?src=" 
        . str_replace('img','', $img) 
        . "&amp;width={$width}&amp;height={$height}&amp;crop-to-fit' alt='&#8203;'/></figure>\n";	
  	} else {
  	  return null;	
  	}
  }
  
  //Returns an imagelink to imdb
  private function IMDbLink($url) {
  	if(!empty($url)) {
  	  return "<a href='{$url}' target='_blank'><img src='img.php?src=IMDB.png&amp;width=54' alt='IMDb'/></a>";
  	}
  }
  
  //Prints content to be displayed in aside
  private function printAside(array $titles, array $data) {
    if(count($titles) == count($data)) {
      $html = "<ul class='aside-info'>\n";
      $res = array_combine($titles, $data);
      foreach ($res as $key => $value) {
      	if(!empty($value)) {
      		$html .= "<li><strong>{$key}:</strong><br>{$value}</li>\n";
      	}
      }
      $html .= "</ul>\n";	
    } else {
      $html = "<output>Information ej tillgänglig</output>\n";	
    }	
    return $html;
  }
  
  //Checks if the id is in the database
  private function inDb($id) {
  	$sql = "SELECT id FROM rm_vmovie;";
  	$res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
	$ids = array();
  	foreach ($res as $value) {
  	  $ids[] =  $value->id;
  	}
  	return in_array($id, $ids);
  }
  
  //Prints out html-form
  private function printForm() {
  	$html = "<form>\n";
  	$html .= "<fieldset>\n";
  	$html .= "<legend>Sök</legend>\n";
  	$html .= "<input type=hidden name=genre value='{$this->genre}'/>\n";
  	$html .= "<input type=hidden name=hits value='{$this->hits}'/>\n";
  	$html .= "<input type=hidden name=page value='1'/>\n";
  	$html .= "<p><label>Titel eller del av titel: <input type='search' name='title' value='{$this->title}'/></label></p>\n";
  	$html .= "<p>" . $this->getGenres($this->db, $this->genre) . "</p>\n";
  	$html .= "<p><label>Skapad mellan åren:</label>\n";
  	$html .= "<input type='text' name='year1' value='{$this->year1}'/>\n";
  	$html .= "-\n";
  	$html .= "<input type='text' name='year2' value='{$this->year2}'/>\n";
  	$html .= "</p>\n";
  	$html .= "<p><input type='submit' name='submit' value='Sök'/></p>\n";
  	$html .= "<p><a href='?'>Visa alla</a></p>\n";
  	$html .= "</fieldset>\n";
  	$html .= "</form>\n";
  	return $html;
  }
  
  private function orderByHtml() {
  	$html = "<div style='text-align: left; float: left;'>\n";
  	$html .= "Title " . $this->orderby('title');
  	$html .= " | År " . $this->orderby('year');
  	$html .= " | Pris " . $this->orderby('price');
  	$html .= " | Tillagd " . $this->orderby('updated');
  	$html .= "</div>\n";
  	return $html;
  }
  
  private function changeLayout() {
  	$html = "<div style='text-align: center; float: left;'>\n";
  	$html .= "<a href='" . getQueryString(array('layout'=>'grid')) . "'><img src='img.php?src=grid.png&amp;width=20' alt='Grid'/></a>";
  	$html .= " <a href='" . getQueryString(array('layout'=>'table')) . "'><img src='img.php?src=list.png&amp;width=20' alt='List'/></a>";
  	$html .= "</div>\n";
  	return $html;
  }
  
  //Returns a html link list of the database genres 
  public static  function getGenres($db, $genre, $home = false) {
  	// Get all genres that are active
  	$sql = '
    SELECT DISTINCT G.name, G.id
    FROM rm_genre AS G
    INNER JOIN rm_movie2genre AS M2G
    ON G.id = M2G.idGenre
    ';
  	$res = $db->ExecuteSelectQueryAndFetchAll($sql);
  	$genres = null;
  	foreach($res as $val) {
  		if($val->name == $genre) {
  			$genres .= "<span class='current'>$val->name</span> ";
  		}
  		else {
  			$genres .= "<a href='" . ($home ? "movies.php?genre={$val->name}#anchor" : getQueryString(array('genre' => $val->name))) . "'>" . ($home ? "<img src='img.php?src=genre/{$val->id}.png&amp;width=40' alt='{$val->name}'/>" : null) . "{$val->name}</a> ";
  		}
  	}
  	// Link to all genres
  	$all = isset($_GET['genre']) ? "<a href='" . getQueryString(array('genre' => null)) . "'>Alla</a>" : "<span class='current'>Alla</span>";
  	$home ? $all = "<a href='movies.php#anchor'><img src='img.php?src=genre/all.png&amp;width=40' alt='Alla'/>Alla</a>" : null;
  	return ($home ? null : "<label>Välj genre: ") . "{$all} {$genres}" . ($home ? null : "</label>") . "\n";
  }
  
  //Gets the max and rows values
  private function getMaxAndRows($sqlOrig, $where, $groupby) {
  	$sql = "
  	SELECT
  	COUNT(id) AS rows
  	FROM
  	(
  	$sqlOrig $where $groupby
  	) AS Movie
  	";
  	$rowsArr = $this->db->ExecuteSelectQueryAndFetchAll($sql, $this->params);
  	$this->rows = $rowsArr[0]->rows;
  	$this->max = ceil($this->rows / $this->hits);
  }
  
  //Prepares the query
  private  function prepareQuery() {
  	// Prepare the query based on incoming arguments
  	$this->sqlOrig = '
  SELECT
    M.*,
    GROUP_CONCAT(G.name) AS genre
  FROM rm_movie AS M
    LEFT OUTER JOIN rm_movie2genre AS M2G
      ON M.id = M2G.idMovie
    INNER JOIN rm_genre AS G
      ON M2G.idGenre = G.id
    ';
  	$where    = null;
  	$groupby  = ' GROUP BY M.id';
  	$limit    = null;
  	if ($this->orderby == 'updated') {
  		$this->orderby = 'IFNULL(updated,published)';
  	}
  	$sort     = " ORDER BY $this->orderby $this->order";
  	$params   = array();
  	
  	// Select by title
  	if($this->title) {
  		$where .= ' AND title LIKE ?';
  		$params[] = '%' . $this->title . '%';
  	}
  	
  	// Select by year
  	if($this->year1) {
  		$where .= ' AND year >= ?';
  		$params[] = $this->year1;
  	}
  	if($this->year2) {
  		$where .= ' AND year <= ?';
  		$params[] = $this->year2;
  	}
  	
  	// Select by genre
  	if($this->genre) {
  		$where .= ' AND G.name = ?';
  		$params[] = $this->genre;
  	}
  	
  	// Pagination
  	if($this->hits && $this->page) {
  		$limit = " LIMIT $this->hits OFFSET " . (($this->page - 1) * $this->hits);
  	}
  	
  	// Complete the sql statement
  	$this->where = $where ? " WHERE published <= NOW() {$where}" : " WHERE published <= NOW()";
  	$this->groupby = $groupby;
  	$this->params = $params;
  	$sql = $this->sqlOrig . $this->where . $this->groupby . $sort . $limit;
  	$this->res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $this->params);
  }
  
  private function shorten($text, $max) {
  	strlen($text) > $max ? $genres = substr($text, 0, $max) . "..." : $genres = $text;
  	return $genres;
  }
  
  private function printDatabaseTable($res) {
    // Put results into a HTML-table
    $tr = "<table>\n";
    $tr .= "<tr><th>Bild</th><th>Titel</th><th>År</th><th>Genre</th><th>Pris</th></tr>";
    foreach($res AS $key => $val) {
      $link = "<a href='?id={$val->id}'>";
      $img = validateImage($val->image, 'placeholder.png');
      $genres = $this->shorten($val->genre, 60);
      $tr .= "<tr class='movie-tr'><td style='text-align: center;'>{$link}<img src='img.php?src={$img}&amp;width=75&amp;height=75&amp;crop-to-fit&amp;sharpen' alt='Bild saknas'/></a></td><td>{$link}{$val->title}</a></td><td>{$link}{$val->year}</a></td><td>{$link}{$genres}</a></td><td>{$link}{$val->price} kr</a></td></tr>\n";
    }
    $tr .="</table>\n";
    return $tr;
  }  
  
  //Prints the movies gallery style
  private function printGallery($res) {
  	$gallery = "<ul class='gallery'>\n";
  	foreach($res as $file) {
      $img = validateImage($file->image, 'placeholder.png');
  	  $item    = "<img src='img.php?src={$img}&amp;width=173&amp;height=260&amp;crop-to-fit' alt='Bild saknas'/>";
  	  $caption = $file->title;
  
  		// Avoid to long captions breaking layout
  	  $fullCaption = $caption;
  	  if(strlen($caption) > 25) {
  	    $caption = substr($caption, 0, 10) . '…' . substr($caption, -5);
  	  }
  
  	  $gallery .= "<li><a href='?id={$file->id}' title='{$fullCaption}'><figure class='figure overview'>{$item}<figcaption><span>{$caption}</span><br>({$file->year})<br>{$file->price} kr</figcaption></figure></a></li>\n";
  	}
  	$gallery .= "</ul>\n";
  
  	return $gallery;
  }
}
