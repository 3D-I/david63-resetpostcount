<?php
/**
*
* @package Reset Post Count Extension
* @copyright (c) 2015 david63
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ACP_POST_RESET'				=> 'Reset post count',
	'ACP_USER_UTILS'				=> 'User utilities',

	'ERROR_INVALID_USER_SPECIFIED'	=> 'The specified user does not exist in the database.',
	'ERROR_NO_DATA_SPECIFIED'		=> 'No post reset data has been entered.',
	'ERROR_NO_POST_COUNT'			=> 'The selected user has no posts.',
	'ERROR_NO_USER_SPECIFIED'		=> 'No user has been selected.',
	'ERROR_RESET_GREATER'			=> 'The reset value is greater than the user’s post count.',

	'LOG_USER_POST_COUNT_RESET'		=> '<strong>Reset post count for %1$s</strong><br />» from %2$s to %3$s',

	'OR_ZERO'						=> '--- OR ---',

	'RESET_OVERIDE'					=> 'Overide errors',
	'RESET_OVERIDE_EXPLAIN'			=> 'Setting this option will allow the increase of a user’s post count.',
	'RESET_POST_COUNT'				=> 'Reset post count',
	'RESET_POST_COUNT_EXPLAIN' 		=> 'Here you can select a user and then reset their post count.',
	'RESET_USER_DETAILS'			=> '%1$s currently has %2$s posts',
	'RESET_USERNAME'				=> 'Username',
	'RESET_USERNAME_EXPLAIN'		=> 'The username of the member that you wish to reset the post count for.',
	'RESET_VALUE'					=> 'Reset value',
	'RESET_VALUE_EXPLAIN'			=> 'The new value for the post count of the selected user.',
	'RESET_ZERO'					=> 'Reset to zero',
	'RESET_ZERO_EXPLAIN'			=> 'Selecting this will reset the user’s post count to zero.<br /><strong>Note:</strong> Selecting this will overwrite any value entered in the <strong>Reset value</strong> field.',

	'USER_POST_COUNT_RESET'			=> 'Successfully reset post count for <strong>%1$s</strong> from %2$s to %3$s.',
));

?>