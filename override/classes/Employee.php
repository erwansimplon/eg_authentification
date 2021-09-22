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

	use PrestaShop\PrestaShop\Adapter\CoreException;
	use PrestaShop\PrestaShop\Adapter\ServiceLocator;
	use PrestaShop\PrestaShop\Core\Crypto\Hashing;

	class Employee extends EmployeeCore
	{
		/** @var string employee code_gestcom */
		public $code_gestcom;

		public function __construct($id = null, $idLang = null, $idShop = null)
		{
			self::$definition['fields']['code_gestcom'] = array('type' => self::TYPE_STRING, 'size' => 6);
			parent::__construct($id, $idLang , $idShop);
		}

		/**
		 * Return employee instance from its code_employee (optionally check password).
		 *
		 * @param string $code_employee      code employee
		 * @param string $plaintextPassword Password is also checked if specified
		 * @param bool   $activeOnly        Filter employee by active status
		 *
		 * @return bool|Employee|EmployeeCore Employee instance
		 *                                    `false` if not found
		 * @throws PrestaShopDatabaseException
		 * @throws PrestaShopException
		 * @throws CoreException
		 */
		public function getByCodeEmployee($code_employee, $plaintextPassword = null, $activeOnly = true)
		{
			if ($plaintextPassword != null && !Validate::isPlaintextPassword($plaintextPassword)) {
				die(Tools::displayError());
			}

			$sql = new DbQuery();
			$sql->select('e.*');
			$sql->from('employee', 'e');
			$sql->where('e.`code_employee` = \''.pSQL($code_employee).'\'');
			if ($activeOnly) {
				$sql->where('e.`active` = 1');
			}

			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
			if (!$result) {
				return false;
			}

			/** @var Hashing $crypto */
			$crypto = ServiceLocator::get(Hashing::class);

			$passwordHash = $result['passwd'];
			$shouldCheckPassword = null !== $plaintextPassword;
			if ($shouldCheckPassword && !$crypto->checkHash($plaintextPassword, $passwordHash)) {
				return false;
			}

			$this->id = $result['id_employee'];
			$this->id_profile = $result['id_profile'];
			foreach ($result as $key => $value) {
				if (property_exists($this, $key)) {
					$this->{$key} = $value;
				}
			}

			if ($shouldCheckPassword && !$crypto->isFirstHash($plaintextPassword, $passwordHash)) {
				$this->passwd = $crypto->hash($plaintextPassword);

				$this->update();
			}

			return $this;
		}
	}
