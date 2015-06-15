<?php
/**
*
* @package Reset Post Count Extension
* @copyright (c) 2015 david63
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace david63\resetpostcount\controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Admin controller
*/
class admin_controller implements admin_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var ContainerInterface */
	protected $container;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP extension */
	protected $phpEx;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor for admin controller
	*
	* @param \phpbb\config\config				$config		Config object
	* @param \phpbb\request\request				$request	Request object
	* @param \phpbb\db\driver\driver_interface	$db
	* @param \phpbb\template\template			$template	Template object
	* @param \phpbb\user						$user		User object
	* @param ContainerInterface					$container	Service container interface
	*
	* @return \david63\resetpostcount\controller\admin_controller
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\request\request $request, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, ContainerInterface $container, $root_path, $php_ext)
	{
		$this->config		= $config;
		$this->request		= $request;
		$this->db			= $db;
		$this->template		= $template;
		$this->user			= $user;
		$this->container	= $container;
		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
	}

	/**
	* Process the post reset
	*
	* @return null
	* @access public
	*/
	public function reset_post_count()
	{
		// Create a form key for preventing CSRF attacks
		$form_key = 'reset_post_count';
		add_form_key($form_key);

		$option			= $this->request->variable('option', '');
		$overide		= $this->request->variable('overide', '');
		$post_count		= $this->request->variable('post_count', 0);
		$reset_username	= $this->request->variable('reset_username', '');
		$reset_value	= $this->request->variable('reset_value', '');
		$reset_zero		= $this->request->variable('reset_zero', '');
		$user_id		= $this->request->variable('user_id', 0);

		$confirm	= false;
		$errors		= $hidden_fields = array();

		// Submit
		if ($this->request->is_set_post('submit'))
		{
			// Is the submitted form is valid?
			if (!check_form_key($form_key))
			{
				trigger_error($this->user->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			switch ($option)
			{
				case 'update':
					if (!$reset_value && !$reset_zero)
					{
						$errors[] = $this->user->lang('ERROR_NO_DATA_SPECIFIED');
					}

					if (($reset_value > $post_count) && !$overide)
					{
						$errors[] = $this->user->lang('ERROR_RESET_GREATER');
					}

					if(!sizeof($errors))
					{
						$new_post_count = ($reset_zero) ? 0 : $reset_value;

					// Update db
					$this->db->sql_query(
						'UPDATE ' . USERS_TABLE . '
							SET user_posts = ' . (int) $new_post_count . '
							WHERE user_id = ' . (int) $user_id
						);

					// Log the action
					$phpbb_log = $this->container->get('log');
					$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_POST_COUNT_RESET',  time(), array($reset_username, $post_count, $new_post_count));
					$phpbb_log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_POST_COUNT_RESET', time(), array('reportee_id' => $this->user->data['username'], $reset_username, $post_count, $new_post_count));
					trigger_error($this->user->lang('USER_POST_COUNT_RESET', $reset_username, $post_count, $new_post_count) . adm_back_link($this->u_action));
					}
					else
					{
						$confirm = true;

						$hidden_fields = array(
							'option'			=> 'update',
							'overide'			=> $overide,
							'post_count'		=> $post_count,
							'reset_username'	=> $reset_username,
							'user_id'			=> $user_id,
						);
					}
				break;

				default:
					if (!$reset_username)
					{
						$errors[] = $this->user->lang('ERROR_NO_USER_SPECIFIED');
					}
					else
					{
						$sql = 'SELECT user_id, user_posts
							FROM ' . USERS_TABLE . "
							WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($reset_username)) . "'";
						$result = $this->db->sql_query($sql);

						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						$user_id	= $row['user_id'];
						$post_count	= $row['user_posts'];

						if (!$user_id)
						{
							$errors[] = $this->user->lang('ERROR_INVALID_USER_SPECIFIED');
						}

						if ($post_count == 0 && !$overide)
						{
							$errors[] = $this->user->lang('ERROR_NO_POST_COUNT');
						}
					}

					if(!sizeof($errors))
					{
						$confirm = true;

						$hidden_fields = array(
							'option'			=> 'update',
							'overide'			=> $overide,
							'post_count'		=> $post_count,
							'reset_username'	=> $reset_username,
							'user_id'			=> $user_id,
						);
					}
				break;
			}
		}

		$this->template->assign_vars(array(
			'ERROR_MSG'				=> implode('<br />', $errors),
			'L_RESET_USER_DETAILS'	=> $this->user->lang('RESET_USER_DETAILS', $reset_username, $post_count),

			'S_CONFIRM'				=> $confirm,
			'S_ERROR'				=> (sizeof($errors)) ? true : false,
			'S_HIDDEN_FIELDS'		=> build_hidden_fields($hidden_fields),

			'U_ACTION'				=> $this->u_action,
			'U_FIND_USERNAME'		=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=reset_post_count&amp;field=reset_username&amp;select_single=true'),
		));
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
