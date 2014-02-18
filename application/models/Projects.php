<?php

class Application_Model_Projects extends Application_Model_Freelancer
{
    public function recent()
    {
        return 'hello';
    }
	
	//$request = $this->getRequest();
	//$page = $request->getParam('p');
	
	
	/*---------------------------------PAGINATION-------------------------------------*/
	/*public function showPagination($total_pages, $UrlOfPage, $UrlAppendix = '') {
		$page_get_sufix = ($UrlAppendix != '')? '&' : '?';
		
		$url = FOLDER . '/' . $this->LangUrl . $UrlOfPage . $UrlAppendix . $page_get_sufix . 'p=';
		
		return $this->show($this->CurrentPage, $this->PerPage, $total_pages, $url);
	}
		
	public function countPages($total_count, $per_page) {
		if ($total_count % $per_page == 0)
			$count = $total_count / $per_page;
		elseif ($total_count % $per_page != 0) {
			$count = intVal($total_count / $per_page);
			$count += 1;
		}
		return $count;
	}
	
	public static function show($current_page, $per_page, $total_pages, $url) {
		$pervpage = '';
		$nextpage = '';
		
		if($current_page != 1) {
			$pervpage = '<a href="'.$url.'1"> '. FIRST_TXT .' </a> <a href="'.$url.($current_page - 1).'"> '. PREVIOUS_TXT .' </a> ';
		}
		
		if($current_page != $total_pages) {
			$nextpage = ' <a href="'.$url.($current_page + 1).'"> '. NEXT_TXT .' </a> <a href="'.$url.$total_pages.'"> '. LAST_TXT .' </a>';
		}
		
		$pages = '';
		$j = 0;
		for( $i = 0; $i <= ($j + 10); $i++ ) {
			if($current_page - 5 + $i > 0 && $current_page - 5 + $i <= $total_pages) {
				if( $current_page == ($current_page - 5 + $i) ) {
					$pages .= ' <a class="active">'.($current_page - 5 + $i ).'</a> ';
				} else {
					$pages .= ' <a href="'.$url.($current_page - 5 + $i).'">'.($current_page - 5 + $i).'</a> ';
				}
			} else if( ($current_page - 5 + $i) <= $total_pages ) {
				$j++;
			}
		}
		
		$pagination = '';
		if($total_pages > 1) {
			$pagination .= $pervpage . $pages . $nextpage;
		} else {
//			$pagination .= '<a class="active">'.$current_page.'</a>';
		}
		
		return $pagination;
	}*/
	
	
}
