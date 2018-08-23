<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsModuleSettings extends CartSms\Controller
{
    public function actionDefault()
    {
        if(_BG_CARTSMS_DEMO_)
        {
            $this->response->redirect($this->link('cartsms/dashboard/actionDefault'));
        }
        $this->oc_proxy->add('save', $this->link('cartsms/module_settings/ajaxSave'));
        $this->oc_proxy->add('logout', $this->link('cartsms/module_settings/ajaxLogout'));

        $this->view('About module', 'ModuleSettings', 'default', false);
    }

    public function ajaxSave()
    {
        $this->runAjax(function (CartSms\Controller $controller, array $post)
        {
            $controller->oc_di->getProxy()->saveSettings($post);
            Extensions\JsonResponse::send(array('redirect' => $controller->link('cartsms/module_settings/actionDefault')));

        }, 'cartsms/settings/actionDefault');
    }


    public function ajaxLogout()
    {
        $this->runAjax(function (CartSms\Controller $controller, array $post)
        {
            $controller->oc_di->getProxy()->logout();
            Extensions\JsonResponse::send(array('token' => 'guest', 'redirect' => $controller->link('cartsms/sign/actionIn')));

        }, 'cartsms/settings/actionDefault');
    }
}