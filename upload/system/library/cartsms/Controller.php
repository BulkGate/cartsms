<?php
namespace CartSms;

use BulkGate\Extensions, BulkGate;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @property \Registry $registry
 * @property \Session $session
 * @property \Config $config
 * @property \Request $request
 * @property \Response $response
 * @property \Document $document
 * @property \Url $url
 * @property \Loader $load
 */
abstract class Controller extends \Controller
{
    /** @var BulkGate\CartSms\DIContainer */
    protected $oc_di;

    /** @var BulkGate\CartSms\CartSMS */
    protected $oc_module;

    /** @var Extensions\Settings */
    protected $oc_settings;

    /** @var BulkGate\CartSms\ProxyGenerator */
    protected $oc_proxy;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $init = new Init($this->registry);
        $this->oc_di = $init->di();
        $this->oc_module = $this->oc_di->getModule();
        $this->oc_settings = $this->oc_di->getSettings();
        $this->oc_proxy = new BulkGate\CartSms\ProxyGenerator();

        $this->load->model('setting/event');
    }

    protected function view($title, $presenter, $action, $box = false)
    {
        $this->synchronize();
        $this->document->addStyle($this->oc_module->getUrl('/dist/css/devices.min.css'));
        $this->document->addStyle($this->oc_module->getUrl('/'.(defined('BULKGATE_DEV_MODE') ? 'dev' : 'dist').'/css/bulkgate-cartsms.css'));
        $this->document->addStyle('https://fonts.googleapis.com/icon?family=Material+Icons|Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i');

        $this->response->setOutput($this->load->view('cartsms/base', array(
            'application_id' => $this->oc_settings->load('static:application_id', ''),
            'language' => $this->oc_settings->load('main:language', 'en'),
            'presenter' => $presenter,
            'action' => $action,
            'title' => $title,
            'mode' => defined('BULKGATE_DEV_MODE') ? 'dev' : 'dist',
            'box' => $box,
            'widget_api_url' => $this->oc_module->getUrl('/'.(defined('BULKGATE_DEV_MODE') ? 'dev' : 'dist').'/widget-api/widget-api.js'),
            'logo' => $this->oc_module->getUrl('/images/products/oc.svg'),
            'proxy' => $this->oc_proxy->get(),
            'authenticate' => $this->link('cartsms/sign/authenticate'),
            'homepage' => $this->link('cartsms/dashboard'),
            'info' => $this->oc_module->info(),
            'header' => $this->load->controller('common/header'),
            'column_left' => $this->load->controller('common/column_left'),
            'footer' => $this->load->controller('common/footer'),
        )));
    }

    protected function link($route, array $params = array())
    {
        return BulkGate\CartSms\Helpers::fixUrl(
            $this->url->link($route, array_merge(array('user_token' => $this->session->data['user_token']), $params), true)
        );
    }

    protected function runAjax($callback, $fail_redirect = 'common/dashboard')
    {
        if(isset($this->request->post['__bulkgate']))
        {
            $post = $this->request->post['__bulkgate'];

            if(is_array($post))
            {
                call_user_func_array($callback, array($this, $post));
            }
            else
            {
                $this->response->redirect($this->url->link($fail_redirect, 'user_token=' . $this->session->data['user_token'], true));
            }
        }
        else
        {
            $this->response->redirect($this->url->link($fail_redirect, 'user_token=' . $this->session->data['user_token'], true));
        }
    }

    protected function synchronize($now = false)
    {
        $status = $this->oc_module->statusLoad(); $language = $this->oc_module->languageLoad(); $store = $this->oc_module->storeLoad(); $return = $this->oc_module->returnStatusLoad();

        $now = $now || $status || $language || $store || $return;

        try
        {
            $this->oc_di->getSynchronize()->run($this->oc_module->getUrl('/module/settings/synchronize'), $now);

            return true;
        }
        catch (Extensions\IO\InvalidResultException $e)
        {
            return false;
        }
    }

    protected function runHook($name, Extensions\Hook\Variables $variables)
    {
        if(!$variables->get('language_id'))
        {
            $language_iso = isset($this->session->data['language']) ? $this->session->data['language'] : null;
            $variables->set('language_id', (int) BulkGate\CartSms\Helpers::getLanguageId($language_iso, $this->oc_di->getDatabase()));
        }

        $hook = new Extensions\Hook\Hook(
            $this->oc_di->getModule()->getUrl('/module/hook'),
            $variables->get('language_id', 0),
            $variables->get('store_id', (int) ($this->config->get('config_store_id') ?: 0)),
            $this->oc_di->getConnection(),
            $this->oc_settings,
            new BulkGate\CartSms\HookLoad($this->oc_di->getDatabase())
        );

        try
        {
            $hook->run((string) $name, $variables);
            return true;
        }
        catch (Extensions\IO\InvalidResultException $e)
        {
            return false;
        }
    }

}
