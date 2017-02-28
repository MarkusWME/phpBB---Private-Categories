<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2016, 2017 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace pcgf\privatecategories\event;

use pcgf\privatecategories\migrations\release_1_0_0;
use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\factory;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

global $phpbb_root_path;
require_once($phpbb_root_path . 'ext/pcgf/privatecategories/includes/functions.php');

/** @version 1.1.0 */
class listener implements EventSubscriberInterface
{
    /** @var factory $db Database object */
    protected $db;

    /** @var config $config Configuration object */
    protected $config;

    /** @var template $template Template object */
    protected $template;

    /** @var  user $user The user object */
    protected $user;

    /** @var  auth $auth The authentication object */
    protected $auth;

    /** @var  helper $helper The helper object */
    protected $helper;

    /**
     * Constructor for the extension listener
     *
     * @access public
     * @since  1.0.0
     *
     * @param factory  $db       Database object
     * @param config   $config   Configuration object
     * @param template $template Template object
     * @param user     $user     The user object
     * @param auth     $auth     The authentication object
     * @param          $helper   $helper The helper object
     */
    public function __construct(factory $db, config $config, template $template, user $user, auth $auth, helper $helper)
    {
        $this->db = $db;
        $this->config = $config;
        $this->template = $template;
        $this->user = $user;
        $this->auth = $auth;
        $this->helper = $helper;
        // Load language data of the extension
        $this->user->add_lang_ext('pcgf/privatecategories', 'privatecategories');
    }

    /**
     * Function that returns the subscribed events
     *
     * @access public
     * @since  1.0.0
     * @return array The subscribed event list
     */
    static public function getSubscribedEvents()
    {
        return array(
            'core.permissions'                  => 'add_permission_data',
            'core.viewtopic_get_post_data'      => 'check_topic_view_permissions',
            'core.viewforum_modify_topics_data' => 'check_category_view_permissions',
        );
    }

    /**
     * Function that adds permission data to the ACP module when it is needed
     *
     * @access public
     * @since  1.0.0
     *
     * @param array $event The event object which contains specific information
     */
    public function add_permission_data($event)
    {
        $event['categories'] = array_merge($event['categories'], array('ACL_CAT_PCGF_PRIVATECATEGORIES' => 'ACL_CAT_PCGF_PRIVATECATEGORIES'));
        $event['permissions'] = array_merge($event['permissions'], array(
            'f_pcgf_privatecategories_see_all'    => array(
                'lang' => 'ACL_F_PCGF_PRIVATECATEGORIES_SEE_ALL',
                'cat'  => 'ACL_CAT_PCGF_PRIVATECATEGORIES',
            ),
            'f_pcgf_privatecategories_invite_all' => array(
                'lang' => 'ACL_F_PCGF_PRIVATECATEGORIES_INVITE_ALL',
                'cat'  => 'ACL_CAT_PCGF_PRIVATECATEGORIES',
            ),
            'f_pcgf_privatecategories_invite_own' => array(
                'lang' => 'ACL_F_PCGF_PRIVATECATEGORIES_INVITE_OWN',
                'cat'  => 'ACL_CAT_PCGF_PRIVATECATEGORIES',
            ),
            'f_pcgf_privatecategories_remove_all' => array(
                'lang' => 'ACL_F_PCGF_PRIVATECATEGORIES_REMOVE_ALL',
                'cat'  => 'ACL_CAT_PCGF_PRIVATECATEGORIES',
            ),
            'f_pcgf_privatecategories_remove_own' => array(
                'lang' => 'ACL_F_PCGF_PRIVATECATEGORIES_REMOVE_OWN',
                'cat'  => 'ACL_CAT_PCGF_PRIVATECATEGORIES',
            ),
        ));
    }

    /**
     * Function that handles the user view permission check
     *
     * @access public
     * @since  1.0.0
     *
     * @param array $event The event object which contains specific information
     */
    public function check_topic_view_permissions($event)
    {
        if (has_permissions($this->user->data['user_id'], $event['forum_id'], $event['topic_id'], $event['topic_data']['topic_poster'], $this->auth, $this->db))
        {
            global $table_prefix;
            // Add the topic owner to the list of allowed viewers
            $allowed_users = array($event['topic_data']['topic_first_poster_name'] => array($event['topic_data']['topic_poster'], $event['topic_data']['topic_first_poster_name'], $event['topic_data']['topic_first_poster_colour']));
            $allowed_groups = array();
            // Allow all users with the see all permission to view the topic
            $allowed_user_ids = $this->auth->acl_get_list(false, 'f_pcgf_privatecategories_see_all', $event['forum_id'])[$event['forum_id']]['f_pcgf_privatecategories_see_all'];
            $allowed_group_ids = array();
            // Get all users and groups which are allowed to view the topic
            $query = 'SELECT user, is_group
                FROM ' . $table_prefix . release_1_0_0::PRIVATECATEGORY_PERMISSION_TABLE . '
                WHERE topic = ' . $this->db->sql_escape($event['topic_id']);
            $result = $this->db->sql_query($query);
            while ($row = $this->db->sql_fetchrow($result))
            {
                if ($row['is_group'] == 1)
                {
                    array_push($allowed_group_ids, $row['user']);
                }
                else
                {
                    array_push($allowed_user_ids, $row['user']);
                }
            }
            $this->db->sql_freeresult($result);
            // Get user data to view the user list
            if (count($allowed_user_ids) > 0)
            {
                $query = 'SELECT user_id, username, user_colour
                FROM ' . USERS_TABLE . '
                WHERE ' . $this->db->sql_in_set('user_id', $allowed_user_ids);
                unset($allowed_user_ids);
                $result = $this->db->sql_query($query);
                while ($user = $this->db->sql_fetchrow($result))
                {
                    $allowed_users[$user['username']] = array($user['user_id'], $user['username'], $user['user_colour']);
                }
                $this->db->sql_freeresult($result);
            }
            // Get group data to view the group list
            if (count($allowed_group_ids) > 0)
            {
                $query = 'SELECT group_id, group_colour
                FROM ' . GROUPS_TABLE . '
                WHERE ' . $this->db->sql_in_set('group_id', $allowed_group_ids);
                unset($allowed_group_ids);
                $result = $this->db->sql_query($query);
                while ($row = $this->db->sql_fetchrow($result))
                {
                    $group_name = get_group_name($row['group_id']);
                    $allowed_groups[$group_name] = array($row['group_id'], $group_name, $row['group_colour']);
                }
                $this->db->sql_freeresult($result);
            }
            // Sort users and groups alphabetically
            ksort($allowed_users);
            ksort($allowed_groups);
            $allowed_users_string = '';
            $allowed_groups_string = '';
            // Create the lists of users and groups
            foreach ($allowed_users as $allowed_user)
            {
                $allowed_users_string .= get_formatted_user($allowed_user) . ',&nbsp;';
            }
            foreach ($allowed_groups as $allowed_group)
            {
                $allowed_groups_string .= get_formatted_group($allowed_group) . ',&nbsp;';
            }
            $this->template->assign_vars(array(
                'PCGF_PRIVATECATEGORIES_ALLOWED_USERS'   => substr($allowed_users_string, 0, -7),
                'PCGF_PRIVATECATEGORIES_ALLOWED_GROUPS'  => substr($allowed_groups_string, 0, -7),
                'PCGF_PRIVATECATEGORIES_SUGGESTION_LINK' => $this->helper->route('pcgf_privatecategories_suggestions'),
                'PCGF_PRIVATECATEGORIES_ADD_LINK'        => $this->helper->route('pcgf_privatecategories_add'),
                'PCGF_PRIVATECATEGORIES_REMOVE_LINK'     => $this->helper->route('pcgf_privatecategories_remove'),
                'PCGF_PRIVATECATEGORIES_OWNER'           => $event['topic_data']['topic_poster'],
            ));
            // Show add and delete options if the user is allowed
            if ($this->auth->acl_get('f_pcgf_privatecategories_invite_all', $event['forum_id']) || ($this->auth->acl_get('f_pcgf_privatecategories_invite_own', $event['forum_id']) && $this->user->data['user_id'] == $event['topic_data']['topic_poster']))
            {
                $this->template->assign_var('PCGF_PRIVATECATEGORIES_ADD', true);
            }
            if ($this->auth->acl_get('f_pcgf_privatecategories_remove_all', $event['forum_id']) || ($this->auth->acl_get('f_pcgf_privatecategories_remove_own', $event['forum_id']) && $this->user->data['user_id'] == $event['topic_data']['topic_poster']))
            {
                $this->template->assign_var('PCGF_PRIVATECATEGORIES_REMOVE', true);
            }
            return;
        }
        // The user wants to see a topic he is not allowed to see, so we prevent that by throwing an error
        trigger_error($this->user->lang('PCGF_PRIVATECATEGORIES_ERROR_VIEW_NOT_ALLOWED'));
    }

    /**
     * Function that checks which topics should be shown to the user
     *
     * @param array $event The event object which contains specific information
     */
    public function check_category_view_permissions($event)
    {
        $topics = $event['topic_list'];
        for ($i = 0; $i < $event['total_topic_count']; $i++)
        {
            if (!has_permissions($this->user->data['user_id'], $event['rowset'][$topics[$i]]['forum_id'], $event['rowset'][$topics[$i]]['topic_id'], $event['rowset'][$topics[$i]]['topic_poster'], $this->auth, $this->db))
            {
                // If the user doesn't have the permissions to view the topic don't show it on the list
                unset($topics[$i--]);
            }
        }
        $event['topic_list'] = $topics;
    }
}
