<?
use Umax\Lib\Internals\UmaxSeoOnPageElementTable;
use Umax\Lib\Internals\UmaxSeoSettingsTable;
use Umax\Lib\Internals\UmaxSeoErrorsTable;
use Umax\Lib\Internals\UmaxSeoOnPageElementErrorsTable;
use Bitrix\Main\Loader;

class UmaxAnalysisTab
{
    function UmaxShowTab(&$form)
    {
        global $APPLICATION;

        if($APPLICATION->GetCurPage() == '/bitrix/admin/iblock_element_edit.php' && Loader::includeModule('umax.seoanalysis') && !\UmaxAnalysisDataManager::isDemoEnd()) {
            $settings = UmaxSeoSettingsTable::getList()->FetchAll();
            $inSettings = false;
            
            if(array_key_exists(array_search($_REQUEST['IBLOCK_ID'], array_column($settings, 'IBLOCK_ID')), $settings)) {
                $inSettings = true;
                $settingAr = [];
                foreach ($settings as $k => $value) {
                    $settingAr[$value['IBLOCK_ID']] = $value['TYPE'];
                }
                $key = $settingAr[$_REQUEST['IBLOCK_ID']];
            }

            if(!$settings) {
                $content = '<div class="header__info top">Проведите настройку модуля<a target="_blank" href="/bitrix/admin/umax_seo_analysis_settings.php" class="save__btn">
                    Провести настройку
                </a></div>';
            } else if ($inSettings) {
                $pageElement = UmaxSeoOnPageElementTable::getList([
                    'filter' => [
                        'ELEMENT_ID' => htmlspecialchars($_REQUEST['ID']),
                        'IBLOCK_TYPE' => $key,
                    ]
                ])->Fetch();
                $curElemPageUrl = \CIBlockElement::GetList([], ['IBLOCK_ID' => htmlspecialchars($_REQUEST['IBLOCK_ID']), 'ID' => htmlspecialchars($_REQUEST['ID'])], false, false, [])->GetNext()['DETAIL_PAGE_URL'];
                if (!$pageElement) {
                    if($curElemPageUrl) {
                        $content = '<div class="header__info top">Анализ не проводился<div data-id="'.$_REQUEST['ID'].'" type="'.$key.'" url="'.$curElemPageUrl.'" id="detail" class="save__btn">
                            Провести анализ
                        </div></div>';
                    } else {
                        $content = '<div class="header__info top">Некоректно настроен ЧПУ<a href="/bitrix/admin/iblock_edit.php?type='.$_REQUEST['type'].'&lang='.$_REQUEST['lang'].'&ID='.$_REQUEST['IBLOCK_ID'].'&admin=Y" class="save__btn">
                            Совершить настройку ЧПУ
                        </a></div>';
                    }
                } else {
                    $curValue = $pageElement['FULL_VALUE'];

                    $red = '';
                    $yellow = '';
                    $blue = '';
                    $green = '';

                    $errorsRes = UmaxSeoOnPageElementErrorsTable::GetList([
                        'filter' => [
                            'ELEMENT_ID' => htmlspecialchars($_REQUEST['ID'])
                        ]
                    ])->FetchAll();
                    $errorsId = [];
                    foreach ($errorsRes as $k => $value) {
                        $errorsId[] = $value['ERROR_ID'];
                    }

                    if($pageElement['RED'] > 0) {
                        $red = '<a class="info__status border-red red" href="#blockRed">'. $pageElement['RED'] .' Высокая критичность</a>';
                        $redErrors = UmaxSeoErrorsTable::getList([
                            'filter' => [
                                'IBLOCK_TYPE' => $key,
                                'VALUE' => 3
                            ]
                        ])->FetchAll();
                        $redErrorsBlock = '';
                        $j = 0;
                        foreach ($redErrors as $k => $value) {
                            if(in_array($value['ID'], $errorsId)) {
                                $recomends = '';
                                $recs = explode(';', $value['RECOMENDS']);
                                foreach ($recs as $rec) {
                                    $recomends .= '
                                        <div class="error-element__recomendations">
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_419_125)">
                                                    <path d="M6.76727 8.74V3.8H8.24727V8.74H6.76727ZM6.72727 11V9.44H8.27727V11H6.72727Z" fill="#D90000"/>
                                                    <circle cx="7.5" cy="7.5" r="7" stroke="#D90000"/>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_419_125">
                                                        <rect width="15" height="15" fill="white"/>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>'.$rec.'</span>
                                        </div>
                                    ';
                                }
                                $class = '';
                                if($j == 0)
                                    $class = 'active';
                                $redErrorsBlock .= '
                                    <div class="error__element">
                                        <div class="error-element__title '.$class.'">
                                            '.$value['NAME'].'
                                            <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M13.7071 8.29289C13.3166 7.90237 12.6834 7.90237 12.2929 8.29289L5.92893 14.6569C5.53841 15.0474 5.53841 15.6805 5.92893 16.0711C6.31946 16.4616 6.95262 16.4616 7.34315 16.0711L13 10.4142L18.6569 16.0711C19.0474 16.4616 19.6805 16.4616 20.0711 16.0711C20.4616 15.6805 20.4616 15.0474 20.0711 14.6569L13.7071 8.29289ZM14 10V9H12V10H14Z" fill="black"/>
                                            </svg>
                                        </div>
                                        <div class="error-element__content '.$class.'">
                                            <div class="error-element__message">
                                                '.$value['DESC'].'
                                            </div>
                                            '.$recomends.'
                                        </div>
                                    </div>
                                ';
                                $j++;
                            }
                        }
                        $redBlock = '
                            <div class="error__block border-red">
                                <div id="blockRed" class="hiddenBlock"></div>
                                <div class="error__title red">
                                    <div class="percentage border-red">
                                        '.$pageElement['RED'].'
                                    </div>
                                    <span>Ошибки высокой критичности</span>
                                </div>
                                <div class="error__wrap">
                                    '.$redErrorsBlock.'
                                </div>
                            </div>
                        ';
                    }
                    if($pageElement['YELLOW'] > 0) {
                        $recomends = '';
                        $yellow = '<a class="info__status border-yellow yellow" href="#blockYellow">'. $pageElement['YELLOW'] .' Средняя критичность</a>';
                        $yellowErrors = UmaxSeoErrorsTable::getList([
                            'filter' => [
                                'IBLOCK_TYPE' => $key,
                                'VALUE' => 2
                            ]
                        ])->FetchAll();
                        $yellowErrorsBlock = '';
                        $j = 0;
                        foreach ($yellowErrors as $k => $value) {
                            if(in_array($value['ID'], $errorsId)) {
                                $recomends = '';
                                $recs = explode(';', $value['RECOMENDS']);
                                foreach ($recs as $rec) {
                                    $recomends .= '
                                        <div class="error-element__recomendations">
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_419_125)">
                                                    <path d="M6.76727 8.74V3.8H8.24727V8.74H6.76727ZM6.72727 11V9.44H8.27727V11H6.72727Z" fill="#F9B812"/>
                                                    <circle cx="7.5" cy="7.5" r="7" stroke="#F9B812"/>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_419_125">
                                                        <rect width="15" height="15" fill="white"/>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>'.$rec.'</span>
                                        </div>
                                    ';
                                }
                                $class = '';
                                if($j == 0)
                                    $class = 'active';
                                $yellowErrorsBlock .= '
                                    <div class="error__element">
                                        <div class="error-element__title '.$class.' '.$value['KEY'].'">
                                            '.$value['NAME'].'
                                            <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M13.7071 8.29289C13.3166 7.90237 12.6834 7.90237 12.2929 8.29289L5.92893 14.6569C5.53841 15.0474 5.53841 15.6805 5.92893 16.0711C6.31946 16.4616 6.95262 16.4616 7.34315 16.0711L13 10.4142L18.6569 16.0711C19.0474 16.4616 19.6805 16.4616 20.0711 16.0711C20.4616 15.6805 20.4616 15.0474 20.0711 14.6569L13.7071 8.29289ZM14 10V9H12V10H14Z" fill="black"/>
                                            </svg>
                                        </div>
                                        <div class="error-element__content '.$class.'">
                                            <div class="error-element__message">
                                                '.$value['DESC'].'
                                            </div>
                                            '.$recomends.'
                                        </div>
                                    </div>
                                ';
                                $j++;
                            }
                        }
                        $yellowBlock = '
                            <div class="error__block border-yellow">
                                <div id="blockYellow" class="hiddenBlock"></div>
                                <div class="error__title yellow">
                                    <div class="percentage border-yellow">
                                        '.$pageElement['YELLOW'].'
                                    </div>
                                    <span>Ошибки средней критичности</span>
                                </div>
                                <div class="error__wrap">
                                    '.$yellowErrorsBlock.'
                                </div>
                            </div>
                        ';
                    }
                    if($pageElement['BLUE'] > 0) {
                        $recomends = '';
                        $blue = '<a class="info__status border-blue blue" href="#blockBlue">'.$pageElement['BLUE'].' Низкая критичность</a>';
                        $blueErrors = UmaxSeoErrorsTable::getList([
                            'filter' => [
                                'IBLOCK_TYPE' => $key,
                                'VALUE' => 1
                            ]
                        ])->FetchAll();
                        $blueErrorsBlock = '';
                        $j = 0;
                        foreach ($blueErrors as $k => $value) {
                            if(in_array($value['ID'], $errorsId)) {
                                $recomends = '';
                                $recs = explode(';', $value['RECOMENDS']);
                                foreach ($recs as $rec) {
                                    $recomends .= '
                                        <div class="error-element__recomendations">
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_419_125)">
                                                    <path d="M6.76727 8.74V3.8H8.24727V8.74H6.76727ZM6.72727 11V9.44H8.27727V11H6.72727Z" fill="#008bd9"/>
                                                    <circle cx="7.5" cy="7.5" r="7" stroke="#008bd9"/>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_419_125">
                                                        <rect width="15" height="15" fill="white"/>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>'.$rec.'</span>
                                        </div>
                                    ';
                                }
                                $class = '';
                                if($j == 0)
                                    $class = 'active';

                                $blueErrorsBlock .= '
                                    <div class="error__element">
                                        <div class="error-element__title '.$class.'">
                                            '.$value['NAME'].'
                                            <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M13.7071 8.29289C13.3166 7.90237 12.6834 7.90237 12.2929 8.29289L5.92893 14.6569C5.53841 15.0474 5.53841 15.6805 5.92893 16.0711C6.31946 16.4616 6.95262 16.4616 7.34315 16.0711L13 10.4142L18.6569 16.0711C19.0474 16.4616 19.6805 16.4616 20.0711 16.0711C20.4616 15.6805 20.4616 15.0474 20.0711 14.6569L13.7071 8.29289ZM14 10V9H12V10H14Z" fill="black"/>
                                            </svg>
                                        </div>
                                        <div class="error-element__content '.$class.'">
                                            <div class="error-element__message">
                                                '.$value['DESC'].'
                                            </div>
                                            '.$recomends.'
                                        </div>
                                    </div>
                                ';
                                $j++;
                            }
                        }
                        $blueBlock = '
                            <div class="error__block border-blue">
                                <div id="blockBlue" class="hiddenBlock"></div>
                                <div class="error__title blue">
                                    <div class="percentage border-blue">
                                        '.$pageElement['BLUE'].'
                                    </div>
                                    <span>Ошибки низкой критичности</span>
                                </div>
                                <div class="error__wrap">
                                    '.$blueErrorsBlock.'
                                </div>
                            </div>
                        ';
                    }
                    if($pageElement['GREEN'] > 0) {
                        $green = '<a class="info__status border-green green" href="#blockGreen">'.$pageElement['GREEN'].' Без ошибок</a>';
                        $greenErrors = UmaxSeoErrorsTable::getList([
                            'filter' => [
                                'IBLOCK_TYPE' => $key,
                            ]
                        ])->FetchAll();
                    
                        foreach ($greenErrors as $k => $value) {
                            if(in_array($value['ID'], $errorsId)) {
                                unset($greenErrors[$k]);
                            }
                        }

                        $greenErrorsBlock = '';
                        $j = 0;
                        foreach ($greenErrors as $k => $value) {
                            $recomends = '';
                            $recs = explode(';', $value['RECOMENDS']);
                            foreach ($recs as $rec) {
                                $recomends .= '
                                    <div class="error-element__recomendations">
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_419_125)">
                                                <path d="M6.76727 8.74V3.8H8.24727V8.74H6.76727ZM6.72727 11V9.44H8.27727V11H6.72727Z" fill="#06b618"/>
                                                <circle cx="7.5" cy="7.5" r="7" stroke="#06b618"/>
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_419_125">
                                                    <rect width="15" height="15" fill="white"/>
                                                </clipPath>
                                            </defs>
                                        </svg>
                                        <span>'.$rec.'</span>
                                    </div>
                                ';
                            }
                            $class = '';
                            if($j == 0)
                                $class = 'active';

                            $greenErrorsBlock .= '
                                <div class="error__element">
                                    <div class="error-element__title '.$class.'">
                                        '.$value['NAME'].'
                                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M13.7071 8.29289C13.3166 7.90237 12.6834 7.90237 12.2929 8.29289L5.92893 14.6569C5.53841 15.0474 5.53841 15.6805 5.92893 16.0711C6.31946 16.4616 6.95262 16.4616 7.34315 16.0711L13 10.4142L18.6569 16.0711C19.0474 16.4616 19.6805 16.4616 20.0711 16.0711C20.4616 15.6805 20.4616 15.0474 20.0711 14.6569L13.7071 8.29289ZM14 10V9H12V10H14Z" fill="black"/>
                                        </svg>
                                    </div>
                                    <div class="error-element__content '.$class.'">
                                        <div class="error-element__message">
                                            '.$value['DESC'].'
                                        </div>
                                        '.$recomends.'
                                    </div>
                                </div>
                            ';
                            $j++;
                        }
                        $greenBlock = '
                            <div class="error__block border-green">
                                <div id="blockGreen" class="hiddenBlock"></div>
                                <div class="error__title green">
                                    <div class="percentage border-green">
                                        '.$pageElement['GREEN'].'
                                    </div>
                                    <span>Без ошибок</span>
                                </div>
                                <div class="error__wrap">
                                    '.$greenErrorsBlock.'
                                </div>
                            </div>
                        ';
                    }

                    if($curValue <= 40) {
                        $color = 'red';
                        $text = 'плохо';
                    }
                    else if($curValue > 40 && $curValue <= 75) {
                        $color = 'yellow';
                        $text = 'средне';
                    }
                    else if($curValue > 75) {
                        $color = 'green';
                        $text = 'хорошо';
                    }

                    $content = '
                    <div class="seoOnPage__header">
                        <div class="header__element">
                            <div class="percentage '.$color.'">
                                '. $curValue .'%
                            </div>
                        </div>
                        <div class="header__element main">
                            <div class="header__info top">
                                <span>
                                    Страница <span class="'.$color.'">'.$text.'</span> оптимизирована под SEO продвижение. <br> Проверьте качество оптимизации страницы по следующему <a href="https://umax.agency/blog/Chek-list-po-optimizaczii-SEO-OnPage-odnostranichnaya-optimizacziya" target="_blank" class="check-list">чек-листу</a>.
                                </span>
                                <div data-id="'.$_REQUEST['ID'].'" type="'.$key.'" url="'.$curElemPageUrl.'" id="detail" class="save__btn">
                                    Обновить
                                </div>
                            </div>
                            <div class="header__info">
                                '.$red.$yellow.$blue.$green.'
                            </div>
                        </div>
                    </div>
                    <div class="seoOnPage__content">
                        '.$redBlock.$yellowBlock.$blueBlock.$greenBlock.'
                    </div>
                    <div class="seoOnPage__scroll">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.0" width="225.000000pt" height="225.000000pt" viewBox="0 0 225.000000 225.000000" preserveAspectRatio="xMidYMid meet">
                            <g transform="translate(0.000000,225.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none">
                                <path d="M1053 1742 c-18 -9 -255 -235 -527 -501 -507 -496 -526 -517 -526 -590 0 -15 10 -47 23 -71 40 -75 133 -109 210 -76 18 7 224 190 459 405 235 216 430 392 435 391 4 -1 199 -178 433 -392 234 -215 439 -397 457 -404 77 -33 170 1 210 76 13 24 23 56 23 71 0 71 -24 99 -388 455 -707 692 -664 654 -739 654 -21 -1 -52 -9 -70 -18z"/>
                            </g>
                        </svg>
                    </div>
                    ';
                }
            }   

            if($content) {
                $form->tabs[] = array("DIV" => "seoOnPage", "TAB" => "Seo OnPage", "ICON"=>"main_user_edit", "TITLE"=>"Seo OnPage", "CONTENT"=> '
                <script src="https://code.jquery.com/jquery-3.5.0.js"></script>
                <link rel="stylesheet" href="/bitrix/modules/umax.seoanalysis/lib/assets/tab.css">
                <link rel="stylesheet" href="/bitrix/modules/umax.seoanalysis/lib/assets/fonts.css">
                <script src="/bitrix/modules/umax.seoanalysis/lib/assets/tab.js"></script>
                <div class="seoOnPage">
                '.$content.'
                <div class="loading"></div>
                </div>');
            }   
        }
    }
}