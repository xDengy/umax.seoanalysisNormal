<?php
namespace Umax\Seo;
use Bitrix\Main\Loader;

class AdminAreaSeoAnalysis
{
	public static function addUmaxSeo(&$content)
	{
        global $APPLICATION;
        \CModule::IncludeModule("iblock");
        $curPage = $APPLICATION->GetCurPage();

        $workPages = [
            '/bitrix/admin/iblock_element_edit.php',
            '/bitrix/admin/iblock_section_edit.php',
        ];
        
        $seoBlock = \CIBlock::GetList([], ['ID' => $_REQUEST['IBLOCK_ID']], false, false, [])->Fetch();
        if(in_array($curPage, $workPages) && isset($_REQUEST['ID']) && Loader::includeModule('umax.seoanalysis') && !\UmaxAnalysisDataManager::isDemoEnd()) {

            if($seoBlock['CODE'] !== 'seo_filters') {
                $curElemPageUrl = \CIBlockSection::GetList([], ['IBLOCK_ID' => $_REQUEST['IBLOCK_ID'], 'ID' => $_REQUEST['ID']], false, false, [])->GetNext()['SECTION_PAGE_URL'];
                $curListPageUrl = \CIBlockSection::GetList([], ['IBLOCK_ID' => $_REQUEST['IBLOCK_ID'], 'ID' => $_REQUEST['ID']], false, false, [])->GetNext()['LIST_PAGE_URL'];
                if($curElemPageUrl && $curPage == '/bitrix/admin/iblock_section_edit.php'):
                    ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function(){
                                let bntCheck = false
                                let btns = document.querySelectorAll('.adm-detail-content-btns a')
                                for (let i = 0; i < btns.length; i++) {
                                    const element = btns[i];
                                    if(element.textContent == 'Просмотр')
                                        bntCheck = true
                                }
                                if(!bntCheck) {
                                    let tabs = document.querySelector('.adm-detail-content-btns')

                                    const tagA = document.createElement("a");
                                    tagA.innerHTML = 'Просмотр'
                                    tagA.style.marginLeft = '5px'
                                    tagA.classList.add('adm-btn')
                                    tagA.setAttribute('href', '<?=$curElemPageUrl?>')
                                    tagA.setAttribute('target', '_blank')
                                    tabs?.appendChild(tagA)
                                }
                            })
                        </script>
                    <?
                elseif($curListPageUrl && $curPage == '/bitrix/admin/iblock_section_edit.php'):
                    ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function(){
                                let bntCheck = false
                                let btns = document.querySelectorAll('.adm-detail-content-btns a')
                                for (let i = 0; i < btns.length; i++) {
                                    const element = btns[i];
                                    if(element.textContent == 'Просмотр')
                                        bntCheck = true
                                }
                                if(!bntCheck) {
                                    let tabs = document.querySelector('.adm-detail-content-btns')

                                    const tagA = document.createElement("a");
                                    tagA.innerHTML = 'Просмотр'
                                    tagA.style.marginLeft = '5px'
                                    tagA.classList.add('adm-btn')
                                    tagA.setAttribute('href', '<?=$curListPageUrl?>')
                                    tagA.setAttribute('target', '_blank')
                                    tabs?.appendChild(tagA)
                                }
                            })
                        </script>
                    <?
                elseif($curPage == '/bitrix/admin/iblock_element_edit.php'):
                    $curElemPageUrl = \CIBlockElement::GetList([], ['IBLOCK_ID' => $_REQUEST['IBLOCK_ID'], 'ID' => $_REQUEST['ID']], false, false, [])->GetNext()['DETAIL_PAGE_URL'];
                    if($curElemPageUrl):
                        ?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function(){
                                    let bntCheck = false
                                    let btns = document.querySelectorAll('.adm-detail-content-btns a')
                                    for (let i = 0; i < btns.length; i++) {
                                        const element = btns[i];
                                        if(element.textContent == 'Просмотр')
                                            bntCheck = true
                                    }
                                    if(!bntCheck) {
                                        let tabs = document.querySelector('.adm-detail-content-btns')

                                        const tagA = document.createElement("a");
                                        tagA.innerHTML = 'Просмотр'
                                        tagA.style.marginLeft = '5px'
                                        tagA.classList.add('adm-btn')
                                        tagA.setAttribute('href', '<?=$curElemPageUrl?>')
                                        tagA.setAttribute('target', '_blank')
                                        tabs?.appendChild(tagA)
                                    }
                                })
                            </script>
                        <?
                    endif;
                endif;
            }
        }
	}
}


