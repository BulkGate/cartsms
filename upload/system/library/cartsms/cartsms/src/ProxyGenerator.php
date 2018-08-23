<?php
namespace BulkGate\CartSms;

use BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class ProxyGenerator extends Extensions\Strict
{
    /** @var array */
    private $proxy = array();

    public function add($action, $url, $reducer = '_generic')
    {
        if(isset($this->proxy[$reducer]))
        {
            $this->proxy[$reducer][$action] = array('url' => $url);
        }
        else
        {
            $this->proxy[$reducer] = array($action => array('url' => $url));
        }
    }

    public function get()
    {
        return $this->proxy;
    }
}
