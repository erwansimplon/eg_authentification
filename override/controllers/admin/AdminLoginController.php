<?php
	/**
	 * 2007-2020 PrestaShop and Contributors
	 *
	 * NOTICE OF LICENSE
	 *
	 * This source file is subject to the Open Software License (OSL 3.0)
	 * that is bundled with this package in the file LICENSE.txt.
	 * It is also available through the world-wide-web at this URL:
	 * https://opensource.org/licenses/OSL-3.0
	 * If you did not receive a copy of the license and are unable to
	 * obtain it through the world-wide-web, please send an email
	 * to license@prestashop.com so we can send you a copy immediately.
	 *
	 * DISCLAIMER
	 *
	 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
	 * versions in the future. If you wish to customize PrestaShop for your
	 * needs please refer to https://www.prestashop.com for more information.
	 *
	 * @author    PrestaShop SA <contact@prestashop.com>
	 * @copyright 2007-2020 PrestaShop SA and Contributors
	 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
	 * International Registered Trademark & Property of PrestaShop SA
	 */
	
	class AdminLoginController extends AdminLoginControllerCore
	{
		public function initContent()
		{
			if ($code_employee = Tools::getValue('code_employee')) {
				$this->context->smarty->assign('code_employee', $code_employee);
			}

			parent::initContent();
		}

		public function processLogin()
		{
			/* Check fields validity */
			$passwd = trim(Tools::getValue('passwd'));
			$code_employee = trim(Tools::getValue('code_employee'));
			if (empty($code_gestcom)) {
				$this->errors[] = $this->trans('Le code employee est vide.', array(), 'Admin.Notifications.Error');
			}
			
			if (empty($passwd)) {
				$this->errors[] = $this->trans('The password field is blank.', array(), 'Admin.Notifications.Error');
			} elseif (!Validate::isPasswd($passwd)) {
				$this->errors[] = $this->trans('Invalid password.', array(), 'Admin.Notifications.Error');
			}
			
			if (!count($this->errors)) {
				// Find employee
				$this->context->employee = new Employee();
				$is_employee_loaded = $this->context->employee->getByCodeEmployee($code_employee, $passwd);
				$employee_associated_shop = $this->context->employee->getAssociatedShops();
				if (!$is_employee_loaded) {
					$this->errors[] = $this->trans('The employee does not exist, or the password provided is incorrect.', array(), 'Admin.Login.Notification');
					$this->context->employee->logout();
				} elseif (empty($employee_associated_shop) && !$this->context->employee->isSuperAdmin()) {
					$this->errors[] = $this->trans('This employee does not manage the shop anymore (either the shop has been deleted or permissions have been revoked).', array(), 'Admin.Login.Notification');
					$this->context->employee->logout();
				} else {
					PrestaShopLogger::addLog($this->trans('Back office connection from %ip%', array('%ip%' => Tools::getRemoteAddr()), 'Admin.Advparameters.Feature'), 1, null, '', 0, true, (int) $this->context->employee->id);
					
					$this->context->employee->remote_addr = (int) ip2long(Tools::getRemoteAddr());
					// Update cookie
					$cookie = Context::getContext()->cookie;
					$cookie->id_employee = $this->context->employee->id;
					$cookie->email = $this->context->employee->email;
					$cookie->profile = $this->context->employee->id_profile;
					$cookie->passwd = $this->context->employee->passwd;
					$cookie->remote_addr = $this->context->employee->remote_addr;
					$cookie->registerSession(new EmployeeSession());
					
					if (!Tools::getValue('stay_logged_in')) {
						$cookie->last_activity = time();
					}
					
					$cookie->write();
					
					// If there is a valid controller name submitted, redirect to it
					if (isset($_POST['redirect']) && Validate::isControllerName($_POST['redirect'])) {
						$url = $this->context->link->getAdminLink($_POST['redirect']);
					} else {
						$tab = new Tab((int) $this->context->employee->default_tab);
						$url = $this->context->link->getAdminLink($tab->class_name);
					}
					
					if (Tools::isSubmit('ajax')) {
						die(json_encode(array('hasErrors' => false, 'redirect' => $url)));
					} else {
						$this->redirect_after = $url;
					}
				}
			}
			if (Tools::isSubmit('ajax')) {
				die(json_encode(array('hasErrors' => true, 'errors' => $this->errors)));
			}
		}
	}
