<?php

namespace Sparrow;

abstract class Payment
{
    /**
     * @var array
     */

    protected array $gateways, $data = [];

    /**
     * Payment constructor.
     * @param array $data
     */
    
    public function __construct(array $data)
    {
        // replace "[]" with an array of your gateways
        $this->gateways = [];
        $this->data = $data;
    }

    /**
     * @return array
     */

    public function sendFactor() : array
    {
        return $this->data;
    }

    public function gateways() : array
    {
        return $this->gateways;
    }

    public abstract function pay();

    public abstract function callback();

    public abstract function verify();
}
