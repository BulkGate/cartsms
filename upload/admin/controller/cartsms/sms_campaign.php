<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsSmsCampaign extends CartSms\Controller
{
    public function actionDefault()
    {
        $this->view('Campaigns', 'SmsCampaign', 'default', true);
	}

	public function actionNew()
    {
        $this->view('Create new Campaign', 'SmsCampaign', 'new', true);
	}

	public function actionCampaign()
    {
        $this->oc_proxy->add('loadModuleData', $this->link('cartsms/sms_campaign/ajaxLoadModuleData'), 'campaign');
        $this->oc_proxy->add('saveModuleCustomers', $this->link('cartsms/sms_campaign/ajaxSaveModuleCustomers'), 'campaign');
        $this->oc_proxy->add('addModuleFilter', $this->link('cartsms/sms_campaign/ajaxAddModuleFilter'), 'campaign');
        $this->oc_proxy->add('removeModuleFilter', $this->link('cartsms/sms_campaign/ajaxRemoveModuleFilter'), 'campaign');
        $this->view('Campaign', 'SmsCampaign', 'campaign', true);
    }

    public function ajaxLoadModuleData()
    {
        $this->runAjax(function (CartSms\Controller $controller, array $post)
        {
            Extensions\JsonResponse::send($controller->oc_di->getProxy()->loadCustomersCount(
                isset($post['application_id']) ? $post['application_id'] : null,
                isset($post['campaign_id']) ? $post['campaign_id'] : null
            ));
        });
    }

    public function ajaxAddModuleFilter()
    {
        $this->runAjax(function (CartSms\Controller $controller, array $post)
        {
            Extensions\JsonResponse::send($controller->oc_di->getProxy()->loadCustomersCount(
                isset($post['application_id']) ? $post['application_id'] : null,
                isset($post['campaign_id']) ? $post['campaign_id'] : null,
                'addFilter',
                $post
            ));
        });
    }

    public function ajaxRemoveModuleFilter()
    {
        $this->runAjax(function (CartSms\Controller $controller, array $post)
        {
            Extensions\JsonResponse::send($controller->oc_di->getProxy()->loadCustomersCount(
                isset($post['application_id']) ? $post['application_id'] : null,
                isset($post['campaign_id']) ? $post['campaign_id'] : null,
                'removeFilter',
                $post
            ));
        });
    }

    public function ajaxSaveModuleCustomers()
    {
        $this->runAjax(function (CartSms\Controller $controller, array $post)
        {
            Extensions\JsonResponse::send($controller->oc_di->getProxy()->saveModuleCustomers(
                isset($post['application_id']) ? $post['application_id'] : null,
                isset($post['campaign_id']) ? $post['campaign_id'] : null
            ));
        });
    }

	public function actionActive()
    {
        $this->view('Campaign', 'SmsCampaign', 'active', false);
    }
}