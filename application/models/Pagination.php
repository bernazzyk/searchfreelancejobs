<?php
class Application_Model_Pagination extends Application_Model_Freelancer
{
	protected $PerPage = 25;
	
	public function getPerPage()
	{
		return $this->PerPage;
	}
	
	public function getCurrentPage($page)
	{
		$p = !empty($page) ? (int) $page : 1; //
		$CurrentPage = (int) $p;
			
		if($CurrentPage == 0) {
			$CurrentPage = 1;
		}
		return $CurrentPage;
	}
	
	public function getFrom($CurrentPage)
	{
		return $this->PerPage * ($CurrentPage  - 1);
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
	
	public function showPagination($total_pages, $current_page, $UrlOfPage, $UrlAppendix = '') {
		
		$url = $UrlOfPage . $UrlAppendix . '/p/';
		
		return $this->show($current_page, $this->PerPage, $total_pages, $url);
	}
	
	public function show($current_page, $per_page, $total_pages, $url) {
		$pervpage = '';
		$nextpage = '';
		
		if($current_page != 1) {
			$pervpage = '<a href="'.$url.'1"> first </a> <a href="'.$url.($current_page - 1).'"> previous </a> ';
		}
		
		if($current_page != $total_pages) {
			$nextpage = ' <a href="'.$url.($current_page + 1).'"> next </a> <a href="'.$url.$total_pages.'"> last </a>';
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
	}
	
}