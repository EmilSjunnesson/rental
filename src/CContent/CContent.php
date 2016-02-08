<?php
class CContent {
        
  protected $db = null;
  private $output = null;
  
  protected $id = null;
  protected $slug = null;
  protected $title = null;
  protected $data = null;
  protected $published = null;
  protected $created = null;
  protected $updated = null;
  protected $deleted = null;
  protected $updatedBy = null;
  protected $publishedBy = null;
  protected $category = null;
 
  // Fetches the database and user class
  public function __construct($db) {
    $this->db = $db;
  }
  
  // Gets the entry with the specified id
  public function getEntryByID($id) {
    $this->id = $id;
    $sql = 'SELECT * FROM rm_news WHERE id = ?';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($this->id));
    if(isset($res[0])) {
      $c = $res[0];
      $this->slug      = htmlentities($c->slug, null, 'UTF-8');
      $this->title     = htmlentities($c->title, null, 'UTF-8');
      $this->data      = htmlentities($c->data, null, 'UTF-8');
      $this->published = htmlentities($c->published, null, 'UTF-8');
      $this->created   = htmlentities($c->created, null, 'UTF-8');
      $this->updated   = htmlentities($c->updated, null, 'UTF-8');
      $this->deleted   = htmlentities($c->deleted, null, 'UTF-8');
      $this->updatedBy = htmlentities($c->updatedBy, null, 'UTF-8');
      $this->publishedBy = htmlentities($c->publishedBy, null, 'UTF-8');
      $this->category = htmlentities($c->category, null, 'UTF-8');
    } else {
      header("Location: 404.php?error=No entry with that id");      
    }
  }
 
  // Return a list with all entries in the content table
  public function getAllAsList() {
    $sql = 'SELECT *, (published <= NOW()) AS available FROM rm_news;';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);

    $html = '<ul>';
    foreach($res AS $val) {
      $html .= "<li>" . htmlentities($val->title, null, 'UTF-8') . ": är" . (!$val->available ? ' inte' : null) . " publicerad ";
      if(CUser::IsAdmin()) {
        $html .= "(<a href='?id={$val->id}'>editera</a> | <a href='" . (!$val->available ? "?publish={$val->id}" : "news.php?slug={$val->slug}") . "'>" . (!$val->available ? 'publisera' : 'visa') . "</a> | <a href='?delete={$val->id}'>ta bort</a>)</li>";
      } else {
        $html .= $val->available ? "(<a href='news.php?slug={$val->slug}'>visa</a>)</li>" : null;      
      }
    }
    $html .= '</ul>';
    return $html;
  }
  
  // Prints out HTML for the update-from
  public function printAndPostUpdate() {
    $html = "<h1>Uppdatera innehåll med id: {$this->id}</h1>\n";
    $html .= "<form method=post>\n";
    $html .= "<fieldset>\n";
    $html .= "<legend>Uppdatera innehåll</legend>\n";
    $html .= "<input type='hidden' name='id' value='{$this->id}'/>\n";
    $html .= "<input type='hidden' name='updatedBy' value='{$this->updatedBy}'/>\n";
    $html .= "<input type='hidden' name='publishedBy' value='{$this->publishedBy}'/>\n";
    $html .= "<p><label>Titel:<br/><input type='text' name='title' value='{$this->title}'/></label></p>\n";
    $html .= "<p><label>Slug:<br/><input type='text' name='slug' value='{$this->slug}' required/></label></p>\n";
    $html .= "<p><label>Kategori:<br/><input type='text' list='cats' name='category' value='{$this->category}' autocomplete='off'/></label></p>\n";   
    $html .= $this->getCategoryList();   
    $html .= "<p><label>Text:<br/><textarea name='data'>{$this->data}</textarea></label></p>\n";
    $html .= "<p><label>Publiseringsdatum:<br/><input type='text' name='published' value='{$this->published}'/></label></p>\n";
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
    $id     = isset($_POST['id'])    ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
    $title  = isset($_POST['title']) ? $_POST['title'] : null;
    $slug   = isset($_POST['slug'])  ? $_POST['slug']  : null;
    $data   = isset($_POST['data'])  ? $_POST['data'] : array();
    $published = isset($_POST['published'])  ? strip_tags($_POST['published']) : array();  
    $updatedBy = strip_tags(CUser::GetName());
    $publishedBy = isset($_POST['publishedBy']) ? $_POST['publishedBy'] : null;
    $category = isset($_POST['category']) ? $_POST['category'] : null;
    if(empty($published)) {
      $published = null;
    }
    $sql = '
    UPDATE rm_news SET
      title   = ?,
      slug    = ?,
      data    = ?,
      published = ?,
      updated = NOW(),
      updatedBy = ?,
      publishedBy = ?,
      category = ?
    WHERE 
      id = ?
    ';
    $slug = empty($slug) ? null : $this->slugify($slug);
    $category = empty($category) ? null : $this->slugify($category);
    $params = array($title, $slug, $data, $published, $updatedBy, $publishedBy, $category, $id);
    $res = $this->db->ExecuteQuery($sql, $params);
    if($res) {
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
    $sql = 'DELETE FROM rm_news WHERE id = ? LIMIT 1';
    $res = $this->db->ExecuteQuery($sql, array($this->id));
    if($res) {
      $this->output = "Innehållet raderades.";
    }  
  }
  
  // Unpublished the entry but keeps it in the database
  private function unpublishEntry() {
    $sql = 'UPDATE rm_news SET published = ?, deleted = NOW() WHERE id = ?';
    $params = array(null, $this->id);
    $res = $this->db->ExecuteQuery($sql, $params);  
    if($res) {
      $this->output = "Innehållet opublicerades.";
    }  
  }
  
  // Republishes the entry
  public function publish($id) {
    $sql = 'UPDATE rm_news SET published = NOW(), deleted = ?, publishedBy = ? WHERE id = ?';
    $params = array(null, CUser::GetName(), $id);
    $res = $this->db->ExecuteQuery($sql, $params);
  }
  
  // Prints out HTML for the add-from
  public function printAndPostAdd() {
    $html = "<h1>Lägg till nytt inlägg</h1>\n";
    $html .= "<form method=post>\n";
    $html .= "<fieldset>\n";
    $html .= "<legend>Lägg till</legend>\n";
    $html .= "<input type='hidden' name='updatedBy' value='{$this->updatedBy}'/>\n";
    $html .= "<input type='hidden' name='publishedBy' value='{$this->publishedBy}'/>\n";
    $html .= "<p><label>Titel:<br/><input type='text' name='title' required /></label></p>\n";
    $html .= "<p><label>Slug:<br/><input type='text' name='slug' required /></label></p>\n";
    $html .= "<p><label>Kategori:<br/><input type='text' list='cats' name='category' autocomplete='off'/></label></p>\n";   
    $html .= $this->getCategoryList();   
    $html .= "<p><label>Text:<br/><textarea name='data'></textarea></label></p>\n";
    $html .= "<p><label>Publiseringsdatum:<br/><input type='text' name='published'/></label></p>\n";
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
    $slug   = isset($_POST['slug'])  ? $_POST['slug']  : null;
    $data   = isset($_POST['data'])  ? $_POST['data'] : array();
    $published = isset($_POST['published'])  ? strip_tags($_POST['published']) : array();
    $updatedBy = isset($_POST['updatedBy']) ? $_POST['updatedBy'] : null;
    $publishedBy = strip_tags(CUser::GetName());
    $category = isset($_POST['category']) ? $_POST['category'] : null;
    if(empty($published)) {
      $published = null;
    }
    $sql = '
    INSERT INTO rm_news (slug, title, data, published, created, updatedBy, publishedBy, category) 
    VALUES(?,?,?,?,NOW(),?,?,?)
    ';
    $slug = empty($slug) ? null : $this->slugify($slug);
    $category = empty($category) ? null : $this->slugify($category);
    $params = array($slug, $title, $data, $published, $updatedBy, $publishedBy, $category);
    $this->db->ExecuteQuery($sql, $params);
    header("Location: edit_news.php");      
  }
  
  //Gets categorys from the database
  private function getCategoryList() {
    $html = "<datalist id='cats'>\n";
    $sql = "SELECT DISTINCT category FROM rm_news;";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    foreach($res AS $cat) {
      $html .= "<option value='{$cat->category}'>";        
    }
    $html .= "</datalist>\n";  
    return $html;
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
  protected  static function slugify($str) {
    $str = mb_strtolower(trim($str));
    $str = str_replace(array('&aring;','&auml;','&ouml;'), array('a','a','o'), $str);
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = trim(preg_replace('/-+/', '-', $str), '-');
    return $str;
  }
}
