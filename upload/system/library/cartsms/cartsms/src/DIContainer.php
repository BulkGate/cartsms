<?php
namespace BulkGate\CartSms;

use BulkGate, BulkGate\Extensions;
use Cart\Cart;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @method Database getDatabase()
 * @method CartSms getModule()
 * @method Customers getCustomers()
 */
class DIContainer extends Extensions\DIContainer
{
    /** @var \Registry */
    private $registry;

    public function __construct(\Registry $registry)
    {
        $this->registry = $registry;
    }


    /**
     * @return Database
     */
    protected function createDatabase()
    {
        return new Database($this->registry->get('db'));
    }


    /**
     * @return CartSMS
     */
    protected function createModule()
    {
        return new CartSMS($this->getService('settings'), $this->getService('database'));
    }


    /**
     * @return Customers
     */
    protected function createCustomers()
    {
        return new Customers($this->getService('database'));
    }
}
