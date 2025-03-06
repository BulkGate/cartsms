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

		// enable access to module backoffice UI
		$this->load->model('user/user_group');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/oc_cartsms/module/cartsms');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/oc_cartsms/module/cartsms');

		// we need to enable phone number fields
		$this->load->model('setting/setting');
		$this->model_setting_setting->editValue('config', 'config_telephone_display', 1);
		$this->model_setting_setting->editValue('config', 'config_telephone_required', 1);

		// we register cron script to process async tasks
		$this->load->model('setting/cron');
		$this->model_setting_cron->addCron('cartsms_asynchronous', '', 'minute', 'extension/oc_cartsms/asynchronous/task', true);


		$this->load->model('setting/event');

		// register custom fields for marketing messages opt-in
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_custom_fields',
			'description' => '',
			'trigger'     => 'catalog/model/account/custom_field.getCustomFields/after',
			'action'      => 'extension/oc_cartsms/event/hook.hookCustomFields',
			'status'      => '1',
			'sort_order'  => '1'
		]);

		// register asynchronous script to process async tasks
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_asynchronous',
			'description' => '',
			'trigger'     => 'catalog/view/common/header/before',
			'action'      => 'extension/oc_cartsms/event/hook.hookAsynchronousAsset',
			'status'      => '1',
			'sort_order'  => '1'
		]);

		// register module into menu
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_menu',
			'description' => '',
			'trigger'     => 'admin/view/common/column_left/before',
			'action'      => 'extension/oc_cartsms/event/hook.hookMenu',
			'status'      => '1',
			'sort_order'  => '1'
		]);

		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_send_message_box',
			'description' => '',
			'trigger'     => 'admin/view/sale/order_info/before',
			'action'      => 'extension/oc_cartsms/event/hook.hookRenderSendMessageBox',
			'status'      => '1',
			'sort_order'  => '1'
		]);

		// user can programmatically send sms
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_send_sms',
			'description' => '',
			'trigger'     => 'system/cartsms.send_sms',
			'action'      => 'extension/oc_cartsms/event/hook.hookSendSms',
			'status'      => '1',
			'sort_order'  => '1'
		]);


		// RETURN:NEW
		// todo: z back office nelze vytvorit return, protoze to hlasi chybu product_id ... z nejakeho duvodu je to 0
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_add_return',
			'description' => '',
			'trigger'     => 'catalog/model/account/returns.addReturn/after',
			'action'      => 'extension/oc_cartsms/event/hook.hookAddReturn',
			'status'      => '1',
			'sort_order'  => '1'
		]);


		// RETURN:CHANGE-STATUS
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_change_return_status',
			'description' => '',
			'trigger'     => 'admin/model/sale/returns.addHistory/before',
			'action'      => 'extension/oc_cartsms/event/hook.hookChangeReturnStatusBefore',
			'status'      => '1',
			'sort_order'  => '1'
		]);
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_change_return_status',
			'description' => '',
			'trigger'     => 'admin/model/sale/returns.addHistory/after',
			'action'      => 'extension/oc_cartsms/event/hook.hookChangeReturnStatusAfter',
			'status'      => '1',
			'sort_order'  => '1'
		]);


		// ORDER:NEW
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_add_order',
			'description' => '',
			'trigger'     => 'catalog/controller/checkout/success/before',
			'action'      => 'extension/oc_cartsms/event/hook.hookAddOrderCheckoutSuccess',
			'status'      => '1',
			'sort_order'  => '1'
		]);
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_add_order',
			'description' => '',
			'trigger'     => 'catalog/controller/api/order/after',
			'action'      => 'extension/oc_cartsms/event/hook.hookAddOrderApi',
			'status'      => '1',
			'sort_order'  => '1'
		]);


		// PRODUCT:OUT-OF-STOCK
		// catalog
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_add_order_history',
			'description' => '',
			'trigger'     => 'catalog/model/checkout/order.addHistory/before',
			'action'      => 'extension/oc_cartsms/event/hook.hookProductOutOfStockBefore',
			'status'      => '1',
			'sort_order'  => '1'
		]);
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_add_order_history',
			'description' => '',
			'trigger'     => 'catalog/model/checkout/order.addHistory/after',
			'action'      => 'extension/oc_cartsms/event/hook.hookProductOutOfStockAfter',
			'status'      => '1',
			'sort_order'  => '1'
		]);
		// admin
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_edit_product',
			'description' => '',
			'trigger'     => 'admin/model/catalog/product.editProduct/before',
			'action'      => 'extension/oc_cartsms/event/hook.hookProductOutOfStockBefore',
			'status'      => '1',
			'sort_order'  => '1'
		]);
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_edit_product',
			'description' => '',
			'trigger'     => 'admin/model/catalog/product.editProduct/after',
			'action'      => 'extension/oc_cartsms/event/hook.hookProductOutOfStockAfter',
			'status'      => '1',
			'sort_order'  => '1'
		]);


		// ORDER:CHANGE-STATUS
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_change_order_status',
			'description' => '',
			'trigger'     => 'catalog/controller/api/order/before',
			'action'      => 'extension/oc_cartsms/event/hook.hookChangeOrderStatusBefore',
			'status'      => '1',
			'sort_order'  => '1'
		]);
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_change_order_status',
			'description' => '',
			'trigger'     => 'catalog/controller/api/order/after',
			'action'      => 'extension/oc_cartsms/event/hook.hookChangeOrderStatusAfter',
			'status'      => '1',
			'sort_order'  => '1'
		]);


		// CUSTOMER:NEW
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_add_customer',
			'description' => '',
			'trigger'     => 'catalog/model/account/customer.addCustomer/after',
			'action'      => 'extension/oc_cartsms/event/hook.hookAddCustomer',
			'status'      => '1',
			'sort_order'  => '1'
		]);
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_add_customer',
			'description' => '',
			'trigger'     => 'admin/model/customer/customer.addCustomer/after',
			'action'      => 'extension/oc_cartsms/event/hook.hookAddCustomer',
			'status'      => '1',
			'sort_order'  => '1'
		]);


		// CONTACT:FORM
		$this->model_setting_event->addEvent([
			'code'        => 'cartsms_contact_form',
			'description' => '',
			'trigger'     => 'catalog/controller/information/contact.send/after',
			'action'      => 'extension/oc_cartsms/event/hook.hookContactForm',
			'status'      => '1',
			'sort_order'  => '1'
		]);
	}

	public function uninstall()
	{
		$this->di_container->getByClass(Plugin\Settings\Settings::class)->uninstall();

		$this->load->model('user/user_group');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/oc_cartsms/module/cartsms');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/oc_cartsms/module/cartsms');

		$this->load->model('setting/cron');
		$this->model_setting_cron->deleteCronByCode('cartsms_asynchronous');

		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('cartsms_custom_fields');
		$this->model_setting_event->deleteEventByCode('cartsms_asynchronous');
		$this->model_setting_event->deleteEventByCode('cartsms_menu');
		$this->model_setting_event->deleteEventByCode('cartsms_send_message_box');
		$this->model_setting_event->deleteEventByCode('cartsms_send_sms');
		$this->model_setting_event->deleteEventByCode('cartsms_add_order');
		$this->model_setting_event->deleteEventByCode('cartsms_add_return');
		$this->model_setting_event->deleteEventByCode('cartsms_change_return_status');
		$this->model_setting_event->deleteEventByCode('cartsms_add_order_history');
		$this->model_setting_event->deleteEventByCode('cartsms_change_order_status');
		$this->model_setting_event->deleteEventByCode('cartsms_add_customer');
		$this->model_setting_event->deleteEventByCode('cartsms_edit_product');
		$this->model_setting_event->deleteEventByCode('cartsms_contact_form');
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
		$requirements = $this->di_container->getByClass(Plugin\Debug\Requirements::class);
		$logger = $this->di_container->getByClass(Plugin\Debug\Logger::class);
		$url = $this->di_container->getByClass(Plugin\IO\Url::class);

		$requirements = $requirements->run([
			$requirements->same('{"message":"BulkGate API"}', file_get_contents($url->get('api/welcome')), 'Api Connection'),
			$requirements->same(true, version_compare($logger->getPlatformVersion(), '4.0.0', '>='), 'Opencart ver. >= 4.0.0'),
		]);

		$this->response->setOutput($this->load->view('extension/oc_cartsms/module/debug', [
			'header' => $this->load->controller('common/header'),
			'column_left' => $this->load->controller('common/column_left'),
			'footer' => $this->load->controller('common/footer'),
			'requirements' => $requirements,
			'errors' => array_reverse($logger->getList()),
			'platform_version' => $logger->getPlatformVersion(),
			'module_version' => $logger->getModuleVersion(),
			'php_version' => phpversion(),
			'url' => $url->get(),
		]));
	}
}

