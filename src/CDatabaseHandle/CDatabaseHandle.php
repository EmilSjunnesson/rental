<?php
class CDatabaseHandle {
	// Meny för navigation mellan sidor
	public function getPageNavigation($hits, $page, $max, $min=1) {
		$nav  = ($page != $min) ? "<a href='" . getQueryString(array('page' => $min)) . "'>&lt;&lt;</a> " : '&lt;&lt; ';
		$nav .= ($page > $min) ? "<a href='" . getQueryString(array('page' => ($page > $min ? $page - 1 : $min) )) . "'>&lt;</a> " : '&lt; ';
	
		for($i=$min; $i<=$max; $i++) {
			if($page == $i) {
				$nav .= "$i ";
			} else {
				$nav .= "<a href='" . getQueryString(array('page' => $i)) . "'>$i</a> ";
			}
		}
	
		$nav .= ($page < $max) ? "<a href='" . getQueryString(array('page' => ($page < $max ? $page + 1 : $max) )) . "'>&gt;</a> " : '&gt; ';
		$nav .= ($page != $max) ? "<a href='" . getQueryString(array('page' => $max)) . "'>&gt;&gt;</a> " : '&gt;&gt; ';
		return $nav;
	}
	
	// Meny för antal träffar
	public function getHitsPerPage($hits, $current=null) {
		$nav = "Träffar per sida: ";
		foreach($hits AS $val) {
			if($current == $val) {
				$nav .= "$val ";
			} else {
			$nav .= "<a class='hits' href='" . getQueryString(array('hits' => $val)) . "'>$val</a> ";
			}
			}
			return $nav;
	}
	
	// Function to create links for sorting
    public function orderby($column) {
	$nav  = "<a href='" . getQueryString(array('orderby'=>$column, 'order'=>'asc')) . "'>&#x25B2;</a>";
			$nav .= "<a href='" . getQueryString(array('orderby'=>$column, 'order'=>'desc')) . "'>&#x25BC;</a>";
					return "<span class='orderby'>" . $nav . "</span>";
    }
    
    /**
     * Create a breadcrumb
     *
     * @param string $home base text.
     * @param string $idName query variable.
     * @return string html with ul/li to display the thumbnail.
     */
    static function createBreadcrumb($home, $idName, $name) {
    	$breadcrumb = "<ul class='breadcrumb'>\n<li><a href='?'>{$home}</a> »</li>\n";
		if(isset($_GET[$idName])) {
    	  $breadcrumb .= "<li><a href='?{$idName}={$_GET[$idName]}'>{$name}</a> » </li>\n";
		} 
    	$breadcrumb .= "</ul>\n";
    	return $breadcrumb;
    }
}