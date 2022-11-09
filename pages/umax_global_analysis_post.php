<?
    use Umax\Lib\Internals\UmaxSeoAnalysisTable;
    use Umax\Lib\Internals\UmaxSeoSettingsTable;
    use Umax\Lib\Internals\UmaxMetasTable;
    use Umax\Lib\Internals\UmaxCommerceTable;
    use Bitrix\Main\Loader;

    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
    require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php');
    global $APPLICATION;
?>
<?

    if (!Loader::includeModule('umax.seoanalysis') || \UmaxAnalysisDataManager::isDemoEnd()) {
        echo CAdminMessage::ShowOldStyleError("Модуль не установлен");
    } else {
        function getUrls($arr) {
            return $arr['loc'];
        }

        $i = 0;
        $pages = [];
        $sitemap = [];
        while(true) {
            if($i == 0)
                $sitemapIndex = '';
            else
                $sitemapIndex = $i;

            $name = $_SERVER["DOCUMENT_ROOT"] . '/' . 'sitemap' . $sitemapIndex . '.xml';
            if(file_exists($name)) {
                $sitemapfile = file_get_contents($name); 
                $xml = simplexml_load_string($sitemapfile);
                $con = json_decode(json_encode($xml), true);
                if(array_key_exists('url', $con))
                    $pages = array_merge($pages, array_map('getUrls', $con['url']));
                else {
                    $sitemap = array_merge($sitemap, array_map('getUrls', $con['sitemap']));
                }
            } else {
                break;
            }
            $i++;
        }
        if(count($sitemap) > 0) {
            foreach ($sitemap as $key => $value) {
                $sitemapfile = file_get_contents($value); 
                $xml = simplexml_load_string($sitemapfile);
                $con = json_decode(json_encode($xml), true);
                if(array_key_exists('url', $con))
                    $pages = array_merge($pages, array_map('getUrls', $con['url']));
            }
        }
        
        $settings = UmaxSeoSettingsTable::getList()->FetchAll();

        $settingAr = [];
        foreach($settings as $setting => $value) {
            $rsElement = CIBlockSection::GetList(
                $arOrder  = array("SORT" => "ASC"),
                $arFilter = array(
                    "ACTIVE"    => "Y",
                    'IBLOCK_ID' => $value['IBLOCK_ID']
                ),
                false,
                ['ID', 'NAME'],
                false,
            );
            $settingAr[$value['TYPE']][$value['IBLOCK_ID']]['IBLOCK_NAME'] = CIBlock::GetById($value['IBLOCK_ID'])->Fetch()['NAME'];
            while($arElement = $rsElement->Fetch()) {
                $settingAr[$value['TYPE']][$value['IBLOCK_ID']][] = $arElement;
            }

            if(!empty($settingAr)) {
                if(is_array($settingAr[$value['TYPE']][$value['IBLOCK_ID']])) {
                    foreach($settingAr[$value['TYPE']][$value['IBLOCK_ID']] as $sectKey => $sect) {
                        if($sectKey !== 'IBLOCK_NAME') {
                            $rsElement = CIBlockElement::GetList(
                                array("SORT" => "ASC"),
                                array(
                                    "ACTIVE"    => "Y",
                                    'IBLOCK_ID' => $value['IBLOCK_ID'],
                                    'IBLOCK_SECTION_ID' => $sect['ID']
                                ),
                                false,
                                false,
                                array()
                            );
                            while($arElement = $rsElement->GetNext()) {
                                $settingAr[$value['TYPE']][$value['IBLOCK_ID']][$sectKey]['items'][] = [
                                    'type' => $value['TYPE'],
                                    'id' => $arElement['ID'],
                                    'page' => $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'] . $arElement['DETAIL_PAGE_URL'],
                                    'name' => $arElement['NAME']
                                ];
                            }
                            if(!isset($settingAr[$value['TYPE']][$value['IBLOCK_ID']][$sectKey]['items'])) 
                                unset($settingAr[$value['TYPE']][$value['IBLOCK_ID']][$sectKey]);
                        }
                    }
                }
            }
            if(count($settingAr[$value['TYPE']][$value['IBLOCK_ID']]) == 1) {
                $rsElement = CIBlockElement::GetList(
                    array("SORT" => "ASC"),
                    array(
                        "ACTIVE"    => "Y",
                        'IBLOCK_ID' => $value['IBLOCK_ID'],
                    ),
                    false,
                    false,
                    array()
                );
                while($arElement = $rsElement->GetNext()) {
                    $settingAr[$value['TYPE']][$value['IBLOCK_ID']]['items'][] = [
                        'type' => $value['TYPE'],
                        'id' => $arElement['ID'],
                        'page' => $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'] . $arElement['DETAIL_PAGE_URL'],
                        'name' => $arElement['NAME']
                    ];
                }

                if(count($settingAr[$value['TYPE']][$value['IBLOCK_ID']]) == 1)
                    unset($settingAr[$value['TYPE']][$value['IBLOCK_ID']]);
            }

            if(count($settingAr[$value['TYPE']]) == 0)
                unset($settingAr[$value['TYPE']]);
        }
        $seotable = UmaxSeoAnalysisTable::getList()->Fetch();
        $isEmpty = UmaxSeoAnalysisTable::checkIfEmpty($seotable);
    ?>
        <div id="analysis__content">
            <?if($_GET['success']):?>
                <p>Обновление успешно завершилось</p>    
                <script>
                    window.history.replaceState('', '', "<?=$APPLICATION->GetCurPage()?>")
                </script>
            <?endif?>
            <p>Рекомендуется выполнять анализ через режим инкогнито, для возможности продолжать работать пока выполняется анализ</p>
            <?if($isEmpty == false):?>
                <?if(!$_GET['detail'] && !$_GET['seo']):?>
                    <a class="download__btn" id="detail" href="<?=$APPLICATION->GetCurPage()?>?detail=Y">
                        Анализ определенных страниц
                    </a>
                    <a class="download__btn" id="seo" href="<?=$APPLICATION->GetCurPage()?>?seo=Y">
                        Анализ категорий
                    </a>
                <?else:?>
                    <a class="download__btn" id="back" href="<?=$APPLICATION->GetCurPage()?>">
                        Вернуться назад
                    </a>
                <?endif;?>
                <?if($_GET['detail']):?>
                    <div id="pages__arr">
                        <?foreach ($pages as $key => $page):?>
                            <div class="pages__arr">
                                <input type="checkbox" value="<?=$page?>" id="page_<?=$key?>">
                                <label for="page_<?=$key?>"><?=$page?></label>
                            </div>
                        <?endforeach;?>
                    </div>
                <?endif;?>
                <?if($_GET['seo']):?>
                    <div id="elems__arr">
                        <div class="elems__arr">
                            <div class="elems__arr baseline">
                                <span>Товары</span>
                                <div class="element__block">
                                    <?if(array_key_exists('GOODS', $settingAr)):?>
                                        <?foreach($settingAr['GOODS'] as $itemKey => $item):?>
                                            <div class="element__block">
                                                <div class="select-zone__block">
                                                    <?=$item['IBLOCK_NAME']?>
                                                    <div class="selectZone">
                                                        <span class="selectAll">
                                                            Выбрать все
                                                        </span>
                                                        <span class="removeAll">
                                                            Убрать все
                                                        </span>
                                                    </div>
                                                </div>
                                                <?if(!array_key_exists('items', $item)):?>
                                                    <?foreach($item as $kk => $itemValue):?>
                                                        <?if($kk !== 'IBLOCK_NAME'):?>
                                                            <div class="element">
                                                                <input type="checkbox" value="<?=$kk?>" id="GOODS-<?=$kk?>-<?=$itemKey?>">
                                                                <label for="GOODS-<?=$kk?>-<?=$itemKey?>"><?=$itemValue['NAME']?></label>
                                                            </div>
                                                        <?endif?>
                                                    <?endforeach?>
                                                <?else:?>
                                                    <div class="element">
                                                        <input type="checkbox" value="GOODS" id="GOODS-items-<?=$itemKey?>">
                                                        <label for="GOODS-items-<?=$itemKey?>">Элементы без разделов</label>
                                                    </div>
                                                <?endif;?>
                                            </div>
                                        <?endforeach?>
                                    <?endif;?>
                                </div>
                            </div>
                        </div>
                        <div class="elems__arr">
                            <div class="elems__arr baseline">
                                <span>Услуги</span>
                                <div class="element__block">
                                    <?if(array_key_exists('SERVICE', $settingAr)):?>
                                        <?foreach($settingAr['SERVICE'] as $itemKey => $item):?>
                                            <div class="element__block">
                                                <div class="select-zone__block">
                                                    <?=$item['IBLOCK_NAME']?>
                                                    <div class="selectZone">
                                                        <span class="selectAll">
                                                            Выбрать все
                                                        </span>
                                                        <span class="removeAll">
                                                            Убрать все
                                                        </span>
                                                    </div>
                                                </div>
                                                <?if(!array_key_exists('items', $item)):?>
                                                    <?foreach($item as $kk => $itemValue):?>
                                                        <?if($kk !== 'IBLOCK_NAME'):?>
                                                            <div class="element">
                                                                <input type="checkbox" value="<?=$kk?>" id="SERVICE-<?=$kk?>-<?=$itemKey?>">
                                                                <label for="SERVICE-<?=$kk?>-<?=$itemKey?>"><?=$itemValue['NAME']?></label>
                                                            </div>
                                                        <?endif?>
                                                    <?endforeach?>
                                                <?else:?>
                                                    <div class="element">
                                                        <input type="checkbox" value="SERVICE" id="SERVICE-items-<?=$itemKey?>">
                                                        <label for="SERVICE-items-<?=$itemKey?>">Элементы без разделов</label>
                                                    </div>
                                                <?endif;?>
                                            </div>
                                        <?endforeach?>
                                    <?endif;?>
                                </div>
                            </div>
                        </div>
                        <div class="elems__arr">
                            <div class="elems__arr baseline">
                                <span>Статьи</span>
                                <div class="element__block">
                                    <?if(array_key_exists('NEWS', $settingAr)):?>
                                        <?foreach($settingAr['NEWS'] as $itemKey => $item):?>
                                            <div class="element__block">
                                                <div class="select-zone__block">
                                                    <?=$item['IBLOCK_NAME']?>
                                                    <div class="selectZone">
                                                        <span class="selectAll">
                                                            Выбрать все
                                                        </span>
                                                        <span class="removeAll">
                                                            Убрать все
                                                        </span>
                                                    </div>
                                                </div>
                                                <?if(!array_key_exists('items', $item)):?>
                                                    <?foreach($item as $kk => $itemValue):?>
                                                        <?if($kk !== 'IBLOCK_NAME'):?>
                                                            <div class="element">
                                                                <input type="checkbox" value="<?=$kk?>" id="NEWS-<?=$kk?>-<?=$itemKey?>">
                                                                <label for="NEWS-<?=$kk?>-<?=$itemKey?>"><?=$itemValue['NAME']?></label>
                                                            </div>
                                                        <?endif?>
                                                    <?endforeach?>
                                                <?else:?>
                                                    <div class="element">
                                                        <input type="checkbox" value="NEWS" id="NEWS-items-<?=$itemKey?>">
                                                        <label for="NEWS-items-<?=$itemKey?>">Элементы без разделов</label>
                                                    </div>
                                                <?endif;?>
                                            </div>
                                        <?endforeach?>
                                    <?endif;?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?endif;?>
                <?if($_GET['update']):?>
                    <?
                        UmaxMetasTable::ude();
                        UmaxCommerceTable::getContent();
                        LocalRedirect($APPLICATION->GetCurPage() . '?success=Y');
                    ?>
                <?endif;?>
            <?endif;?>
            <div class="count__list hidden">
                <canvas id="count"></canvas>
                <span class="count">
                </span>
            </div>
            <?if(!$settings):?>
                <a class="download__btn" href="/bitrix/admin/umax_seo_analysis_settings.php">
                    Совершить настройку
                </a>
            <?else:?>
                <div class="download__btn" id="start">
                    <?if($_GET['detail']):?>
                        Анализ выбранных страниц
                    <?elseif($_GET['seo']):?>
                        Анализ категорий
                    <?else:?>
                        Анализ всех страниц
                    <?endif;?>
                </div>
            <?endif;?>
            <div class="metaCommerce">
                <a class="download__btn" id="seo" href="<?=$APPLICATION->GetCurPage()?>?update=Y">
                    Обновить информацию о META и коммерции
                </a>
            </div>
        </div>
        <?
            $pages = json_encode($pages);
            $settingAr = json_encode($settingAr);
        ?>
        <script src="https://code.jquery.com/jquery-3.5.0.js"></script>
        <link rel="stylesheet" href="/bitrix/themes/.default/umax.seoanalysis/fonts.css">
        <script>
            function drawLine(ctx, startX, startY, endX, endY,color){
                ctx.save();
                ctx.strokeStyle = color;
                ctx.beginPath();
                ctx.moveTo(startX,startY);
                ctx.lineTo(endX,endY);
                ctx.stroke();
                ctx.restore();
            }

            function drawBar(ctx, upperLeftCornerX, upperLeftCornerY, width, height,color){
                ctx.save();
                ctx.fillStyle=color;
                ctx.fillRect(upperLeftCornerX,upperLeftCornerY,width,height);
                ctx.restore();
            }

            function drawArc(ctx, centerX, centerY, radius, startAngle, endAngle, color){
                ctx.save();
                ctx.strokeStyle = color;
                ctx.beginPath();
                ctx.arc(centerX, centerY, radius, startAngle, endAngle);
                ctx.restore();
            }

            function drawPieSlice(ctx,centerX, centerY, radius, startAngle, endAngle, fillColor, strokeColor) {
                ctx.save();
                ctx.fillStyle = fillColor;
                ctx.strokeStyle = strokeColor;
                ctx.beginPath();
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, startAngle, endAngle, strokeColor);
                ctx.closePath();
                ctx.fill();
                ctx.restore();
            }

            function sum( obj ) {
                var sum = 0;
                for( var el in obj ) {
                    if( obj.hasOwnProperty( el ) ) {
                    sum += parseFloat( obj[el] );
                    }
                }
                return sum;
            }

            var Barchart = function(options){
                this.options = options;
                this.canvas = options.canvas;
                this.ctx = this.canvas.getContext("2d");
                this.colors = options.colors;

                this.draw = function(){
                    var maxValue = 0;
                    for (var categ in this.options.data){
                        maxValue = Math.max(maxValue,this.options.data[categ]);
                    }
                    var canvasActualHeight = this.canvas.height - this.options.padding * 2;
                    var canvasActualWidth = this.canvas.width - this.options.padding * 2;

                    //drawing the bars
                    var barIndex = 0;
                    var categIndex = 0;

                    maxValue = this.options.maxValue;
                    for (categ in this.options.data){
                        var numberOfBars = Object.keys(this.options.data[categ]).length;
                        var barSize = (canvasActualWidth)/numberOfBars - 40;
                        var lastElem = 0;
                        for (ctg in this.options.data[categ]){
                            var val = this.options.data[categ][ctg];
                            var barHeight = canvasActualWidth * (val/maxValue);
                            drawBar(
                                this.ctx,
                                lastElem,
                                categIndex * 30,
                                barHeight,
                                30,
                                this.colors[barIndex%this.colors.length]
                            );
                            let fillTextCtg = Math.floor((this.options.data[categ][ctg] * 100) / maxValue)
                            this.ctx.font = "bold 12px Mont";
                            this.ctx.fillStyle = '#fff';
                            if(fillTextCtg > 5 && barIndex == 0)
                                this.ctx.fillText(fillTextCtg.toString() + '%', (barHeight / 2) + lastElem - 10, categIndex * 30 + 17.5);
                            lastElem += barHeight
                            
                            barIndex++;
                        }
                        categIndex++;
                    }
                }
            }

            document.addEventListener("DOMContentLoaded", function() {
                let pages = '<?=$pages?>'
                pages = JSON.parse(pages);
                let types = '<?=$settingAr?>'
                types = JSON.parse(types);

                let selectAll = document.querySelectorAll('.selectAll')
                for (let index = 0; index < selectAll.length; index++) {
                    selectAll[index].addEventListener('click', function() {
                        let selectAllCheckBoxes = selectAll[index].parentNode.parentNode.parentNode.querySelectorAll('input')
                        for(let jIndex = 0; jIndex < selectAllCheckBoxes.length; jIndex++) {
                            selectAllCheckBoxes[jIndex].checked = true
                        }
                    })
                }
                let removeAll = document.querySelectorAll('.removeAll')
                for (let index = 0; index < removeAll.length; index++) {
                    removeAll[index].addEventListener('click', function() {
                        let removeAllCheckBoxes = removeAll[index].parentNode.parentNode.parentNode.querySelectorAll('input')
                        for(let jIndex = 0; jIndex < removeAllCheckBoxes.length; jIndex++) {
                            removeAllCheckBoxes[jIndex].checked = false
                        }
                    })
                }

                let btn = document.querySelector('.download__btn#start')
                btn?.addEventListener('click', function() {
                    let pagesAr = document.querySelectorAll('.pages__arr')
                    if(pagesAr.length > 0) {
                        pages = [];
                        $('#analysis__content input:checked').each((key, element) => {
                            pages.push(element.value)
                        });
                    }
                    var seo = false;
                    <?if($_GET['seo']):?>
                        seo = true
                    <?endif?>

                    if(seo) {
                        let elemsAr = document.querySelectorAll('.elems__arr')
                        if(elemsAr.length > 0) {
                            typesAr = [];
                            $('#analysis__content .elems__arr input:checked').each((key, element) => {
                                typesAr.push(element.id.split('-'))
                            });
                        }
                        let k = 0;
                        let elems = [];
                        for (let j in types) {
                            if(typesAr.find(typeElem => typeElem.includes(j))) {
                                for (let k = 0; k < typesAr.length; k++) {
                                    if(typesAr[k][0] == j && typesAr[k][1] !== 'items')
                                        elems = elems.concat(types[j][typesAr[k][2]][typesAr[k][1]]['items']);
                                    else if(typesAr[k][0] == j && typesAr[k][1] == 'items')
                                        elems = elems.concat(types[j][typesAr[k][2]]['items']);
                                }
                            }
                        }
                        let countList = document.querySelector('.count__list')
                        let back = document.querySelector('.download__btn#back')
                        let elems__arr = document.querySelector('#elems__arr')
                        let metaCommerce = document.querySelector('.metaCommerce')
                        var iAr = [];

                        var count1 = document.getElementById("count");
                        count1.width = 500;
                        count1.height = 30;
                        var ctxCount1 = count1.getContext("2d");

                        var metaObj1 = {
                            'obj': {
                                'calc': 0,
                                '100calc': 100,
                            },
                        };

                        var Count1 = new Barchart(
                        {
                            canvas:count1,
                            seriesName:"",
                            id:'count',
                            padding:0,
                            gridScale:5,
                            horizontal: true,
                            gridColor:"#fff",
                            maxValue: 100,
                            data: metaObj1,
                            colors:["#56C400", "#C3C3C3"]
                        })
                        Count1.draw();
                        document.querySelector('span.count').textContent = '0 из ' + elems.length

                        for(let i = 0; i < elems.length; i++) {
                            if(iAr.length == 0) {
                                btn.classList.add('hidden');
                                metaCommerce.classList.add('hidden');
                                back.classList.add('hidden');
                                countList.classList.remove('hidden');
                                if(elems__arr)
                                    elems__arr.classList.add('hidden')
                            }

                            var elemUrl = false
                            var text = ''

                            setTimeout(() => {
                                if(elems[i].type == 'GOODS') {
                                    elemUrl = '/bitrix/admin/umax_global_analysis_ajax_detail_goods.php'
                                }
                                else if(elems[i].type == 'SERVICE') {
                                    elemUrl = '/bitrix/admin/umax_global_analysis_ajax_detail_services.php'
                                }
                                else if(elems[i].type == 'NEWS') {
                                    elemUrl = '/bitrix/admin/umax_global_analysis_ajax_detail_news.php'
                                }
                                if(iAr.length == 0)
                                    document.querySelector('span.count').textContent = 0 + ' из ' + elems.length
                                $.ajax({
                                    type: "POST",
                                    url: elemUrl,
                                    data: elems[i],
                                }).then(res => {
                                    var count = document.getElementById("count");
                                    count.width = 500;
                                    count.height = 30;

                                    var ctxCount = count.getContext("2d");

                                    var metaObj = {
                                        'obj': {
                                            'calc': Math.floor(((iAr.length + 1) * 100) / elems.length),
                                            '100calc': 100 - Math.floor(((iAr.length + 1) * 100) / elems.length),
                                        },
                                    };

                                    var Count = new Barchart(
                                    {
                                        canvas:count,
                                        seriesName:"",
                                        id:'count',
                                        padding:0,
                                        gridScale:5,
                                        horizontal: true,
                                        gridColor:"#fff",
                                        maxValue: 100,
                                        data: metaObj,
                                        colors:["#56C400", "#C3C3C3"]
                                    })
                                    Count.draw();
                                    document.querySelector('span.count').textContent = (iAr.length + 1) + ' из ' + elems.length

                                    if(iAr.length == elems.length - 1) {
                                        setTimeout(() => {
                                            countList.classList.add('hidden');
                                            metaCommerce.classList.remove('hidden');
                                            btn.classList.remove('hidden');
                                            back.classList.remove('hidden');
                                            if(elems__arr)
                                                elems__arr.classList.remove('hidden')
                                        }, 1000);
                                    }

                                    iAr.push(i);
                                })
                            }, 10000 * i);
                        }
                    }
                    if(pages) {
                        if(pages.length > 0) {
                            if(!seo) {
                                $.ajax({
                                    type: "POST",
                                    url: '/bitrix/admin/umax_global_analysis_empty.php',
                                    data: {
                                        <?if($_GET['detail']):?>
                                            pages: pages
                                        <?endif?>
                                    }
                                }).done(empty => {
                                    let countList = document.querySelector('.count__list')
                                    let detail = document.querySelector('.download__btn#detail')
                                    let seo = document.querySelector('.download__btn#seo')
                                    let pages__arr = document.querySelector('#pages__arr')
                                    let metaCommerce = document.querySelector('.metaCommerce')
                                    var iAr = [];

                                    var count1 = document.getElementById("count");
                                    count1.width = 500;
                                    count1.height = 30;
                                    var ctxCount1 = count1.getContext("2d");

                                    var metaObj1 = {
                                        'obj': {
                                            'calc': 0,
                                            '100calc': 100,
                                        },
                                    };

                                    var Count1 = new Barchart(
                                    {
                                        canvas:count1,
                                        seriesName:"",
                                        id:'count',
                                        padding:0,
                                        gridScale:5,
                                        horizontal: true,
                                        gridColor:"#fff",
                                        maxValue: 100,
                                        data: metaObj1,
                                        colors:["#56C400", "#C3C3C3"]
                                    })
                                    Count1.draw();
                                    document.querySelector('span.count').textContent = '0 из ' + pages.length

                                    for(let i = 0; i < pages.length; i++) {
                                        if(iAr.length == 0) {
                                            btn?.classList.add('hidden');
                                            metaCommerce?.classList.add('hidden');
                                            detail?.classList.add('hidden');
                                            seo?.classList.add('hidden');
                                            countList?.classList.remove('hidden');
                                            if(pages__arr)
                                                pages__arr?.classList.add('hidden')
                                        }
                                        
                                        var ajaxTime= new Date().getTime();
                                        setTimeout(() => {
                                            $.ajax({
                                                type: "POST",
                                                url: '/bitrix/admin/umax_global_analysis_ajax.php',
                                                data: {
                                                    page: pages[i],
                                                    id: i,
                                                    length: pages.length
                                                }
                                            }).done(res => {
                                                var count = document.getElementById("count");
                                                count.width = 500;
                                                count.height = 30;

                                                var ctxCount = count.getContext("2d");

                                                var metaObj = {
                                                    'obj': {
                                                        'calc': Math.floor(((iAr.length + 1) * 100) / pages.length),
                                                        '100calc': 100 - Math.floor(((iAr.length + 1) * 100) / pages.length),
                                                    },
                                                };

                                                var Count = new Barchart(
                                                {
                                                    canvas:count,
                                                    seriesName:"",
                                                    id:'count',
                                                    padding:0,
                                                    gridScale:5,
                                                    horizontal: true,
                                                    gridColor:"#fff",
                                                    maxValue: 100,
                                                    data: metaObj,
                                                    colors:["#56C400", "#C3C3C3"]
                                                })
                                                Count.draw();
                                                document.querySelector('span.count').textContent = (iAr.length + 1) + ' из ' + pages.length

                                                if(iAr.length == pages.length - 1) {
                                                    setTimeout(() => {
                                                        countList?.classList.add('hidden');
                                                        btn?.classList.remove('hidden');
                                                        metaCommerce?.classList.remove('hidden');
                                                        detail?.classList.remove('hidden');
                                                        seo?.classList.remove('hidden');
                                                        if(pages__arr)
                                                            pages__arr?.classList.remove('hidden')
                                                    }, 1000);
                                                }

                                                iAr.push(i);
                                            })
                                        }, 10000 * i);
                                    }
                                })      
                            }
                        } else {
                            alert('Файл sitemap.xml не найден')
                        }
                    } else {
                        alert('Файл sitemap.xml не найден')
                    }
                })
            })
        </script>
        <style>
            .count__list {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                width: fit-content;
            }
            .download__btn {
                padding: 10px 25px;
                background-color: #8EB339;
                border-radius: 5px;
                color: #fff;
                text-decoration: none;
                width: fit-content;
                cursor: pointer;
                margin-bottom: 15px;
            }
            .download__btn:hover {
                text-decoration: none;
                background: #a4b974;
            }
            .count {
                font-weight: 900;
                margin-top: 10px;
            }
            .hidden {
                display: none;
            }
            #analysis__content {
                display: flex;
                flex-direction: column;
                font-family: 'Mont';
                font-style: normal;
                font-size: 16px;
            }
            #analysis__content .pages__arr, #analysis__content .elems__arr {
                display: flex;
                align-items: center;
                margin-bottom: 15px;
            }
            #analysis__content .elems__arr.baseline {
                align-items: baseline;
            }
            #analysis__content .elems__arr.baseline > span {
                margin-right: 10px;
                width: 75px;
            }
            #analysis__content .element__block > .element__block {
                margin-bottom: 10px;
            }
            #analysis__content .select-zone__block {
                margin-bottom: 10px;
                display: flex;
                align-items: center;
            }
            #analysis__content .selectZone {
                margin-left: 10px;
            }
            #analysis__content .selectZone > span {
                border-bottom: 3px dotted #000;
                padding-bottom: 2px;
                cursor: pointer;
                margin-right: 10px;
            }
            #analysis__content .selectZone > span:hover {
                border-bottom-color: #5555;
                color: #5555;
            }
            #analysis__content .metaCommerce {
                padding: 20px 0;
                border-top: 3px solid;
                width: fit-content;
            }
            #analysis__content .metaCommerce .download__btn {
                display: flex;
            }
        </style>
<?
    if(!$_GET['detail'] && !$_GET['seo'])
        $APPLICATION->SetTitle('Анализ всех страниц');
    else if($_GET['detail'])
        $APPLICATION->SetTitle('Анализ определенных страниц');
    else
        $APPLICATION->SetTitle('Seo OnPage элементов');
    }
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
