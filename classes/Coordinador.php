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

class Coordinador extends ObjectModel
{	

	public $id_customer;
	public $id_shop;
    public $date_add;
    public $date_upd;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'serv_coordinador',
		'primary' => 'id_coordinador',
		'multishop' => true,		
		'fields' => array(
			// Lang fields
			'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
			'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),		
		),
	);

	public	function __construct($id_coordinador = null, $id_shop = null, Context $context = null)
	{
		Shop::addTableAssociation('serv_coordinador', array('type' => 'shop'));
		parent::__construct($id_coordinador, $id_shop);
	}


	 /**
     * Return customers list.
     *
     * @param null|bool $onlyActive Returns only active customers when `true`
     *
     * @return array Customers
     */
    public static function getCoordinador($onlyActive = null)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT co.`id_coordinador`, cu.`firstname`, cu.`lastname`
            FROM `' . _DB_PREFIX_ . 'serv_coordinador` co
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` cu ON (cu.`id_customer`= co.`id_customer`)
            WHERE cu.`active` = 1
            ORDER BY `id_coordinador` ASC'
        );
    }

}
