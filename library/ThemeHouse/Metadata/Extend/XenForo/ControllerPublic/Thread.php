<?php

/**
 *
 * @see XenForo_ControllerPublic_Thread
 */
class ThemeHouse_Metadata_Extend_XenForo_ControllerPublic_Thread extends XFCP_ThemeHouse_Metadata_Extend_XenForo_ControllerPublic_Thread
{

    /**
     *
     * @see XenForo_ControllerPublic_Thread::actionIndex()
     */
    public function actionIndex()
    {
        $response = parent::actionIndex();

        if ($response instanceof XenForo_ControllerResponse_View) {
            $threadModel = $this->_getThreadModel();

            $thread = $response->params['thread'];
            $forum = $response->params['forum'];
            $posts = $response->params['posts'];

            $response->params['posts'] = $this->_addAltTagToImagesInPosts($posts, $thread, $forum);

            $response->params['canEditThreadMetadata'] = $threadModel->canEditThreadMetadata($thread, $forum);
        }

        return $response;
    }

    protected function _addAltTagToImagesInPosts(array $posts, array $thread, array $forum)
    {
        $xenOptions = XenForo_Application::get('options');

        if ($xenOptions->th_metadata_postImageAltTagDefault) {

            $altTag = strtr($xenOptions->th_metadata_postImageAltTagDefault,
                array(
                    '{$title}' => $thread['title'],
                    '{$forumTitle}' => $forum['title'],
                    '{$author}' => $thread['username'],
                    '{$keywords}' => $thread['keywords_metadata']
                ));

            foreach ($posts as $postId => $post) {
                if (!empty($post['attachments'])) {
                    foreach ($post['attachments'] as $attachmentId => $attachment) {
                        $attachment['alt'] = strtr($altTag,
                            array(
                                '{$filename}' => $attachment['filename']
                            ));
                        $posts[$postId]['attachments'][$attachmentId] = $attachment;
                    }
                }
            }
        }

        return $posts;
    }

    /**
     *
     * @see XenForo_ControllerPublic_Thread::actionEdit()
     */
    public function actionEdit()
    {
        $response = parent::actionEdit();

        if ($response instanceof XenForo_ControllerResponse_View) {
            $threadModel = $this->_getThreadModel();

            $thread = $response->params['thread'];
            $forum = $response->params['forum'];

            $metadata = $threadModel->getThreadMetadata($thread['thread_id']);
            if ($metadata) {
                $thread = array_merge($metadata, $thread);
            }

            $response->params['thread'] = $thread;

            $response->params['canEditDescriptionMetadata'] = $threadModel->canEditThreadDescriptionMetadata($thread,
                $forum);
            $response->params['canEditKeywordsMetadata'] = $threadModel->canEditThreadKeywordsMetadata($thread, $forum);
            $response->params['canEditRobotsMetadata'] = $threadModel->canEditThreadRobotsMetadata($thread, $forum);
            $response->params['canEditTitleMetadata'] = $threadModel->canEditThreadTitleMetadata($thread, $forum);
        }

        return $response;
    }

    /**
     * Displays a form to edit a thread's metadata.
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionEditMetadata()
    {
        $this->_assertRegistrationRequired();

        $threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);

        $threadModel = $this->_getThreadModel();

        $fetchOptions = array(
            'th_metadata_join' => ThemeHouse_Metadata_Extend_XenForo_Model_Thread::FETCH_METADATA
        );

        $ftpHelper = $this->getHelper('ForumThreadPost');
        list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId, $fetchOptions);

        if (!$threadModel->canEditThreadMetadata($thread, $forum, $errorPhraseKey)) {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($this->isConfirmedPost()) {
            $GLOBALS['XenForo_ControllerPublic_Thread'] = $this;

            $dw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
            $dw->setExistingData($threadId);
            $dw->setExtraData(XenForo_DataWriter_Discussion_Thread::DATA_FORUM, $forum);
            $dw->preSave();

            $dw->save();

            $this->_updateModeratorLogThreadEdit($thread, $dw);
            $thread = $dw->getMergedData();

            // regular redirect
            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildPublicLink('threads', $thread));
        } else {

            $viewParams = array(
                'thread' => $thread,
                'forum' => $forum,

                'canEditDescriptionMetadata' => $threadModel->canEditThreadDescriptionMetadata($thread, $forum),
                'canEditKeywordsMetadata' => $threadModel->canEditThreadKeywordsMetadata($thread, $forum),
                'canEditRobotsMetadata' => $threadModel->canEditThreadRobotsMetadata($thread, $forum),
                'canEditTitleMetadata' => $threadModel->canEditThreadTitleMetadata($thread, $forum),

                'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum)
            );

            return $this->responseView('ThemeHouse_Metadata_ViewPublic_Thread_EditMetadata',
                'th_thread_edit_metadata_metadata', $viewParams);
        }
    }

    public function actionSave()
    {
        $GLOBALS['XenForo_ControllerPublic_Thread'] = $this;

        return parent::actionSave();
    }

    protected function _getThreadForumFetchOptions()
    {
        $fetchOptions = parent::_getThreadForumFetchOptions();

        list($threadFetchOptions, $forumFetchOptions) = $fetchOptions;

        $this->_getThreadModel();

        $threadFetchOptions['th_metadata_join'] = ThemeHouse_Metadata_Extend_XenForo_Model_Thread::FETCH_METADATA;

        return array(
            $threadFetchOptions,
            $forumFetchOptions
        );
    }
}