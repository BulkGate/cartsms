<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions, BulkGate\CartSms\Helpers;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsEvents extends CartSms\Controller
{
    /**
     * catalog/model/checkout/order/addOrderHistory/after
     * @param string $hook
     * @param array $input
     * @param null $output
     */
    public function changeOrderStatusHook($hook, $input, $output)
    {
        list($order_id, $order_status_id, $comment, $notify) = array_pad($input, 4, null);

        if((isset($_POST['notifySms']) && (int) $_POST['notifySms'] === 1) || !isset($_POST['notifySms']))
        {
            $this->runHook('order_status_change_'.$order_status_id, new Extensions\Hook\Variables(array(
                'order_status_id' => (int) $order_status_id,
                'order_id' => (int) $order_id,
                'order_status_message' => $comment
            )));
        }
    }

    /**
     * catalog/model/account/customer/addCustomer/after
     * @param string $hook
     * @param array $input
     * @param int $customer_id
     */
    public function customerAddHook($hook, $input, $customer_id)
    {
        $this->runHook('customer_account_new', new Extensions\Hook\Variables(array(
            'customer_id' => (int) $customer_id,
        )));
    }

    /**
     * bulkgate/cartsms/new/order/hook
     * @param string $hook
     * @param array $input
     */
    public function orderAddHook($hook, $input)
    {
        list($order_id) = array_pad($input, 1, null);

        $this->runHook('order_new', new Extensions\Hook\Variables([
            'order_id' => (int) $order_id
        ]));

        foreach(Helpers::productsOutOfStock($this->oc_di->getDatabase()) as $product_id)
        {
            if(Extensions\Helpers::outOfStockCheck($this->oc_settings, $product_id))
            {
                $this->runHook('product_out_of_stock', new Extensions\Hook\Variables([
                    'product_id' => (int) $product_id
                ]));
            }
        }
    }

    /**
     * bulkgate/cartsms/contact/form/hook
     * @param $hook
     * @param $input
     */
    public function contactFormHook($hook, $input)
    {
        list($email, $name, $text) = array_pad($input, 3, null);

        if($text !== null)
        {
            $this->runHook('contact_form', new Extensions\Hook\Variables(array(
                'customer_email' => $email,
                'customer_name' => $name,
                'customer_message' => $text,
                'customer_message_short_50' => Helpers::subStr($text, 0, 50),
                'customer_message_short_80' => Helpers::subStr($text, 0, 80),
                'customer_message_short_100' => Helpers::subStr($text, 0, 100),
                'customer_message_short_120' => Helpers::subStr($text, 0, 120),
            )));
        }
    }

    /**
     * catalog/model/account/return/addReturn/after
     * @param string $hook
     * @param array $input
     * @param int $return_id
     */
    public function returnGoods($hook, $input, $return_id)
    {
        $this->runHook('product_return', new Extensions\Hook\Variables(array(
            'return_id' => (int) $return_id,
        )));
    }
}
