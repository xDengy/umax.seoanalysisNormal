<?php
namespace Umax\Lib\Internals;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class UmaxImagesTable extends \UmaxAnalysisDataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_umax_images';
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
            'page_url' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => 'Страница',
            ),
            'img_url' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'Путь изображения',
            ),
            'alt' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'Alt',
            ),
            'title' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'Title',
            ),
            'size' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'Размер',
            ),
        );
    }

    public static function getFilledImages()
    {
        $allImages = self::getList()->fetchAll();

        $resultAr = [];
        foreach ($allImages as $key => $image) {
            if($image['alt'] && $image['title'])
                $resultAr[$image['page_url']]['filled'][] = $image['ID'];
            else
                $resultAr[$image['page_url']]['empty'][] = $image['ID'];
        }
        foreach ($resultAr as $key => $value) {
            if(count($value) == 1)
                unset($resultAr[$key]);
            else {
                $math = round((count($value['filled']) * 100) / (count($value['filled']) + count($value['empty'])));
                if($math < 90)
                 unset($resultAr[$key]);
            }
        }

        return count($resultAr);
    }
}
