<?php

class ThemeHouse_Metadata_Listener_FileHealthCheck
{

    public static function fileHealthCheck(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes = array_merge($hashes,
            array(
                'library/ThemeHouse/Metadata/Extend/XenForo/ControllerPublic/Forum.php' => 'f780715e09f6603bca2d55436c9be737',
                'library/ThemeHouse/Metadata/Extend/XenForo/ControllerPublic/Thread.php' => '48b20136f516bb46dde5c2cb7346e9b1',
                'library/ThemeHouse/Metadata/Extend/XenForo/DataWriter/Discussion/Thread.php' => '002a5a8ff7a967b8e263f719f8794102',
                'library/ThemeHouse/Metadata/Extend/XenForo/Model/Thread.php' => '472966676d779e2593db9892239d9fb0',
                'library/ThemeHouse/Metadata/Extend/XenResource/ControllerPublic/Resource.php' => '7b0b3175a1562ac9dc70a53ecb14b786',
                'library/ThemeHouse/Metadata/Extend/XenResource/DataWriter/Resource.php' => '8b9f0f8e301c91a69a31e95e2ebb37fc',
                'library/ThemeHouse/Metadata/Extend/XenResource/Model/Resource.php' => '75ec1510857e27129f8d5001d86391e6',
                'library/ThemeHouse/Metadata/Install/Controller.php' => '424b2dacd9bceb57f781603a29448992',
                'library/ThemeHouse/Metadata/Listener/LoadClass.php' => 'd0d58ccd0d7ba6ba12906739263e81e0',
                'library/ThemeHouse/Metadata/Option/ResManEnabled.php' => '932cdabbf9a45e0b2dc30f76cd159055',
                'library/ThemeHouse/Install.php' => '18f1441e00e3742460174ab197bec0b7',
                'library/ThemeHouse/Install/20151109.php' => '2e3f16d685652ea2fa82ba11b69204f4',
                'library/ThemeHouse/Deferred.php' => 'ebab3e432fe2f42520de0e36f7f45d88',
                'library/ThemeHouse/Deferred/20150106.php' => 'a311d9aa6f9a0412eeba878417ba7ede',
                'library/ThemeHouse/Listener/ControllerPreDispatch.php' => 'fdebb2d5347398d3974a6f27eb11a3cd',
                'library/ThemeHouse/Listener/ControllerPreDispatch/20150911.php' => 'f2aadc0bd188ad127e363f417b4d23a9',
                'library/ThemeHouse/Listener/InitDependencies.php' => '8f59aaa8ffe56231c4aa47cf2c65f2b0',
                'library/ThemeHouse/Listener/InitDependencies/20150212.php' => 'f04c9dc8fa289895c06c1bcba5d27293',
                'library/ThemeHouse/Listener/LoadClass.php' => '5cad77e1862641ddc2dd693b1aa68a50',
                'library/ThemeHouse/Listener/LoadClass/20150518.php' => 'f4d0d30ba5e5dc51cda07141c39939e3',
            ));
    }
}