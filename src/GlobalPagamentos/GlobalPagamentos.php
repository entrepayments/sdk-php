<?php
namespace GlobalPagamentos;

use GlobalPagamentos\Request\Environment;

class GlobalPagamentos
{
    private Merchant $merchant;
    private Environment $environment;

    public function __construct(Merchant $merchant, Environment $environment)
    {
        $this->merchant    = $merchant;
        $this->environment = $environment;
    }

    public function authorize(Payment $payment): Payment
    {

        return $payment;
    }
}
