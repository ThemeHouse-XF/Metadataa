<?php

/**
 *
 * @see XenResource_ControllerPublic_Resource
 */
class ThemeHouse_Metadata_Extend_XenResource_ControllerPublic_Resource extends XFCP_ThemeHouse_Metadata_Extend_XenResource_ControllerPublic_Resource
{

    /**
     *
     * @see XenResource_ControllerPublic_Resource::actionView()
     */
    public function actionView()
    {
        $response = parent::actionView();

        if ($response instanceof XenForo_ControllerResponse_View) {
            $resourceModel = $this->_getResourceModel();

            $resource = $response->params['resource'];
            $category = $response->params['category'];

            $response->params['canEditResourceMetadata'] = $resourceModel->canEditResourceMetadata($resource, $category);
        }

        return $response;
    }

    /**
     *
     * @see XenResource_ControllerPublic_Resource::_getResourceAddOrEditResponse()
     */
    protected function _getResourceAddOrEditResponse(array $resource, array $category, array $attachments = array())
    {
        $response = parent::_getResourceAddOrEditResponse($resource, $category, $attachments);

        if ($response instanceof XenForo_ControllerResponse_View) {
            $resourceModel = $this->_getResourceModel();

            $resource = $response->params['resource'];
            $category = $response->params['category'];

            if (!empty($resource['resource_id'])) {
                $metadata = $resourceModel->getResourceMetadata($resource['resource_id']);
                if ($metadata) {
                    $resource = array_merge($metadata, $resource);

                    $response->params['resource'] = $resource;
                }
            }

            $response->params['canEditDescriptionMetadata'] = $resourceModel->canEditResourceDescriptionMetadata(
                $resource, $category);
            $response->params['canEditKeywordsMetadata'] = $resourceModel->canEditResourceKeywordsMetadata(
                $resource, $category);
            $response->params['canEditRobotsMetadata'] = $resourceModel->canEditResourceRobotsMetadata(
                $resource, $category);
            $response->params['canEditTitleMetadata'] = $resourceModel->canEditResourceTitleMetadata($resource,
                $category);
        }

        return $response;
    }

    /**
     * Displays a form to edit a resource's metadata.
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionEditMetadata()
    {
        $resourceModel = $this->_getResourceModel();

        $fetchOptions = array(
            'join' => XenResource_Model_Resource::FETCH_DESCRIPTION,
            'th_metadata_join' => ThemeHouse_Metadata_Extend_XenResource_Model_Resource::FETCH_METADATA
        );
        list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable(null, $fetchOptions);

        if (!$resourceModel->canEditResourceMetadata($resource, $category, $errorPhraseKey)) {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $resourceId = $resource['resource_id'];

        if ($this->isConfirmedPost()) {
            $GLOBALS['XenResource_ControllerPublic_Resource'] = $this;

            $dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource');
            $dw->setExistingData($resourceId);
            $dw->preSave();

            $dw->save();

            $resource = $dw->getMergedData();

            // regular redirect
            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildPublicLink('resources', $resource));
        } else {

            $viewParams = array(
                'resource' => $resource,
                'category' => $category,

                'canEditDescriptionMetadata' => $resourceModel->canEditResourceDescriptionMetadata($resource,
                    $category),
                'canEditKeywordsMetadata' => $resourceModel->canEditResourceKeywordsMetadata($resource,
                    $category),
                'canEditRobotsMetadata' => $resourceModel->canEditResourceRobotsMetadata($resource, $category),
                'canEditTitleMetadata' => $resourceModel->canEditResourceTitleMetadata($resource, $category),
            );

            return $this->responseView('ThemeHouse_Metadata_ViewPublic_Resource_EditMetadata',
                'th_resource_edit_metadata_metadata', $viewParams);
        }
    }

    /**
     *
     * @see XenResource_ControllerPublic_Resource::actionSave()
     */
    public function actionSave()
    {
        $GLOBALS['XenResource_ControllerPublic_Resource'] = $this;

        return parent::actionSave();
    }

    protected function _getResourceViewInfo(array $fetchOptions = array())
    {
        $this->_getResourceModel();

        $fetchOptions['th_metadata_join'] = ThemeHouse_Metadata_Extend_XenResource_Model_Resource::FETCH_METADATA;

        return parent::_getResourceViewInfo($fetchOptions);
    }
}