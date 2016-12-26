<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2016 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace pcgf\privatecategories\acp;

global $phpbb_root_path;
require($phpbb_root_path . 'ext/pcgf/privatecategories/includes/functions.php');

/** @version 1.0.0 */
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

    public function main($id, $mode)
    {
        global $user, $request, $config, $db, $template;
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
                    $result = $db->sql_query($query);
                    if ($db->sql_affectedrows() != 1)
                    {
                        trigger_error($user->lang('ACP_PCGF_PRIVATECATEGORIES_SETTING_PRIVATE_FAILED') . adm_back_link($this->u_action), E_USER_WARNING);
                    }
                break;
                case 'clean':
                    /// TODO: Clean up permissions table
                    trigger_error('This function has not been implemented so far!' . adm_back_link($this->u_action), E_USER_WARNING);
                break;
                case 'save':
                    // Save the auto inheritance setting
                    $config->set('pcgf_privatecategories_auto_inheritance', $request->variable('privatecategories_auto_inheritance', 1));
                break;
            }
        }
        else if ($request->variable('action', 'void') == 'delete')
        {
            // Remove the private flag from the selected category in the database
            $query = 'UPDATE ' . FORUMS_TABLE . '
                              SET private_category = 0
                              WHERE forum_id = ' . $db->sql_escape($request->variable('category_id', -1));
            $result = $db->sql_query($query);
            if ($db->sql_affectedrows() != 1)
            {
                trigger_error($user->lang('ACP_PCGF_PRIVATECATEGORIES_UNSET_PRIVATE_FAILED') . adm_back_link($this->u_action), E_USER_WARNING);
            }
        }
        $category_array = pcgf_privatecategories_get_private_categories();
        $private_categories = false;
        $inherited_categories = false;
        foreach ($category_array as $category)
        {
            if ($category['private'] > 0)
            {
                // Add the category to the private category list
                $private_categories = true;
                $template->assign_block_vars('private_category_list', array(
                    'CATEGORY'    => $category['name'],
                    'CATEGORY_ID' => $category['id'],
                ));
            }
            else
            {
                if ($category['private'] == 0)
                {
                    // Add the category to the inherited private category list
                    $inherited_categories = true;
                    $template->assign_block_vars('inherited_private_category_list', array(
                        'CATEGORY' => $category['name'],
                    ));
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