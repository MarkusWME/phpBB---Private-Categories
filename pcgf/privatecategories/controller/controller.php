<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2017 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace pcgf\privatecategories\controller;

use pcgf\privatecategories\migrations\release_1_0_0;
use phpbb\auth\auth;
use phpbb\db\driver\factory;
use phpbb\group\helper;
use phpbb\json_response;
use phpbb\request\request;
use phpbb\user;

global $phpbb_root_path;
require_once($phpbb_root_path . 'ext/pcgf/privatecategories/includes/functions.php');

/** @version 1.1.0 */
class controller
{
    /** @const Max amount of suggestions for users or groups */
    const MAX_SUGGESTIONS = 5;

    /** @var  request The request object */
    protected $request;

    /** @var  factory The database object */
    protected $db;

    /** @var  auth The authentication object */
    protected $auth;

    /** @var  user The user object */
    protected $user;

    /**
     * Constructor
     *
     * @access public
     * @since  1.1.0
     *
     * @param request $request The request object
     * @param factory $db      The database object
     * @param auth    $auth    The authentication object
     * @param user    $user    The user object
     */
    public function __construct(request $request, factory $db, auth $auth, user $user)
    {
        $this->request = $request;
        $this->db = $db;
        $this->auth = $auth;
        $this->user = $user;
    }

    /**
     * Function that gets user and group name suggestions which are not already permitted to view a topic
     *
     * @access public
     * @since  1.1.0
     */
    public function get_suggestions()
    {
        $response = new json_response();
        $response_data = array('search' => $this->request->variable('search', '', true), 'users' => array(), 'groups' => array());
        // Only answer if the request is an AJAX request
        if ($this->request->is_ajax())
        {
            global $table_prefix, $phpbb_container;
            $search = utf8_normalize_nfc(strtolower($response_data['search']));
            if (strlen($search) > 0)
            {
                // Only search if the keyword is larger than 0
                $search = $this->db->sql_escape($search);
                $category = $this->request->variable('category', 0);
                $topic = $this->request->variable('topic', 0);
                $owner = $this->request->variable('owner', 0);
                // Get user suggestions that don't have permissions right now
                $query = 'SELECT *
                    FROM ' . USERS_TABLE . '
                    WHERE ' . $this->db->sql_in_set('user_type', array(USER_NORMAL, USER_FOUNDER)) . '
                        AND username_clean ' . $this->db->sql_like_expression($this->db->get_any_char() . $search . $this->db->get_any_char()) . '
                    ORDER BY username_clean ' . $this->db->sql_like_expression($search . $this->db->get_any_char()) . ' DESC, username DESC';
                $result = $this->db->sql_query($query);
                $count = 0;
                while ($user = $this->db->sql_fetchrow($result))
                {
                    $this->auth->acl($user);
                    if (!has_permissions($user['user_id'], $category, $topic, $owner, $this->auth, $this->db))
                    {
                        array_push($response_data['users'], array($user['user_id'], get_username_string('no_profile', $user['user_id'], $user['username'], $user['user_colour'])));
                    }
                    if (++$count == self::MAX_SUGGESTIONS)
                    {
                        break;
                    }
                }
                $this->db->sql_freeresult($result);
                // Get group suggestions that don't have permissions right now
                $query = 'SELECT user
                    FROM ' . $table_prefix . release_1_0_0::PRIVATECATEGORY_PERMISSION_TABLE . '
                    WHERE is_group = 1
                        AND topic = ' . $this->db->sql_escape($topic);
                $result = $this->db->sql_query($query);
                $group_ids = array();
                while ($group = $this->db->sql_fetchrow($result))
                {
                    array_push($group_ids, $group['user']);
                }
                $this->db->sql_freeresult($result);
                $query = 'SELECT group_id, group_name
                    FROM ' . GROUPS_TABLE . '
                    WHERE LOWER(group_name) ' . $this->db->sql_like_expression($this->db->get_any_char() . $search . $this->db->get_any_char());
                if (count($group_ids) > 0)
                {
                    $query .= ' AND ' . $this->db->sql_in_set('group_id', $group_ids, true);
                }
                $query .= ' ORDER BY LOWER(group_name) ' . $this->db->sql_like_expression($search . $this->db->get_any_char()) . ' DESC';
                $result = $this->db->sql_query($query);
                $count = 0;
                $group_helper = $phpbb_container->get('group_helper');
                while ($group = $this->db->sql_fetchrow($result))
                {
                    array_push($response_data['groups'], array($group['group_id'], $group_helper->get_name($group['group_name'])));
                    if (++$count == self::MAX_SUGGESTIONS)
                    {
                        break;
                    }
                }
                $this->db->sql_freeresult($result);
            }
        }
        // Return the suggestion result set
        $response->send($response_data);
    }

    /**
     * Function to add a viewer to the topic
     *
     * @access public
     * @since  1.1.0
     */
    public function add_viewer()
    {
        $response = new json_response();
        $added_viewer = array();
        // Only answer if the request is an AJAX request
        if ($this->request->is_ajax())
        {
            global $table_prefix, $phpbb_container;
            $group = $this->request->variable('is_group', 0);
            $category = $this->request->variable('category', 0);
            $viewer = $this->db->sql_escape($this->request->variable('viewer', 0));
            $topic = $this->db->sql_escape($this->request->variable('topic', 0));
            $owner = $this->request->variable('owner', 0);
            // Check if the current user is allowed to add a viewer
            if ($this->auth->acl_get('f_pcgf_privatecategories_invite_all', $category) || ($this->auth->acl_get('f_pcgf_privatecategories_invite_own', $category) && $this->user->data['user_id'] == $owner))
            {
                // Add viewer permissions
                $insert_data = array(
                    'topic'    => $topic,
                    'user'     => $viewer,
                    'is_group' => $this->db->sql_escape($group),
                );
                $query = 'INSERT INTO ' . $table_prefix . release_1_0_0::PRIVATECATEGORY_PERMISSION_TABLE . ' ' . $this->db->sql_build_array('INSERT', $insert_data);
                $this->db->sql_query($query);
                if ($this->db->sql_affectedrows() == 1)
                {
                    if ($group > 0)
                    {
                        // Get group link
                        $query = 'SELECT group_id, group_name, group_colour
                        FROM ' . GROUPS_TABLE . '
                        WHERE group_id = ' . $viewer;
                        $result = $this->db->sql_query($query);
                        if ($group = $this->db->sql_fetchrow($result))
                        {
                            $added_viewer['type'] = 'group';
                            $group_helper = $phpbb_container->get('group_helper');
                            $added_viewer['viewer'] = get_formatted_user(array($group['group_id'], $group_helper->get_name($group['group_name']), $group['group_colour']));
                        }
                        $this->db->sql_freeresult($result);
                    }
                    else
                    {
                        // Get user link
                        $query = 'SELECT user_id, username, user_colour
                        FROM ' . USERS_TABLE . '
                        WHERE user_id = ' . $viewer;
                        $result = $this->db->sql_query($query);
                        if ($user = $this->db->sql_fetchrow($result))
                        {
                            $added_viewer['type'] = 'user';
                            $added_viewer['viewer'] = get_formatted_user(array($user['user_id'], $user['username'], $user['user_colour']));
                        }
                        $this->db->sql_freeresult($result);
                    }
                }
            }
        }
        $response->send($added_viewer);
    }

    /**
     * Function to remove a viewer from the topic
     *
     * @access public
     * @since  1.1.0
     */
    public function remove_viewer()
    {
        $response = new json_response();
        $removed_viewer = false;
        // Only answer if the request is an AJAX request
        if ($this->request->is_ajax())
        {
            global $table_prefix;
            $group = $this->db->sql_escape($this->request->variable('is_group', 0));
            $category = $this->request->variable('category', 0);
            $viewer = $this->db->sql_escape($this->request->variable('viewer', 0));
            $topic = $this->db->sql_escape($this->request->variable('topic', 0));
            $owner = $this->request->variable('owner', 0);
            // Check if the current user is allowed to remove a viewer
            if ($this->auth->acl_get('f_pcgf_privatecategories_remove_all', $category) || ($this->auth->acl_get('f_pcgf_privatecategories_remove_own', $category) && $this->user->data['user_id'] == $owner))
            {
                // Remove viewer permission
                $query = 'DELETE
                FROM ' . $table_prefix . release_1_0_0::PRIVATECATEGORY_PERMISSION_TABLE . '
                WHERE topic = ' . $topic . '
                    AND user = ' . $viewer . '
                        AND is_group = ' . $group;
                $this->db->sql_query($query);
                $removed_viewer = $this->db->sql_affectedrows() == 1;
            }
        }
        $response->send(array($removed_viewer));
    }
}
