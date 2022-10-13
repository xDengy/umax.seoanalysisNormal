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
            UmaxSeoSettingsTable::clear();
            $postAr = [];
            foreach ($_POST as $key => $value) {
                foreach($value as $block) {
                    $postAr[] = [
                        'TYPE' => $key,
                        'IBLOCK_ID' => $block
                    ];
                }
            }
            $allSet = UmaxSeoSettingsTable::getList()->fetch();
            unset($allSet['ID']);
            foreach ($allSet as $k => $v) {
                if(intval($allSet[$k]) !== intval($_POST[$k])) {
                    UmaxSeoOnPageElementTable::clear([
                        'filter' => [
                            'IBLOCK_TYPE' => $k
                        ]
                    ]);
                }                
            }
            UmaxSeoSettingsTable::addMultiple($postAr);
            LocalRedirect($APPLICATION->GetCurPage());
        ?>
    <?endif;?>
    <form action="<?=$APPLICATION->GetCurPage()?>" method="post">
        <div class="select__block">
            <span>Каталог</span>
            <select multiple name="GOODS[]">
                <option value="" <?if(!array_key_exists(array_search('GOODS', array_column($seo, 'TYPE')), $seo)):?>selected<?endif;?>></option>
                <?foreach ($iblocks as $iblock):?>
                    <?
                        $exist = array_search($iblock['ID'], array_column($seo, 'IBLOCK_ID'));    
                    ?>
                    <option <?if(array_key_exists($exist, $seo) && $seo[$exist]['TYPE'] == 'GOODS'):?>selected<?endif;?> value="<?=$iblock['ID']?>"><?=$iblock['TITLE']?></option>
                <?endforeach;?>
            </select>
        </div>
        <div class="select__block">
            <span>Услуги</span>
            <select multiple name="SERVICE[]">
                <option value="" <?if(!array_key_exists(array_search('SERVICE', array_column($seo, 'TYPE')), $seo)):?>selected<?endif;?>></option>
                <?foreach ($iblocks as $iblock):?>
                    <?
                        $exist = array_search($iblock['ID'], array_column($seo, 'IBLOCK_ID'));    
                    ?>
                    <option <?if(array_key_exists($exist, $seo) && $seo[$exist]['TYPE'] == 'SERVICE'):?>selected<?endif;?> value="<?=$iblock['ID']?>"><?=$iblock['TITLE']?></option>
                <?endforeach;?>
            </select>
        </div>
        <div class="select__block">
            <span>Статьи</span>
            <select multiple name="NEWS[]">
                <option value="" <?if(!array_key_exists(array_search('NEWS', array_column($seo, 'TYPE')), $seo)):?>selected<?endif;?>></option>
                <?foreach ($iblocks as $iblock):?>
                    <?
                        $exist = array_search($iblock['ID'], array_column($seo, 'IBLOCK_ID'));    
                    ?>
                    <option <?if(array_key_exists($exist, $seo) && $seo[$exist]['TYPE'] == 'NEWS'):?>selected<?endif;?> value="<?=$iblock['ID']?>"><?=$iblock['TITLE']?></option>
                <?endforeach;?>
            </select>
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
            font-family: 'Mont'
        }
        .select__block span {
            margin-bottom: 5px;
            font-weight: 700;
        }
    </style>
    <?
    $APPLICATION->SetTitle('Настройка');
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>