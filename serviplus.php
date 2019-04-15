<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since   1.5.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

include_once(__DIR__ . '/classes/Empleado.php');
include_once(__DIR__ . '/classes/Coordinador.php');

class Serviplus extends Module implements WidgetInterface
{
    protected $_html = '';
    protected $templateFile;

    public function __construct()
    {
        $this->name = 'serviplus';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Roanja';
        $this->need_instance = 0;
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->getTranslator()->trans('Management Serviplus system', array(), 'Modules.Serviplus.Admin');
        $this->description = $this->getTranslator()->trans('Online resources management and planning system.', array(), 'Modules.Serviplus.Admin');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:serviplus/views/templates/hook/serviplus.tpl';
    }

    /**
     * @see Module::install()
     */
    public function install()
    {
        /* Adds Module */
        if (parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayHome') &&
            $this->registerHook('actionShopDataDuplication')
        ) {
            $shops = Shop::getContextListShopID();
            $shop_groups_list = array();

            /* Setup each shop */
            foreach ($shops as $shop_id) {
                $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

                if (!in_array($shop_group_id, $shop_groups_list)) {
                    $shop_groups_list[] = $shop_group_id;
                }

                $res = Configuration::updateValue('SERVIPLUS_VERSION', $this->version, false, $shop_group_id, $shop_id);

            } 

            if (count($shop_groups_list)) {
                foreach ($shop_groups_list as $shop_group_id) {
                    $res &= Configuration::updateValue('SERVIPLUS_VERSION', $this->version, false, $shop_group_id); 
                } 
            }

            $res &= Configuration::updateValue('SERVIPLUS_VERSION', $this->version);

            $res &= $this->createTables();

            $this->installTab('AdminParentTabServiplus', 'Serviplus Configuration'); 
            $this->installTab('AdminServiplusEmpleado', 'Datos extras', 'AdminParentCustomer');
            $this->installTab('AdminServiplusCoordinador', 'Coordinadores', 'AdminParentCustomer');
            // $this->installTab('AdminMipServiciosTipo', 'Types of services', 'AdminParentTabServiplus');
            // $this->installTab('AdminMipPrioridadTipo', 'Types of priority', 'AdminParentTabServiplus');
            
            $this->installTab('AdminServiplusModule', 'Configuration', 'AdminParentCustomer');

            return (bool)$res;
        }

        return false;
    }

    public function installTab($className, $tabName, $tabParentName = false)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }
        if ($tabParentName) {
            $tab->id_parent = (int)Tab::getIdFromClassName($tabParentName);
        } else {
            $tab->id_parent = 0;
        }
        
        $tab->module = $this->name;
        return $tab->add();
    }

    /**
    * @see Module::uninstall()
    */
    public function uninstall()
    {
        /* Deletes Module */
        if (parent::uninstall()) {
            /* Deletes tables */
            $res = $this->deleteTables();

            /* Unsets configuration */
            $res &= Configuration::deleteByName('MIPLANNING_VERSION');
            $res &= $this->uninstallTab('AdminParentTabServiplus');
            $res &= $this->uninstallTab('AdminServiplusModule');
            $res &= $this->uninstallTab('AdminServiplusEmpleado');
            $res &= $this->uninstallTab('AdminServiplusCoordinador');
            // $res &= $this->uninstallTab('AdminMipServiciosTipo');
            // $res &= $this->uninstallTab('AdminMipPrioridadTipo');

            return (bool)$res;
        }

        return false;
    }

    public function uninstallTab($tabName = '')
    {
        //$tab_class = Tools::ucfirst($this->name) . Tools::ucfirst($class_sfx);
        $id_tab    = Tab::getIdFromClassName($tabName);
        if ($id_tab != 0) {
            $tab = new Tab($id_tab);
            $tab->delete();
            return true;
        }
    }

     /**
     * Creates tables
     */
    protected function createTables()
    {
        // Install SQL
        $sql = array();
        include dirname(__FILE__) . '/sql/sql-install.php';
        foreach ($sql as $s) {
            if (!Db::getInstance()->Execute($s)) {
                return false;
            }
        }
        return true;
    }

     /**
     * deletes tables
     */
    protected function deleteTables()
    {
        return Db::getInstance()->execute('
            DROP TABLE IF EXISTS 
                `'._DB_PREFIX_.'empleado`,
                `'._DB_PREFIX_.'empleado_shop`,
                `'._DB_PREFIX_.'serv_coordinador`,
                `'._DB_PREFIX_.'serv_coordinador_shop`,
                `'._DB_PREFIX_.'serv_nacionalidades`;              
        ');
    }

    public function getContent()
    {
        # code...
    }


    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId())) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }

        return $this->fetch($this->templateFile, $this->getCacheId());
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        return ['serviplus' => "Hola serviplus!"];
    }
}