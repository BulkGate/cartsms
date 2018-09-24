<?php

require_once DIR_SYSTEM.'/library/cartsms/Controller.php';
require_once DIR_APPLICATION . "controller/marketplace/modification.php";

/** 
 * @property \Document $document
 * @property \Response $response
 * @property Cart\User $user
 * @property ModelUserUserGroup $model_user_user_group
 * @property \ModelSettingEvent $model_setting_event
 */
class ControllerExtensionModuleCartsms extends CartSms\Controller
{
    public function index()
    {
        $this->response->redirect($this->url->link('cartsms/module_settings/actionDefault', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function install()
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
        $this->model_setting_event->addEvent('cartsms', 'catalog/model/checkout/order/addOrderHistory/after', 'cartsms/events/changeOrderStatusHook');
        $this->model_setting_event->addEvent('cartsms', 'catalog/model/account/customer/addCustomer/after', 'cartsms/events/customerAddHook');
        $this->model_setting_event->addEvent('cartsms', 'catalog/model/account/return/addReturn/after', 'cartsms/events/returnGoods');
        $this->model_setting_event->addEvent('cartsms', 'bulkgate/cartsms/new/order/hook', 'cartsms/events/orderAddHook');
        $this->model_setting_event->addEvent('cartsms', 'bulkgate/cartsms/contact/form/hook', 'cartsms/events/contactFormHook');

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
    }
}

