<?php
/**
 * 2016-2019 ROANJA.COM
 *
 * NOTICE OF LICENSE
 *
 *  @author Romell Jaramillo <integraciones@roanja.com>
 *  @copyright 2016-2019 ROANJA.COM
 *  @license GNU General Public License version 2
 *
 * You can not resell or redistribute this software.
 */

require_once _PS_MODULE_DIR_.'serviplus/classes/Empleado.php';

class AdminServiplusEmpleadoController extends ModuleAdminController
{

    protected $coordinador_list = array();

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'serv_empleado';
        $this->className = 'Empleado';
        $this->allow_export = true;
        $this->identifier = 'id_empleado';
        $this->_defaultOrderBy = 'id_empleado';
        $this->_defaultOrderWay = 'ASC';

        parent::__construct();

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('view');

        if (!Tools::getValue('realedit')) {
            $this->deleted = false;
        }

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->trans('Delete selected', array(), 'Admin.Empleado.Serviplus'),
                'confirm' => $this->trans('Delete selected items?', array(), 'Admin.Empleado.Serviplus'),
                'icon' => 'icon-trash'
            )
        );       
    }

    public function renderList()
    {
        $this->fields_list = array(
            'id_empleado' => array(
                'title' => $this->trans('ID', array(), 'Admin.Empleado.Serviplus'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'seguridad_social' => array(
                'title' => $this->trans('Seguridad social', array(), 'Admin.Empleado.Serviplus')
            ),
            'nacionalidad' => array(
                'title' => $this->trans('Nacionalidad', array(), 'Admin.Empleado.Serviplus'),
                'type' => 'select',
                'list' => $this->getNacionalidades(),
                'filter_key' => 'cl!codigo',
            ),
            'discapacidad' => array(
                'title' => $this->trans('Discapacidad', array(), 'Admin.Empleado.Serviplus')
            ),
            'coordinador' => array(
                'title' => $this->trans('Coordinador', array(), 'Admin.Empleado.Serviplus'),
                'type' => 'select',
                'list' => Coordinador::getCoordinador(),
                'filter_key' => 'c!id',
            ),
            'date_add' => array(
                'title' => $this->trans('Start Time', array(), 'Admin.Empleado.Serviplus')
            ),
            'date_upd' => array(
                'title' => $this->trans('End Time', array(), 'Admin.Empleado.Serviplus')
            )
        );

        $this->_select = 'cl.`nacionalidad` as nacionalidad, cu.`firstname` as coordinador';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'serv_nacionalidades` cl ON (cl.`codigo` = a.`id_nacionalidad`)
            LEFT JOIN `' . _DB_PREFIX_ . 'serv_coordinador` c ON (c.`id_coordinador` = a.`id_coordinador`)
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` cu ON (c.id_customer = cu.id_customer)';

        return parent::renderList();
    }

    public function renderView()
    {

        $this->tpl_view_vars = array(
            'empleado' => $this->object
        );

        $this->base_tpl_view = 'view.tpl';

        return parent::renderView();
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_empleado'] = array(
                'href' => self::$currentIndex.'&addserv_empleado&token='.$this->token,
                'desc' => $this->trans('Add new Employe', array(), 'Admin.Empleado.Serviplus'),
                'icon' => 'process-icon-new'
            );
        }

        parent::initPageHeaderToolbar();
    }   
 
    public function renderForm()
    {          
        $coordinadores = Coordinador::getCoordinador();

        foreach ($coordinadores as $coordinador) {
            $this->coordinador_list[] = array('id' => $coordinador['id_coordinador'], 'name' => $coordinador['firstname']. ' ' . $coordinador['lastname']);
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->trans('Customer', array(), 'Admin.Global'),
                'icon' => 'icon-user',
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->trans('Nº Seguridad social', array(), 'Admin.Empleado.Serviplus'),
                    'name' => 'seguridad_social',
                    'col' => '4',                    
                    'required' => true,
                ),
                 array(
                    'type' => 'select',
                    'label' => $this->trans('Nacionalidad', array(), 'Admin.Empleado.Serviplus'),
                    'name' => 'id_nacionalidad',
                    'required' => true,
                    'options' => array(
                        'query' => $this->getNacionalidades(),
                        'id' => 'codigo',
                        'name' => 'nacionalidad',
                        'default' => array(
                            'label' => $this->trans('Nacionalidad', array(), 'Admin.Empleado.Serviplus'),
                            'value' => 0,
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->trans('Discapacidad', array(), 'Admin.Actions'),
                    'name' => 'discapacidad',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Admin.Global')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Admin.Global')
                        )
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Porcentaje discapacidad', array(), 'Admin.Empleado.Serviplus'),
                    'name' => 'porcentaje_discapacidad',
                    'col' => '4',                    
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('IBAN', array(), 'Admin.Empleado.Serviplus'),
                    'name' => 'iban',
                    'placeholder' => 'ES6000491500051234567892',
                    'col' => '4',                    
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Estudios', array(), 'Admin.Empleado.Serviplus'),
                    'name' => 'id_estudios',
                    'col' => '4',                    
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Cod. Población', array(), 'Admin.Empleado.Serviplus'),
                    'name' => 'id_cod_poblacion',
                    'col' => '4',                    
                    'required' => true,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->trans('Coordinador', array(), 'Admin.Empleado.Serviplus'),
                    'name' => 'id_coordinador',
                    'options' => array(
                        'query' => $this->coordinador_list,
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'label' => $this->trans('Coordinador', array(), 'Admin.Empleado.Serviplus'),
                            'value' => 0,
                        ),
                    ),
                ),
            )
        );
        
        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->trans('Shop association', array(), 'Admin.Global'),
                'name' => 'checkBoxShopAsso',
            );
        }

        $this->fields_form['submit'] = array(
            'title' => $this->trans('Save', array(), 'Admin.Actions')
        );

        return parent::renderForm();
    }

    public function getNacionalidades()
    { 
        $nacionalidades = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT `codigo`, `nacionalidad`
            FROM '._DB_PREFIX_.'serv_nacionalidades           
            ORDER BY nacionalidad'
        );

        return $nacionalidades;
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();

        return $breadcrumb;
    }

}
