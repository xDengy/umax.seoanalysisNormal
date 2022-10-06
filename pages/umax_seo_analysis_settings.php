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
    $seo = UmaxSeoSettingsTable::getList()->Fetch();
    ?>
    <?if($_POST):?>
        <?
            foreach ($_POST as $key => $value) {
                $_POST[$key] = intval($value);
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
            UmaxSeoSettingsTable::createOrUpdate($_POST);
            LocalRedirect($APPLICATION->GetCurPage());
        ?>
    <?endif;?>
    <form action="<?=$APPLICATION->GetCurPage()?>" method="post">
        <div class="select__block">
            <span>Каталог</span>
            <select required name="GOODS">
                <option value="" selected></option>
                <?foreach ($iblocks as $iblock):?>
                    <option <?if($seo['GOODS'] == $iblock['ID']):?>selected<?endif;?> value="<?=$iblock['ID']?>"><?=$iblock['TITLE']?></option>
                <?endforeach;?>
            </select>
        </div>
        <div class="select__block">
            <span>Услуги</span>
            <select required name="SERVICE">
                <option value="" selected></option>
                <?foreach ($iblocks as $iblock):?>
                    <option <?if($seo['SERVICE'] == $iblock['ID']):?>selected<?endif;?> value="<?=$iblock['ID']?>"><?=$iblock['TITLE']?></option>
                <?endforeach;?>
            </select>
        </div>
        <div class="select__block">
            <span>Статьи</span>
            <select required name="NEWS">
                <option value="" selected></option>
                <?foreach ($iblocks as $iblock):?>
                    <option <?if($seo['NEWS'] == $iblock['ID']):?>selected<?endif;?> value="<?=$iblock['ID']?>"><?=$iblock['TITLE']?></option>
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