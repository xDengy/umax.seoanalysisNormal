<?
    use Umax\Lib\Internals\UmaxSeoAnalysisTable;
    use Umax\Lib\Internals\UmaxCommerceTable;
    use Umax\Lib\Internals\UmaxMetasTable;
    use Umax\Lib\Internals\UmaxImagesTable;
    use Umax\Lib\Internals\UmaxIndexesTable;
    use Umax\Lib\Internals\UmaxSeoOnPageTable;
    use Umax\Lib\Internals\UmaxSummaryTable;
    use Umax\Lib\Internals\UmaxSeoPagesTable;
    use Bitrix\Main\Loader;

    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
    require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php');
?>
<?
    if (Loader::includeModule('umax.seoanalysis') && !\UmaxAnalysisDataManager::isDemoEnd()) {
        if(!$_POST['pages']) {
            UmaxSeoAnalysisTable::empty(UmaxSeoAnalysisTable::getMap());
            UmaxCommerceTable::clear();
            UmaxMetasTable::clear();
            UmaxImagesTable::clear();
            UmaxIndexesTable::clear();
            UmaxSeoOnPageTable::clear();
            UmaxSummaryTable::clear();
            UmaxSeoPagesTable::clear();
        } else {
            foreach ($_POST['pages'] as $key => $value) {
                UmaxSeoAnalysisTable::minus($value);
                UmaxMetasTable::deleteByPageUrl($value);
                UmaxImagesTable::deleteByPageUrl($value);
                UmaxIndexesTable::deleteByPageUrl($value);
                UmaxSummaryTable::deleteByPageUrl($value);
            }
        }
    }

    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>