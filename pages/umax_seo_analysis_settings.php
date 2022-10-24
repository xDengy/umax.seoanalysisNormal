<?
use Umax\Lib\Internals\UmaxSeoSettingsTable;
use Umax\Lib\Internals\UmaxSeoOnPageElementTable;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php');
?>
<?
if (!Loader::includeModule('umax.seoanalysis') || \UmaxAnalysisDataManager::isDemoEnd()) {
    echo CAdminMessage::ShowOldStyleError("Модуль не установлен");
} else {
    global $APPLICATION;
    $rsBlock = \CIBlock::GetList(
        array(),
        array("ACTIVE" => "Y"),
        false
    );
    $iblocks = [];
    while($arBlocks = $rsBlock->Fetch()) {
        $iblocks[] = [
            'ID' => $arBlocks['ID'],
            'TITLE' => $arBlocks['NAME'],
        ];
    }
    $seo = UmaxSeoSettingsTable::getList()->FetchAll();
    ?>
    <?if($_POST):?>
        <?
            $postAr = [];
            foreach ($_POST as $key => $value) {
                foreach($value as $block) {
                    $postAr[] = [
                        'TYPE' => $key,
                        'IBLOCK_ID' => $block
                    ];
                }
            }
            $allSet = UmaxSeoSettingsTable::getList()->FetchAll();
            foreach ($allSet as $k => $v) {
                unset($allSet[$k]['ID']);
            }
            $allSetAr = [];
            foreach($postAr as $k => $v) {
                $allSetAr[$v['TYPE'] . '_' . $v['IBLOCK_ID']] = $v;
            }
            foreach($allSet as $k => $v) {
                if(!array_key_exists($v['TYPE'] . '_' . $v['IBLOCK_ID'], $allSetAr)) {
                    
                    UmaxSeoOnPageElementTable::clear([
                        'filter' => [
                            'IBLOCK_TYPE' => $v['TYPE'],
                            'IBLOCK_ID' => $v['IBLOCK_ID']
                        ]
                    ]);
                    $curSet = UmaxSeoSettingsTable::getList([
                        'filter' => [
                            'TYPE' => $v['TYPE'],
                            'IBLOCK_ID' => $v['IBLOCK_ID']
                        ]])->Fetch();

                    UmaxSeoSettingsTable::delete($curSet['ID']);
                }
            }
            foreach($allSetAr as $key => $value) {
                if(in_array($value, $allSet))
                    unset($allSetAr[$key]);
            }
            if(count($allSetAr) > 0) {
                UmaxSeoSettingsTable::addMultiple($allSetAr);
            }
            LocalRedirect($APPLICATION->GetCurPage());
        ?>
    <?endif;?>
    <form action="<?=$APPLICATION->GetCurPage()?>" method="post">
        <div class="select__block">
            <span>Каталог</span>
            <div class="checkbox-wrap">
                <?foreach ($iblocks as $iblock):?>
                    <?
                        $exist = array_search($iblock['ID'], array_column($seo, 'IBLOCK_ID'));    
                    ?>
                    <div>
                        <input type="checkbox" name="GOODS[]" <?if(gettype($exist) == 'integer'): if(array_key_exists($exist, $seo) && $seo[$exist]['TYPE'] == 'GOODS'):?>checked<?endif;endif;?> value="<?=$iblock['ID']?>" id="GOODS-<?=$iblock['ID']?>">
                        <label for="GOODS-<?=$iblock['ID']?>"><?=$iblock['TITLE']?></label>
                    </div>
                <?endforeach;?>
            </div>
        </div>
        <div class="select__block">
            <span>Услуги</span>
            <div class="checkbox-wrap">
                <?foreach ($iblocks as $iblock):?>
                    <?
                        $exist = array_search($iblock['ID'], array_column($seo, 'IBLOCK_ID'));    
                    ?>
                    <div>
                        <input type="checkbox" name="SERVICE[]" <?if(gettype($exist) == 'integer'): if(array_key_exists($exist, $seo) && $seo[$exist]['TYPE'] == 'SERVICE'):?>checked<?endif;endif;?> value="<?=$iblock['ID']?>" id="SERVICE-<?=$iblock['ID']?>">
                        <label for="SERVICE-<?=$iblock['ID']?>"><?=$iblock['TITLE']?></label>
                    </div>
                <?endforeach;?>
            </div>
        </div>
        <div class="select__block">
            <span>Статьи</span>
            <div class="checkbox-wrap">
                <?foreach ($iblocks as $iblock):?>
                    <?
                        $exist = array_search($iblock['ID'], array_column($seo, 'IBLOCK_ID'));    
                    ?>
                    <div>
                        <input type="checkbox" name="NEWS[]" <?if(gettype($exist) == 'integer'): if(array_key_exists($exist, $seo) && $seo[$exist]['TYPE'] == 'NEWS'):?>checked<?endif;endif;?> value="<?=$iblock['ID']?>" id="NEWS-<?=$iblock['ID']?>">
                        <label for="NEWS-<?=$iblock['ID']?>"><?=$iblock['TITLE']?></label>
                    </div>
                <?endforeach;?>
            </div>
        </div>
        <input type="submit" value="Сохранить">
    </form>
    <link rel="stylesheet" href="/bitrix/themes/.default/umax.seoanalysis/fonts.css">
    <style>
        .select__block {
            display: flex;
            flex-direction: column;
            width: 500px;
            margin-bottom: 10px;
            font-family: 'Mont';
            border: 1px solid #3d3d3d;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 30px;
        }
        .select__block span {
            margin-bottom: 5px;
            font-weight: 700;
        }
        .checkbox-wrap {
            overflow: auto;
            max-height: 100px;
        }
    </style>
    <?
    $APPLICATION->SetTitle('Настройка');
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>