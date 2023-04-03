<?php
use \Bitrix\Main\ModuleManager;

Class umax_seoanalysis extends CModule
{
  var $MODULE_ID = "umax.seoanalysis";
  var $MODULE_VERSION;
  var $MODULE_VERSION_DATE;
  var $MODULE_NAME;
  var $MODULE_DESCRIPTION;
  var $MODULE_CSS;

  function __construct() {
    $arModuleVersion = array();
    $this->PARTNER_NAME = "Umax agency"; 
    $this->PARTNER_URI = "https://umax.agency/";
    $path = str_replace("\\", "/", __FILE__);
    $path = substr($path, 0, strlen($path) - strlen("/index.php"));
    include($path."/version.php");
    if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
    {
      $this->MODULE_VERSION = $arModuleVersion["VERSION"];
      $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
    }
    
    $this->MODULE_NAME = "SEO анализ сайта для Bitix от UMAX, SEO OnPage - одностраничная оптимизация";
    $this->MODULE_DESCRIPTION = "Аналитика элементов инфоблоков";
  }

  function InstallEvents()
  {
    RegisterModuleDependences(
      "main",
      "OnEndBufferContent",
      $this->MODULE_ID,
      '\Umax\EventHandlersLibAnalysis',
      'OnEndBufferContentHandler');

    RegisterModuleDependences(
      "main",
      "OnBuildGlobalMenu",
      $this->MODULE_ID,
      '\Umax\EventHandlersLibAnalysis',
      'OnBuildGlobalMenuHandler');

    RegisterModuleDependences(
      "main", 
      "OnAdminTabControlBegin", 
      $this->MODULE_ID, 
      '\UmaxAnalysisTab', 
      "UmaxShowTab");

    RegisterModuleDependences(
      "main", 
      "OnAdminListDisplay", 
      $this->MODULE_ID, 
      '\Umax\EventHandlersLibAnalysis', 
      "OnAdminListDisplayHandler");
  }

  function UnInstallEvents()
  {
    UnRegisterModuleDependences(
      "main", 
      "OnEndBufferContent",
      $this->MODULE_ID,
      '\Umax\EventHandlersLibAnalysis',
      'OnEndBufferContentHandler');

    UnRegisterModuleDependences(
      "main",
      "OnBuildGlobalMenu",
      $this->MODULE_ID,
      '\Umax\EventHandlersLibAnalysis',
      'OnBuildGlobalMenuHandler');

    UnRegisterModuleDependences(
        "main", 
        "OnAdminTabControlBegin", 
        $this->MODULE_ID, 
        '\UmaxAnalysisTab', 
        "UmaxShowTab");

    UnRegisterModuleDependences(
      "main", 
      "OnAdminListDisplay", 
      $this->MODULE_ID, 
      '\Umax\EventHandlersLibAnalysis', 
      "OnAdminListDisplayHandler");
  }

  function InstallFiles()
  {
    CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/umax.seoanalysis/install/css",
                $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default", true, true);

    CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/umax.seoanalysis/install/moduleAssets",
                $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default", true, true);

    CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/umax.seoanalysis/install/images",
                $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default/icons/umax.seoanalysis", true, true);
                
    CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/umax.seoanalysis/admin",
                $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
  }

  function UnInstallFiles()
  {
    DeleteDirFilesEx("/bitrix/themes/.default/icons/umax.seoanalysis");
    rmdir('/bitrix/themes/.default/umax.seoanalysis');
    unlink($_SERVER["DOCUMENT_ROOT"].'/bitrix/themes/.default/umax.seoanalysis.css');

    unlink($_SERVER["DOCUMENT_ROOT"].'/bitrix/admin/umax_global_analysis_ajax.php');
    unlink($_SERVER["DOCUMENT_ROOT"].'/bitrix/admin/umax_global_analysis_empty.php');
    unlink($_SERVER["DOCUMENT_ROOT"].'/bitrix/admin/umax_global_analysis_export.php');
    unlink($_SERVER["DOCUMENT_ROOT"].'/bitrix/admin/umax_global_analysis_post.php');
    unlink($_SERVER["DOCUMENT_ROOT"].'/bitrix/admin/umax_global_analysis.php');
    unlink($_SERVER["DOCUMENT_ROOT"].'/bitrix/admin/umax_seo_analysis_settings.php');
    unlink($_SERVER["DOCUMENT_ROOT"].'/bitrix/admin/umax_global_analysis_ajax_detail_goods.php');
    unlink($_SERVER["DOCUMENT_ROOT"].'/bitrix/admin/umax_global_analysis_ajax_detail_news.php');
    unlink($_SERVER["DOCUMENT_ROOT"].'/bitrix/admin/umax_global_analysis_ajax_detail_services.php');
    unlink($_SERVER["DOCUMENT_ROOT"].'/bitrix/admin/umax_global_analysis_empty_elems.php');
  }

  function InstallDB()
  {
      global $DB;
      $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/umax.seoanalysis/install/db/mysql/install.sql');
  }

  function UnInstallDB()
  {
      global $DB;
      $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/umax.seoanalysis/install/db/mysql/uninstall.sql');
  }

  function DoInstall() {
    global $DOCUMENT_ROOT, $APPLICATION;

    $this->InstallFiles();
    $this->InstallEvents();
    $this->InstallDB();

    ModuleManager::registerModule($this->MODULE_ID);
    
    $APPLICATION->IncludeAdminFile("Установка модуля Umax_seo_analysis", $DOCUMENT_ROOT."/bitrix/modules/umax.seoanalysis/install/step.php");
  }

  function DoUninstall()
  {
    global $DOCUMENT_ROOT, $APPLICATION;

    $this->UnInstallFiles();
    $this->UnInstallEvents();
    $this->UnInstallDB();

    ModuleManager::unRegisterModule($this->MODULE_ID);
    $APPLICATION->IncludeAdminFile("Деинсталяция модуля Umax_seo_analysis", $DOCUMENT_ROOT."/bitrix/modules/umax.seoanalysis/install/unstep.php");
  }
}
?>

