<?php

namespace Umax\Lib;

class MenuAnalysis
{
    public static function getAdminMenu(
        &$arGlobalMenu,
        &$arModuleMenu
    ) {
        if (!isset($arGlobalMenu['global_menu_umax'])) {
            $arGlobalMenu['global_menu_umax'] = [
                'menu_id'   => 'umax',
                'text'      => 'SEO UMAX',
                'title'     => 'SEO UMAX',
                'sort'      => 500,
                'items_id'  => 'global_menu_umax_items',
                "icon"      => 'umax',
                "page_icon" => 'umax',
            ];
        }

        $iModuleID = "umax.seoanalysis";
        global $APPLICATION;
        if ($APPLICATION->GetGroupRight($iModuleID) != "D") {

            $items = [
                [
                    "text" => 'Общий анализ',
                    "url" => '/bitrix/admin/umax_global_analysis.php',
                    "title" => 'Общий анализ',
                    'more_url' => [
                        '/bitrix/admin/umax_global_analysis_post.php',
                    ]
                ],
                [
                    "text" => 'Настройки',
                    "url" => '/bitrix/admin/umax_seo_analysis_settings.php',
                    "title" => 'Настройки',
                ],
            ];
            
            $aMenu = array(
                "parent_menu" => 'global_menu_umax',
                "section" => 'umax.seoanalysis',
                "sort" => 350,
                "text" => 'SEO ANALYSIS UMAX',
                "title" => 'SEO ANALYSIS UMAX',
                "icon" => 'subicon',
                "page_icon" => 'subicon',
                "items_id" => "menu_umax.seoanalysis",
                "dynamic" => true,
                'items' => $items,
            );

            $arGlobalMenu['global_menu_umax']['items']['umax.seoanalysis'] = $aMenu;
        }
    }
}
?>
