<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsModuleNotifications extends CartSms\Controller
{
    public function actionAdmin()
    {
        $this->oc_proxy->add('save', $this->link('cartsms/module_notifications/ajaxSaveAdmin'));
        $this->view('Admin SMS', 'ModuleNotifications', 'admin', true);
    }

    public function ajaxSaveAdmin()
    {
        $this->runAjax(function (CartSms\Controller $controller, array $post)
        {
            $post['template'] = htmlspecialchars_decode($post['template']);
            Extensions\JsonResponse::send(
                $controller->oc_di->getProxy()->saveAdminNotifications($post)
            );
        });
    }

    public function actionCustomer()
    {
        $this->oc_proxy->add('save', $this->link('cartsms/module_notifications/ajaxSaveCustomer'));
        $this->view('Customer SMS', 'ModuleNotifications', 'customer', true);
    }

    public function ajaxSaveCustomer()
    {
        $this->runAjax(function (CartSms\Controller $controller, array $post)
        {
            $post['template'] = htmlspecialchars_decode($post['template']);
            Extensions\JsonResponse::send(
                $controller->oc_di->getProxy()->saveCustomerNotifications($post)
            );
        });
    }
}