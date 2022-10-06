<?php
namespace Umax\Lib\Internals;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class UmaxSeoPagesTable extends \UmaxAnalysisDataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_umax_seo_pages';
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
            'SUMMARY_META' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Суммарно Мета-Тэгов',
            ),
            'SUMMARY_IMAGES' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Суммарно изображений',
            ),
            'responce' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'Ответ',
            ),
            'PAGES_INDEX' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Индексируется',
            ),
            'PAGES_NOINDEX' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Не индексируется',
            ),
            'PAGES_ERROR' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Ошибка',
            ),
            'INDEX_CLOSED' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Идексация закрыта на странице',
            ),
            'INDEX_META_ROBOTS' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Идексация закрыта в robots.txt',
            ),
            'INDEX_CANONICAL' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Canonical',
            ),
            'INDEX_NOT_GET' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Ответ не 2ХХ',
            ),
            'IMAGES_ALL' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Всего изображений',
            ),
            'IMAGES_NO_ALT' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Изображений без alt',
            ),
            'IMAGES_NO_TITLE' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Изображений без title',
            ),
            'IMAGES_MAX_SIZE' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Изображений с максимальным размером',
            ),
        );
    }
}
