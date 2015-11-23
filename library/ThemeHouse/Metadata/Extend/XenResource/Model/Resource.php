<?php

/**
 *
 * @see XenResource_Model_Resource
 */
class ThemeHouse_Metadata_Extend_XenResource_Model_Resource extends XFCP_ThemeHouse_Metadata_Extend_XenResource_Model_Resource
{

    const FETCH_METADATA = 0x01;

    /**
     *
     * @see XenResource_Model_Resource::prepareResourceFetchOptions()
     */
    public function prepareResourceFetchOptions(array $fetchOptions)
    {
        $resourceFetchOptions = parent::prepareResourceFetchOptions($fetchOptions);

        $selectFields = $resourceFetchOptions['selectFields'];
        $joinTables = $resourceFetchOptions['joinTables'];

        if (!empty($fetchOptions['th_metadata_join'])) {
            if ($fetchOptions['th_metadata_join'] & self::FETCH_METADATA) {
                $selectFields .= ',
					metadata.description_metadata, metadata.keywords_metadata, metadata.robots_metadata, metadata.title_metadata';
                $joinTables .= '
					LEFT JOIN xf_resource_metadata_th AS metadata ON
						(metadata.resource_id = resource.resource_id)';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables
        );
    }

    /**
     *
     * @see XenResource_Model_Resource::prepareResource()
     */
    public function prepareResource(array $resource, array $category = null, array $viewingUser = null)
    {
        $resource = parent::prepareResource($resource, $category, $viewingUser);

        $xenOptions = XenForo_Application::get('options');

        if (!empty($resource['title_metadata'])) {
            $resource['titleMetadata'] = $resource['title_metadata'];
        } elseif ($xenOptions->th_metadata_resourceTitleMetadataDefault) {
            $resource['titleMetadata'] = strtr($xenOptions->th_metadata_resourceTitleMetadataDefault,
                array(
                    '{$title}' => $resource['title'],
                    '{$categoryTitle}' => $category['category_title'],
                    '{$author}' => $resource['username']
                ));
        }

        if (!empty($resource['description_metadata'])) {
            $resource['descriptionMetadata'] = $resource['description_metadata'];
        } elseif ($xenOptions->th_metadata_resourceDescriptionMetadataDefault) {
            $resource['descriptionMetadata'] = strtr($xenOptions->th_metadata_resourceDescriptionMetadataDefault,
                array(
                    '{$title}' => $resource['title'],
                    '{$categoryTitle}' => $category['category_title'],
                    '{$author}' => $resource['username']
                ));
        }

        if (!empty($resource['keywords_metadata'])) {
            $resource['keywordsMetadata'] = $resource['keywords_metadata'];
        } elseif ($xenOptions->th_metadata_resourceKeywordsMetadataDefault) {
            $resource['keywordsMetadata'] = strtr($xenOptions->th_metadata_resourceKeywordsMetadataDefault,
                array(
                    '{$title}' => $resource['title'],
                    '{$categoryTitle}' => $category['category_title'],
                    '{$author}' => $resource['username']
                ));
        }

        if (!empty($resource['robots_metadata'])) {
            $resource['robotsMetadata'] = $resource['robots_metadata'];
        } elseif ($xenOptions->th_metadata_resourceRobotsMetadataDefault) {
            $resource['robotsMetadata'] = $xenOptions->th_metadata_resourceRobotsMetadataDefault;
        }

        return $resource;
    }

    public function getResourceMetadata($resourceId)
    {
        return $this->_getDb()->fetchRow(
            '
                SELECT * FROM xf_resource_metadata_th
                WHERE resource_id = ?
            ', $resourceId);
    }

    /**
     * Determines if any resource metadata can be edited with the given
     * permissions.
     * This does not check resource viewing permissions.
     *
     * @param array $resource
     * @param array $category
     * @param string $errorPhraseKey
     * @param array $viewingUser
     * @param array|null $categoryPermissions
     *
     * @return boolean
     */
    public function canEditResourceMetadata(array $resource, array $category, &$errorPhraseKey = '',
        array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if (XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny')) {
            return true;
        }

        if ($resource['user_id'] == $viewingUser['user_id'] &&
             XenForo_Permission::hasContentPermission($categoryPermissions, 'updateSelf')) {
            if (XenForo_Permission::hasContentPermission($categoryPermissions, 'editOwnResDescMeta')) {
                return true;
            } elseif (XenForo_Permission::hasContentPermission($categoryPermissions, 'editOwnResKeywordsMeta')) {
                return true;
            } elseif (XenForo_Permission::hasContentPermission($categoryPermissions, 'editOwnResRobotsMeta')) {
                return true;
            } elseif (XenForo_Permission::hasContentPermission($categoryPermissions, 'editOwnResTitleMeta')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if the resource description metadata can be edited with the
     * given permissions.
     * This does not check resource viewing permissions.
     *
     * @param array $resource
     * @param array $category
     * @param string $errorPhraseKey
     * @param array $viewingUser
     * @param array|null $categoryPermissions
     *
     * @return boolean
     */
    public function canEditResourceDescriptionMetadata(array $resource, array $category, &$errorPhraseKey = '',
        array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if (XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny')) {
            return true;
        }

        if ($resource['user_id'] == $viewingUser['user_id'] &&
             XenForo_Permission::hasContentPermission($categoryPermissions, 'updateSelf')) {
            return XenForo_Permission::hasContentPermission($categoryPermissions, 'editOwnResDescMeta');
        }

        return false;
    }

    /**
     * Determines if the resource keywords metadata can be edited with the
     * given permissions.
     * This does not check resource viewing permissions.
     *
     * @param array $resource
     * @param array $category
     * @param string $errorPhraseKey
     * @param array $viewingUser
     * @param array|null $categoryPermissions
     *
     * @return boolean
     */
    public function canEditResourceKeywordsMetadata(array $resource, array $category, &$errorPhraseKey = '',
        array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if (XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny')) {
            return true;
        }

        if ($resource['user_id'] == $viewingUser['user_id'] &&
             XenForo_Permission::hasContentPermission($categoryPermissions, 'updateSelf')) {
            return XenForo_Permission::hasContentPermission($categoryPermissions, 'editOwnResKeywordsMeta');
        }

        return false;
    }

    /**
     * Determines if the resource robots metadata can be edited with the
     * given permissions.
     * This does not check resource viewing permissions.
     *
     * @param array $resource
     * @param array $category
     * @param string $errorPhraseKey
     * @param array $viewingUser
     * @param array|null $categoryPermissions
     *
     * @return boolean
     */
    public function canEditResourceRobotsMetadata(array $resource, array $category, &$errorPhraseKey = '',
        array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if (XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny')) {
            return true;
        }

        if ($resource['user_id'] == $viewingUser['user_id'] &&
             XenForo_Permission::hasContentPermission($categoryPermissions, 'updateSelf')) {
            return XenForo_Permission::hasContentPermission($categoryPermissions, 'editOwnResRobotsMeta');
        }

        return false;
    }

    /**
     * Determines if the resource title metadata can be edited with the
     * given permissions.
     * This does not check resource viewing permissions.
     *
     * @param array $resource
     * @param array $category
     * @param string $errorPhraseKey
     * @param array $viewingUser
     * @param array|null $categoryPermissions
     *
     * @return boolean
     */
    public function canEditResourceTitleMetadata(array $resource, array $category, &$errorPhraseKey = '',
        array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if (XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny')) {
            return true;
        }

        if ($resource['user_id'] == $viewingUser['user_id'] &&
             XenForo_Permission::hasContentPermission($categoryPermissions, 'updateSelf')) {
            return XenForo_Permission::hasContentPermission($categoryPermissions, 'editOwnResTitleMeta');
        }

        return false;
    }
}