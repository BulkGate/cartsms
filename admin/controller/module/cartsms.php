<?php

namespace Opencart\Admin\Controller\Extension\OcCartsms\Module;

use BulkGate\CartSms\Ajax;
use BulkGate\Plugin;

require_once DIR_EXTENSION . 'oc_cartsms/vendor/autoload.php';

class Cartsms extends \BulkGate\CartSms\Controller
{
	public function index()
    {
		$this->di_container->getByClass(Plugin\Eshop\EshopSynchronizer::class)->run();

		$jwt = $this->di_container->getByClass(Plugin\User\Sign::class)->authenticate(false, ['expire' => time() + 300]);

		$this->load->language('extension/oc_cartsms/module/cartsms');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->response->setOutput($this->load->view('extension/oc_cartsms/module/cartsms', [
			'header' => $this->load->controller('common/header'),
			'column_left' => $this->load->controller('common/column_left'),
			'footer' => $this->load->controller('common/footer'),
			'synchronizer' => $this->di_container->getByClass(Plugin\Settings\Synchronizer::class),
			'settings' => $this->di_container->getByClass(Plugin\Settings\Settings::class),
			'url' => $this->di_container->getByClass(Plugin\IO\Url::class),
			'path' => new class($this->url, $this->session->data['user_token']) {
				public function __construct(private $url, private $token)
				{
				}

				public function get(string $route, array $args = [])
				{
					return $this->url->link($route, [...['user_token' => $this->token], ...$args], true);
				}
			},
			'token' => $jwt,
		]));
    }

	public function install()
	{
		$this->di_container->getByClass(Plugin\Settings\Settings::class)->install();
		//throw new \Exception("install: test error");
	}

	public function uninstall()
	{
		$this->di_container->getByClass(Plugin\Settings\Settings::class)->uninstall();
		//throw new \Exception("unintall: test error");
	}

	public function proxy()
	{
		$this->response->addHeader('Content-Type: application/json');

		switch ($this->request->get('action')) {
			case "login":
				['email' => $email, 'password' => $password] = $this->request->post;

				return $this->response->setOutput(json_encode($this->di_container->getByClass(Plugin\User\Sign::class)->in($email, $password, '/dashboard')));
			case "logout":
				return $this->response->setOutput(json_encode($this->di_container->getByClass(Plugin\User\Sign::class)->out('/sign/in')));
			case "authenticate":
				return $this->response->setOutput(json_encode($this->di_container->getByClass(Ajax\Authenticate::class)->run('/sign/in')));
			case "module-settings":
				return $this->response->setOutput(json_encode($this->di_container->getByClass(Ajax\PluginSettings::class)->run($this->request->post, fn(string $lang) => $this->url->link('extension/oc_cartsms/module/cartsms', ['user_token' => $this->session->data['user_token'], 'reload' => $lang], true) . '#/dashboard')));
		}
	}

	public function debug()
	{
		bdump($this->di_container->getByClass(Plugin\Settings\Settings::class)->load('static:application_id'));
		bdump($this->di_container->getByClass(Plugin\Database\Connection::class)->getSqlList());
	}

    /*public function install()
    {
        $this->load->model('setting/event');
        $this->load->model('user/user_group');

        $this->model_setting_event->deleteEvent('cartsms');

        $this->oc_settings->install();

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/black_list');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/dashboard');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/history');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/inbox');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/module_about');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/module_notifications');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/module_settings');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/payment');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/sign');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/sms_campaign');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/sms_price');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/sms_settings');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/statistics');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/top');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/user');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'cartsms/wallet');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/cartsms');

        $this->model_setting_event->addEvent('cartsms', 'admin/model/sale/return/addReturnHistory/after', 'cartsms/events/returnGoodsStatus');
        $this->model_setting_event->addEvent('cartsms', 'admin/model/customer/customer/addCustomer/after', 'cartsms/events/customerAddHook');
        $this->model_setting_event->addEvent('cartsms', 'admin/model/catalog/product/deleteProduct/before', 'cartsms/events/productDeleteHook');
        $this->model_setting_event->addEvent('cartsms', 'admin/model/sale/return/addReturn/after', 'cartsms/events/returnGoods');
        $this->model_setting_event->addEvent('cartsms', 'catalog/model/checkout/order/addOrderHistory/after', 'cartsms/events/changeOrderStatusHook');
        $this->model_setting_event->addEvent('cartsms', 'catalog/model/account/customer/addCustomer/after', 'cartsms/events/customerAddHook');
        $this->model_setting_event->addEvent('cartsms', 'catalog/model/account/return/addReturn/after', 'cartsms/events/returnGoods');
        $this->model_setting_event->addEvent('cartsms', 'catalog/bulkgate/cartsms/new/order/hook', 'cartsms/events/orderAddHook');
        $this->model_setting_event->addEvent('cartsms', 'catalog/bulkgate/cartsms/contact/form/hook', 'cartsms/events/contactFormHook');

        $this->installOcMod();
    }

    public function uninstall()
    {
        $this->load->model('setting/event');
        $this->load->model('user/user_group');

        $this->oc_settings->uninstall();

        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/black_list');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/dashboard');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/history');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/inbox');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/module_about');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/module_notifications');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/module_settings');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/payment');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/sign');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/sms_campaign');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/sms_price');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/sms_settings');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/statistics');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/top');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/user');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'cartsms/wallet');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/module/cartsms');

        $this->model_setting_event->deleteEventByCode('cartsms');

        $this->uninstallOcMod();
    }

    private function installOcMod()
    {
        $this->uninstallOcMod();

        $db = $this->oc_di->getDatabase();

        $db->execute($db->prepare("
            INSERT INTO `{$db->table('modification')}` (`name`, `author`, `version`, `link`, `xml`, `status`, `date_added`, `code`) 
            VALUES (%s, %s, %s, %s, \"".$db->escape(file_get_contents(_BG_CARTSMS_DIR_ . DIRECTORY_SEPARATOR . CartSms\Init::MODULE_CODE . '.ocmod.xml'))."\", 1, NOW(), %s)
        ", array(
            CartSms\Init::NAME,
            CartSms\Init::AUTHOR,
            CartSms\Init::VERSION,
            CartSms\Init::URL,
            CartSms\Init::MODULE_CODE
        )));

        $refresh = new ControllerMarketplaceModification($this->registry);
        $refresh->refresh();
    }

    private function uninstallOcMod()
    {
        $db = $this->oc_di->getDatabase();
        $db->execute($db->prepare("DELETE FROM `{$db->table('modification')}` WHERE `code` = %s", array(CartSms\Init::MODULE_CODE)));
    }*/
}

