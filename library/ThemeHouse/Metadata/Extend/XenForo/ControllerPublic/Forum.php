<?php

/**
 *
 * @see XenForo_ControllerPublic_Forum
 */
class ThemeHouse_Metadata_Extend_XenForo_ControllerPublic_Forum extends XFCP_ThemeHouse_Metadata_Extend_XenForo_ControllerPublic_Forum
{

    /**
     *
     * @see XenForo_ControllerPublic_Forum::actionCreateThread()
     */
    public function actionCreateThread()
    {
        $response = parent::actionCreateThread();

        if ($response instanceof XenForo_ControllerResponse_View) {
            $threadModel = $this->_getThreadModel();

            $forum = $response->params['forum'];

            $thread = $response->params['thread'];
            $thread['node_id'] = $forum['node_id'];

            $response->params['canEditDescriptionMetadata'] = $threadModel->canEditThreadDescriptionMetadata($thread,
                $forum);
            $response->params['canEditKeywordsMetadata'] = $threadModel->canEditThreadKeywordsMetadata($thread, $forum);
            $response->params['canEditRobotsMetadata'] = $threadModel->canEditThreadRobotsMetadata($thread, $forum);
            $response->params['canEditTitleMetadata'] = $threadModel->canEditThreadTitleMetadata($thread, $forum);
        }

        return $response;
    }

    public function actionAddThread()
    {
        $GLOBALS['XenForo_ControllerPublic_Forum'] = $this;

        return parent::actionAddThread();
    }
}