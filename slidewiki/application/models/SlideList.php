<?php


class SlideList extends Model {
	public $slides;
	const slides_per_page=12;
	const max_page_links=10;
        public $pager_code;
        public $languages = array();
        public $current;
        public $own = false;
	function __construct(){
		$slides = array();
		
		// connect to db
		if( $this->connect(DB_DSN, DB_USER, DB_PASSWORD) == 0 )
			die ("Could not connect to db");
	}
		
	public function pager ($total,$page) {
            $first = 1;			
            $max_count=$this::max_page_links;
            $pages_count = ceil($total/$this::slides_per_page);
            if ($this->own){
                $function = 'submitSearchOwn';
            }else{
                $function = 'submitSearch';
            }
            if ($pages_count > 1) { //if we need a pager
                $this->pager_code.= '<div class="pager">';
                if ($page > $max_count) {
                    $first = $page;
                    $pre_first = $first - 1;
                    $this->pager_code.= '<a href="javascript:'.$function.'(\'slide\',\'1\')"><span class="pager"><<</span></a>';				
                }
                if ($page > $max_count + 1) {
                    $this->pager_code.= '<a href="javascript:'.$function.'(\'slide\','.$pre_first.')"><span class="pager"><</span></a>';
                }
                $last = $first + $max_count - 1;
                $pos_last = $last + 1;
                for ($i = $first; $i <= $pages_count && $i <= $last; $i++) {
                    $class = 'pager';
                    if ($i == $page) $class .= ' current';
                    $this->pager_code .= '<a href="javascript:'.$function.'(\'slide\','.$i.')"><span class="'.$class.'">'.$i.'</span></a>';
                }
                if ($max_count + $first - 2 < $pages_count) {
                    $this->pager_code .= '<a href="javascript:'.$function.'(\'slide\','.$pos_last.')"><span class="pager">></span></a>';
                }
                if ($max_count + $first - 1 < $pages_count) {
                    $this->pager_code.= '<a href="javascript:'.$function.'(\'slide\','.$pages_count.')"><span class="pager">>></span></a>';
                }
                $this->pager_code.= '</div>';
            }
	}
        
        public function createLanguageList($keywords, $current = false, $user_id = false){
            $languages = array();
            if (strlen($keywords) > 2){
                if ($this->own){
                    $languages = $this->dbGetCol('SELECT slide.language FROM slide_revision JOIN slide ON slide_revision.slide=slide.id WHERE slide_revision.user_id=\''.$user_id.'\' AND MATCH(content) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) GROUP BY `slide` ');
                }else{
                    $languages = $this->dbGetCol('SELECT slide.language FROM slide_revision JOIN slide ON slide_revision.slide=slide.id WHERE MATCH(content) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) GROUP BY `slide` ');
                }
            }else{
                if ($this->own){
                    $languages = $this->dbGetCol('SELECT slide.language FROM slide_revision JOIN slide ON slide_revision.slide=slide.id WHERE slide_revision.user_id=\''.$user_id.'\' GROUP BY `slide` ');
                }else{
                    $languages = $this->dbGetCol('SELECT slide.language FROM slide_revision JOIN slide ON slide_revision.slide=slide.id GROUP BY `slide` ');
                }
            }
            
            if ($languages){
                $result_language = array();
                $languages_arr = array_unique(array_filter($languages));
                $key = array_search('-', $languages_arr); //delete empty languages
                if ($key !== false){
                    unset($languages_arr[$key]);
                }
                foreach ($languages_arr as $language){ //take the language name 
                    $result_language['db'] = $language;
                    $tmp_array = array();
                    $tmp_array = explode('-',$language);
                    $result_language['name'] = $tmp_array['1'];
                    $this->languages[] = $result_language;
                }
                if (!$current){ //show all the slides if !current
                    $current = 'all languages';
                }else{ //adding 'all languages' to the list and removing current from the list
                    $all_array = array();
                    $all_array['db'] = 'all languages';
                    $all_array['name'] = 'all languages';
                    $this->languages[] = $all_array;
                    $current_arr = explode('-', $current);
                    $current = $current_arr['1'];
                    foreach($this->languages as $key => $language){
                        if ($language['name'] == $current){
                            unset($this->languages[$key]);
                        }
                    }
                }
                $this->current = $current;
            }
        }
	public function searchMatch($keywords, $order, $page, $user_id = false, $language = false) {
            $query='';
            $result = array();
            
            switch ($order) {			
                    case 'date' : $order_db='`timestamp` DESC '; break;
                    case 'title' : $order_db='`content` ASC'; break;
                    case 'popularity' : $order_db='`popularity` DESC'; break;
                    default: $order_db='`timestamp` DESC';		
            }
            
            //create languages list
            if ($language == 'all languages') $language = false;
            $this->createLanguageList($keywords, $language, $user_id);
            
            //search for slides
            if ($this->own){ //search only own slides
                //unpaged
                if (!$language){
                    if (strlen ( $keywords ) > 2){
                        $query =  'SELECT * FROM slide_revision JOIN slide ON slide_revision.slide=slide.id WHERE slide_revision.user_id=:user_id AND MATCH(content) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) GROUP BY `slide` ORDER BY '. $order_db  ;
                    }else{
                        $query =  'SELECT * FROM slide_revision JOIN slide ON slide_revision.slide=slide.id WHERE slide_revision.user_id=:user_id GROUP BY `slide` ORDER BY '. $order_db  ;
                    }
                    $result = $this->dbQuery ($query,array('user_id' => $user_id));
                }else{
                    if (strlen ( $keywords ) > 2){
                        $query =  'SELECT * FROM slide_revision JOIN slide ON slide_revision.slide=slide.id WHERE slide_revision.user_id=:user_id AND slide.language=:language AND MATCH(content) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) GROUP BY `slide` ORDER BY '. $order_db  ;
                    }else{
                        $query =  'SELECT * FROM slide_revision JOIN slide ON slide_revision.slide=slide.id WHERE slide_revision.user_id=:user_id AND slide.language=:language GROUP BY `slide` ORDER BY '. $order_db  ;
                    }
                    $result = $this->dbQuery ($query,array('language'=>$language,'user_id' => $user_id));
                }
                $total = count($result);
                //paged
                if ($page>1)
                    $query = $query. ' LIMIT '. strval(($page-1)*$this::slides_per_page) .','. strval($this::slides_per_page) ;	
                else 
                    $query = $query. ' LIMIT 0 ,'. strval($this::slides_per_page) ;
                if (!$language){
                    $result = $this->dbQuery ($query,array('user_id' => $user_id));
                }else{
                    $result = $this->dbQuery ($query,array('language'=>$language,'user_id' => $user_id));
                }
                
            }else{ //search slides of all users
                //unpaged
                if (!$language){
                    if (strlen ( $keywords ) > 2) {
                        $query =  'SELECT * FROM slide_revision JOIN slide ON slide_revision.slide=slide.id WHERE MATCH(content) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) GROUP BY `slide` ORDER BY '. $order_db  ;
                    } else {
                        $query =  'SELECT * FROM slide_revision JOIN slide ON slide_revision.slide=slide.id GROUP BY `slide` ORDER BY '. $order_db  ;
                    }
                    $result = $this->dbQuery ($query);
                }else{
                    if (strlen ( $keywords ) > 2) {
                        $query =  'SELECT * FROM slide_revision JOIN slide ON slide_revision.slide=slide.id WHERE slide.language=:language AND MATCH(content) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) GROUP BY `slide` ORDER BY '. $order_db  ;
                    } else {
                        $query =  'SELECT * FROM slide_revision JOIN slide ON slide_revision.slide=slide.id WHERE slide.language=:language GROUP BY `slide` ORDER BY '. $order_db  ;
                    }
                    $result = $this->dbQuery ($query,array('language'=>$language));
                }

                $total = count($result);
                //paged
                if ($page>1)
                    $query = $query. ' LIMIT '. strval(($page-1)*$this::slides_per_page) .','. strval($this::slides_per_page) ;	
                else 
                    $query = $query. ' LIMIT 0 ,'. strval($this::slides_per_page) ;
                if (!$language){
                    $result = $this->dbQuery ($query);
                }else{
                    $result = $this->dbQuery ($query,array('language'=>$language));
                }
            }
            $this->pager($total,$page);
            return $result;
	}
}
