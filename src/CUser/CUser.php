<?php
/**
 * Class handeling login and out
 *
 */
class CUser extends CDatabaseHandle {
  
  private $db;
  private $error = null;   
  
  private $id = null;
  private $type = null;
  private $image = null;
  private $acronym = null;
  private $salt = null;
        
  public function __construct($database) {
    $this->db = $database;
  }
        
  public function Login($user, $password) {
    $sql = "SELECT acronym, name, image, type, since FROM rm_user WHERE acronym = ? AND password = md5(concat(?, salt))";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($user, $password));
    if(isset($res[0])) {
      $_SESSION['user'] = $res[0];
    }        
  }
  
  public function Logout() {
    unset($_SESSION['user']);       
  }
  
  public function Register($user, $name, $password, $confirm) {
  	if($password !== $confirm) {
  	  $this->error = ", lösenorden matchar inte";
  	  return false;	
  	}
  	$sql = "SELECT acronym FROM rm_user;";
  	$res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
  	$users = null;
  	foreach($res AS $val) {
  	  $users[] = $val->acronym;	
  	}
  	if(in_array($user, $users)) {
  	  $this->error = ", användarnamnet finns redan";
  	  return false;
  	}
  	$sql = "INSERT INTO rm_user (acronym, name, type, since, salt) VALUES 
    (?, ?, 'USER', NOW(), unix_timestamp());";
  	$params = array($user, $name);
  	$res = $this->db->ExecuteQuery($sql, $params);
  	if($res) {
  		$sql = "UPDATE rm_user SET password = md5(concat(?, salt)) WHERE acronym = ?;";
  		$params = array($password, $user);
  		$res = $this->db->ExecuteQuery($sql, $params);
  		if($res) {
  		  return true;	
  		} else {
  		  return false;	
  		}
  	} else {
  	  return false;	
  	}
  }
  
  public function getError() {
  	return $this->error;
  }
  
  public static function IsAuthenticated() {
    $state = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
    return $state;
  }
  
  public static function GetAcronym() {
    return $_SESSION['user']->acronym;      
  }
  
  public static function GetName() {
    return $_SESSION['user']->name;        
  }
  
  public static function IsAdmin() {
    $state = false; 
    if(self::IsAuthenticated()) {
      if($_SESSION['user']->type === 'ADMIN') {
        $state = true;      
      }
    }
    return $state;      
  }
  
  public static function userNav() {
    $nav = null;
    if(self::IsAuthenticated()) {
      if(self::IsAdmin()) {      
        $nav = array('text'=>'Admin', 'url'=>'admin.php', 'title' => 'Administratör', 'submenu' => array('items' => array(
            'logout' => array('text'=>'Logga ut', 'url'=>'logout.php', 'title' => 'Logga ut'),
            'editmovies' => array('text'=>'Hantera filmer', 'url'=>'edit_movies.php', 'title' => 'Hantera databasen som innehåller filmer'),
            'editnews' => array('text'=>'Hantera nyheter', 'url'=>'edit_news.php', 'title' => 'Hantera nyheterna/bloggen'),
            'editusers' => array('text'=>'Hantera användare', 'url'=>'edit_users.php', 'title' => 'Hantera databasen som innehåller användare'),
            ),),);      
      } else {
        $nav = array('text'=>'Min sida', 'url'=>'me.php', 'title' => 'Min personliga sida', 'submenu' => array('items' => array(
            'logout' => array('text'=>'Logga ut', 'url'=>'logout.php', 'title' => 'Logga ut'),
            'editmovies' => array('text'=>'Hantera filmer', 'url'=>'edit_movies.php', 'title' => 'Hantera databasen som innehåller filmer'),
            'editnews' => array('text'=>'Hantera nyheter', 'url'=>'edit_news.php', 'title' => 'Hantera nyheterna/bloggen'),
            'editusers' => array('text'=>'Hantera användare', 'url'=>'edit_users.php', 'title' => 'Hantera databasen som innehåller användare'),
            ),),);       
      }
    } else {
      $nav = array('text'=>'Logga in', 'url'=>'login.php', 'title' => 'Logga in');       
    }
    return $nav;      
  }
  
  // Return a list with all entries in the content table
  public function getUserList() {
  	$hits     = isset($_GET['hits'])  ? htmlentities($_GET['hits'], null, 'UTF-8')  : 10;
  	$page     = isset($_GET['page'])  ? htmlentities($_GET['page'], null, 'UTF-8')  : 1;
  	$orderby  = isset($_GET['orderby']) ? htmlentities(strtolower($_GET['orderby']), null, 'UTF-8') : 'id';
  	$order    = isset($_GET['order'])   ? htmlentities(strtolower($_GET['order']), null, 'UTF-8')   : 'asc';
  	
  	// Check that incoming parameters are valid
  	is_numeric($hits) or header("Location: 404.php?error=Hits must be numeric.");
  	is_numeric($page) or header("Location: 404.php?error=Page must be numeric.");
  	
  	// Get rows and max
  	$sql = "SELECT COUNT(id) AS rows FROM rm_user;";
  	$rowsArr = $this->db->ExecuteSelectQueryAndFetchAll($sql);
  	$rows = $rowsArr[0]->rows;
  	$max = ceil($rows / $hits);
  	
  	$sort = " ORDER BY $orderby $order";
  	// Pagination
  	$limit    = null;
  	if($hits && $page) {
  		$limit = " LIMIT $hits OFFSET " . (($page - 1) * $hits);
  	}
  	
  	$sql = 'SELECT id, type, image, acronym, name, since FROM rm_user' . $sort . $limit;
  	$res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
  
  	// Put results into a HTML-table
  	$tr = "<div style='text-align: right; float:right;'>{$rows} träffar. " . $this->getHitsPerPage(array(5,10,20)) . "</div>\n";
  	$tr .= "<table class='admin'>\n";
  	$tr .= "<tr><th>Id{$this->orderby('id')}</th><th>Typ{$this->orderby('type')}</th><th>Bild</th><th>Acronym{$this->orderby('acronym')}</th><th>Namn{$this->orderby('name')}</th><th>Medlem sedan{$this->orderby('since')}</th>" . (self::IsAdmin() ? "<th>Editera</th><th>Ta bort</th>" : null) . "</tr>";
  	foreach($res AS $key => $val) {
  		$id = htmlentities($val->id, null, 'UTF-8');
  		$img = validateImage($val->image, 'profile.png');
  		$tr .= "<tr>";
  		$tr .= "<td>{$id}</td>";
  		$tr .= "<td>" . htmlentities($val->type, null, 'UTF-8') . "</td>";
  		$tr .= "<td><img src='img.php?src={$img}&amp;width=75&amp;height=75&amp;crop-to-fit&amp;sharpen' alt='Bild saknas'/></td>";
  		$tr .= "<td>" . htmlentities($val->acronym, null, 'UTF-8') . "</td>";
  		$tr .= "<td>" . htmlentities($val->name, null, 'UTF-8') . "</td>";
  		$tr .= "<td>" . htmlentities($val->since, null, 'UTF-8') . "</td>";
  		if (self::IsAdmin()) {
  			$tr .= "<td><a href='?id={$id}'><img src='img.php?src=edit.png&amp;width=25' alt='edit'/></a></td>";
  			$tr .= "<td>" . ($val->acronym !== 'admin' ? "<a href='?delete={$id}' onclick='return confirm(\"Är du säker på att du vill radera användaren?\");'><img src='img.php?src=delete.png&amp;width=25' alt='delete'/></a>" : null) . "</td>";
  		}
  		$tr .= "</tr>\n";
  	}
  	$tr .="</table>\n";
  	$tr .= "<div style='text-align: center;'>" . $this->getPageNavigation($hits, $page, $max) . "</div>\n";
  	return $tr;
  }
  
  // Gets the entry with the specified id
  public function getEntryByID($id) {
  	$this->id = $id;
  	$sql = 'SELECT * FROM rm_user WHERE id = ?';
  	$res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($this->id));
  	if(isset($res[0])) {
  		$c = $res[0];
  		$this->type    = htmlentities($c->type, null, 'UTF-8');
  		$this->image   = htmlentities($c->image, null, 'UTF-8');
  		$this->acronym = htmlentities($c->acronym, null, 'UTF-8');
  		$this->name    = htmlentities($c->name, null, 'UTF-8');
  		$this->salt    = htmlentities($c->salt, null, 'UTF-8');
  	} else {
  		header("Location: 404.php?error=No entry with that id");
  	}
  }
  
  // Deletes the entry
  public function deleteEntry() {
  	$sql = 'DELETE FROM rm_user WHERE id = ? LIMIT 1';
  	$res = $this->db->ExecuteQuery($sql, array($this->id));
  	if($res) {
  		header("Location: edit_users.php");
  	}
  }
  
  // Prints out HTML for the update-from
  public function printAndPostUpdate() {
  	$html = $this->IsAdmin() ? "<h1>Uppdatera användare med id: {$this->id}</h1>\n": null;
  	$html .= "<form method=post>\n";
  	$html .= "<fieldset>\n";
  	$html .= "<legend>Uppdatera användare</legend>\n";
  	$html .= "<input type='hidden' name='id' value='{$this->id}'/>\n";
  	$html .= "<input type='hidden' name='salt' value='{$this->salt}'/>\n";
  	$html .= "<p><label>Acronym:<br/><input type='text' name='acronym' value='{$this->acronym}' autocomplete='off' required/></label></p>\n";
  	$html .= "<p><label>Namn:<br/><input type='text' name='name' value='{$this->name}' autocomplete='off' required/></label></p>\n";
  	$html .= "<p><label>Bildlänk:<br/><input class='link' type='text' name='image' value='{$this->image}'/></label></p>\n"; 	
  	$html .= $this->IsAdmin() ? "<p><label>Befogenheter:</label><br/>
  	<input type='radio' name='type' value='USER' " . ($this->type=="USER" ? "checked" : null) . " required/> Användare
  	<br><input type='radio' name='type' value='ADMIN' " . ($this->type=="ADMIN" ? "checked" : null) . " required/> Administratör</p>\n" : null;
  	$html .= $this->IsAdmin() ? "<p><label>Återställ lösenord:</label><br/><input type='checkbox' name='reset-pw'/> (Nytt lösen: 0000)</p>\n" : 
  	"<p><label>Ändra lösenord:<br/><input type='password' name='password'/></label></p>\n";
  	$html .= "<p class=buttons><input type='submit' name='save' value='Spara'/> <input type='reset' value='Återställ'/></p>\n";
  	$html .= "<p><a href='?'>" . ($this->IsAdmin() ? "Visa alla" : "Tillbaka till profil") . "</a></p>\n";
  
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
  	$id = isset($_POST['id'])    ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
  	$acronym = isset($_POST['acronym']) ? $_POST['acronym'] : null;
  	$name = isset($_POST['name'])  ? $_POST['name']  : null;
  	$image = isset($_POST['image'])  ? $_POST['image']  : null;
  	$type = isset($_POST['type'])  ? $_POST['type']  : 'USER';
  	$pw = isset($_POST['reset-pw'])  ? $_POST['reset-pw']  : null;
  	$password = isset($_POST['password'])  ? $_POST['password']  : null;
  	$salt = isset($_POST['salt'])  ? $_POST['salt']  : null;
  	
  	if($pw) {
  	  $sql = "UPDATE rm_user SET password = md5(concat(?, ?)) WHERE acronym = ?;";
  	  $params = array('0000', $salt, $acronym);
  	  $res = $this->db->ExecuteQuery($sql, $params);
  	  !$res ? header("Location: " . getCurrentUrl() . "&fail"): null;	
  	}
  	if($password) {
  		$sql = "UPDATE rm_user SET password = md5(concat(?, ?)) WHERE acronym = ?;";
  		$params = array($password, $salt, $acronym);
  		$res = $this->db->ExecuteQuery($sql, $params);
  		!$res ? header("Location: " . getCurrentUrl() . "&fail"): null;
  	}
  	
  	$image = CEditMovies::addFolder($image);
  
  	$sql = '
    UPDATE rm_user SET
      acronym = ?,
      name = ?,
      image = ?,
  	  type = ?
    WHERE
      id = ?
    ';
  	$params = array($acronym, $name, $image, $type, $id);
  	$res = $this->db->ExecuteQuery($sql, $params);
  	if($res) {
  		header("Location: " . getCurrentUrl() . "&success");
  	}
  	else {
  		header("Location: " . getCurrentUrl() . "&fail");
  	}
  }
  
  // Prints out HTML for the add-from
  public function printAndPostAdd() {
  	$html = "<h1>Lägg till ny användare</h1>\n";
  	$html .= "<form method='post' id='add'  enctype='multipart/form-data'>\n";
  	$html .= "<fieldset>\n";
  	$html .= "<legend>Lägg till</legend>\n";
  	$html .= "<p><label>Användarnamn:<br/><input type='text' name='acronym' required autocomplete='off'/></label></p>\n";
  	$html .= "<p><label>Namn:<br/><input type='text' name='name' required autocomplete='off'/></label></p>\n";
  	$html .= "<p><label>Lösenord:<br><input type=password name='password' id='password' required /></label></p>\n";
	$html .= "<p><label>Bekräfta lösenord:<br><input type=password name='confirm_password' id='confirm_password' required /> <span id='message'></span></label></p>\n";
  	$html .= "<p><label>Bildlänk:<br/><input type='text' class='link' name='image'/></label></p>\n";
  	$html .= "<p><label>Befogenheter:</label><br/>
  	<input type='radio' name='type' value='USER' required/> Användare
  	<br><input type='radio' name='type' value='ADMIN' required/> Administratör</p>\n";
  	$html .= "<p class=buttons><input type='submit' name='add' value='Lägg till'/> <input type='reset' value='Återställ'/></p>\n";
  	$html .= "<p><a href='?'>Visa alla</a></p>\n";
  	$html .= "<br></fieldset>\n";
  	$html .= "</form>\n";
  	
  	isset($_POST['add'])  ? $this->addEntry() : null;
  	
  	if(isset($_GET['fail'])) {
  		$html .= "<br><output class='error'>Informationen sparades EJ. ". (isset($_GET['error']) ? $_GET['error'] : null) ."</output><br>\n";
  	}
  
  	return $html;
  }
  
  // Saves the information from the form
  private function addEntry() {
  
  	// Get parameters
  	$acronym  = isset($_POST['acronym']) ? $_POST['acronym'] : null;
  	$name = isset($_POST['name'])  ? $_POST['name']  : null;
  	$password    = isset($_POST['password'])  ? $_POST['password']  : null;
  	$confirm   = isset($_POST['confim_password'])  ? $_POST['confim_password']  : null;
  	$image    = isset($_POST['image'])  ? $_POST['image']  : null;
  	$type     = isset($_POST['type'])  ? $_POST['type']  : null;
  
  	$image = CEditMovies::addFolder($image);
  	
  	if($password !== $confirm) {
  		header("Location: " . getCurrentUrl() . "&fail&error=Löseorden matchar inte.");
  	}
  	
  	$sql = "SELECT acronym FROM rm_user;";
  	$res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
  	$users = null;
  	foreach($res AS $val) {
  		$users[] = $val->acronym;
  	}
  	if(in_array($acronym, $users)) {
  		header("Location: " . getCurrentUrl() . "&fail&error=Användarnamnet finns redan.");
  	}
  	
  	$sql = "INSERT INTO rm_user (acronym, name, type, image, since, salt) VALUES
    (?, ?, ?, ?, NOW(), unix_timestamp());";
  	$params = array($acronym, $name, $type, $image);
  	$res = $this->db->ExecuteQuery($sql, $params);
  	if($res) {
  		$sql = "UPDATE rm_user SET password = md5(concat(?, salt)) WHERE acronym = ?;";
  		$params = array($password, $acronym);
  		$res = $this->db->ExecuteQuery($sql, $params);
  		if($res) {
  			header("Location: edit_users.php");
  		} else {
  			header("Location: " . getCurrentUrl() . "&fail");
  		}
  	} else {
  		header("Location: " . getCurrentUrl() . "&fail");
  	}
  }
  
  public function printProfile($acronym) {
  	$sql = "SELECT id, acronym, name, image, since FROM rm_user WHERE acronym = ?";
  	$res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($acronym));
  	$profile = $res[0];
  	$img = validateImage($profile->image, 'profile.png');
  	
    $html = "<figure class='top left'><img src='img.php?src={$img}&amp;width=200&amp;height=200&amp;crop-to-fit' alt='&zwnj;'/></figure>\n";
  	$html .= "<h2>{$profile->name}</h2>\n";
  	$html .= "<p><strong>Användarnam:</strong> {$profile->acronym}</p>\n";
  	$html .= "<p><strong>Medlem sedan:</strong> {$profile->since}</p>\n";
    $html .= "<br><p><a class='as-button' href='?id={$profile->id}'>Ändra profil</a> <a class='as-button' href='logout.php'>Logga ut</a></p>\n";
  	return $html;
  }
  
}
