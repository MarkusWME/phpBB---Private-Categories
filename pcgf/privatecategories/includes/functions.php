<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2016 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

if (!defined('IN_PHPBB'))
{
    exit;
}

/**
 * Function that returns all catagories and the privacy status of them
 *
 * @access public
 * @since  1.0.0
 *
 * @return array Information array containing all categories
 */
function pcgf_privatecategories_get_private_categories()
{
    global $db;
    $query = 'SELECT forum_id, parent_id, forum_name, private_category
              FROM ' . FORUMS_TABLE . '
              ORDER BY forum_id';
    $result = $db->sql_query($query);
    $categories = array();
    $inheritance_list = array();
    while ($category = $db->sql_fetchrow($result))
    {
        // Add the category to the category list
        $categories[$category['forum_id']] = array(
            'name'    => $category['forum_name'],
            'private' => $category['private_category'],
        );
        // Assign the category to its parent
        if (isset($inheritance_list[$category['parent_id']]))
        {
            array_push($inheritance_list[$category['parent_id']], $category['forum_id']);
        }
        else
        {
            $inheritance_list[$category['parent_id']] = array($category['forum_id']);
        }
    }
    $db->sql_freeresult($result);
    $category_array = array();
    pcgf_privatecategories_get_category_array($category_array, $categories, $inheritance_list, -1, 0);
    return $category_array;
}

/**
 * Function to build the private category tree recursively
 *
 * @access public
 * @since  1.0.0
 *
 * @param array $category_array   The output array where data should be added to
 * @param array $categories       Array with all categories
 * @param array $inheritance_list Array wich contains inheritance information for the categories
 * @param int   $level            The indentation level of the category
 * @param int   $id               The id of the category where information should be retrieved
 * @param bool  $is_private       Private flag for inheritance reasons
 */
function pcgf_privatecategories_get_category_array(&$category_array, &$categories, &$inheritance_list, $level, $id, $is_private = false)
{
    global $config;
    // Add current category data
    if ($id > 0)
    {
        array_push($category_array, array(
            'id'      => $id,
            'level'   => $level,
            'name'    => $categories[$id]['name'],
            'private' => ($categories[$id]['private'] == true ? 1 : ($is_private == true ? 0 : -1)),
        ));
    }
    // Add data for all childs of the current category
    if (isset($inheritance_list[$id]))
    {
        foreach ($inheritance_list[$id] as $child)
        {
            pcgf_privatecategories_get_category_array($category_array, $categories, $inheritance_list, $level + 1, $child, $is_private || ($config['pcgf_privatecategories_auto_inheritance'] == true ? $categories[$id]['private'] : false));
        }
    }
}
