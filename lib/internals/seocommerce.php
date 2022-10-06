<?php
namespace Umax\Lib\Internals;

use Bitrix\Main\Localization\Loc;
use Umax\Lib\Internals\UmaxSeoAnalysisTable;
Loc::loadMessages(__FILE__);

class UmaxCommerceTable extends \UmaxAnalysisDataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_umax_commerce';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => 'ID',
            ),
            'factor' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => 'Фактор',
            ),
            'contain' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'Содержит',
            ),
        );
    }

    public static function str_contains ($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }

    public static function getTemplateContent($content, $type, $contacts = false, $about = false) {
        $aboutAr = [
            'о себе',
            'о компании',
            'о нас',
        ];
        $buyerAr = [
            'возврат',
            'обмен',
            'гарантия',
        ];
        $payAr = [
            'доставка',
            'оплата',
        ];
        $blogAr = [
            'доставка',
            'оплата',
        ];
        $socials = [
            'instagram.com',
            'facebook.com',
            'twitter.com',
            'odnoklassniki.ru',
            'ok.ru',
            'tiktok.com',
            'vk.com',
            'zen.yandex.ru',
            'youtube.com',
            'rutube.ru',
        ];
        $messengers = [
            't.me',
            'whatsapp.com',
            'viber',
        ];
        $days = [
            'пн',
            'сб',
            'вс',
            'понедельник',
        ];
        $calls = [
            'обратный звонок',
            'позвонить',
            'записаться',
            'консультация',
        ];
        $addresses = [
            'ул.',
            'улица.',
            'г.',
            'город.',
            'район',
            'р-он',
            'корп',
            'корпус',
            'этаж',
            'эт.',
            'офис',
            'оф',
        ];

        $commerce = 0;
        $a = $content->getElementsByTagName('a');
        $ar = [];
        $reasonsAr = [];
        foreach($a as $aValue) {
            if(UmaxCommerceTable::str_contains(trim(mb_strtolower($aValue->textContent)), '+79') || UmaxCommerceTable::str_contains(trim(mb_strtolower($aValue->textContent)), '88')) {
                $commerce++;
                $reasonsAr[$type . ' наличие номера телефона'] = 'Да';
                if('tel:' . trim(mb_strtolower($aValue->textContent)) == $aValue->getAttribute('href')) {
                    $commerce++;
                    $reasonsAr[$type . ' номер телефона равен ссылки на номер телефона'] = 'Да';
                }
                $phoneCheck = 1;
            }
            if($phoneCheck)
                continue;
            if(UmaxCommerceTable::str_contains(trim(mb_strtolower($aValue->textContent)), '@')) {
                $commerce++;
                $reasonsAr[$type . ' наличие адреса почты'] = 'Да';
                if('mailto:' . trim(mb_strtolower($aValue->textContent)) == $aValue->getAttribute('href')) {
                    $commerce++;
                    $reasonsAr[$type . ' адрес почты равна адресу ссылки на почту'] = 'Да';
                }
                $mailCheck = 1;
            }
            if($mailCheck)
                continue;
            if($type !== 'страница контактов') {
                if(UmaxCommerceTable::str_contains(trim(mb_strtolower($aValue->textContent)), 'контакт')) {
                    $commerce++;
                    $reasonsAr[$type . ' наличие страницы контакты'] = 'Да';
                    $contacts = $aValue->getAttribute('href');
                    $contactsCheck = 1;
                }
                if($contactsCheck)
                    continue;
                if(in_array(trim(mb_strtolower($aValue->textContent)), $aboutAr)) {
                    $commerce++;
                    $about = $aValue->getAttribute('href');
                    $reasonsAr[$type . ' наличие станицы о нас/о компании'] = 'Да';
                    $aboutCheck = 1;
                }
                if($aboutCheck)
                    continue;
                if(in_array(trim(mb_strtolower($aValue->textContent)), $buyerAr)) {
                    $commerce++;
                    $reasonsAr[$type . ' наличие страниц покупателей'] = 'Да';
                    $butCheck = 1;
                }
                if($butCheck)
                    continue;
                if(in_array(trim(mb_strtolower($aValue->textContent)), $payAr)) {
                    $commerce++;
                    $reasonsAr[$type . ' наличие станиц оплаты'] = 'Да';
                    $payCheck = 1;
                }
                if($payCheck)
                    continue;
                if(in_array(trim(mb_strtolower($aValue->textContent)), $blogAr)) {
                    $commerce++;
                    $reasonsAr[$type . ' наличие страницы блогов'] = 'Да';
                    $blogCheck = 1;
                }
                if($blogCheck)
                    continue;
                if(trim(mb_strtolower($aValue->textContent)) == 'новости') {
                    $commerce++;
                    $reasonsAr[$type . ' наличие страницы новостей'] = 'Да';
                    $newsCheck = 1;
                }
                if($newsCheck)
                    continue;
            }
            if($type == 'футер' || $type == 'страница контактов') {
                if(trim(mb_strtolower($aValue->textContent)) == 'политика конфиденциальности' || trim(mb_strtolower($aValue->textContent)) == 'персональные данные') {
                    $commerce++;
                    $reasonsAr[$type . ' наличие политики конфиденциальности или персональных данных'] = 'Да';
                    $politicCheck = 1;
                }
            }
            if($politicCheck)
                continue;
            foreach ($socials as $social) {
                if(UmaxCommerceTable::str_contains($aValue->getAttribute('href'), $social)) {
                    $commerce++;
                    $reasonsAr[$type . ' наличие социальных сетей'] = 'Да';
                    $socialCheck = 1;
                    break;
                }
                if($socialCheck)
                    break;
            }
            if($socialCheck)
                continue;
            foreach ($messengers as $messenger) {
                if(UmaxCommerceTable::str_contains($aValue->getAttribute('href'), $messenger)) {
                    $commerce++;
                    $reasonsAr[$type . ' наличие мессенджеров'] = 'Да';
                    $messengerCheck = 1;
                    break;
                }
                if($messengerCheck)
                    break;
            }
            if($messengerCheck)
                continue;
        }
        foreach($days as $day) {
            if(UmaxCommerceTable::str_contains(mb_strtolower($content->innerHTML), $day)) {
                $commerce++;
                $reasonsAr[$type . ' наличие графика работы'] = 'Да';
                $dayCheck = 1;
                break;
            }
            if($dayCheck)
                break;
        }
        if($type !== 'страница контактов') {
            foreach($calls as $call) {
                if(UmaxCommerceTable::str_contains(mb_strtolower($content->innerHTML), $call)) {
                    $commerce++;
                    $reasonsAr[$type . ' наличие форм связи'] = 'Да';
                    $callCheck = 1;
                    break;
                }
                if($callCheck)
                    break;
            }
        }
        if($type == 'футер' || $type == 'страница контактов') {
            foreach($addresses as $address) {
                if(in_array($address, explode(' ', $content->innerHTML))) {
                    $commerce++;
                    $reasonsAr[$type . ' наличие адреса'] = 'Да';
                    $addressCheck = 1;
                    break;
                }
                if($addressCheck)
                    break;
            }
        }
        if($type == 'страница контактов') {
            $h1 = $content->getElementsByTagName('h1')[0];
            if(strlen($h1->textContent) > 0) {
                $reasonsAr[$type . ' наличие h1'] = 'Да';
                $commerce++;
            }
        }
        return ['commerce' => $commerce, 'about' => $about, 'contacts' => $contacts, 'reasonsAr' => $reasonsAr];
    }

    public static function getContent()
    {
        $page = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['SERVER_NAME'];
        $commerce = 0;

        $dom = new \DOMDocument;
        $getContents = file_get_contents($page . '/');
        $dom->loadHTML($getContents);

        $contacts = false;
        $about = false;

        $reasonsAr = [
            'хидер наличие номера телефона' => 'Нет',
            'футер наличие номера телефона' => 'Нет',
            'страница контактов наличие номера телефона' => 'Нет',
            'хидер номер телефона равен ссылки на номер телефона' => 'Нет',
            'футер номер телефона равен ссылки на номер телефона' => 'Нет',
            'страница контактов номер телефона равен ссылки на номер телефона' => 'Нет',
            'хидер наличие адреса почты' => 'Нет',
            'футер наличие адреса почты' => 'Нет',
            'страница контактов наличие адреса почты' => 'Нет',
            'хидер адрес почты равна адресу ссылки на почту' => 'Нет',
            'футер адрес почты равна адресу ссылки на почту' => 'Нет',
            'страница контактов адрес почты равна адресу ссылки на почту' => 'Нет',
            'хидер наличие страницы контакты' => 'Нет',
            'футер наличие страницы контакты' => 'Нет',
            'хидер наличие станицы о нас/о компании' => 'Нет',
            'футер наличие станицы о нас/о компании' => 'Нет',
            'хидер наличие страниц покупателей' => 'Нет',
            'футер наличие страниц покупателей' => 'Нет',
            'хидер наличие станиц оплаты' => 'Нет',
            'футер наличие станиц оплаты' => 'Нет',
            'хидер наличие страницы блогов' => 'Нет',
            'футер наличие страницы блогов' => 'Нет',
            'хидер наличие страницы новостей' => 'Нет',
            'футер наличие страницы новостей' => 'Нет',
            'футер наличие политики конфиденциальности или персональных данных' => 'Нет',
            'страница контактов наличие политики конфиденциальности или персональных данных' => 'Нет',
            'страница контактов наличие реквизитов' => 'Нет',   
            'хидер наличие социальных сетей' => 'Нет',
            'футер наличие социальных сетей' => 'Нет',
            'страница контактов наличие социальных сетей' => 'Нет',
            'хидер наличие мессенджеров' => 'Нет',
            'футер наличие мессенджеров' => 'Нет',
            'страница контактов наличие мессенджеров' => 'Нет',
            'хидер наличие графика работы' => 'Нет',
            'футер наличие графика работы' => 'Нет',
            'страница контактов наличие графика работы' => 'Нет',
            'хидер наличие форм связи' => 'Нет',
            'футер наличие форм связи' => 'Нет',
            'футер наличие адреса' => 'Нет',
            'страница контактов наличие адреса' => 'Нет',
            'страница контактов наличие h1' => 'Нет',
            'страница о нас/о компании наличие видео' => 'Нет',
            'страница о нас/о компании наличие отзывов' => 'Нет',
            'страница о нас/о компании наличие h1' => 'Нет',
            'страница о нас/о компании наличие минимум 5 изображений' => 'Нет',
            'страница контактов наличие карты' => 'Нет',
            'страница контактов наличие формы' => 'Нет',
            'страница контактов наличие фраз транспорта' => 'Нет',
            'страница контактов наличие минимум 5 изображений' => 'Нет',
            'страница о нас/о компании наличие сертификатов/наград/дипломов' => 'Нет',
        ];
        
        $header = $dom->getElementsByTagName('header')[0];
        if(isset($header)) {
            $headerContent = UmaxCommerceTable::getTemplateContent($header, 'хидер', $contacts, $about);
            $commerce += $headerContent['commerce'];
            $contacts = $headerContent['contacts'];
            $about = $headerContent['about'];
            $reasonsAr = array_merge($reasonsAr, $headerContent['reasonsAr']);
        } else {
            $header = $dom->getElementsByClassName('header')[0];
            if(isset($header)) {
                $headerContent = UmaxCommerceTable::getTemplateContent($header, 'хидер', $contacts, $about);
                $commerce += $headerContent['commerce'];
                $contacts = $headerContent['contacts'];
                $about = $headerContent['about'];
                $reasonsAr = array_merge($reasonsAr, $headerContent['reasonsAr']);
            }
        }
        
        $footer = $dom->getElementsByTagName('footer')[0];
        if(isset($footer)) {
            $footerContent = UmaxCommerceTable::getTemplateContent($footer, 'футер', $contacts, $about);
            $commerce += $footerContent['commerce'];
            $contacts = $footerContent['contacts'];
            $about = $footerContent['about'];
            $reasonsAr = array_merge($reasonsAr, $footerContent['reasonsAr']);
        } else {
            $footer = $dom->getElementsByClassName('footer')[0];
            if(isset($footer)) {
                $footerContent = UmaxCommerceTable::getTemplateContent($footer, 'футер', $contacts, $about);
                $commerce += $footerContent['commerce'];
                $contacts = $footerContent['contacts'];
                $about = $footerContent['about'];
                $reasonsAr = array_merge($reasonsAr, $footerContent['reasonsAr']);
            }
        }

        if($about) {
            $aboutDom = new \DOMDocument;
            $aboutContents = file_get_contents($page . $about);
            $aboutDom->loadHTML($aboutContents);

            $anyElems = parent::getMainZone($aboutDom);
            $aboutImages = [];
            foreach ($anyElems as $key => $value) {
                $aboutImages = array_merge($aboutImages, $value->getElementsByTagName('img'));
                $video = $value->getElementsByTagName('video')[0];
                if($video) {
                    $reasonsAr['страница о нас/о компании наличие видео'] = 'Да';
                    $commerce++;
                } else {
                    $videoA = $value->getElementsByTagName('a');
                    foreach ($videoA as $a) {
                        if(UmaxCommerceTable::str_contains($a->getAttribute('href'), 'youtube')) {
                            $reasonsAr['страница о нас/о компании наличие видео'] = 'Да';
                            $commerce++;
                            break;
                        }
                    }
                }
                if(UmaxCommerceTable::str_contains($value->innerHTML, 'отзывы') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'отзывы клиентов') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'от клиентов') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'о нас говорят')) {
                        $reasonsAr['страница о нас/о компании наличие отзывов'] = 'Да';
                        $commerce++;
                }
                if(UmaxCommerceTable::str_contains($value->innerHTML, 'сертификаты') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'награды') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'дипломы')) {
                        $reasonsAr['страница о нас/о компании наличие сертификатов/наград/дипломов'] = 'Да';
                        $commerce++;
                }
            }
            $h1 = $aboutDom->getElementsByTagName('h1')[0];
            if(strlen($h1->textContent) > 0) {
                $reasonsAr['страница о нас/о компании наличие h1'] = 'Да';
                $commerce++;
            }
            if(count($aboutImages) >= 5) {
                $commerce++;
                $reasonsAr['страница о нас/о компании наличие минимум 5 изображений'] = 'Да';
            }
        }

        if($contacts) {
            $contactsDom = new \DOMDocument;
            $contactsContents = file_get_contents($page . $contacts);
            $contactsDom->loadHTML($contactsContents);
            $anyElems = parent::getMainZone($contactsDom);

            $contactsImages = [];
            foreach ($anyElems as $key => $value) {
                $contactsTemplateContent = UmaxCommerceTable::getTemplateContent($value, 'страница контактов');
                $commerce += $contactsTemplateContent['commerce'];
                $reasonsAr = array_merge($reasonsAr, $contactsTemplateContent['reasonsAr']);

                $images = $value->getElementsByTagName('img');
                foreach ($images as $img) {
                    $src = $img->getAttribute('src');
                    $type = explode('.', $src)[1];
                    if(mb_strtolower($type) == 'jpg' || mb_strtolower($type) == 'png' || mb_strtolower($type) == 'jpeg')
                        $contactsImages[] = $img;
                }
                $map = $value->getElementsByTagName('ymaps')[0];
                if($map) {
                    $reasonsAr['страница контактов наличие карты'] = 'Да';
                    $commerce++;
                } else {
                    $mapA = $value->getElementsByTagName('a');
                    foreach ($mapA as $a) {
                        if(UmaxCommerceTable::str_contains($a->getAttribute('href'), 'google.com/maps')) {
                            $commerce++;
                            $reasonsAr['страница контактов наличие карты'] = 'Да';
                            break;
                        }
                    }
                }
                $form = $value->getElementsByTagName('form')[0];
                if($form) {
                    $reasonsAr['страница контактов наличие формы'] = 'Да';
                    $commerce++;
                }
                if(UmaxCommerceTable::str_contains($value->innerHTML, 'ИНН') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'ОГРН') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'БИК') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'Юридический адрес')) {
                        $reasonsAr['страница контактов наличие реквизитов'] = 'Да';
                        $commerce++;
                }
                if(UmaxCommerceTable::str_contains($value->innerHTML, 'добраться') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'доехать') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'автобус') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'маршрут') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'автомобиль') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'авто') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'машина') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'троллейбус') || 
                    UmaxCommerceTable::str_contains($value->innerHTML, 'трамвай')) {
                        $reasonsAr['страница контактов наличие фраз транспорта'] = 'Да';
                        $commerce++;
                }

            }
            if(count($contactsImages) >= 5) {
                $commerce++;
                $reasonsAr['страница контактов наличие минимум 5 изображений'] = 'Да';
            }
        }   

        $updateReasons = [];
        foreach ($reasonsAr as $key => $value) {
            $updateReasons[] = [
                'factor' => $key,
                'contain' => $value,
            ];
        }

        foreach($updateReasons as $reason) {
            $id = UmaxCommerceTable::getList(['filter' => ['factor' => $reason['factor']]])->Fetch()['ID'];
            if($id)
                UmaxCommerceTable::update($id, $reason);
            else
                UmaxCommerceTable::add($reason);
        }
        
        UmaxSeoAnalysisTable::update(1, [
            'SUMMARY_COMMERCE' => $commerce,
            'COMMERCE' => $commerce,
        ]);
        return $commerce;
    }
}
