<?php
class CBlog extends CContent {
        
  private $posts = null;
  private $longText = false;
  private $shot = true;
  private $shot1 = true;
 
  // Initialize parent
  public function __construct($db) {
    parent::__construct($db);
  }
  
  public static function homeNews($db) {
  	$sql = "SELECT * FROM rm_news WHERE published <= NOW() ORDER BY IFNULL(updated,published) DESC LIMIT 3; ";
  	$res = $db->ExecuteSelectQueryAndFetchAll($sql);
  	$html = null;
  	foreach($res AS $val) {  
		
  		$title = htmlentities($val->title, null, 'UTF-8');
  		$data = CTextFilter::doAllFilters(htmlentities($val->data, null, 'UTF-8'));
  		$slug = htmlentities($val->slug, null, 'UTF-8');
  		$published = htmlentities($val->published, null, 'UTF-8');
  		$updated = htmlentities($val->updated, null, 'UTF-8');
  		$publishedBy = htmlentities($val->publishedBy, null, 'UTF-8');
  		$updatedBy = htmlentities($val->updatedBy, null, 'UTF-8');
  		$category = htmlentities($val->category, null, 'UTF-8');
  		
  		$data = shortenText($data, "news.php?slug={$slug}");
  		
  		$html .= "<section class='blog-post'>\n";
  		$html .= "<article>\n";
  		$html .= "<header>\n";
  		$html .= "<h2><a href='news.php?slug={$slug}'>{$val->title}</a></h2>\n";
  		$html .= "</header>\n";
  		$html .= "<p class='cat'>Kategori: " . (empty($category) ? "<a href='news.php?cat=ovrigt'>övrigt</a>" : "<a href='news.php?cat={$category}'>$category</a>") . "</p>\n";
  		$html .= "<div>\n";
  		$html .= $data . "\n";
  		$html .= "</div>\n";
  		$html .= "<footer>\n";
  		$html .= "<p>" . ($updated ? "Uppdaterad: {$updated} av {$updatedBy}<br>" : null) . "Publicerad: {$published} av {$publishedBy}</p>\n";
  		$html .= "</footer>\n";
  		$html .= "</article>\n";
  		$html .= "</section>\n";
  	}
  	return $html;
  }
  
  // Checks if there is any posts available
  public function postsExists() {    
    if(isset($this->posts)) {      
      if(count($this->posts) > 0) {
        return true;      
      } else {
        return false;      
      }
    }
  }
  
  // Checks if there is more than one post
  public function postsMulti() {
    if(count($this->posts) > 1) {
      return true;      
    } else {
      return false;      
    }
  }
  
  // Gets blog array based on slug
  public function getPostsFromSlug($slug, $cat = null) {
    $this->longText = $slug ? false : true;
    $slugSql = $slug ? 'slug = ?' : '1';
    if($cat == null) {
      $sql = "SELECT *
      FROM rm_news
      WHERE
      $slugSql AND
      published <= NOW()
      ORDER BY IFNULL(updated,published) DESC
      ;";
      $this->posts = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($slug));
    } else {
      if($cat == 'ovrigt') {
        $cat = '';      
      }
      $sql = "SELECT *
      FROM rm_news
      WHERE
      category = ? AND
      published <= NOW()
      ORDER BY IFNULL(updated,published) DESC
      ;";
      $this->posts = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($cat));      
    }
  }
  
  // Prints out HTML displaying the posts
  public function printPosts() {
          
    $html = "<h1>Nyheter</h1>\n";
    
    foreach($this->posts as $post) {
      $title = htmlentities($post->title, null, 'UTF-8');
      $data = CTextFilter::doAllFilters(htmlentities($post->data, null, 'UTF-8'));
      $slug = htmlentities($post->slug, null, 'UTF-8');
      $published = htmlentities($post->published, null, 'UTF-8');
      $updated = htmlentities($post->updated, null, 'UTF-8');
      $publishedBy = htmlentities($post->publishedBy, null, 'UTF-8');
      $updatedBy = htmlentities($post->updatedBy, null, 'UTF-8');
      $category = htmlentities($post->category, null, 'UTF-8');
      
      // Shorten text in "alla" view
      if($this->longText) {
        $data = shortenText($data, "news.php?slug={$slug}");      
      }
      
      if($this->shot1) {
        $html .= CDatabaseHandle::createBreadcrumb('Nyheter', 'slug', $title);
        if($this->longText) {
        	$html .= $this->getCategorys();
        }
        $this->shot1 = false;
      }
      
      $html .= "<section class='blog-post'>\n";
      $html .= "<article>\n";
      $html .= "<header>\n";
      $html .= "<h2>" . ($this->longText ? "<a href='news.php?slug={$slug}'>{$title}</a>" : "$title <a title='Tillbaka till alla poster' href='news.php'>&#8617;</a>") .  "</h2>\n";
      $html .= "</header>\n";
      $html .= "<p class='cat'>Kategori: " . (empty($category) ? "<a href='news.php?cat=ovrigt'>övrigt</a>" : "<a href='news.php?cat={$category}'>$category</a>") . "</p>\n";
      $html .= "<div " . ($this->longText ? null : "style='min-height: 200px;'") . ">\n";
      $html .= $data. "\n";
      $html .= "</div>\n";
      $html .= "<footer>\n";
      $html .= "<p>" . ($updated ? "Uppdaterad: {$updated} av {$updatedBy}<br>" : null) . "Publicerad: {$published} av {$publishedBy}</p>\n";
      $html .= "</footer>\n";
      $html .= "</article>\n";
      $html .= "</section>\n";
    }  
    return $html;    
  }
  
  public function getBlogNav() {
    $this->getPostsFromSlug(null);
    $navArray = array();
    foreach($this->posts AS $post) {     
      $navArray[$post->id] = array('text'=>$post->title, 'url'=> 'blog.php?slug=' . $post->url, 'title' => 'Blogginlägg: ' . $post->title);      
    }
    return $navArray;        
  }
  
  private function getCategorys() {
    $sql = "SELECT DISTINCT category FROM rm_news;";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    $html = "<p>Kategori: <a class='as-button' href='news.php'>alla</a> ";
    foreach($res AS $cat) {
      if($cat->category != null) {      
        $html .= "<a class='as-button' href='news.php?cat={$cat->category}'>{$cat->category}</a> ";      
      } elseif($this->shot) {
        $html .= "<a class='as-button' href='news.php?cat=ovrigt'>övrigt</a></p>\n";  
        $this->shot = false;    
      }
    }
    return $html;      
  }
  
}
