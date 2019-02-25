<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Pagination SEO Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Simon Andersohn
 * @link		https://github.com/ignetic
 * @ee_version	2.4.0 >
 */

class Pagination_seo_ext {
	
	public $settings 		= array();
	public $description		= 'Enables rel links and page numbering on paginated pages for SEO';
	public $docs_url		= 'https://github.com/ignetic/ee-pagination-seo';
	public $name			= 'Pagination SEO';
	public $settings_exist	= 'y';
	public $version			= '1.4.1';
	
	private $pagination = array();
	
	private $defaults = array(
		'page_num_title' => ' - Page {page_num}',
		'page_num_description' => 'Page {page_num} of {total_pages} ({total_items} items) for ',
		'display_on_first_page' => 'n',
		'enable_redirect' => 'n',
		'use_caching' => 'n',
	);
	
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->settings = $settings;
		
		$this->enable_redirect = FALSE;
		if (isset($this->settings['enable_redirect']) && $this->settings['enable_redirect'] == 'y')
		{
			$this->enable_redirect = TRUE;
		}
		
	}
	// ----------------------------------------------------------------------

	
	/**
	 * Activate Extension
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'pagination_create',
			'hook'		=> 'pagination_create',
			'settings'	=> serialize($this->settings),
			'priority' => 10,
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);
		
		if (version_compare(APP_VER, '2.7', '<'))
		{
			$data['hook'] = 'channel_module_create_pagination';
		}

		ee()->db->insert('extensions', $data);
				
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'template_post_parse',
			'hook'		=> 'template_post_parse',
			'settings'	=> serialize($this->settings),
			'priority' => 99,
			'version'	=> $this->version,
			'enabled'	=> 'y',
		);

		ee()->db->insert('extensions', $data);
		
	}	
	// ----------------------------------------------------------------------

	
	/**
	 * pagination_create
	 *
	 * @param $data, $count
	 * @return void
	 */
	public function pagination_create($data, $count)
	{
		$settings = (array_merge($this->defaults, $this->settings));
		
		$params = ee()->TMPL->tagparams;

		$total_items = isset($data->total_items) ? $data->total_items : 0;
		$per_page = $data->per_page;
		$total_pages = (int) ceil($total_items / $per_page);
		$offset = $data->offset;
		$prefix = isset($data->prefix) ? $data->prefix : 'P';
		
		$segment_array = ee()->uri->segment_array();
		$segment_count = count($segment_array);
		$last_segment = end($segment_array);
		$current_url = implode('/', $segment_array);

		// Let's check to see if Better Pagination extension is installed and active and support this
		// Unable to use sql queries which conflicts with comments module query caching
		//$extensions  = ee()->addons->get_installed('extensions');
		//if (isset($extensions['better_pagination']))

		$sql  = "SELECT version FROM ".ee()->db->dbprefix."extensions WHERE class = 'Better_pagination_ext' AND enabled = 'y' LIMIT 1";
		$query = ee()->db->query($sql);
		if ($query->num_rows > 0)
		{
			// Use Better Pagination
			$config = ee()->config->item('better_pagination');
			$prefix = 'page';
			if (isset($config['page_name']))
			{
				$prefix = $config['page_name'];
			}
			$prefix_uri = '?'.$prefix.'=';
			
			// Redirect invalid paginated urls
			if ($this->enable_redirect == TRUE)
			{
				// Let's redirect to querystring pagination if default ee P# pagination is found
				if (preg_match('/'.$data->prefix.'\d+/', $last_segment))
				{
					$redirect_offset = (int) substr($last_segment,1);
					unset($segment_array[$segment_count]);
					$redirect = implode('/', $segment_array);
					if ($redirect_offset > 0)
					{
						$redirect .= $prefix_uri.$redirect_offset;
					}
					header("HTTP/1.1 301 Moved Permanently"); 
					header( "Location: /".$redirect );
					die();
				}
				// Redirect if pagination is 0
				if ($offset == 0 && ee()->input->get($prefix) === '0')
				{
					$redirect = implode('/', $segment_array);
					header("HTTP/1.1 301 Moved Permanently"); 
					header( "Location: /".$redirect );
					die();
				}	
			}			
		}
		else
		{
			if (preg_match('/'.$prefix.'\d+/', $last_segment))
			{
				// make sure we have an offset - not always available
				if ($offset == 0)
				{
					$offset = (int) substr($last_segment,1);
				}
				unset($segment_array[$segment_count]);
			}
			$prefix_uri = '/'.$prefix;
		}

		
		// Use library to store these pagination settings later (unable to store via session cache or global variables)
		ee()->load->library('pagination_seo');


		// Use pagination base url if set
		if (isset($params['paginate_base']) && !empty($params['paginate_base']))
		{
			$url = $params['paginate_base'];
		}
		else
		{
			$url = implode('/', $segment_array);
		}

		if ($offset > 0 && $total_items > 0 && $offset < $total_items)
		{
			ee()->pagination_seo->settings['prev_uri'] = $url.($offset-$per_page>0 ? $prefix_uri.($offset-$per_page) : '');
			if ($offset-$per_page > 0) ee()->pagination_seo->settings['prev_num'] = $offset-$per_page;
		}
		
		if ($total_items > 0 && $offset < $total_items-$per_page)
		{
			ee()->pagination_seo->settings['next_uri'] = $url.$prefix_uri.($offset+$per_page);
			ee()->pagination_seo->settings['next_num'] = $offset+$per_page;
		}
		
		
		// Page number_format
		$page_num = ($offset/$per_page)+1;

		
		// Diplay on first page?
		$display_on_first_page = FALSE;
		
		if ($page_num == 1 && isset($settings['display_on_first_page']) && $settings['display_on_first_page'] == 'y')
		{
			$display_on_first_page = TRUE;
		}
		

		// Title and Description
		$title = '';
		$description = '';

		if (isset($settings['page_num_title']) && !empty($settings['page_num_title']))
		{
			$title = $settings['page_num_title'];
		}
		if (isset($settings['page_num_description']) && !empty($settings['page_num_description']))
		{
			$description = $settings['page_num_description'];
		}

		$title = str_replace(array('{page_num}', '{total_pages}', '{total_items}'), array($page_num, $total_pages, $total_items), $title);
		$description = str_replace(array('{page_num}', '{total_pages}', '{total_items}'), array($page_num, $total_pages, $total_items), $description);
		
		
		// Store vars
		ee()->pagination_seo->settings['page_num'] = $page_num;
		ee()->pagination_seo->settings['total_pages'] = $total_pages;
		ee()->pagination_seo->settings['total_items'] = $total_items;

		if ($page_num > 1 || $display_on_first_page == TRUE)
		{
			ee()->pagination_seo->settings['title'] = $title;
			ee()->pagination_seo->settings['description'] = $description;
		}
		
		// Only redirect if it isn't an ajax request
		$is_ajax = ee()->input->is_ajax_request();

		// Redirect to main page if pagination not found
		if (!$is_ajax && $this->enable_redirect == TRUE)
		{
			// check if page number fits within per_page value and if it is within the total number of item
			// otherwise redirect to firt page
			if(is_float($page_num) || ($offset > 0 && $total_items > 0 && $offset >= $total_items && $url != $current_url))
			{
				header( "HTTP/1.1 301 Moved Permanently" );
				header( "Location: /".$url );
				die();
			}
		}
		
		// An attempt to store pagination info somewhere when the pagination has been cached.
		// When cached it is not normally possible to get pagination info - doesn't get called
		// So try storing the link urls as 'tag cache' within the pagination so we can grab later
		if (isset($settings['use_caching']) && $settings['use_caching'] == 'y')
		{
			// Template data may be in an array (seems like older versions of EE aren't)
			$first_key = '';
				
			$template_data = $data->__get('template_data');
			if (is_array($data->template_data))
			{
				$first_key = @key($template_data);
			}

			$tag_cache = '';
			
			foreach (ee()->pagination_seo->settings as $key => $val)
			{
				if ($val) $tag_cache .= '{pagination_seo:set:'.$key.'='.$val.'}';			
			}

			
			// Escape tag if CE Cache is used, otherwise it will display the unparsed tag
			// Remove the following otherwise the query caches within pagination
			//$query = ee()->db->select('module_version')->from('modules')->where('module_name', 'ce_cache')->limit(1)->get();
			$sql  = "SELECT module_version FROM ".ee()->db->dbprefix."modules WHERE module_name = 'ce_cache' LIMIT 1";
			$query = ee()->db->query($sql);
			if ($query->num_rows > 0)
			{
				$tag_cache = '{exp:ce_cache:escape:pagination_seo}'.$tag_cache.'{/exp:ce_cache:escape:pagination_seo}';
			}
			$query->free_result();

			// Write back to pagination template
			if (!empty($first_key))
			{
				$template_data[$first_key] .= $tag_cache;
			}
			else
			{
				$template_data .= $tag_cache;
			}
			$data->__set('template_data', $template_data);
	
		}
		
	}
	// ----------------------------------------------------------------------

	
	/**
	 * pagination_create
	 *
	 * @param $final_template, $sub, $site_id
	 * @return string
	 */
	function template_post_parse($final_template, $is_partial, $site_id)
	{
		if (isset(ee()->extensions->last_call) && ee()->extensions->last_call)
		{
		    $final_template = ee()->extensions->last_call;
		}

		ee()->load->library('pagination_seo');
		
		$this->pagination = ee()->pagination_seo->settings;

		$site_url = ee()->functions->fetch_site_index();
	
	
		// Get cached pagination values (where pagination has been cached)
		if (isset($this->settings['use_caching']) && $this->settings['use_caching'] == 'y')
		{
			foreach ($this->pagination as $key => $val)
			{
				$pattern = '|'.LD."pagination_seo:set:$key=(.+?)".RD.'|';
				preg_match($pattern, $final_template, $matches);
				if (!empty($matches))
				{
					$this->pagination[$key] = $matches[1];
				}
			}
		}

		
		// Update template tags (base template to limit the replaces)
		if ( ! $is_partial)
		{
			foreach ($this->pagination as $key => $val)
			{
				$final_template = str_replace('{pagination_seo:'.$key.'}', $val, $final_template);			
			}
			
			$prev_uri = $this->pagination['prev_uri'];
			$next_uri = $this->pagination['next_uri'];
			
			$final_template = str_replace('{pagination_seo:prev_url}', ($prev_uri ? $site_url.$prev_uri : ''), $final_template);
			$final_template = str_replace('{pagination_seo:next_url}', ($next_uri ? $site_url.$next_uri : ''), $final_template);
			
			$final_template = str_replace('{pagination_seo:prev_num}', $this->pagination['prev_num'], $final_template);
			$final_template = str_replace('{pagination_seo:next_num}', $this->pagination['next_num'], $final_template);
			
			$final_template = str_replace('{pagination_seo:prev}', ($prev_uri ? '<link rel="prev" href="'.$site_url.$prev_uri.'" />' : ''), $final_template);
			$final_template = str_replace('{pagination_seo:next}', ($next_uri ? '<link rel="next" href="'.$site_url.$next_uri.'" />' : ''), $final_template);
			
			
			// Remove any tags used for cached link urls
			if (isset($this->settings['use_caching']) && $this->settings['use_caching'] == 'y')
			{
				foreach ($this->pagination as $key => $val)
				{
					$final_template = str_replace('{pagination_seo:set:'.$key.'='.$val.'}', '', $final_template);
				}
			}
			
		}

		return $final_template;
	}
	
	
	/**
	 * Settings Form
	 *
	 * @param   Array   Settings
	 * @return  void
	 */
	function settings_form($current)
	{
		
		ee()->load->helper('form');
		ee()->load->library('table');

		$settings = (array_merge($this->defaults, $current));
		
		$vars = array();

		$vars['settings']['page_num_title'] = form_input('page_num_title', $settings['page_num_title']);
		$vars['settings']['page_num_description'] = form_input('page_num_description', $settings['page_num_description']);
		$vars['settings']['display_on_first_page'] = '<label>'.form_radio('display_on_first_page', 'y', $settings['display_on_first_page'] == 'y') .' '. lang('yes') .' </label> &nbsp; <label>' . form_radio('display_on_first_page', 'n', $settings['display_on_first_page'] == 'n') .' '. lang('no') .' </label>';
		$vars['settings']['enable_redirect'] = '<label>'.form_radio('enable_redirect', 'y', $settings['enable_redirect'] == 'y') .' '. lang('yes') .' </label> &nbsp; <label>' . form_radio('enable_redirect', 'n', $settings['enable_redirect'] == 'n') .' '. lang('no') .' </label>';
		$vars['settings']['use_caching'] = '<label>'.form_radio('use_caching', 'y', $settings['use_caching'] == 'y') .' '. lang('yes') .' </label> &nbsp; <label>' . form_radio('use_caching', 'n', $settings['use_caching'] == 'n') .' '. lang('no') .' </label>';
		
		if (APP_VER >= 3)
		{
			$vars['save_url'] = ee('CP/URL', 'addons/settings/pagination_seo/save');
		}
		else
		{
			$vars['save_url'] = BASE.AMP.'C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=pagination_seo';
		}

		return ee()->load->view('index', $vars, TRUE);
	}

		
	/**
	 * Save Settings
	 *
	 * @return void
	 */
	function save_settings()
	{

		if (empty($_POST))
		{
			show_error(lang('unauthorized_access'));
		}

		unset($_POST['submit']);

//		ee()->lang->loadfile('pagination_seo');

		ee()->db->where('class', __CLASS__);
		ee()->db->update('extensions', array('settings' => serialize($_POST)));

		ee()->session->set_flashdata(
			'message_success',
			lang('preferences_updated')
		);
		
		
		if (APP_VER >= 3)
		{
			$redirect_url = ee('CP/URL', 'addons/settings/pagination_seo');
		} 
		else
		{
			$redirect_url =  BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file=pagination_seo';
		}

		ee()->functions->redirect($redirect_url);

	}
	

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		ee()->db->where('class', __CLASS__);
		ee()->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		ee()->db->where('class', __CLASS__);
		ee()->db->update(
				'extensions', 
				array('version' => $this->version)
		);
		
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.pagination_seo.php */
/* Location: /system/expressionengine/third_party/pagination_seo/ext.pagination_seo.php */