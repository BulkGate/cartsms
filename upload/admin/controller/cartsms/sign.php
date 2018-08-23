<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsSign extends CartSms\Controller
{
    public function actionIn()
    {
        $this->oc_proxy->add('login', $this->link('cartsms/sign/ajaxIn'));
        $this->view('Sign in', 'ModuleSign', 'in', false);
    }

    public function ajaxIn()
    {
        $this->runAjax(function (CartSms\Controller $controller, array $post)
        {
            $response =  $controller->oc_di->getProxy()->login(array_merge(array('name' => $controller->config->get('config_meta_title')), $controller->request->post['__bulkgate']));

            if($response instanceof Extensions\IO\Response)
            {
                Extensions\JsonResponse::send($response);
            }
            Extensions\JsonResponse::send(array(
                'token' => $response,
                'redirect' => $controller->link('cartsms/dashboard/actionDefault')
            ));
        });
    }

    public function actionUp()
    {
        $this->view('Sign up', 'Sign', 'up', false);
    }

    public function authenticate()
    {
        try
        {
            Extensions\JsonResponse::send($this->oc_di->getProxy()->authenticate());
        }
        catch (Extensions\IO\AuthenticateException $e)
        {
            Extensions\JsonResponse::send(array('redirect' => $this->link('cartsms/sign/actionIn')));
        }
    }
}