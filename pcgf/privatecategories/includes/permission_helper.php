<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2016 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace pcgf\privatecategories\includes;

use pcgf\privatecategories\migrations\release_1_0_0;
use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\db\driver\factory;

/** @version 1.1.1 */
class permission_helper
{
    /** @var auth $auth The authentication object */
    protected $auth;

    /** @var factory $db The database object */
    protected $db;

    /** @var config $config The configuration object */
    protected $config;

    /** @var string $phpbb_root_path The forum root path */
    protected $phpbb_root_path;

    /** @var string $php_ext The PHP extension */
    protected $php_ext;

    /**
     * Constructor
     *
     * @public public
     * @since  1.1.1
     *
     * @param auth    $auth            The authentication object
     * @param factory $db              The database object
     * @param config  $config          The configuration object
     * @param string  $phpbb_root_path The forum root path
     * @param string  $php_ext         The PHP extension
     */
    public function __construct(auth $auth, factory $db, config $config, $phpbb_root_path, $php_ext)
    {
        $this->auth = $auth;
        $this->db = $db;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->php_ext = $php_ext;
    }

    /**
     * Function that checks if a given category is set to private
     *
     * @access public
     * @since  1.0.0
     *
     * @param int $category_id The id of the category that should be checked
     *
     * @return bool If the category is private or not
     */
    public function is_private($category_id)
    {
        $categories = $this->get_private_categories();
        foreach ($categories as $category)
        {
            if ($category['id'] == $category_id)
            {
                return $category['private'] >= 0;
            }
        }
        return false;
    }

    /**
     * Function that returns all categories and the privacy status of them
     *
     * @access public
     * @since  1.0.0
     *
     * @return array Information array containing all categories
     */
    public function get_private_categories()
    {
        $query = 'SELECT forum_id, parent_id, forum_name, forum_type, private_category
              FROM ' . FORUMS_TABLE . '
              ORDER BY forum_id';
        $result = $this->db->sql_query($query);
        $categories = array();
        $inheritance_list = array();
        while ($category = $this->db->sql_fetchrow($result))
        {
            // Add the category to the category list
            $categories[$category['forum_id']] = array(
                'name'    => $category['forum_name'],
                'type'    => $category['forum_type'],
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
        $this->db->sql_freeresult($result);
        $category_array = array();
        $this->get_category_array($category_array, $categories, $inheritance_list, -1, 0);
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
     * @param int   $private          If the category inherits privacy or not
     */
    public function get_category_array(&$category_array, &$categories, &$inheritance_list, $level, $id, $private = -1)
    {
        // Add current category data
        if ($id > 0)
        {
            $private = $categories[$id]['private'] == true ? 1 : ($private >= 0 ? 0 : -1);
            array_push($category_array, array(
                'id'      => $id,
                'level'   => $level,
                'name'    => $categories[$id]['name'],
                'private' => $private,
            ));
        }
        // Add data for all childs of the current category
        if (isset($inheritance_list[$id]))
        {
            foreach ($inheritance_list[$id] as $child)
            {
                $this->get_category_array($category_array, $categories, $inheritance_list, $level + 1, $child, ($private >= 0 && ($this->config['pcgf_privatecategories_auto_inheritance'] == true || $categories[$id]['type'] == 0)) ? 0 : -1);
            }
        }
    }

    /**
     * Checks if the stated user has the permission to view the selected topic
     *
     * @param int $user_id     The user id of the user that should be checked
     * @param int $category_id The category id of the topic the user wants to see
     * @param int $topic_id    The id of the topic the user wants to see
     * @param int $poster_id   The id of the poster
     *
     * @return bool If the user has the permission to view the topic
     */
    public function has_permissions($user_id, $category_id, $topic_id, $poster_id)
    {
        if (!$this->is_private($category_id))
        {
            return true;
        }
        if ($this->auth->acl_get('f_pcgf_privatecategories_see_all', $category_id))
        {
            return true;
        }
        if ($poster_id == $user_id)
        {
            return true;
        }
        global $table_prefix;
        $user_id = $this->db->sql_escape($user_id);
        $query_array = array(
            'SELECT'    => 'p.topic',
            'FROM'      => array(
                $table_prefix . release_1_0_0::PRIVATECATEGORY_PERMISSION_TABLE => 'p',
            ),
            'LEFT_JOIN' => array(
                array(
                    'FROM' => array(USER_GROUP_TABLE => 'ug'),
                    'ON'   => 'ug.user_id = ' . $user_id . '
                                    AND p.is_group = 1
                                        AND ug.group_id = p.user',
                ),
            ),
            'WHERE'     => '(ug.group_id > 0
                                OR (p.is_group = 0
                                    AND p.user = ' . $user_id . '))
                                        AND p.topic = ' . $this->db->sql_escape($topic_id),
        );
        $result = $this->db->sql_query($this->db->sql_build_query('SELECT', $query_array));
        $has_permission = $this->db->sql_fetchrow($result) !== false;
        $this->db->sql_freeresult($result);
        return $has_permission;
    }

    /**
     * Function that returns a formatted user link
     *
     * @param array $user_data The id, name and colour of the user
     *
     * @return string Formatted user link
     */
    public function get_formatted_user($user_data)
    {
        $user_string = get_username_string('full', $user_data[0], $user_data[1], $user_data[2]);
        if (strpos($user_string, '<a') !== 0)
        {
            $user_string = '<a data="' . $user_data[0] . '">' . $user_string . '</a>';
        }
        return $user_string;
    }

    /**
     * Function that returns a formatted group link
     *
     * @param array $group_data The id, name and colour of the group
     *
     * @return string Formatted group link
     */
    public function get_formatted_group($group_data)
    {
        return '<a' . ($group_data[2] != '' ? ' style="color: #' . $group_data[2] . '"' : '') . ' href="' . append_sid($this->phpbb_root_path . 'memberlist.' . $this->php_ext, 'mode=group&amp;g=' . $group_data[0]) . '">' . $group_data[1] . '</a>';
    }
}
