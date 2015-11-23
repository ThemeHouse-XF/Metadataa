<?php

/**
 *
 * @see XenResource_DataWriter_Resource
 */
class ThemeHouse_Metadata_Extend_XenResource_DataWriter_Resource extends XFCP_ThemeHouse_Metadata_Extend_XenResource_DataWriter_Resource
{

    /**
     *
     * @see XenResource_DataWriter_Resource::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();

        $fields['xf_resource_metadata_th'] = array(
            'resource_id' => array(
                'type' => self::TYPE_UINT,
                'default' => array(
                    'xf_resource',
                    'resource_id'
                ),
                'required' => true
            ),
            'description_metadata' => array(
                'type' => self::TYPE_STRING,
                'default' => ''
            ),
            'keywords_metadata' => array(
                'type' => self::TYPE_STRING,
                'default' => ''
            ),
            'robots_metadata' => array(
                'type' => self::TYPE_STRING,
                'default' => ''
            ),
            'title_metadata' => array(
                'type' => self::TYPE_STRING,
                'default' => ''
            )
        );

        return $fields;
    }

    /**
     *
     * @see XenResource_DataWriter_Resource::_getExistingData()
     */
    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data, 'resource_id')) {
            return false;
        }

        if (!$resource = $this->_getResourceModel()->getResourceById($id)) {
            return false;
        }

        $returnData = $this->getTablesDataFromArray($resource);

        return $returnData;
    }

    /**
     *
     * @see XenForo_DataWriter::_setAutoIncrementValue()
     */
    protected function _setAutoIncrementValue($insertId, $tableName, $updateAll = false)
    {
        $return = parent::_setAutoIncrementValue($insertId, $tableName, $updateAll);

        if (XenForo_Application::$versionId >= 1040470) {
            return $return;
        }

        if (!$return) {
            return false;
        }

        if (!$updateAll) {
            return true;
        }

        $field = $this->_getAutoIncrementField($tableName);

        $insertId += 0;

        foreach ($this->_fields as $table => $fieldData) {
            foreach ($fieldData as $fieldName => $fieldType) {
                if (!isset($fieldType['default']) || !is_array($fieldType['default'])) {
                    continue;
                }

                if ($fieldType['default'][0] == $tableName && $field != $fieldName && $fieldType['default'][1] == $field &&
                     !$this->get($fieldName, $table)) {
                    $this->_newData[$table][$fieldName] = $insertId;
                }
            }
        }
        return true;
    }

    /**
     *
     * @see XenResource_DataWriter_Resource::_preSave()
     */
    protected function _preSave()
    {
        parent::_preSave();

        if (!empty($GLOBALS['XenResource_ControllerPublic_Resource'])) {
            /* @var $controller XenResource_ControllerPublic_Resource */
            $controller = $GLOBALS['XenResource_ControllerPublic_Resource'];

            $dwInput = $controller->getInput()->filter(
                array(
                    'title_metadata' => XenForo_Input::STRING,
                    'description_metadata' => XenForo_Input::STRING,
                    'keywords_metadata' => XenForo_Input::STRING,
                    'robots_metadata' => XenForo_Input::STRING,
                    'metadata_shown' => XenForo_Input::UINT
                ));

            if ($dwInput['metadata_shown']) {
                unset($dwInput['metadata_shown']);
                $resourceModel = $this->_getResourceModel();

                $resource = $this->getMergedData();

                if (!$resourceModel->canEditResourceDescriptionMetadata($resource, $resource)) {
                    unset($dwInput['description_metadata']);
                }
                if (!$resourceModel->canEditResourceKeywordsMetadata($resource, $resource)) {
                    unset($dwInput['keywords_metadata']);
                }
                if (!$resourceModel->canEditResourceRobotsMetadata($resource, $resource)) {
                    unset($dwInput['robots_metadata']);
                }
                if (!$resourceModel->canEditResourceTitleMetadata($resource, $resource)) {
                    unset($dwInput['title_metadata']);
                }

                $this->bulkSet($dwInput);
            }
        }
    }
}