<?php
namespace CartSms;

use BulkGate;

define('_BG_CARTSMS_DIR_', __DIR__);

if(!file_exists(_BG_CARTSMS_DIR_.'/extensions/src/_extension.php'))
{
    echo 'CartSMS: BulkGate extensions (https://github.com/BulkGate/extensions) must be installed.';
    exit;
}

define('_BG_CARTSMS_DEMO_', false);

require_once _BG_CARTSMS_DIR_.'/extensions/src/_extension.php';
require_once __DIR__.'/cartsms/src/_extension.php';

file_exists(_BG_CARTSMS_DIR_.'/extensions/src/debug.php') && require_once _BG_CARTSMS_DIR_.'/extensions/src/debug.php';

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Init extends BulkGate\Extensions\Strict
{
    const NAME = 'CartSMS';

    const AUTHOR = 'TOPefekt s.r.o.';

    const VERSION = '6.0.1';

    const MODULE_CODE = 'cartsms';

    const URL = 'http://www.cart-sms.com/';

    const DEMO = false;

    /** @var \Registry */
    private $registry;

    /** @var BulkGate\CartSms\DIContainer */
    private $di = null;


    public function __construct(\Registry $registry)
    {
        $this->registry = $registry;
    }


    /**
     * @return BulkGate\CartSms\DIContainer
     */
    public function di()
    {
        if($this->di instanceof BulkGate\CartSms\DIContainer)
        {
            return $this->di;
        }
        return new BulkGate\CartSms\DIContainer($this->registry);
    }


    /**
     * @return array
     */
    public function menu()
    {
        $translator = $this->di()->getTranslator();
        $settings = $this->di()->getSettings();

        /** @var \Url $url */
        $url = $this->registry->get('url');

        /** @var \Session $session */
        $session = $this->registry->get('session');

        if($settings->load("static:application_token", false))
        {
            return array(
                'id'       => 'menu-cart-sms',
                'icon'	   => 'fa-envelope-o',
                'name'	   => 'CartSMS',
                'href'     => '',
                'children' => array(
                    array(
                        'name'	   => $translator->translate('dashboard', 'Dashboard'),
                        'href'     => $url->link('cartsms/dashboard/actionDefault', 'user_token=' . $session->data['user_token'], true),
                        'children' => array()
                    ),
                    array(
                        'name'	   => $translator->translate('sms', 'SMS'),
                        'href'     => '',
                        'children' => array(
                            array(
                                'name'	   => $translator->translate('start_campaign','Start Campaign'),
                                'href'     => $url->link('cartsms/sms_campaign/actionNew', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                            array(
                                'name'	   => $translator->translate('campaigns','Campaigns'),
                                'href'     => $url->link('cartsms/sms_campaign/actionDefault', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                            array(
                                'name'	   => $translator->translate('inbox','Inbox'),
                                'href'     => $url->link('cartsms/inbox/actionList', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                            array(
                                'name'	   => $translator->translate('history','History'),
                                'href'     => $url->link('cartsms/history/actionList', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                            array(
                                'name'	   => $translator->translate('statistics','Statistics'),
                                'href'     => $url->link('cartsms/statistics/actionDefault', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                            array(
                                'name'	   => $translator->translate('black_list','Black list'),
                                'href'     => $url->link('cartsms/black_list/actionDefault', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                            array(
                                'name'	   => $translator->translate('price_list', 'Price list'),
                                'href'     => $url->link('cartsms/sms_price/actionList', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                        )
                    ),
                    array(
                        'name'	   => $translator->translate('payments', 'Payments'),
                        'href'     => '',
                        'children' => array(
                            array(
                                'name'	   => $translator->translate('buy_credit', 'Buy Credit'),
                                'href'     => $url->link('cartsms/top/actionUp', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                            array(
                                'name'	   => $translator->translate('invoices', 'Invoices'),
                                'href'     => $url->link('cartsms/payment/actionList', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                            array(
                                'name'	   => $translator->translate('payments_data', 'Payment Data'),
                                'href'     => $url->link('cartsms/wallet/actionDetail', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                        )
                    ),
                    array(
                        'name'	   => $translator->translate('settings', 'Settings'),
                        'href'     => '',
                        'children' => array(
                            array(
                                'name'	   => $translator->translate('user_profile', 'User profile'),
                                'href'     => $url->link('cartsms/user/actionProfile', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                            array(
                                'name'	   => $translator->translate('admin_sms', 'Admin SMS'),
                                'href'     => $url->link('cartsms/module_notifications/actionAdmin', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                            array(
                                'name'	   => $translator->translate('customer_sms', 'Customer SMS'),
                                'href'     => $url->link('cartsms/module_notifications/actionCustomer', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                            array(
                                'name'	   => $translator->translate('sender_id_settings', 'Sender ID Settings'),
                                'href'     => $url->link('cartsms/sms_settings/actionDefault', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                            array(
                                'name'	   => $translator->translate('module_settings', 'Module settings'),
                                'href'     => $url->link('cartsms/module_settings/actionDefault', 'user_token=' . $session->data['user_token'], true),
                                'children' => array()
                            ),
                        )
                    ),
                    array(
                        'name'	   => $translator->translate('about_module', 'About module'),
                        'href'     => $url->link('cartsms/module_about/actionDefault', 'user_token=' . $session->data['user_token'], true),
                        'children' => array()
                    ),
                )
            );
        }
        else
        {
            return array(
                'id'       => 'menu-cart-sms',
                'icon'	   => 'fa-envelope-o',
                'name'	   => 'CartSMS',
                'href'     => '',
                'children' => array(
                    array(
                        'name'	   => $translator->translate('sign_in', 'Sign in'),
                        'href'     => $url->link('cartsms/sign/actionIn', 'user_token=' . $session->data['user_token'], true),
                        'children' => array()
                    ),
                    array(
                        'name'	   => $translator->translate('sign_up', 'Sign up'),
                        'href'     => $url->link('cartsms/sign/actionUp', 'user_token=' . $session->data['user_token'], true),
                        'children' => array()
                    ),
                    array(
                        'name'	   => $translator->translate('about_module', 'About module'),
                        'href'     => $url->link('cartsms/about/actionDefault', 'user_token=' . $session->data['user_token'], true),
                        'children' => array()
                    ),
                )
            );
        }

    }
}
