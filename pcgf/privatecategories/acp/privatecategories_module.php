<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2016, 2017 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace pcgf\privatecategories\acp;

use pcgf\privatecategories\includes\permission_helper;
use pcgf\privatecategories\migrations\release_1_0_0;

/** @version 1.1.1 */
class privatecategories_module
{
    /** @const Defines the space count that indents subcategories */
    const CATEGORY_INDENTATION_MULTIPLIER = 3;

    /** @var  string $page_title The title of the page */
    public $page_title;

    /** @var  string $tpl_name The name of the template file */
    public $tpl_name;

    /** @var  object $u_action The user action */
    public $u_action;

    /** @var permission_helper $permission_helper The permission helper object */
    protected $permission_helper;

    /**
     * Constructor
     *
     * @access public
     * @since  1.1.1
     */
    public function __construct()
    {
        global $phpbb_container;
        $this->permission_helper = $phpbb_container->get('pcgf.privatecategories.permission_helper');
    }

    public function main($id, $mode)
    {
        global $user, $request, $config, $table_prefix, $db, $template;
        $this->page_title = $user->lang['ACP_PCGF_PRIVATECATEGORIES'];
        $this->tpl_name = 'acp_privatecategories_body';
        add_form_key('pcgf/privatecategories');
        if ($request->is_set_post('submit'))
        {
            if (!check_form_key('pcgf/privatecategories') || !$request->is_set_post('action'))
            {
                trigger_error('FORM_INVALID', E_USER_WARNING);
            }
            switch ($request->variable('action', 'void'))
            {
                case 'add':
                    // Set the private flag of the selected category in the database
                    $query = 'UPDATE ' . FORUMS_TABLE . '
                              SET private_category = 1
                              WHERE forum_id = ' . $db->sql_escape($request->variable('category_id', -1));
                    $db->sql_query($query);
                    if ($db->sql_affectedrows() != 1)
                    {
                        trigger_error($user->lang('ACP_PCGF_PRIVATECATEGORIES_SETTING_PRIVATE_FAILED') . adm_back_link($this->u_action), E_USER_WARNING);
                    }
                break;
                case 'clean':
                    // Cleanup invalid topics
                    $query = 'SELECT topic_id
                        FROM ' . TOPICS_TABLE;
                    $result = $db->sql_query($query);
                    $ids = array();
                    while ($row = $db->sql_fetchrow($result))
                    {
                        array_push($ids, $row['topic_id']);
                    }
                    $db->sql_freeresult($result);
                    $query = 'DELETE FROM ' . $table_prefix . release_1_0_0::PRIVATECATEGORY_PERMISSION_TABLE . ' WHERE ' . $db->sql_in_set('topic', $ids, true);
                    $db->sql_query($query);
                    // Cleanup invalid groups
                    $query = 'SELECT group_id
                        FROM ' . GROUPS_TABLE;
                    $result = $db->sql_query($query);
                    $ids = array();
                    while ($row = $db->sql_fetchrow($result))
                    {
                        array_push($ids, $row['group_id']);
                    }
                    $db->sql_freeresult($result);
                    $query = 'DELETE FROM ' . $table_prefix . release_1_0_0::PRIVATECATEGORY_PERMISSION_TABLE . ' WHERE is_group = 1 AND ' . $db->sql_in_set('user', $ids, true);
                    $db->sql_query($query);
                    // Cleanup invalid users
                    $query = 'SELECT user_id
                        FROM ' . USERS_TABLE;
                    $result = $db->sql_query($query);
                    $ids = array();
                    while ($row = $db->sql_fetchrow($result))
                    {
                        array_push($ids, $row['user_id']);
                    }
                    $db->sql_freeresult($result);
                    $query = 'DELETE FROM ' . $table_prefix . release_1_0_0::PRIVATECATEGORY_PERMISSION_TABLE . ' WHERE is_group = 0 AND ' . $db->sql_in_set('user', $ids, true);
                    $db->sql_query($query);
                    trigger_error($user->lang('ACP_PCGF_PRIVATECATEGORIES_CLEANUP_EXECUTED') . adm_back_link($this->u_action));
                break;
                case 'save':
                    // Save the auto inheritance setting
                    $config->set('pcgf_privatecategories_auto_inheritance', $request->variable('privatecategories_auto_inheritance', 1));
                    trigger_error($user->lang('ACP_PCGF_PRIVATECATEGORIES_SETTINGS_SAVED') . adm_back_link($this->u_action));
                break;
            }
        }
        else if ($request->variable('action', 'void') == 'delete')
        {
            // Remove the private flag from the selected category in the database
            $query = 'UPDATE ' . FORUMS_TABLE . '
                              SET private_category = 0
                              WHERE forum_id = ' . $db->sql_escape($request->variable('category_id', -1));
            $db->sql_query($query);
            if ($db->sql_affectedrows() != 1)
            {
                trigger_error($user->lang('ACP_PCGF_PRIVATECATEGORIES_UNSET_PRIVATE_FAILED') . adm_back_link($this->u_action), E_USER_WARNING);
            }
        }
        $category_array = $this->permission_helper->get_private_categories();
        $category_count = count($category_array);
        $private_categories = false;
        $inherited_categories = false;
        $private_additions = array();
        for ($i = 0; $i < $category_count; $i++)
        {
            $category = $category_array[$i];
            if ($category['private'] > 0)
            {
                // Add the category to the private category list
                $private_categories = true;
                $private_additions[$category['level']] = $i;
                $template->assign_block_vars('private_category_list', array(
                    'CATEGORY'    => $category['name'],
                    'CATEGORY_ID' => $category['id'],
                ));
            }
            else
            {
                $private_additions[$category['level']] = -1;
                if ($category['private'] == 0)
                {
                    // Add the category to the inherited private category list
                    $inherited_categories = true;
                    $template->assign_block_vars('inherited_private_category_list', array(
                        'CATEGORY' => $category['name'],
                    ));
                }
                // If the parent category is set to private show it in the addition list so that the structure is not confusing
                for ($j = 0; $j < $category['level']; $j++)
                {
                    if ($private_additions[$j] < 0)
                    {
                        continue;
                    }
                    $template->assign_block_vars('category_list', array(
                        'CATEGORY_ID'   => -1,
                        'CATEGORY_NAME' => str_repeat('&nbsp', $category_array[$private_additions[$j]]['level'] * self::CATEGORY_INDENTATION_MULTIPLIER) . $category_array[$private_additions[$j]]['name'],
                    ));
                    $private_additions[$j] = -1;
                }
                // Add the category to the addition list so that it could be set to private
                $template->assign_block_vars('category_list', array(
                    'CATEGORY_ID'   => $category['id'],
                    'CATEGORY_NAME' => str_repeat('&nbsp', $category['level'] * self::CATEGORY_INDENTATION_MULTIPLIER) . $category['name'],
                ));
            }
        }
        $template->assign_vars(array(
            'AUTO_INHERITANCE_ACTIVATED'        => $config['pcgf_privatecategories_auto_inheritance'],
            'PCGF_PRIVATE_CATEGORIES'           => $private_categories,
            'PCGF_INHERITED_PRIVATE_CATEGORIES' => $inherited_categories,
            'U_ACTION'                          => $this->u_action,
        ));
    }
}
