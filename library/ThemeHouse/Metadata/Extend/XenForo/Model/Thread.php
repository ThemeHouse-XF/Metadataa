<?php

/**
 *
 * @see XenForo_Model_Thread
 */
class ThemeHouse_Metadata_Extend_XenForo_Model_Thread extends XFCP_ThemeHouse_Metadata_Extend_XenForo_Model_Thread
{

    const FETCH_METADATA = 0x01;

    /**
     *
     * @see XenForo_Model_Thread::prepareThreadFetchOptions()
     */
    public function prepareThreadFetchOptions(array $fetchOptions)
    {
        $threadFetchOptions = parent::prepareThreadFetchOptions($fetchOptions);

        $selectFields = $threadFetchOptions['selectFields'];
        $joinTables = $threadFetchOptions['joinTables'];
        $orderClause = $threadFetchOptions['orderClause'];

        if (!empty($fetchOptions['th_metadata_join'])) {
            if ($fetchOptions['th_metadata_join'] & self::FETCH_METADATA) {
                $selectFields .= ',
					metadata.description_metadata, metadata.keywords_metadata, metadata.robots_metadata, metadata.title_metadata';
                $joinTables .= '
					LEFT JOIN xf_thread_metadata_th AS metadata ON
						(metadata.thread_id = thread.thread_id)';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables,
            'orderClause' => $orderClause
        );
    }

    /**
     *
     * @see XenForo_Model_Thread::prepareThread()
     */
    public function prepareThread(array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
    {
        $thread = parent::prepareThread($thread, $forum, $nodePermissions, $viewingUser);

        $xenOptions = XenForo_Application::get('options');

        if (!empty($thread['title_metadata'])) {
            $thread['titleMetadata'] = $thread['title_metadata'];
        } elseif ($xenOptions->th_metadata_threadTitleMetadataDefault) {
            $thread['titleMetadata'] = strtr($xenOptions->th_metadata_threadTitleMetadataDefault,
                array(
                    '{$title}' => $thread['title'],
                    '{$forumTitle}' => $forum['title'],
                    '{$author}' => $thread['username']
                ));
        }

        if (!empty($thread['description_metadata'])) {
            $thread['descriptionMetadata'] = $thread['description_metadata'];
        } elseif ($xenOptions->th_metadata_threadDescriptionMetadataDefault) {
            $thread['descriptionMetadata'] = strtr($xenOptions->th_metadata_threadDescriptionMetadataDefault,
                array(
                    '{$title}' => $thread['title'],
                    '{$forumTitle}' => $forum['title'],
                    '{$author}' => $thread['username']
                ));
        }

        if (!empty($thread['keywords_metadata'])) {
            $thread['keywordsMetadata'] = $thread['keywords_metadata'];
        } elseif ($xenOptions->th_metadata_threadKeywordsMetadataDefault) {
            $thread['keywordsMetadata'] = strtr($xenOptions->th_metadata_threadKeywordsMetadataDefault,
                array(
                    '{$title}' => $thread['title'],
                    '{$forumTitle}' => $forum['title'],
                    '{$author}' => $thread['username']
                ));
        }

        if (!empty($thread['robots_metadata'])) {
            $thread['robotsMetadata'] = $thread['robots_metadata'];
        } elseif ($xenOptions->th_metadata_threadRobotsMetadataDefault) {
            $thread['robotsMetadata'] = $xenOptions->th_metadata_threadRobotsMetadataDefault;
        }

        return $thread;
    }

    public function getThreadMetadata($threadId)
    {
        return $this->_getDb()->fetchRow(
            '
                SELECT * FROM xf_thread_metadata_th
                WHERE thread_id = ?
            ', $threadId);
    }

    /**
     * Determines if any thread metadata can be edited with the given
     * permissions.
     * This does not check thread viewing permissions.
     *
     * @param array $thread Info about the thread
     * @param array $forum Info about the forum the thread is in
     * @param string $errorPhraseKey Returned phrase key for a specific error
     * @param array|null $nodePermissions
     * @param array|null $viewingUser
     *
     * @return boolean
     */
    public function canEditThreadMetadata(array $thread, array $forum, &$errorPhraseKey = '',
        array $nodePermissions = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if (!$thread['discussion_open'] &&
             !$this->canLockUnlockThread($thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser)) {
            $errorPhraseKey = 'you_may_not_perform_this_action_because_discussion_is_closed';
            return false;
        }

        if (XenForo_Permission::hasContentPermission($nodePermissions, 'manageAnyThread')) {
            return true;
        }

        if ($thread['user_id'] == $viewingUser['user_id'] &&
             XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnPost')) {
            $editLimit = XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnPostTimeLimit');

            if ($editLimit != -1 && (!$editLimit || $thread['post_date'] < XenForo_Application::$time - 60 * $editLimit)) {
                $errorPhraseKey = array(
                    'message_edit_time_limit_expired',
                    'minutes' => $editLimit
                );
                return false;
            }

            if (empty($forum['allow_posting'])) {
                $errorPhraseKey = 'you_may_not_perform_this_action_because_forum_does_not_allow_posting';
                return false;
            }

            if (XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnThreadDescMeta')) {
                return true;
            } elseif (XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnThreadKeywordsMeta')) {
                return true;
            } elseif (XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnThreadRobotsMeta')) {
                return true;
            } elseif (XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnThreadTitleMeta')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if the thread description metadata can be edited with the
     * given permissions.
     * This does not check thread viewing permissions.
     *
     * @param array $thread Info about the thread
     * @param array $forum Info about the forum the thread is in
     * @param string $errorPhraseKey Returned phrase key for a specific error
     * @param array|null $nodePermissions
     * @param array|null $viewingUser
     *
     * @return boolean
     */
    public function canEditThreadDescriptionMetadata(array $thread, array $forum, &$errorPhraseKey = '',
        array $nodePermissions = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if (!$thread['discussion_open'] &&
             !$this->canLockUnlockThread($thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser)) {
            $errorPhraseKey = 'you_may_not_perform_this_action_because_discussion_is_closed';
            return false;
        }

        if (XenForo_Permission::hasContentPermission($nodePermissions, 'manageAnyThread')) {
            return true;
        }

        if ($thread['user_id'] == $viewingUser['user_id'] &&
             XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnPost')) {
            $editLimit = XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnPostTimeLimit');

            if ($editLimit != -1 && (!$editLimit || $thread['post_date'] < XenForo_Application::$time - 60 * $editLimit)) {
                $errorPhraseKey = array(
                    'message_edit_time_limit_expired',
                    'minutes' => $editLimit
                );
                return false;
            }

            if (empty($forum['allow_posting'])) {
                $errorPhraseKey = 'you_may_not_perform_this_action_because_forum_does_not_allow_posting';
                return false;
            }

            return XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnThreadDescMeta');
        }

        return false;
    }

    /**
     * Determines if the thread keywords metadata can be edited with the
     * given permissions.
     * This does not check thread viewing permissions.
     *
     * @param array $thread Info about the thread
     * @param array $forum Info about the forum the thread is in
     * @param string $errorPhraseKey Returned phrase key for a specific error
     * @param array|null $nodePermissions
     * @param array|null $viewingUser
     *
     * @return boolean
     */
    public function canEditThreadKeywordsMetadata(array $thread, array $forum, &$errorPhraseKey = '',
        array $nodePermissions = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if (!$thread['discussion_open'] &&
             !$this->canLockUnlockThread($thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser)) {
            $errorPhraseKey = 'you_may_not_perform_this_action_because_discussion_is_closed';
            return false;
        }

        if (XenForo_Permission::hasContentPermission($nodePermissions, 'manageAnyThread')) {
            return true;
        }

        if ($thread['user_id'] == $viewingUser['user_id'] &&
             XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnPost')) {
            $editLimit = XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnPostTimeLimit');

            if ($editLimit != -1 && (!$editLimit || $thread['post_date'] < XenForo_Application::$time - 60 * $editLimit)) {
                $errorPhraseKey = array(
                    'message_edit_time_limit_expired',
                    'minutes' => $editLimit
                );
                return false;
            }

            if (empty($forum['allow_posting'])) {
                $errorPhraseKey = 'you_may_not_perform_this_action_because_forum_does_not_allow_posting';
                return false;
            }

            return XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnThreadKeywordsMeta');
        }

        return false;
    }

    /**
     * Determines if the thread robots metadata can be edited with the
     * given permissions.
     * This does not check thread viewing permissions.
     *
     * @param array $thread Info about the thread
     * @param array $forum Info about the forum the thread is in
     * @param string $errorPhraseKey Returned phrase key for a specific error
     * @param array|null $nodePermissions
     * @param array|null $viewingUser
     *
     * @return boolean
     */
    public function canEditThreadRobotsMetadata(array $thread, array $forum, &$errorPhraseKey = '',
        array $nodePermissions = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if (!$thread['discussion_open'] &&
             !$this->canLockUnlockThread($thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser)) {
            $errorPhraseKey = 'you_may_not_perform_this_action_because_discussion_is_closed';
            return false;
        }

        if (XenForo_Permission::hasContentPermission($nodePermissions, 'manageAnyThread')) {
            return true;
        }

        if ($thread['user_id'] == $viewingUser['user_id'] &&
             XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnPost')) {
            $editLimit = XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnPostTimeLimit');

            if ($editLimit != -1 && (!$editLimit || $thread['post_date'] < XenForo_Application::$time - 60 * $editLimit)) {
                $errorPhraseKey = array(
                    'message_edit_time_limit_expired',
                    'minutes' => $editLimit
                );
                return false;
            }

            if (empty($forum['allow_posting'])) {
                $errorPhraseKey = 'you_may_not_perform_this_action_because_forum_does_not_allow_posting';
                return false;
            }

            return XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnThreadRobotsMeta');
        }

        return false;
    }

    /**
     * Determines if the thread title metadata can be edited with the
     * given permissions.
     * This does not check thread viewing permissions.
     *
     * @param array $thread Info about the thread
     * @param array $forum Info about the forum the thread is in
     * @param string $errorPhraseKey Returned phrase key for a specific error
     * @param array|null $nodePermissions
     * @param array|null $viewingUser
     *
     * @return boolean
     */
    public function canEditThreadTitleMetadata(array $thread, array $forum, &$errorPhraseKey = '',
        array $nodePermissions = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if (!$thread['discussion_open'] &&
             !$this->canLockUnlockThread($thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser)) {
            $errorPhraseKey = 'you_may_not_perform_this_action_because_discussion_is_closed';
            return false;
        }

        if (XenForo_Permission::hasContentPermission($nodePermissions, 'manageAnyThread')) {
            return true;
        }

        if ($thread['user_id'] == $viewingUser['user_id'] &&
             XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnPost')) {
            $editLimit = XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnPostTimeLimit');

            if ($editLimit != -1 && (!$editLimit || $thread['post_date'] < XenForo_Application::$time - 60 * $editLimit)) {
                $errorPhraseKey = array(
                    'message_edit_time_limit_expired',
                    'minutes' => $editLimit
                );
                return false;
            }

            if (empty($forum['allow_posting'])) {
                $errorPhraseKey = 'you_may_not_perform_this_action_because_forum_does_not_allow_posting';
                return false;
            }

            return XenForo_Permission::hasContentPermission($nodePermissions, 'editOwnThreadTitleMeta');
        }

        return false;
    }
}