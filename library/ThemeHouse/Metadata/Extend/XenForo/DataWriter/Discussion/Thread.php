<?php

/**
 *
 * @see XenForo_DataWriter_Discussion_Thread
 */
class ThemeHouse_Metadata_Extend_XenForo_DataWriter_Discussion_Thread extends XFCP_ThemeHouse_Metadata_Extend_XenForo_DataWriter_Discussion_Thread
{

    /**
     *
     * @see XenForo_DataWriter_Discussion_Thread::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();

        $fields['xf_thread_metadata_th'] = array(
            'thread_id' => array(
                'type' => self::TYPE_UINT,
                'default' => array(
                    'xf_thread',
                    'thread_id'
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
     * @see XenForo_DataWriter_Discussion_Thread::_getExistingData()
     */
    protected function _getExistingData($data)
    {
        if (!$threadId = $this->_getExistingPrimaryKey($data, 'thread_id')) {
            return false;
        }

        if (!$thread = $this->_getThreadModel()->getThreadById($threadId)) {
            return false;
        }

        $returnData = $this->getTablesDataFromArray($thread);

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
     * @see XenForo_DataWriter::preSave()
     */
    public function preSave()
    {
        parent::preSave();

        if (!empty($GLOBALS['XenForo_ControllerPublic_Thread'])) {
            /* @var $controller XenForo_ControllerPublic_Thread */
            $controller = $GLOBALS['XenForo_ControllerPublic_Thread'];
        } elseif (!empty($GLOBALS['XenForo_ControllerPublic_Forum'])) {
            /* @var $controller XenForo_ControllerPublic_Forum */
            $controller = $GLOBALS['XenForo_ControllerPublic_Forum'];
        }

        if (!empty($controller)) {
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
                $threadModel = $this->_getThreadModel();

                $thread = $this->getMergedData();

                $forum = $this->_getForumData();

                if (!$threadModel->canEditThreadDescriptionMetadata($thread, $forum)) {
                    unset($dwInput['description_metadata']);
                }
                if (!$threadModel->canEditThreadKeywordsMetadata($thread, $forum)) {
                    unset($dwInput['keywords_metadata']);
                }
                if (!$threadModel->canEditThreadRobotsMetadata($thread, $forum)) {
                    unset($dwInput['robots_metadata']);
                }
                if (!$threadModel->canEditThreadTitleMetadata($thread, $forum)) {
                    unset($dwInput['title_metadata']);
                }

                $options['setAfterPreSave'] = true;
                $this->bulkSet($dwInput, $options);
            }
        }
    }
}