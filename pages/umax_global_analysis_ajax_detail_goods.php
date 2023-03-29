<?
    require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php');
    
    global $APPLICATION;
?>
<?
    if (\Bitrix\Main\Loader::includeModule('umax.seoanalysis') && !\UmaxAnalysisDataManager::isDemoEnd()) {
        function DOMinnerHTML($element) 
        { 
            return mb_strtolower($element->innerHTML);
        } 

        if (!function_exists("get_http_code")) {
            function get_http_code($url) {
                $handle = curl_init($url);
                curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
                $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                curl_close($handle);
                return $httpCode;
            }    
        }
        if (!function_exists('str_contains')) {
            function str_contains($haystack, $needle) {
                return $needle !== '' && mb_strpos($haystack, $needle) !== false;
            }
        }

        $page = $_REQUEST['page'];
        $type = $_REQUEST['type'];
        $elemId = $_REQUEST['id'];
        
        $fullAr['page_url'] = $page;
        $fullAr['IBLOCK_TYPE'] = $type;
        $fullAr['ELEMENT_ID'] = $elemId;

        $metasTable = \Umax\Lib\Internals\UmaxMetasTable::getList([
            'filter' => [
                'page_url' => $page
            ]
        ])->Fetch();

        if(!$metasTable)
            $errors['SITEMAP'] = 'SITEMAP';

        $errors = [];

        if($_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https')
            $errors['SSL'] = 'SSL';

        $dom = new \DOMDocument;
        $getContents = file_get_contents($page);
        $dom->loadHTML($getContents);

        $titleAr = [
            'купить', 'заказать', 'цена', 'доставка', 'гарантия', 'руб', 'цены', 'прайс', 'доставку'
        ];

        $title = $dom->getElementsByTagName('title');
        $titleCheck = false;
        if(count($title) > 0) {
            foreach ($titleAr as $key => $titleValue) {
                if(str_contains($title[0]->textContent, $titleValue)) {
                    $titleCheck = true;
                }
            }
            if(strlen($title[0]->textContent) < 50)
                $errors['META-TITLE'] = 'META-TITLE';

            if($metasTable) {
                if($metasTable['title_dubles'])
                    $errors['TITLE-DUBLES'] = 'TITLE-DUBLES';
            }
        } else
            $errors['META-TITLE'] = 'META-TITLE';

        if(!$titleCheck)
            $errors['TITLE'] = 'TITLE';

        $elementName = $_REQUEST['name'];
        $allElems = $dom->getElementsByTagName('*');
        $microCheck = false;
        foreach ($allElems as $key => $elem) {
            if(($elem->getAttribute('itemprop') == 'name' && $elem->getAttribute('content') == $elementName) || ($elem->getAttribute('itemprop') == 'review' && $elem->getAttribute('itemtype') == 'https://schema.org/Review'))
                $microCheck = true;
        }
        if(!$microCheck)
            $errors['MICRO'] = 'MICRO';

        $metas = $dom->getElementsByTagName('meta');
        foreach ($metas as $key => $meta) {
            if($meta->getAttribute('name') == 'description') {
                $description = $meta->getAttribute('content');
            }

            if($meta->getAttribute('name') == 'robots') {
                $robots = $meta->getAttribute('content');
            }
        }

        if(isset($robots)) {
            if($robots == '')
                $errors['ROBOTS'] = 'ROBOTS';
        } else
            $errors['ROBOTS'] = 'ROBOTS';

        $descAr = [
            'купить', 'заказать', 'цена', 'доставка', 'гарантия', 'руб', 'цены', 'прайс', 'доставку', 'город', 'улица', '+79', '88', 'телефон', 'адрес'
        ];

        if(isset($description)) {
            $descCheck = false;
            foreach ($descAr as $key => $descValue) {
                if(str_contains($description, $descValue)) {
                    $descCheck = true;
                }
            }
            if(!$descCheck)
                $errors['DESCRIPTION'] = 'DESCRIPTION';

            if(strlen($description) < 150)
                $errors['META-DESCRIPTION'] = 'META-DESCRIPTION';

            if($metasTable) {
                if($metasTable['description_dubles'])
                    $errors['DESCRIPTION-DUBLES'] = 'DESCRIPTION-DUBLES';
            }
        } else {
            $errors['DESCRIPTION'] = 'DESCRIPTION';
            $errors['META-DESCRIPTION'] = 'META-DESCRIPTION';
        }

        $h1 = $dom->getElementsByTagName('h1');
        if(count($h1) > 0) {
            if(count($h1) > 1)
                $errors['H1'] = 'H1';
            else {
                if(!$h1[0]->textContent || $h1[0]->textContent == '')
                    $errors['H1'] = 'H1';
                else if (strlen($h1[0]->textContent) <= 5)
                    $errors['H1'] = 'H1';
            }
        } else
            $errors['H1'] = 'H1';

        $robotsTxt = $_SERVER["DOCUMENT_ROOT"] . '/' . 'robots.txt';
        if(file_exists($robotsTxt)) {
            $robotsTxtFile = file_get_contents($robotsTxt);
            $robotsTxtExploded = explode (PHP_EOL, $robotsTxtFile);
            foreach($robotsTxtExploded as $key => $value) {
                $robotsTxtExploded[$key] = trim($value);
            }
        }
        if(in_array('Disallow: '. $page , $robotsTxtExploded))
            $errors['ROBOTS.TXT'] = 'ROBOTS.TXT';

        $ul = [];
        $ol = [];
        $table = [];
        $videoCheck = false;
        $images = [];
        $formCheck = false;
        $checkAlt = false;
        $checkTitle = false;
        $domNode = $dom;
        $doms = $domNode->getElementsByTagName('body')[0];

        $forms = $domNode->getElementsByTagName('form');
        foreach ($forms as $form) {
            $curForm = DOMinnerHTML($form);
            if(str_contains($curForm, 'оставить отзыв') || str_contains($curForm, 'отправить на модерацию') || str_contains($curForm, 'разместить отзыв') || str_contains($curForm, 'оставить комментарий'))
                $formCheck = true;
        }
        $domElAr = [];
        foreach($domNode->getElementsByTagName('images') as $domEl) {
            $domElAr[] = $domEl;
        }
        $images = array_merge($images, $domElAr);
        $checkImages = $domNode->getElementsByTagName('images');
        foreach ($checkImages as $img) {
            if($img->getAttribute('alt') && $img->getAttribute('alt') !== '')
                $checkAlt = true;
            if($img->getAttribute('title') && $img->getAttribute('title') !== '')
                $checkTitle = true;
        }
        $videos = $domNode->getElementsByTagName('video');
        if(count($videos) < 1) {
            $youtube = $domNode->getElementsByTagName('a');
            foreach ($youtube as $key => $ytb) {
                if(str_contains($ytb->getAttribute('href'), 'youtube'))
                    $videoCheck = true;
            }
            $iframes = $domNode->getElementsByTagName('iframe');
            foreach ($iframes as $key => $iframe) {
                if(str_contains($iframe->getAttribute('src'), 'youtube'))
                    $videoCheck = true;
            }
        } else {
            $videoCheck = true;
        }
        $domElAr = [];
        foreach($domNode->getElementsByTagName('ul') as $domEl) {
            $domElAr[] = $domEl;
        }
        $ul = array_merge($ul, $domElAr);
        $domElAr = [];
        foreach($domNode->getElementsByTagName('ol') as $domEl) {
            $domElAr[] = $domEl;
        }
        $ol = array_merge($ol, $domElAr);
        $domElAr = [];
        foreach($domNode->getElementsByTagName('table') as $domEl) {
            $domElAr[] = $domEl;
        }
        $table = array_merge($table, $domElAr);

        if(count($ul) == 0 && count($ol) == 0 && count($table) == 0)
            $errors['LISTS'] = 'LISTS';

        if(!$checkTitle)
            $errors['IMG-TITLE'] = 'IMG-TITLE';

        if(!$checkAlt)
            $errors['IMG-ALT'] = 'IMG-ALT';

        if(!$videoCheck)
            $errors['VIDEO'] = 'VIDEO';

        if(count($images) < 5)
            $errors['IMAGES'] = 'IMAGES';

        if(!$formCheck)
            $errors['FORM'] = 'FORM';

        if(!str_contains(DOMinnerHTML($doms), 'заказать') && !str_contains(DOMinnerHTML($doms), 'в корзину') && !str_contains(DOMinnerHTML($doms), 'купить'))
            $errors['ORDER'] = 'ORDER';

        if(!str_contains(DOMinnerHTML($doms), 'в избранные') && !str_contains(DOMinnerHTML($doms), 'добавить товар в избранные') && !str_contains(DOMinnerHTML($doms), 'в избранном'))
            $errors['FAVOURITE'] = 'FAVOURITE';

        if(!str_contains(DOMinnerHTML($doms), 'отзывы') && !str_contains(DOMinnerHTML($doms), 'говорят клиенты'))
            $errors['REVIEWS'] = 'REVIEWS';

        $doms = mb_strtolower($doms->textContent);

        if(!str_contains($doms, 'в наличии') && !str_contains($doms, 'на складе'))
            $errors['STORAGE'] = 'STORAGE';

        if(!str_contains($doms, 'руб') && !str_contains($doms, '₽'))
            $errors['PRICE'] = 'PRICE';

        if(!str_contains($doms, 'похожие товары') && !str_contains($doms, 'с этим покупают') && !str_contains($doms, 'рекомендуемые товары') && !str_contains($doms, 'хит продаж') && !str_contains($doms, 'вы смотрели'))
            $errors['LINK_IN'] = 'LINK_IN';

        if(!str_contains($doms, 'доставка') && !str_contains($doms, 'доставку осуществляем') && !str_contains($doms, 'СДЕК') && !str_contains($doms, 'Почта России') && !str_contains($doms, 'доставляем по'))
            $errors['DELIVERY'] = 'DELIVERY';

        if(!str_contains($doms, 'гарантия') && !str_contains($doms, 'возврат') && !str_contains($doms, 'гарантийный случай'))
            $errors['GUARANTEE'] = 'GUARANTEE';

        \Umax\Lib\Internals\UmaxSeoOnPageElementErrorsTable::clear([
            'filter' => [
                'ELEMENT_ID' => $elemId
            ]
        ]);

        $fullValue = 0;
        $red = 0;
        $yellow = 0;
        $blue = 0;

        foreach ($errors as $key => $error) {
            $curError = \Umax\Lib\Internals\UmaxSeoErrorsTable::getList([
                'filter' => [
                    'IBLOCK_TYPE' => $fullAr['IBLOCK_TYPE'],
                    'KEY' => $error
                ]
            ])->Fetch();

            $fullAr['ERROR_ID'] = $curError['ID'];
            $fullValue += $curError['VALUE'];

            if($curError['VALUE'] == 3)
                $red += 1;
            else if($curError['VALUE'] == 2)
                $yellow += 1;
            else if($curError['VALUE'] == 1)
                $blue += 1;
                    
            \Umax\Lib\Internals\UmaxSeoOnPageElementErrorsTable::add($fullAr);
        }

        $green = \Umax\Lib\Internals\UmaxSeoErrorsTable::getList([
            'filter' => [
                'IBLOCK_TYPE' => $fullAr['IBLOCK_TYPE'],
            ]
        ])->FetchAll();

        foreach ($green as $key => $value) {
            if(in_array($value['KEY'], $errors)) {
                unset($green[$key]);
            }
        }
        $green = count($green);

        $allErrors = \Umax\Lib\Internals\UmaxSeoErrorsTable::getList([
            'filter' => [
                'IBLOCK_TYPE' => $type,
            ]
        ])->FetchAll();
        $fullErrorsValue = 0;
        foreach ($allErrors as $er) {
            $fullErrorsValue += $er['VALUE'];
        }
        $curValue = round((($fullErrorsValue - $fullValue) * 100) / $fullErrorsValue);

        $elementAr = [
            'page_url' => $page,
            'IBLOCK_TYPE' => $fullAr['IBLOCK_TYPE'],
            'ELEMENT_ID' => $elemId,
            'VALUE' => $fullValue,
            'FULL_VALUE' => $curValue,
            'RED' => $red,
            'YELLOW' => $yellow,
            'GREEN' => $green,
            'BLUE' => $blue,
            'IBLOCK_ID' => \CIBlockElement::GetById($elemId)->Fetch()['IBLOCK_ID']
        ];

        $elemRes = \Umax\Lib\Internals\UmaxSeoOnPageElementTable::GetList([
            'filter' => [
                'IBLOCK_TYPE' => $fullAr['IBLOCK_TYPE'],
                'ELEMENT_ID' => $elemId,
            ]
        ])->Fetch();

        $d = new \DateTime;
        $elementAr['DATE_CHANGE'] = $d->format("Y-m-d H:m:s");

        if($elemRes)
            \Umax\Lib\Internals\UmaxSeoOnPageElementTable::update($elemRes['ID'], $elementAr);
        else
            \Umax\Lib\Internals\UmaxSeoOnPageElementTable::add($elementAr);

        $element = new \CIBlockElement;
        $arElement = \CIBlockElement::GetByID($elemId)->Fetch();
        $element->Update($arElement["ID"], array());
    }
?>