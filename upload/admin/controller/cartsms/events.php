<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsEvents extends CartSms\Controller
{
    /**
     * admin/model/sale/return/addReturnHistory/after
     * @param string $hook
     * @param array $input
     * @param null $output
     */
    public function returnGoodsStatus($hook, $input, $output)
    {
        list($return_id, $return_status_id, $comment, $notify) = array_pad($input, 4, null);

        if($return_id)
        {
            $this->runHook('return_status_change_'.$return_status_id, new Extensions\Hook\Variables([
                'return_id' => (int) $return_id,
                'return_status_id' => (int) $return_status_id,
                'return_customer_message' => $comment
            ]));
        }
    }


    /**
     * admin/model/sale/return/addReturn/after
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


    /**
     * admin/model/catalog/product/deleteProduct/before
     * @param string $hook
     * @param array $input
     */
    public function productDeleteHook($hook, $input)
    {
        list($product_id) = array_pad($input, 1, null);

        if($product_id)
        {
            $this->runHook('product_delete', new Extensions\Hook\Variables([
                'product_id' => (int) $product_id
            ]));
        }
    }

    /**
     * admin/model/customer/customer/addCustomer/after
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
}
