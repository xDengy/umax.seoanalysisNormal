<?php
namespace Umax\Seo;
use \Umax\Lib\Internals\UmaxSeoOnPageElementTable;
use \Umax\Lib\Internals\UmaxSeoSettingsTable;
use Bitrix\Main\Loader;

class ListAnalysis
{
    public static function setFields(&$list)
    {
      global $APPLICATION;
      $curPage = $APPLICATION->GetCurPage();

      $iblockId = $_REQUEST['IBLOCK_ID'];
      $setting = UmaxSeoSettingsTable::getList()->fetchAll();
      $newSet = [];
      if(is_array($setting)) {
        foreach($setting as $setKey => $setVal) {
          if($setKey !== 'ID')
            $newSet[$setVal['IBLOCK_ID']] = $setVal['TYPE'];
        }
        $type = $newSet[$iblockId];
      }

      if(($curPage == '/bitrix/admin/iblock_element_admin.php' || $curPage == '/bitrix/admin/iblock_list_admin.php') && $type && Loader::includeModule('umax.seoanalysis') && !\UmaxAnalysisDataManager::isDemoEnd()) {
        $arHeader = array(             
          "id" => "ANALYSIS",
          "content" => 'Анализ',
          "sort" => "",
          "default" => false,
          "align" => "left",
        );
        $list->aHeaders["ANALYSIS"] = $arHeader;
        $list->aVisibleHeaders["ANALYSIS"] = $arHeader;
        $list->arVisibleColumns[]= 'ANALYSIS';
        if(is_array($list->aRows) || is_object($list->aRows)) {
          foreach ($list->aRows as $row) {
            $curId = explode('>', $row->aFields['ID']['view']['value'])[1];
            $seoOnPage = UmaxSeoOnPageElementTable::getList([
              'filter' => [
                'IBLOCK_TYPE' => $type,
                'ELEMENT_ID' => $curId
              ]
            ])->Fetch();
            if($seoOnPage) {
              $curValue = $seoOnPage['FULL_VALUE'];

              if($curValue <= 40) {
                $color = '#D90000';
              }
              else if($curValue > 40 && $curValue <= 75) {
                $color = '#F9B812';
              }
              else if($curValue > 75) {
                $color = '#06B618';
              }

              $row->addField(
                'ANALYSIS',
                '<div style="display: flex;align-items: center;justify-content: center;"><div style="width: 30px;height: 30px;min-width: 30px;min-height: 30px;border: 1px solid;border-radius: 50%;padding:5px;display: flex;align-items: center;justify-content: center;font-size: 14px;line-height: 14px;border-color:'.$color.';color:'.$color.'">
                  '. $curValue .'%
                </div></div>'
              );
            }
          }
        }
      }
    }
}


