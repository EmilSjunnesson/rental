<?php
class CEditMovies extends CDatabaseHandle {
        
  protected $db = null;
  private $output = null;
  
  protected $id = null;
  protected $title = null;
  protected $director = null;
  protected $length = null;
  protected $year = null;
  protected $plot = null;
  protected $image = null;
  protected $language = null;
  protected $price = null;
  protected $imdb = null;
  protected $youtube = null;
  protected $published = null;
  protected $updated = null;
 
  // Fetches the database and user class
  public function __construct($db) {
    $this->db = $db;
  }
  
  // Gets the entry with the specified id
  public function getEntryByID($id) {
    $this->id = $id;
    $sql = 'SELECT * FROM rm_vmovie WHERE id = ?';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($this->id));
    if(isset($res[0])) {
      $c = $res[0];
      $this->title     = htmlentities($c->title, null, 'UTF-8');
      $this->director  = htmlentities($c->director, null, 'UTF-8');
      $this->length    = htmlentities($c->length, null, 'UTF-8');
      $this->year      = htmlentities($c->year, null, 'UTF-8');
      $this->plot      = htmlentities($c->plot, null, 'UTF-8');
      $this->image     = htmlentities($c->image, null, 'UTF-8');
      $this->language  = htmlentities($c->language, null, 'UTF-8');
      $this->price     = htmlentities($c->price, null, 'UTF-8');
      $this->imdb      = htmlentities($c->imdb, null, 'UTF-8');
      $this->youtube   = htmlentities($c->youtube, null, 'UTF-8');
      $this->published = htmlentities($c->published, null, 'UTF-8');
      $this->updated   = htmlentities($c->updated, null, 'UTF-8');
    } else {
      header("Location: 404.php?error=No entry with that id");      
    }
  }
 
  // Return a list with all entries in the content table
  public function getAllAsList() {
  	$hits     = isset($_GET['hits'])  ? htmlentities($_GET['hits'], null, 'UTF-8')  : 10;
  	$page     = isset($_GET['page'])  ? htmlentities($_GET['page'], null, 'UTF-8')  : 1;
  	$orderby  = isset($_GET['orderby']) ? htmlentities(strtolower($_GET['orderby']), null, 'UTF-8') : 'id';
  	$order    = isset($_GET['order'])   ? htmlentities(strtolower($_GET['order']), null, 'UTF-8')   : 'desc';
  	 
  	// Check that incoming parameters are valid
  	is_numeric($hits) or header("Location: 404.php?error=Hits must be numeric.");
  	is_numeric($page) or header("Location: 404.php?error=Page must be numeric.");
  	
  	// Get rows and max
  	$sql = "SELECT COUNT(id) AS rows FROM rm_movie;";
  	$rowsArr = $this->db->ExecuteSelectQueryAndFetchAll($sql);
  	$rows = $rowsArr[0]->rows;
  	$max = ceil($rows / $hits);
  	 
  	$sort = " ORDER BY $orderby $order";
  	// Pagination
  	$limit    = null;
  	if($hits && $page) {
  		$limit = " LIMIT $hits OFFSET " . (($page - 1) * $hits);
  	}
  	
    $sql = 'SELECT id, title, (published <= NOW()) AS available FROM rm_vmovie' . $sort . $limit;
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);

    // Put results into a HTML-table
    $tr = "<div style='text-align: right; float:right;'>{$rows} träffar. " . $this->getHitsPerPage(array(5,10,20)) . "</div>\n";
    $tr .= "<table class='admin'>\n";
    $tr .= "<tr><th>Id{$this->orderby('id', true)}</th><th>Titel{$this->orderby('title', true)}</th><th>Status</th>" . (CUser::IsAdmin() ? "<th>Editera</th><th>Ta bort</th>" : null) . "</tr>";
    foreach($res AS $key => $val) {
      $id = htmlentities($val->id, null, 'UTF-8');
      $tr .= "<tr>";
      $tr .= "<td>{$id}</td>";
      $tr .= "<td style='text-align: left;'>" . htmlentities($val->title, null, 'UTF-8') . "</td>";
      if (CUser::IsAdmin()) {
      	$tr .= "<td>" . (!$val->available ? ' inte' : null) . " publicerad<br>";
      	$tr .= "<a href='" . (!$val->available ? "?publish={$val->id}" : "movies.php?id={$id}") . "'>" . (!$val->available ? 'publisera' : 'visa') . "</a></td>";
        $tr .= "<td><a href='?id={$id}'><img src='img.php?src=edit.png&amp;width=25' alt='edit'/></a></td>";
        $tr .= "<td><a href='?delete={$id}'><img src='img.php?src=delete.png&amp;width=25' alt='delete'/></a></td>";
      } else {
        $tr .= "<td>" . (!$val->available ? ' inte' : null) . " publicerad<br>";
        $tr .= ($val->available ? "<a href='movies.php?id={$id}'>visa</a></li>" : null) ."</td>";
      }
      $tr .= "</tr>\n";
    }
    $tr .="</table>\n";
    $tr .= "<div style='text-align: center;'>" . $this->getPageNavigation($hits, $page, $max) . "</div>\n";
    return $tr;
  }
  
  // Prints out HTML for the update-from
  public function printAndPostUpdate() {
    $html = "<h1>Uppdatera film med id: {$this->id}</h1>\n";
    $html .= "<form method=post id='update'>\n";
    $html .= "<fieldset>\n";
    $html .= "<legend>Uppdatera innehåll</legend>\n";
    $html .= "<input type='hidden' name='id' value='{$this->id}'/>\n";
    $html .= "<p><label>Titel:<br/><input type='text' name='title' value='{$this->title}' required/></label></p>\n";
    $html .= "<p><label>Regissör:<br/><input type='text' name='director' value='{$this->director}'/></label></p>\n";
    $html .= "<p><label>År:<br/><input type='text' name='year' value='{$this->year}' required/></label></p>\n";
    $html .= "<p><label>Längd i miuter:<br/><input type='number' min='0' name='length' value='" . (empty($this->length) ? null : $this->length) ."'/></label></p>\n";
    $html .= "<p><label>Språk (ISO):<br/><input type='text' name='language' value='{$this->language}'/></label></p>\n";
    $html .= "<p><label>Pris i kronor:<br/><input type='text' name='price' value='{$this->price}' required/></label></p>\n";
    $html .= "<p><label>Bildlänk:<br/><input type='text' name='image' class='link' value='{$this->image}'/></label></p>\n";
    $html .= "<p><label>IMDb-länk:<br/><input type='text' name='imdb' class='link' value='{$this->imdb}'/></label></p>\n";
    $html .= "<p><label>Youtube-länk:<br/><input type='text' name='youtube' class='link' value='{$this->youtube}'/></label></p>\n";
    $html .= "<p><label>Plot:<br/><textarea name='plot'>{$this->plot}</textarea></label></p>\n";
    $html .= $this->getGenreList($this->getGenres($this->id));
    $html .= "<p class=buttons><input type='submit' name='save' value='Spara'/> <input type='reset' value='Återställ'/></p>\n";
    $html .= "<p><a href='?'>Visa alla</a></p>\n";
    
    isset($_POST['save'])  ? $this->saveEntry() : null; 
    
    if(isset($_GET['success'])) {
      $html .= "<br><output class='info'>Informationen sparades.</output><br>\n";
    } elseif(isset($_GET['fail'])) {
      $html .= "<br><output class='info'>Informationen sparades EJ.<br><pre>" . print_r($this->db->ErrorInfo(), 1) . "</pre></output><br>\n";      
    }
    
    $html .= "<br></fieldset>\n";
    $html .= "</form>\n";
    
    return $html;
  }
  
  // Saves the information from the form
  private function saveEntry() {   
          
    // Get parameters 
    $id       = isset($_POST['id'])    ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
    $title    = isset($_POST['title']) ? $_POST['title'] : null;
    $director = isset($_POST['director'])  ? $_POST['director']  : null;
    $year     = isset($_POST['year'])  ? $_POST['year']  : null;
    $length   = isset($_POST['length'])  ? $_POST['length']  : null;
    $language = isset($_POST['language'])  ? $_POST['language']  : null;
    $price    = isset($_POST['price'])  ? $_POST['price']  : null;
    $image    = isset($_POST['image'])  ? $_POST['image']  : null;
    $imdb     = isset($_POST['imdb'])  ? $_POST['imdb']  : null;
    $youtube  = isset($_POST['youtube'])  ? $_POST['youtube']  : null;
    $plot     = isset($_POST['plot'])  ? $_POST['plot'] : array();
    
    $image = $this->addFolder($image);
    
    $sql = '
    UPDATE rm_movie SET
      title   = ?,
      director    = ?,
      year    = ?,
      length = ?,
      language = ?,
      price = ?,
      image = ?,
      imdb = ?,
      youtube = ?,
      plot = ?,
      updated = NOW()						
    WHERE 
      id = ?
    ';
    $params = array($title, $director, $year, $length, $language, $price, $image, $imdb, $youtube, $plot, $id);
    $res = $this->db->ExecuteQuery($sql, $params);
    if($res) {
      $this->saveGenres($_POST['genres']);	
      header("Location: " . getCurrentUrl() . "&success");      
    }
    else {
      header("Location: " . getCurrentUrl() . "&fail");
    }
  }
  
  // Prints HTML for the delete-page
  public function printAndPostDelete() {        
    $html = "<h1>Ta bort innehåll med id: {$this->id}</h1>\n";
    $html .= "<form method=post>\n";
    $html .= "<fieldset>\n";
    $html .= "<legend>Ta bort innehåll</legend>\n";
    $html .= "<input type='hidden' name='id' value='{$this->id}'/>\n";
    $html .= "<p>Vill du opublicera eller radera innehållet?<br>\n";
    $html .= "&rarr;" . $this->title . "</p>\n";
    $html .= "<p class=buttons><input type='submit' name='unpublish' value='Opublicera'/> <input type='submit' name='delete' value='Radera'/></p>\n";
    $html .= "<p><a href='?'>Visa alla</a></p>\n";
   
    isset($_POST['unpublish'])  ? $this->unpublishEntry() : null; 
    isset($_POST['delete'])  ? $this->deleteEntry() : null; 
    
    if(isset($this->output)) {
      $html .= "<br><output class='info'>{$this->output}</output><br>\n";
    }
    
    $html .= "<br></fieldset>\n";
    $html .= "</form>\n";
    
    return $html;
  }
  
  // Deletes the entry
  private function deleteEntry() {
  	$sql = 'DELETE FROM rm_movie2genre WHERE idMovie = ?';
  	$res = $this->db->ExecuteQuery($sql, array($this->id));
    $sql = 'DELETE FROM rm_movie WHERE id = ? LIMIT 1';
    $res = $this->db->ExecuteQuery($sql, array($this->id));
    if($res) {
      header("Location: edit_movies.php");
    }  
  }
  
  // Unpublished the entry but keeps it in the database
  private function unpublishEntry() {
    $sql = 'UPDATE rm_movie SET published = ?, updated = ? WHERE id = ?';
    $params = array(null, null, $this->id);
    $res = $this->db->ExecuteQuery($sql, $params);  
    if($res) {
      $this->output = "Innehållet opublicerades.";
    }  
  }
  
  // Republishes the entry
  public function publish($id) {
    $sql = 'UPDATE rm_movie SET published = NOW() WHERE id = ?';
    $params = array($id);
    $res = $this->db->ExecuteQuery($sql, $params);
  }
  
  // Prints out HTML for the add-from
  public function printAndPostAdd() {
    $html = "<h1>Lägg till ny film</h1>\n";
    $html .= "<form method='post' id='add'  enctype='multipart/form-data'>\n";
    $html .= "<fieldset>\n";
    $html .= "<legend>Lägg till</legend>\n";
    $html .= "<p><label>Titel:<br/><input type='text' name='title' required /></label></p>\n";
    $html .= "<p><label>Regissör:<br/><input type='text' name='director'/></label></p>\n";
    $html .= "<p><label>År:<br/><input type='text' name='year' required/></label></p>\n";
    $html .= "<p><label>Längd i miuter:<br/><input type='number' min='0' name='length'/></label></p>\n";
    $html .= "<p><label>Språk (ISO):<br/><input type='text' name='language'/></label></p>\n";
    $html .= "<p><label>Pris i kronor:<br/><input type='text' name='price' required/></label></p>\n";
    $html .= "<p><label>Bildlänk:<br/><input type='text' class='link' name='image'/></label></p>\n";
    $html .= "<p><label>IMDb-länk:<br/><input type='text' class='link' name='imdb' /></label></p>\n";
    $html .= "<p><label>Youtube-länk:<br/><input type='text' class='link' name='youtube'/></label></p>\n";     
    $html .= "<p><label>Plot:<br/><textarea name='plot'></textarea></label></p>\n";
    $html .= $this->getGenreList();
    $html .= "<p><em>Obs! Glöm inte att publicera filmen på sidan hantera filmer.</em></p>\n";
    $html .= "<p class=buttons><input type='submit' name='add' value='Lägg till'/> <input type='reset' value='Återställ'/></p>\n";
    $html .= "<p><a href='?'>Visa alla</a></p>\n";    
    $html .= "<br></fieldset>\n";
    $html .= "</form>\n";
    
    isset($_POST['add'])  ? $this->addEntry() : null;
    
    return $html;
  }
  
  // Saves the information from the form
  private function addEntry() {   
          
    // Get parameters 
    $title  = isset($_POST['title']) ? $_POST['title'] : null;
    $director = isset($_POST['director'])  ? $_POST['director']  : null;
    $year     = isset($_POST['year'])  ? $_POST['year']  : null;
    $length   = isset($_POST['length'])  ? $_POST['length']  : null;
    $language = isset($_POST['language'])  ? $_POST['language']  : null;
    $price    = isset($_POST['price'])  ? $_POST['price']  : null;
    $image    = isset($_POST['image'])  ? $_POST['image']  : null;
    $imdb     = isset($_POST['imdb'])  ? $_POST['imdb']  : null;
    $youtube  = isset($_POST['youtube'])  ? $_POST['youtube']  : null;
    $plot     = isset($_POST['plot'])  ? $_POST['plot'] : array();
	
	$image = $this->addFolder($image);
    
    $sql = '
    INSERT INTO rm_movie (title, director, year, length, language, price, image, imdb, youtube, plot) 
    VALUES(?,?,?,?,?,?,?,?,?,?)
    ';
    $params = array($title, $director, $year, $length, $language, $price, $image, $imdb, $youtube, $plot);
    $this->db->ExecuteQuery($sql, $params);
    $this->id = $this->db->getLastId();
    $this->saveGenres($_POST['genres']);
    header("Location: edit_movies.php");      
  }
  
  //Display genres
  private function getGenreList(array $oldGenres = array()) {
    $html = "<p><label>Genre:</label> <span id='invalid'>Minst en genre måste väljas</span><br>\n";
    $sql = "SELECT * FROM rm_genre;";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    $everyOther = false;
    foreach($res AS $genre) {
      $html .= "<span class='genre-check'><input type='checkbox' name='genres[]' value='{$genre->id}' ";
      if (!empty($oldGenres)) {
      	$html .= in_array($genre->id, $oldGenres) ? "checked" : null ;
      }
      $html .= ">{$genre->name}</span>" . ($everyOther ? "<br>" : null) . "\n";        
      $everyOther = !$everyOther;
    }
    $html .= "</p>\n";  
    return $html;
  }
  
  private function getGenres($id) {
  	$array = array();
  	$sql = "SELECT idGenre FROM rm_movie2genre WHERE idMovie = ?;";
  	$res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
  	foreach ($res as $val) {
  	  $array[] = $val->idGenre;
  	}
  	return $array;
  }
  
  private function saveGenres(array $genres) {
  	$sql = 'DELETE FROM rm_movie2genre WHERE idMovie = ?';
  	$res = $this->db->ExecuteQuery($sql, array($this->id));
  	foreach ($genres as $val) {
  	  $sql = 'INSERT INTO rm_movie2genre VALUES (?,?)';
  	  $res = $this->db->ExecuteQuery($sql, array($this->id, $val));
  	}
  }
  
  public static function addFolder($path) {
  	if(!empty($path)) {
  	if (preg_match('#^img/#i', $path) === 0) {
  	  $path = "img/" . $path;
  	}
      return $path;	
  	} else {
  	  return null;	
  	}
  }
  
  /**
   * Create a link to the content, based on its type.
   *
   * @param object $content to link to.
   * @return string with url to display content.
   */
  function getUrlToContent($content) {
    switch($content->type) {
      case 'page': return "page.php?url={$content->url}"; break;
      case 'post': return "blog.php?slug={$content->slug}"; break;
      default: return null; break;
    }
  }
  
  /**
   * Create a slug of a string, to be used as url.
   *
   * @param string $str the string to format as slug.
   * @returns str the formatted slug. 
   */
  function slugify($str) {
    $str = mb_strtolower(trim($str));
    $str = str_replace(array('&aring;','&auml;','&ouml;'), array('a','a','o'), $str);
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = trim(preg_replace('/-+/', '-', $str), '-');
    return $str;
  }
}
