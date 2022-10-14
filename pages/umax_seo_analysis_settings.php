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
            unset($allSet['ID']);
            foreach ($allSet as $k => $v) {
                if(intval($allSet[$k]['IBLOCK_ID']) !== intval($postAr[$k]['IBLOCK_ID'])) {
                    UmaxSeoOnPageElementTable::clear([
                        'filter' => [
                            'IBLOCK_TYPE' => $allSet[$k]['TYPE'],
                            'IBLOCK_ID' => $allSet[$k]['IBLOCK_ID']
                        ]
                    ]);
                    unset($postAr[$k]);
                    UmaxSeoSettingsTable::delete($allSet[$k]['ID']);
                }
            }
            UmaxSeoSettingsTable::addMultiple($postAr);
            LocalRedirect($APPLICATION->GetCurPage());
        ?>
    <?endif;?>
    <form action="<?=$APPLICATION->GetCurPage()?>" method="post">
        <div class="select__block">
            <span>Товары</span>
            <div class="checkbox-wrap">
                <?foreach ($iblocks as $iblock):?>
                    <?
                        $exist = array_search($iblock['ID'], array_column($seo, 'IBLOCK_ID'));    
                    ?>
                    <div>
                        <input type="checkbox" name="GOODS[]" <?if(array_key_exists($exist, $seo) && $seo[$exist]['TYPE'] == 'GOODS'):?>checked<?endif;?> value="<?=$iblock['ID']?>" id="GOODS-<?=$iblock['ID']?>">
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
                        <input type="checkbox" name="SERVICE[]" <?if(array_key_exists($exist, $seo) && $seo[$exist]['TYPE'] == 'SERVICE'):?>checked<?endif;?> value="<?=$iblock['ID']?>" id="SERVICE-<?=$iblock['ID']?>">
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
                        <input type="checkbox" name="NEWS[]" <?if(array_key_exists($exist, $seo) && $seo[$exist]['TYPE'] == 'NEWS'):?>checked<?endif;?> value="<?=$iblock['ID']?>" id="NEWS-<?=$iblock['ID']?>">
                        <label for="NEWS-<?=$iblock['ID']?>"><?=$iblock['TITLE']?></label>
                    </div>
                <?endforeach;?>
            </div>
        </div>
        <input type="submit" value="Сохранить">
    </form>
        <link rel="stylesheet" href="/bitrix/modules/umax.seoanalysis/lib/assets/fonts.css">
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
