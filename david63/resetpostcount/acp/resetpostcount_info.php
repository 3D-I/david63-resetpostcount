<?php
/**
*
* @package Reset Post Count Extension
* @copyright (c) 2015 david63
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace david63\resetpostcount\acp;

class resetpostcount_info
{
	function module()
	{
		return array(
			'filename'	=> '\david63\resetpostcount\acp\resetpostcount_module',
			'title'		=> 'ACP_POST_RESET',
			'modes'		=> array(
				'main'		=> array('title' => 'ACP_POST_RESET', 'auth' => 'ext_david63/resetpostcount && acl_a_user', 'cat' => array('ACP_CAT_USERS')),
			),
		);
	}
}
