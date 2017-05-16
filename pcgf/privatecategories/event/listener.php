<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2016, 2017 MarkusWME
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace pcgf\privatecategories\event;

use pcgf\privatecategories\includes\permission_helper;
use pcgf\privatecategories\migrations\release_1_0_0;
use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\factory;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/** @version 1.2.4 */
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

    /** @var  permission_helper $permission_helper The permission helper object */
    protected $permission_helper;

    /** @var string $table_prefix The phpBB table prefix */
    protected $table_prefix;

    /** @var int $search_results Search result count */
    protected $search_results;

    /**
     * Constructor for the extension listener
     *
     * @access public
     * @since  1.0.0
     *
     * @param factory           $db                Database object
     * @param config            $config            Configuration object
     * @param template          $template          Template object
     * @param user              $user              The user object
     * @param auth              $auth              The authentication object
     * @param helper            $helper            $helper The helper object
     * @param permission_helper $permission_helper The permission helper object
     * @param string            $table_prefix      The phpBB table prefix
     */
    public function __construct(factory $db, config $config, template $template, user $user, auth $auth, helper $helper, permission_helper $permission_helper, $table_prefix)
    {
        $this->db = $db;
        $this->config = $config;
        $this->template = $template;
        $this->user = $user;
        $this->auth = $auth;
        $this->helper = $helper;
        $this->permission_helper = $permission_helper;
        $this->table_prefix = $table_prefix;
        // Load language data of the extension
        $this->user->add_lang_ext('pcgf/privatecategories', 'privatecategories');
    }

    /**
     * Function that returns the subscribed events
     *
     * @access public
     * @since  1.0.0
     *
     * @return array The subscribed event list
     */
    static public function getSubscribedEvents()
    {
        return array(
            'core.permissions'                          => 'add_permission_data',
            'core.viewtopic_get_post_data'              => 'check_topic_view_permissions',
            'core.viewforum_modify_topics_data'         => 'check_category_view_permissions',
            'core.display_forums_modify_forum_rows'     => 'obfuscate_forum_data',
            'core.search_modify_rowset'                 => 'filter_search_results',
            'core.search_results_modify_search_title'   => 'set_search_result_count',
            'core.display_user_activity_modify_actives' => 'set_activity',
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
        if ($this->permission_helper->has_permissions($this->user->data['user_id'], $event['forum_id'], $event['topic_id'], $event['topic_data']['topic_poster']))
        {
            // Add the topic owner to the list of allowed viewers
            $allowed_users = array($event['topic_data']['topic_first_poster_name'] => array($event['topic_data']['topic_poster'], $event['topic_data']['topic_first_poster_name'], $event['topic_data']['topic_first_poster_colour']));
            $allowed_groups = array();
            // Allow all users with the see all permission to view the topic
            $allowed_user_ids = $this->auth->acl_get_list(false, 'f_pcgf_privatecategories_see_all', $event['forum_id']);
            if (isset($allowed_user_ids[$event['forum_id']]['f_pcgf_privatecategories_see_all']))
            {
                $allowed_user_ids = $allowed_user_ids[$event['forum_id']]['f_pcgf_privatecategories_see_all'];
            }
            else
            {
                $allowed_user_ids = array();
            }
            $allowed_group_ids = array();
            // Get all users and groups which are allowed to view the topic
            $query = 'SELECT user, is_group
                FROM ' . $this->table_prefix . release_1_0_0::PRIVATECATEGORY_PERMISSION_TABLE . '
                WHERE topic = ' . ((int)$event['topic_id']);
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
                $allowed_users_string .= $this->permission_helper->get_formatted_user($allowed_user) . ',&nbsp;';
            }
            foreach ($allowed_groups as $allowed_group)
            {
                $allowed_groups_string .= $this->permission_helper->get_formatted_group($allowed_group) . ',&nbsp;';
            }
            $this->template->assign_vars(array(
                'PCGF_PRIVATECATEGORIES_ALLOWED_USERS'   => substr($allowed_users_string, 0, -7),
                'PCGF_PRIVATECATEGORIES_ALLOWED_GROUPS'  => substr($allowed_groups_string, 0, -7),
                'PCGF_PRIVATECATEGORIES_SUGGESTION_LINK' => $this->helper->route('pcgf_privatecategories_suggestions'),
                'PCGF_PRIVATECATEGORIES_ADD_LINK'        => $this->helper->route('pcgf_privatecategories_add'),
                'PCGF_PRIVATECATEGORIES_REMOVE_LINK'     => $this->helper->route('pcgf_privatecategories_remove'),
                'PCGF_PRIVATECATEGORIES_OWNER'           => $event['topic_data']['topic_poster'],
                'PCGF_IS_PRIVATETOPIC'                   => $this->permission_helper->is_private($event['forum_id']),
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
     * @access public
     * @since  1.1.0
     *
     * @param array $event The event object which contains specific information
     */
    public function check_category_view_permissions($event)
    {
        $topics = $event['topic_list'];
        for ($i = 0; $i < $event['total_topic_count']; $i++)
        {
            if (!$this->permission_helper->has_permissions($this->user->data['user_id'], $event['rowset'][$topics[$i]]['forum_id'], $event['rowset'][$topics[$i]]['topic_id'], $event['rowset'][$topics[$i]]['topic_poster']))
            {
                // If the user doesn't have the permissions to view the topic don't show it on the list
                unset($topics[$i]);
            }
        }
        $event['topic_list'] = $topics;
    }

    /**
     * Function that obfuscates all forums so that only visible topics (title and count) will be shown to the user
     *
     * @access public
     * @since  1.1.0
     *
     * @param array $event The event object
     */
    public function obfuscate_forum_data($event)
    {
        $forums = $event['forum_rows'];
        foreach ($forums as $forum)
        {
            if ($forum['parent_id'] == $event['branch_root_id'])
            {
                $forum['forum_last_post_id'] = 0;
                $forum['forum_last_post_subject'] = '';
                $forum['forum_last_post_time'] = 0;
                $forum['forum_last_poster_id'] = 0;
                $forum['forum_last_poster_name'] = '';
                $forum['forum_last_poster_colour'] = '';
                $forum['forum_topics'] = 0;
                $forum['forum_posts'] = 0;
                // Get subforums and count posts
                $forum_ids = $this->get_subforums(array($forum['forum_id']));
                $query = 'SELECT topic_id, forum_id, topic_last_post_id, topic_last_post_subject, topic_last_post_time, topic_last_poster_id, topic_last_poster_name, topic_last_poster_colour, topic_poster, topic_posts_approved + topic_posts_unapproved + topic_posts_softdeleted AS posts
                    FROM ' . TOPICS_TABLE . '
                    WHERE ' . $this->db->sql_in_set('forum_id', $forum_ids);
                $result = $this->db->sql_query($query);
                while ($topic = $this->db->sql_fetchrow($result))
                {
                    if ($this->permission_helper->has_permissions($this->user->data['user_id'], $topic['forum_id'], $topic['topic_id'], $topic['topic_poster']))
                    {
                        if ($topic['topic_last_post_time'] > $forum['forum_last_post_time'])
                        {
                            // Show the last visible post
                            $forum['forum_last_post_id'] = $topic['topic_last_post_id'];
                            $forum['forum_last_post_subject'] = $topic['topic_last_post_subject'];
                            $forum['forum_last_post_time'] = $topic['topic_last_post_time'];
                            $forum['forum_last_poster_id'] = $topic['topic_last_poster_id'];
                            $forum['forum_last_poster_name'] = $topic['topic_last_poster_name'];
                            $forum['forum_last_poster_colour'] = $topic['topic_last_poster_colour'];
                        }
                        $forum['forum_topics']++;
                        $forum['forum_posts'] += $topic['posts'];
                    }
                }
                $this->db->sql_freeresult($result);
                // Reassign the forum
                $forums[$forum['forum_id']] = $forum;
            }
        }
        $event['forum_rows'] = $forums;
    }

    /**
     * Function that retrieves all subforums to a given forum list
     *
     * @access protected
     * @since  1.1.0
     *
     * @param array $forum_ids A list of forum ids
     *
     * @return array List with all forum ids including the given ones
     */
    protected function get_subforums($forum_ids)
    {
        if (count($forum_ids) <= 0)
        {
            return array();
        }
        // Get all subforum ids recursively
        $query = 'SELECT forum_id
            FROM ' . FORUMS_TABLE . '
            WHERE ' . $this->db->sql_in_set('parent_id', $forum_ids);
        $result = $this->db->sql_query($query);
        $subforums = array();
        while ($row = $this->db->sql_fetchrow($result))
        {
            array_push($subforums, $row['forum_id']);
        }
        $this->db->sql_freeresult($result);
        return array_merge($forum_ids, $this->get_subforums($subforums));
    }

    /**
     * Function that filters search results
     *
     * @access public
     * @since  1.2.0
     *
     * @param array $event The event data
     */
    public function filter_search_results($event)
    {
        $rowset = $event['rowset'];
        if ($event['show_results'] == 'topics')
        {
            foreach ($rowset as $row)
            {
                if (!$this->permission_helper->has_permissions($this->user->data['user_id'], $row['forum_id'], $row['topic_id'], $row['topic_poster']))
                {
                    unset($rowset[$row['topic_id']]);
                }
            }
        }
        else
        {
            $post_count = count($rowset);
            for ($i = 0; $i < $post_count; $i++)
            {
                if (!$this->permission_helper->has_permissions($this->user->data['user_id'], $rowset[$i]['forum_id'], $rowset[$i]['topic_id'], $rowset[$i]['topic_poster']))
                {
                    unset($rowset[$i]);
                }
            }
        }
        $event['rowset'] = $rowset;
        $this->search_results = count($rowset);
    }

    /**
     * Function that displays the correct result count for search queries
     *
     * @access public
     * @since  1.2.4
     *
     * @param array $event The event data
     */
    public function set_search_result_count($event)
    {
        $event['total_match_count'] = $this->search_results;
        $this->template->assign_var('SEARCH_MATCHES', $this->user->lang('FOUND_SEARCH_MATCHES', $this->search_results));
        $this->template->assign_var('TOTAL_MATCHES', $this->search_results);
    }

    /**
     * Function that corrects the activity statistics so that no user can see activities he should not see
     *
     * @access public
     * @since  1.2.4
     *
     * @param array $event The event data
     */
    public function set_activity($event)
    {
        $forum_list = array();
        $topic_list = array();
        $post_count = 0;
        // Get all posts to calculate the correct post count and activity statistics
        $query = 'SELECT topic_id, forum_id, poster_id
                    FROM ' . POSTS_TABLE . '
                    WHERE poster_id = ' . ((int)$event['userdata']['user_id']);
        $result = $this->db->sql_query($query);
        while ($post = $this->db->sql_fetchrow($result))
        {
            if ($this->permission_helper->has_permissions($this->user->data['user_id'], $post['forum_id'], $post['topic_id'], $post['poster_id']))
            {
                $post_count++;
                if (isset($forum_list[$post['forum_id']]))
                {
                    $forum_list[$post['forum_id']]++;
                }
                else
                {
                    $forum_list[$post['forum_id']] = 1;
                }
                if (isset($topic_list[$post['topic_id']]))
                {
                    $topic_list[$post['topic_id']]++;
                }
                else
                {
                    $topic_list[$post['topic_id']] = 1;
                }
            }
        }
        $this->db->sql_freeresult($result);
        // Assign the post count
        $userdata = $event['userdata'];
        $userdata['user_posts'] = $post_count;
        $event['userdata'] = $userdata;
        // Set correct most active forum
        $max_count = 0;
        $max_count_id = 0;
        foreach ($forum_list as $forum => $count)
        {
            if ($count > $max_count)
            {
                $max_count = $count;
                $max_count_id = $forum;
            }
        }
        if ($max_count_id > 0)
        {
            $query = 'SELECT forum_name
                        FROM ' . FORUMS_TABLE . '
                        WHERE forum_id = ' . ((int)$max_count_id);
            $result = $this->db->sql_query($query);
            $forum = $this->db->sql_fetchrow($result);
            $this->db->sql_freeresult($result);
            $forum = array(
                'forum_id'   => $max_count_id,
                'num_posts'  => $max_count,
                'forum_name' => $forum['forum_name'],
            );
            $event['active_f_row'] = $forum;
        }
        // Set correct most active topic
        $max_count = 0;
        $max_count_id = 0;
        foreach ($topic_list as $topic => $count)
        {
            if ($count > $max_count)
            {
                $max_count = $count;
                $max_count_id = $topic;
            }
        }
        if ($max_count_id > 0)
        {
            $query = 'SELECT topic_title
                        FROM ' . TOPICS_TABLE . '
                        WHERE topic_id = ' . ((int)$max_count_id);
            $result = $this->db->sql_query($query);
            $topic = $this->db->sql_fetchrow($result);
            $this->db->sql_freeresult($result);
            $topic = array(
                'topic_id'    => $max_count_id,
                'num_posts'   => $max_count,
                'topic_title' => $topic['topic_title'],
            );
            $event['active_t_row'] = $topic;
        }
    }
}
