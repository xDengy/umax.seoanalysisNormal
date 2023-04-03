<?php

Bitrix\Main\Loader::registerAutoLoadClasses(null, [
    '\Umax\EventHandlersLibAnalysis' => "/bitrix/modules/umax.seoanalysis/lib/eventhandlers.php",
    '\Umax\Seo\AdminAreaSeoAnalysis' => "/bitrix/modules/umax.seoanalysis/lib/seo/adminarea.php",
    '\Umax\Lib\MenuAnalysis' => "/bitrix/modules/umax.seoanalysis/lib/menu.php",
    '\UmaxAnalysisDataManager' => '/bitrix/modules/umax.seoanalysis/include.php',
    '\Umax\Lib\Internals\UmaxSeoAnalysisTable' => '/bitrix/modules/umax.seoanalysis/lib/internals/seoanalysis.php',
    '\Umax\Lib\Internals\UmaxSeoOnPageElementTable' => '/bitrix/modules/umax.seoanalysis/lib/internals/seoonpageelement.php',
    '\Umax\Lib\Internals\UmaxSeoSettingsTable' => '/bitrix/modules/umax.seoanalysis/lib/internals/seosettings.php',
    '\Umax\Lib\Internals\UmaxCommerceTable' => '/bitrix/modules/umax.seoanalysis/lib/internals/seocommerce.php',
    '\Umax\Lib\Internals\UmaxMetasTable' => '/bitrix/modules/umax.seoanalysis/lib/internals/seometas.php',
    '\Umax\Lib\Internals\UmaxImagesTable' => '/bitrix/modules/umax.seoanalysis/lib/internals/seoimages.php',
    '\Umax\Lib\Internals\UmaxIndexesTable' => '/bitrix/modules/umax.seoanalysis/lib/internals/seoindexes.php',
    '\Umax\Lib\Internals\UmaxSeoOnPageTable' => '/bitrix/modules/umax.seoanalysis/lib/internals/seoonpage.php',
    '\Umax\Lib\Internals\UmaxSummaryTable' => '/bitrix/modules/umax.seoanalysis/lib/internals/seosummary.php',
    '\Umax\Lib\Internals\UmaxSeoPagesTable' => '/bitrix/modules/umax.seoanalysis/lib/internals/seopages.php',
    '\Umax\Lib\Internals\UmaxSeoErrorsTable' => '/bitrix/modules/umax.seoanalysis/lib/internals/seoerrors.php',
    '\Umax\Lib\Internals\UmaxSeoOnPageElementErrorsTable' => '/bitrix/modules/umax.seoanalysis/lib/internals/seoonpageelementerrors.php',
    '\UmaxAnalysisTab' => '/bitrix/modules/umax.seoanalysis/lib/seo/seotab.php',
    '\Umax\Seo\ListAnalysis' => '/bitrix/modules/umax.seoanalysis/lib/seo/listAnalysis.php',
]);

class UmaxAnalysisDataManager extends Bitrix\Main\Entity\DataManager
{
    /**
     * @var
     */
    static private $demo = null;

    /**
     *
     */
    private static function setDemo()
    {
        self::$demo = \Bitrix\Main\Loader::includeSharewareModule( 'umax.seoanalysis' );
    }

    /**
     * @return bool
     */
    public static function isDemoEnd()
    {
        if(is_null(self::$demo))
        {
            self::setDemo();
        }
        if(self::$demo == 0 || self::$demo == 3)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    /**
     * @return int
     */
    public static function getDemo()
    {
        if(is_null(self::$demo))
        {
            self::setDemo();
        }
        return self::$demo;
    }

    public static function createOrUpdate(array $data = array()) {
        $elem = self::getList()->Fetch();
        if($elem) {
            return parent::update($elem['ID'], $data);
        } else {
            return parent::add($data);
        }
    }

    public static function clear(array $parameters = array()) {
        $delRes = self::getList($parameters)->fetchAll();
        foreach ($delRes as $key => $value) {
            parent::delete($value['ID']);
        }
    }

    public static function plus(array $data = array()) {
        $updateAr = [];
        $d = new DateTime;
        $parent = self::getById(1)->Fetch();
        $seoOnPage = ['SEO_ON_PAGE_GOODS', 'SEO_ON_PAGE_SERVICE', 'SEO_ON_PAGE_NEWS'];
        if($parent) {
            foreach ($parent as $key => $value) {
                if($key !== 'DATE_CHANGE' && $key !== 'ID' && !in_array($key, $seoOnPage))
                    $updateAr[$key] = intval($value) + intval($data[$key]);
                else if (in_array($key, $seoOnPage))
                    $updateAr[$key] = intval($data[$key]);
            }
            $updateAr['DATE_CHANGE'] = $d->format("Y-m-d H:m:s");
            return parent::update(1, $updateAr);
        }
    }

    public static function minus($page)
    {
        $curPage = \Umax\Lib\Internals\UmaxSeoPagesTable::getList(['filter' => ['page_url' => $page]])->Fetch();
        if($curPage) {
            $parent = self::getList()->Fetch();
            unset($curPage['ID']);
            $curPage['SUMMARY_INDEX'] = 1;
            foreach ($curPage as $key => $value) {
                $parent[$key] = intval($parent[$key]) - intval($value);
            }
            \Umax\Lib\Internals\UmaxSeoPagesTable::deleteByPageUrl($page);
            return parent::update($parent['ID'], $parent);
        }
        return true;
    }

    public static function deleteByPageUrl($page)
    {
        $curId = self::getList(['filter' => ['page_url' => $page]])->Fetch()['ID'];
        if($curId)
            return self::delete($curId);
        else
            return false;
    }

    public static function empty($list) {
        $updateAr = [];
        foreach ($list as $key => $value) {
            if($key !== 'ID')
                $updateAr[$key] = 0;
        }
        return self::createOrUpdate($updateAr);
    }

    public static function checkIfEmpty($data) {
        unset($data['ID']);
        unset($data['DATE_CHANGE']);
        $checkAr = [];
        foreach ($data as $key => $value) {
            if($value !== 0)
                $checkAr[] = $value;
        }
        $checkAr = array_unique($checkAr, SORT_REGULAR);
        if(count($checkAr) <= 1)
            return true;
        else
            return false;
    }

    public static function addMultiple(array $data = array()) {
        $res = [];
        foreach ($data as $key => $value) {
            $res[$key] = self::add($value);
        }
        return $res;
    }

    public static function updateMultiple(array $data = array()) {
        $res = [];
        foreach ($data as $key => $value) {
            $res[$key] = self::update($key, $value);
        }
        return $res;
    }

    public static function getList(array $parameters = array()) {
        return parent::getList($parameters);
    }

    public static function getListEncode(array $parameters = array(), $yesOrNo = array()) {
        $list = self::getList($parameters)->fetchAll();
        foreach ($list as $key => $value) {
            unset($list[$key]['ID']);
        }

        $name = self::getEntity()->getNamespace() . self::getEntity()->getName() . 'Table';

        $map = $name::getMap();

        foreach ($list as $key => $value) {
            foreach ($value as $k => $v) {
                if(self::isJson($v)) {
                    $list[$key][$map[$k]['title']] = json_decode($v, true);
                    unset($list[$key][$k]);
                }
                else {
                    $list[$key][$map[$k]['title']] = $v;
                    unset($list[$key][$k]);
                }
            }
        }
        $tableName = self::getEntity()->getName();
        if($tableName == 'UmaxSeoPages') {
            foreach ($list as $key => $value) {
                foreach($yesOrNo as $k => $v) {
                    if($value[$map[$v]['title']] == 1)
                        $list[$key][$map[$v]['title']] = 'Да';
                    else
                        $list[$key][$map[$v]['title']] = 'Нет';
                }
            }
        }
        if($tableName == 'UmaxSeoOnPageElement') {
            foreach ($list as $key => $value) {
                $curType = $value['Тип страницы'];
                switch ($curType) {
                    case 'GOODS':
                        $list[$key]['Тип страницы'] = 'Товар';
                        break;
                    case 'SERVICE':
                        $list[$key]['Тип страницы'] = 'Услуга';
                        break;
                    default:
                        $list[$key]['Тип страницы'] = 'Статья';
                        break;
                }
            }
        }
        return $list;
    }

    public static function getById($id = "") {
        return parent::getById($id);
    }

    public static function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    public static function getMainZone($dom) {
        $anyElems = $dom->evaluate('*');
        $headerId = 0;
        $footerId = $anyElems->count() - 1;
        foreach ($anyElems as $key => $value) {
            if($value->parentNode->tagName == 'body' && $value->tagName !== 'script' && $value->tagName !== 'style') {
                $any[$key] = $value;
                if($value->classList == null) {
                    $value->classList = [];
                }
                if($value->tagName == 'header' || in_array('header', $value->classList))
                    $headerId = $key;
                if($value->tagName == 'footer' || in_array('footer', $value->classList))
                    $footerId = $key;
            }
        }
        foreach ($any as $key => $value) {
            if($key <= $headerId)
                unset($any[$key]);
            if($key >= $footerId)
                unset($any[$key]);
        }
        return $any;
    }
}
?>