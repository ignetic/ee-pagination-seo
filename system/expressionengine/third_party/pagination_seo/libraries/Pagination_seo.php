<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pagination_seo {

	public $settings = array(
		'prev_uri' => null,
		'next_uri' => null,
		'page_num' => null,
		'total_pages' => null,
		'total_items' => null,
		'title' => null,
		'description' => null,
	); 
	public $prev; 
	public $next; 
	public $page_num; 
	public $total_pages; 
	public $total_items; 
	public $title; 
	public $description; 

}


?>