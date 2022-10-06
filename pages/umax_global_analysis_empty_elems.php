<?
    use \Umax\Lib\Internals\UmaxSeoOnPageElementTable;
    use Bitrix\Main\Loader;

    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
    require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php');
?>
<?
    if (Loader::includeModule('umax.seoanalysis') && !\UmaxAnalysisDataManager::isDemoEnd()) {
        $type = $_REQUEST['types'];
        UmaxSeoOnPageElementTable::clear([
            'filter' => [
                'IBLOCK_TYPE' => $type
            ]
        ]);
    }

    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>