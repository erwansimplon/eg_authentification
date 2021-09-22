<?php
	/**
	 * 2007-2020 PrestaShop
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
	 *  @author    PrestaShop SA <contact@prestashop.com>
	 *  @copyright 2007-2020 PrestaShop SA
	 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
	 *  International Registered Trademark & Property of PrestaShop SA
	 */
	
	if (!defined('_PS_VERSION_')) {
		exit;
	}
	
	class Eg_authentification extends Module
	{
		public function __construct()
		{
			$this->name = 'eg_authentification';
			$this->tab = 'administration';
			$this->version = '1.0.0';
			$this->author = 'Erwan';
			$this->need_instance = 0;
			$this->bootstrap = true;
			
			parent::__construct();
			
			$this->displayName = $this->l('Admin authentification');
			$this->description = $this->l('Module pour l\'authentification par code employé');
			
			$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
			$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
		}
		
		public function install(): bool
		{
			return parent::install() && $this->createAlterTable();
		}

		public function createAlterTable(): bool
		{
			return (bool)Db::getInstance()->execute('
				ALTER TABLE `'._DB_PREFIX_.'employee` ADD `code_employee` VARCHAR(6) NOT NULL;
			');
		}
		
		public function uninstall(): bool
		{
			return parent::uninstall() && $this->deleteAlterTable();
		}

		public function deleteAlterTable(): bool
		{
			return (bool)Db::getInstance()->execute('
				ALTER TABLE `'._DB_PREFIX_.'employee` DROP `code_employee`;
			');
		}
	}
