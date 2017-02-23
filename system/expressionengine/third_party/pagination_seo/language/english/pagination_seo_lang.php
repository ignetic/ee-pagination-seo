<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$lang = array(
	'pagination_seo_description' => 'Pagination SEO makes it simple to place rel next/prev links within paginated pages. Simply place the tags within the head of the HTML to output the rel links.',

	'available_tags' => 'Available Tags',

	'page_num_title' => 'Page numbers in title tag:<br><em>Available tags: {page_num} {total_pages} {total_items}</em>',
	'page_num_description' => 'Page numbers in description tag:<br><em>Available tags: {page_num} {total_pages} {total_items}</em>',
	
	'display_on_first_page' => 'Display page numbers within the title and description on the first paginated page?<br><em>Enable this to show the Page Numbers on the first paginated page</em>',
	'enable_redirect' => 'Enable redirect to first page where pagination is not found?<br><em>This will help avoid out-of-range pagination problems which automatically redirects to the first page</em>',
	'use_caching' => 'Enable caching support (experimental)?<br><em>Enable this if the tags don\'t display when caching is enabled (e.g. with CE Cache)</em>',
	
	'pagination_seo_rel_links_tag' => 'Place these tag in the head of the html. This will output for an example',
	'pagination_seo_urls_tag' => 'This will output just the URLs',
	'pagination_seo_uris_tag' => 'This will output just the URIs',
	'pagination_seo_nums_tag' => 'This will output just the page numbers',
	'pagination_seo_title_tag' => 'Place this within the Title tag to appand/prepend your default title. <br>Edit the "<em>Page numbers in title tag</em>" preference below to customise the output.',
	'pagination_seo_description_tag' => 'Place this within the Meta Description tag to appand/prepend your default description. <br>Edit the "<em>Page numbers in description tag</em>" preference below to customise the output.',
	'pagination_seo_vars_tag' => 'This will output additional values as page numbers, total pages and total items within the paginated page',
	
);

/* End of file pagination_seo_lang.php */
/* Location: /system/expressionengine/third_party/ab_pagination/language/english/pagination_seo_lang.php */
