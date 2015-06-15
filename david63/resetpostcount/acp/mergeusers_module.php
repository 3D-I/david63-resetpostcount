<?php
/**
*
* @package Merge Users Extension
* @copyright (c) 2014 david63
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace david63\mergeusers\acp;

class mergeusers_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $user, $template, $request, $phpbb_root_path, $phpEx;

		$this->request	= $request;
		$this->template = $template;
		$this->user		= $user;

		$this->user->add_lang_ext('david63/mergeusers', 'mergeusers_common');
		$this->tpl_name		= 'merge_users';
		$this->page_title	= $this->user->lang('MERGE_USERS');
		$form_key			= 'mergeusers';
		add_form_key($form_key);

		$submit 		= ($this->request->is_set_post('submit')) ? true : false;
		$action			= $request->variable('action', '');
		$merge			= ($action == 'merge') ? true : false;
		$old_username	= utf8_normalize_nfc($request->variable('old_username', '', true));
		$new_username	= utf8_normalize_nfc($request->variable('new_username', '', true));
		$delete_old		= $request->variable('delete_old_user', '');

		$errors = array();

		if ($submit)
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->user->lang('FORM_INVALID'));
			}
		}

		if($submit || $merge)
		{
			$old_user_id = $this->check_user($old_username, $errors, true);
			$new_user_id = $this->check_user($new_username, $errors, false);

			if ($old_user_id == $new_user_id)
			{
				$errors[] = $this->user->lang('CANNOT_MERGE_SAME');
			}
		}

		if(($submit || $merge) && !sizeof($errors))
		{
			if(confirm_box(true))
			{
				user_merge($old_user_id, $new_user_id, $delete_old);
				$phpbb_log->add('admin', 'LOG_USERS_MERGED', $old_username . ' &raquo; ' . $new_username);
				trigger_error($this->user->lang('USERS_MERGED') . adm_back_link($this->u_action));
			}
			else
			{
				$hidden_fields = array(
					'i'					=> $id,
					'mode'				=> $mode,
					'old_username'		=> $old_username,
					'new_username'		=> $new_username,
					'action'			=> 'merge',
					'delete_old_user'	=> $delete_old,
				);

				confirm_box(false, sprintf($this->user->lang('MERGE_USERS_CONFIRM'), $old_username, $new_username), build_hidden_fields($hidden_fields));
			}
		}

		$this->template->assign_vars(array(
			'ERROR_MSG'					=> implode('<br />', $errors),
			'NEW_USERNAME'				=> (!empty($new_user_id)) ? $new_username : '',
			'OLD_USERNAME'				=> (!empty($old_user_id)) ? $old_username : '',
			'S_ERROR'					=> (sizeof($errors)) ? true : false,
			'U_ACTION'					=> $this->u_action,
			'U_FIND_NEW_USERNAME'		=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=user_merge&amp;field=new_username&amp;select_single=true'),
			'U_FIND_OLD_USERNAME'		=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=user_merge&amp;field=old_username&amp;select_single=true'),
		));
	}

	/**
	* Checks to see if we can use this username for a merge, based on a few factors.
	*
	* @param 	string	$username	The username to check
	* @param 	array	&$errors	Errors array to work with
	* @param 	int		$old_user	Old user check
	*
	* @return	mixed	Return the user's ID (integer) if valid, return void if there was an error
	*/
	function check_user($username, &$errors, $old_user)
	{
		global $db, $user;

		if (!empty($username))
		{
			$sql = 'SELECT user_id, user_type
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $db->sql_query($sql);

			$user_id	= (int) $db->sql_fetchfield('user_id');
			$user_type	= (int) $db->sql_fetchfield('user_type');

			$db->sql_freeresult($result);

			if (!$user_id)
			{
				$errors[] = ($old_user) ? $this->user->lang('NO_OLD_USER') : $this->user->lang('NO_NEW_USER');
				return;
			}
		}
		else
		{
			$errors[] = ($old_user) ? $this->user->lang('NO_OLD_USER_SPECIFIED') : $this->user->lang('NO_NEW_USER_SPECIFIED');
			return;
		}

		// Check to see if it is ourselves here
		if($user_id === (int) $this->user->data['user_id'] && $old_user)
		{
			$errors[] = $this->user->lang('CANNOT_MERGE_SELF');
			return;
		}

		// Make sure this isn't a founder
		if($user_type === USER_FOUNDER && $old_user && $this->user->data['user_type'] !== USER_FOUNDER)
		{
			$errors[] = $this->user->lang('CANNOT_MERGE_FOUNDER');
			return;
		}
		return $user_id;
	}
}

/**
* Merge two user accounts into one
*
* @author eviL3
* @param	int $old_user		User id of the old user
* @param	int $new_user		User id of the new user
* @param	int $delete_old		Delete the old user
*
* @return	void
*/
function user_merge($old_user, $new_user, $delete_old)
{
	global $db;

	if (!function_exists('user_add'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
	}

	$old_user = (int) $old_user;
	$new_user = (int) $new_user;

	$total_posts = 0;

	// Add up the total number of posts for both...
	$sql = 'SELECT user_posts
		FROM ' . USERS_TABLE . '
		WHERE ' . $db->sql_in_set('user_id', array($old_user, $new_user));
	$result = $db->sql_query($sql);

	while($return = $db->sql_fetchrow($result))
	{
		$total_posts = $total_posts + (int) $return['user_posts'];
	}

	$db->sql_freeresult($result);

	// Now set the new user to have the total amount of posts.  ;)
	$db->sql_query('UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
		'user_posts' => $total_posts,
	)) . ' WHERE user_id = ' . $new_user);

	// Get both users userdata
	$data = array();

	foreach (array($old_user, $new_user) as $key)
	{
		$sql = 'SELECT user_id, username, user_colour
			FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $key;
		$result = $db->sql_query($sql);

		$data[$key] = $db->sql_fetchrow($result);

		$db->sql_freeresult($result);
	}

	$update_ary = array(
		ATTACHMENTS_TABLE		=> array('poster_id'),
		FORUMS_TABLE			=> array(array('forum_last_poster_id', 'forum_last_poster_name', 'forum_last_poster_colour')),
		LOG_TABLE				=> array('user_id', 'reportee_id'),
		MODERATOR_CACHE_TABLE	=> array(array('user_id', 'username')),
		POSTS_TABLE				=> array(array('poster_id', 'post_username'), 'post_edit_user'),
		POLL_VOTES_TABLE		=> array('vote_user_id'),
		PRIVMSGS_TABLE			=> array('author_id', 'message_edit_user'),
		PRIVMSGS_TO_TABLE		=> array('user_id', 'author_id'),
		REPORTS_TABLE			=> array('user_id'),
		TOPICS_TABLE			=> array(array('topic_poster', 'topic_first_poster_name', 'topic_first_poster_colour'), array('topic_last_poster_id', 'topic_last_poster_name', 'topic_last_poster_colour')),
	);

	foreach ($update_ary as $table => $field_ary)
	{
		foreach ($field_ary as $field)
		{
			$sql_ary = array();

			if (!is_array($field))
			{
				$field = array($field);
			}

			$sql_ary[$field[0]] = $new_user;

			if (!empty($field[1]))
			{
				$sql_ary[$field[1]] = $data[$new_user]['username'];
			}

			if (!empty($field[2]))
			{
				$sql_ary[$field[2]] = $data[$new_user]['user_colour'];
			}

			$primary_field = $field[0];

			$sql = "UPDATE $table SET " . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE $primary_field = $old_user";
			$db->sql_query($sql);
		}
	}

	if ($delete_old)
	{
		user_delete('remove', $old_user);
	}
}

?>