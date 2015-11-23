<?php

class ThemeHouse_Metadata_Listener_LoadClass extends ThemeHouse_Listener_LoadClass
{

    protected function _getExtendedClasses()
    {
        return array(
            'ThemeHouse_Metadata' => array(
                'model' => array(
                    'XenForo_Model_Thread',
                    'XenResource_Model_Resource'
                ),
                'datawriter' => array(
                    'XenForo_DataWriter_Discussion_Thread',
                    'XenResource_DataWriter_Resource'
                ),
                'controller' => array(
                    'XenForo_ControllerPublic_Thread',
                    'XenResource_ControllerPublic_Resource',
                    'XenForo_ControllerPublic_Forum'
                ),
            ),
        );
    }

    public static function loadClassModel($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Metadata_Listener_LoadClass', $class, $extend, 'model');
    }

    public static function loadClassDataWriter($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Metadata_Listener_LoadClass', $class, $extend, 'datawriter');
    }

    public static function loadClassController($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Metadata_Listener_LoadClass', $class, $extend, 'controller');
    }
}