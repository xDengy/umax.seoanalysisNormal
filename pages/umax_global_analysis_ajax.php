<?
    use Umax\Lib\Internals\UmaxSeoAnalysisTable;
    use Umax\Lib\Internals\UmaxSeoSettingsTable;
    use Umax\Lib\Internals\UmaxCommerceTable;
    use Umax\Lib\Internals\UmaxMetasTable;
    use Umax\Lib\Internals\UmaxImagesTable;
    use Umax\Lib\Internals\UmaxIndexesTable;
    use Umax\Lib\Internals\UmaxSeoOnPageTable;
    use Umax\Lib\Internals\UmaxSummaryTable;
    use Umax\Lib\Internals\UmaxSeoPagesTable;
    use Bitrix\Main\Loader;

    require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php');
    
    global $APPLICATION;
?>
<?
    if (Loader::includeModule('umax.seoanalysis') && !\UmaxAnalysisDataManager::isDemoEnd()) {
        if (!function_exists("get_http_code")) {
            function get_http_code($url) {
                $handle = curl_init($url);
                curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
                $response = curl_exec($handle);
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

        $summaryAr = UmaxSummaryTable::getMap();
        $seoOnPageAr = UmaxSeoOnPageTable::getMap();
        $indexesAr = UmaxIndexesTable::getMap();
        $metasAr = UmaxMetasTable::getMap();
        $imagesAr = [];

        foreach ($summaryAr as $key => $value) {
            $summaryAr[$key] = null;
        }
        foreach ($seoOnPageAr as $key => $value) {
            $seoOnPageAr[$key] = null;
        }
        foreach ($indexesAr as $key => $value) {
            $indexesAr[$key] = null;
        }
        foreach ($metasAr as $key => $value) {
            $metasAr[$key] = null;
        }

        unset($summaryAr['ID']);
        unset($seoOnPageAr['ID']);
        unset($indexesAr['ID']);
        unset($metasAr['ID']);
        unset($imagesAr['ID']);

        $summaryAr['page_url'] = $page;
        $seoOnPageAr['page_url'] = $page;
        $indexesAr['page_url'] = $page;
        $metasAr['page_url'] = $page;
        $pageType = '';

        $settingsTable = UmaxSeoSettingsTable::getList()->Fetch();
        $goodsId = $settingsTable['GOODS'];
        $serviceId = $settingsTable['SERVICE'];
        $newsId = $settingsTable['NEWS'];
        
        $curStep = 0;
        if(isset($_SESSION['UMAX_STEP'])) {
            $curStep = $_SESSION['UMAX_STEP'];
        }

        $blockCatalog = CIBlock::GetById($goodsId)->Fetch();
        $goods = CIBlockElement::GetList(
            array("SORT" => "ASC"),
            array(
                'IBLOCK_ID' => $goodsId,
                "ACTIVE"    => "Y"
            ),
            []
        );

        $pageNew = explode('#SITE_DIR#', $page)[1];

        if(str_contains($pageNew, $blockCatalog['LIST_PAGE_URL']))
            $pageType = 'Товар';

        $blockService = CIBlock::GetById($serviceId)->Fetch();
        $service = CIBlockElement::GetList(
            array("SORT" => "ASC"),
            array(
                'IBLOCK_ID' => $serviceId,
                "ACTIVE"    => "Y"
            ),
            []
        );
        if(str_contains($pageNew, $blockService['LIST_PAGE_URL']))
            $pageType = 'Услуга';

        $blockNews = CIBlock::GetById($newsId)->Fetch();
        $news = CIBlockElement::GetList(
            array("SORT" => "ASC"),
            array(
                'IBLOCK_ID' => $newsId,
                "ACTIVE"    => "Y"
            ),
            []
        );
        if(str_contains($pageNew, $blockNews['LIST_PAGE_URL']))
            $pageType = 'Статья';

        $summaryAr['type'] = $pageType;
        $seoOnPageAr['type'] = $pageType;

        $robotsTxt = $_SERVER["DOCUMENT_ROOT"] . '/' . 'robots.txt';
        if(file_exists($robotsTxt)) {
            $robotsTxtFile = file_get_contents($robotsTxt);
            $robotsTxtExploded = explode (PHP_EOL, $robotsTxtFile);
            foreach($robotsTxtExploded as $key => $value) {
                $robotsTxtExploded[$key] = trim($value);
            }
        }
        
        $emptyAlt = [];
        $emptyTitle = [];
        $imgSize = [];
        $checkImg = [];
        $fullAr = [];

        $dom = new DOMDocument;
        $getContents = file_get_contents($page . '/');
        $dom->loadHTML($getContents);

        $links = $dom->getElementsByTagName('link');
        $linkContent = [];
        foreach ($links as $k => $link) {
            if($link->getAttribute('rel') == 'canonical')
                $linkContent = $page;
        }

        $robotContent = [];

        $indexPages = 1;
        $noIndexPages = 0;
        $allMeta = 0;
        $descriptions = 0;
        $metaAr = $dom->getElementsByTagName('meta');
        foreach($metaAr as $node)
        {
            if($node->getAttribute('name') == 'robots' && $node->getAttribute('content') == 'noindex')
                $robotContent = $page;

            if($node->getAttribute('name') == 'description') {
                $curDesc = trim($node->getAttribute('content'));
                if(strlen($curDesc) > 0) {
                    $descriptions += 1;
                    $summaryAr['description'] = $curDesc;
                    $metasAr['description'] = $curDesc;
                }
            }

            $allMeta += 1;
        }
        $titles = trim($dom->getElementsByTagName('title')[0]->textContent);
        $summaryAr['title'] = $titles;
        $metasAr['title'] = $titles;
        if(strlen($titles) > 0) {
            $titles = 1;
        }
        else
            $titles = 0;

        $h1s = trim($dom->getElementsByTagName('h1')[0]->textContent);
        $summaryAr['h1'] = $h1s;
        $metasAr['h1'] = $h1s;
        if(strlen($h1s) > 0) {
            $h1s = 1;
        }
        else
            $h1s = 0;

        $newDom = \UmaxAnalysisDataManager::getMainZone($dom);
        $domNode = $dom;
        // foreach ($newDom as $key => $domNode) {
            $imgAr = $domNode->getElementsByTagName('img'); 
            foreach($imgAr as $k => $node)
            {
                $imagesAr[$k]['page_url'] = $page;

                $imagesAr[$k]['alt'] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $node->getAttribute('alt'));
                if(!$node->hasAttribute('alt') || $node->getAttribute('alt') == '')
                    $emptyAlt[] = $node;

                $imagesAr[$k]['title'] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $node->getAttribute('title'));
                if(!$node->hasAttribute('title') || $node->getAttribute('title') == '')
                    $emptyTitle[] = $node;
                    
                $allImg[] = $node;
                if((!$node->hasAttribute('title') || $node->getAttribute('title') == '') && (!$node->hasAttribute('alt') || $node->getAttribute('alt') == ''))
                    $checkImg[] = $node;
                
                $src = $node->getAttribute('src');
                if(!str_contains($node->getAttribute('src'), 'https://') || !str_contains($node->getAttribute('src'), 'https://'))
                    $src = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['SERVER_NAME'] . $src;

                $imgSizeValue = round(get_headers($src, 1)["Content-Length"] / 1024);
                $imagesAr[$k]['img_url'] = $src;
                $imagesAr[$k]['size'] = $imgSizeValue;

                if($imgSizeValue >= 400)
                    $imgSize[] = $node;

            }
        // }

        $status = substr(get_http_code($page), 0, 1);
        $summaryAr['responce'] = get_http_code($page);
        $indexesAr['responce'] = get_http_code($page);
        $fullAr['responce'] = get_http_code($page);
        $summaryAr['index'] = 1;
        if($status !== '2')
            $get = $page;
        if($status == '4')
            $error = $page;

        if(in_array('Disallow: '. explode($_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['SERVER_NAME'], $page)[1] , $robotsTxtExploded))
            $robotsInTxt = $page;

        if($page == $robotsInTxt || $page == $robotContent || $page == $linkContent || $page == $get || $page == $error) {
            $indexPages = 0;
            $noIndexPages = 1;
            $summaryAr['index'] = 0;
        }

        $indexesAr['reasons'] = [];
        if($page == $robotsInTxt)
            $indexesAr['reasons'][] = 'закрыты в robots.txt';
        if($page == $robotContent)
            $indexesAr['reasons'][] = 'закрыты в мета теге robots';
        if($page == $linkContent)
            $indexesAr['reasons'][] = 'rel=canonical';
        if($page == $get)
            $indexesAr['reasons'][] = 'страницы не 2хх';
                
        if(count($indexesAr['reasons']) > 0)
            $indexesAr['reasons'] = json_encode($indexesAr['reasons']);
        
        $summaryAr['seo_on_page'] = 0;
        $seoOnPageAr['seo_on_page'] = 0;

        if($get == $page)
            $get = 1;
        else
            $get = 0;
        if($error == $page)
            $error = 1;
        else
            $error = 0;
        if($robotsInTxt == $page)
            $robotsInTxt = 1;
        else
            $robotsInTxt = 0;
        if($robotContent == $page)
            $robotContent = 1;
        else
            $robotContent = 0;
        if($linkContent == $page)
            $linkContent = 1;
        else
            $linkContent = 0;

        $fullAr['page_url'] = $page;
        $fullAr['SUMMARY_SEO_ON_PAGE'] = 0;
        $fullAr['IMAGES_NO_ALT'] = count($emptyAlt);

        $fullAr['META_TITLE_UNIQUE'] = 0;
        $fullAr['META_TITLE_NOUNIQUE'] = 0;
        $fullAr['META_EMPTY'] = 0;

        $fullAr['META_DESC_UNIQUE'] = 0;
        $fullAr['META_DESC_NOUNIQUE'] = 0;
        $fullAr['META_DESC_EMPTY'] = 0;

        $fullAr['META_H1_UNIQUE'] = 0;
        $fullAr['META_H1_NOUNIQUE'] = 0;
        $fullAr['META_H1_EMPTY'] = 0;

        $fullAr['IMAGES_NO_TITLE'] = count($emptyTitle);

        $fullAr['IMAGES_ALL'] = count($allImg);
        if(count($allImg) > 0) {
            $fullAr['SUMMARY_IMAGES'] = round((count($checkImg) * 100) / count($allImg));
        } else {
            $fullAr['SUMMARY_IMAGES'] = 0;
        }
        $fullAr['SUMMARY_META'] = $allMeta;

        $fullAr['INDEX_CANONICAL'] = $linkContent;
        $fullAr['INDEX_META_ROBOTS'] = $robotContent;
        $fullAr['INDEX_CLOSED'] = $robotsInTxt;

        $fullAr['PAGES_ERROR'] = $error;
        $fullAr['INDEX_NOT_GET'] = $get;

        $fullAr['IMAGES_MAX_SIZE'] = count($imgSize);
        $fullAr['SUMMARY_INDEX'] = 1;

        $fullAr['PAGES_INDEX'] = $indexPages;
        $fullAr['PAGES_NOINDEX'] = $noIndexPages;

        $fullAr['SEO_ON_PAGE_GOODS'] = $goods;
        $fullAr['SEO_ON_PAGE_SERVICE'] = $service;
        $fullAr['SEO_ON_PAGE_NEWS'] = $news;

        $res = UmaxSeoAnalysisTable::plus($fullAr);
        $res = UmaxSeoPagesTable::add($fullAr);
        $res = UmaxSummaryTable::add($summaryAr);
        if($seoOnPageAr['type'] !== '')
            $res = UmaxSeoOnPageTable::add($seoOnPageAr);
        $res = UmaxIndexesTable::add($indexesAr);
        $res = UmaxMetasTable::add($metasAr);
        UmaxImagesTable::addMultiple($imagesAr);

        if($_POST['length'] - 1 == $_POST['id']) {
            UmaxMetasTable::ude();
            UmaxCommerceTable::getContent();
        }
    }
?>