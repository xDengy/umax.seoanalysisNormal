<?php
namespace Umax;

class EventHandlersLibAnalysis
{
    public static function OnEndBufferContentHandler(&$content)
    {
        \Umax\Seo\AdminAreaSeoAnalysis::addUmaxSeo($content);
    }

    public static function OnBuildGlobalMenuHandler(&$arGlobalMenu, &$arModuleMenu){
        \Umax\Lib\MenuAnalysis::getAdminMenu($arGlobalMenu, $arModuleMenu);
    }

    public static function OnAdminListDisplayHandler(&$list){
        \Umax\Seo\ListAnalysis::setFields($list);
    }
}
?>
