<?php

class ThemeHouse_Metadata_Install_Controller extends ThemeHouse_Install
{

    protected $_resourceManagerUrl = 'https://xenforo.com/community/resources/metadata.4036/';

    protected $_minVersionId = 1020000;

    protected $_minVersionString = '1.2.0';

    protected function _getTables()
    {
        return array(
            'xf_thread_metadata_th' => array(
                'thread_id' => 'int UNSIGNED NOT NULL PRIMARY KEY',
                'description_metadata' => 'mediumtext',
                'keywords_metadata' => 'mediumtext',
                'robots_metadata' => 'mediumtext',
                'title_metadata' => 'mediumtext'
            ),
            'xf_resource_metadata_th' => array(
                'resource_id' => 'int UNSIGNED NOT NULL PRIMARY KEY',
                'description_metadata' => 'mediumtext',
                'keywords_metadata' => 'mediumtext',
                'robots_metadata' => 'mediumtext',
                'title_metadata' => 'mediumtext'
            )
        );
    }

    protected function _getPermissionEntries()
    {
        return array(
            'forum' => array(
                'editOwnThreadDescMeta' => array(
                    'permission_group_id' => 'forum',
                    'permission_id' => 'editOwnThreadTitle'
                ),
                'editOwnThreadKeywordsMeta' => array(
                    'permission_group_id' => 'forum',
                    'permission_id' => 'editOwnThreadTitle'
                ),
                'editOwnThreadRobotsMeta' => array(
                    'permission_group_id' => 'forum',
                    'permission_id' => 'editOwnThreadTitle'
                ),
                'editOwnThreadTitleMeta' => array(
                    'permission_group_id' => 'forum',
                    'permission_id' => 'editOwnThreadTitle'
                )
            ),
            'resource' => array(
                'editOwnResDescMeta' => array(
                    'permission_group_id' => 'resource',
                    'permission_id' => 'updateSelf'
                ),
                'editOwnResKeywordsMeta' => array(
                    'permission_group_id' => 'resource',
                    'permission_id' => 'updateSelf'
                ),
                'editOwnResRobotsMeta' => array(
                    'permission_group_id' => 'resource',
                    'permission_id' => 'updateSelf'
                ),
                'editOwnResTitleMeta' => array(
                    'permission_group_id' => 'resource',
                    'permission_id' => 'updateSelf'
                )
            )
        );
    }

    protected function _postInstall()
    {
        $this->_db->query(
            '
            INSERT IGNORE INTO xf_thread_metadata_th (thread_id)
            SELECT thread_id FROM xf_thread
        ');
        if ($this->_isAddOnInstalled('XenResource')) {
            $this->_db->query(
                '
                INSERT IGNORE INTO xf_resource_metadata_th (resource_id)
                SELECT resource_id FROM xf_resource
            ');
        }

        $addOn = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('YoYo_');
        if ($addOn) {
            $db->query("
                INSERT INTO xf_thread_metadata_th (thread_id, description_metadata, keywords_metadata, robots_metadata, title_metadata)
                    SELECT thread_id, description_metadata, keywords_metadata, robots_metadata, title_metadata
                        FROM xf_waindigoread_metadata_waindigo"); 
            $db->query("
                INSERT INTO xf_resource_metadata_th (resource_id, description_metadata, keywords_metadata, robots_metadata, title_metadata)
                    SELECT resource_id, description_metadata, keywords_metadata, robots_metadata, title_metadata
                        FROM xf_resource_metadata_waindigo"); 
        }
    }
}