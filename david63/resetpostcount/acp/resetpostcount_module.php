<?php
/**
*
* @package Reset Post Count Extension
* @copyright (c) 2015 david63
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace david63\resetpostcount\acp;

class resetpostcount_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container, $user;

		$this->tpl_name		= 'reset_post_count';
		$this->page_title	= $user->lang('ACP_POST_RESET');

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('david63.resetpostcount.admin.controller');

		$admin_controller->reset_post_count();

		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);
	}
}
