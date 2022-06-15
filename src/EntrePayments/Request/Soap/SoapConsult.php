<?php

namespace EntrePayments\Request\Soap;

use DOMException;
use EntrePayments\Payment;

class SoapConsult extends SoapRequest
{
    /**
     * Create a consultation request.
     *
     * @throws DOMException
     */
    public function consult(
        Payment $payment,
        string $transactionType = '1'
    ): Payment {
        $order = $payment->getOrder();

        return $this->soap->parseResponseConsult(
            $this->soap->setConsult()->sendMessage(
                'consultaOperaciones',
                preg_replace(
                    "/(\n|\s*)</",
                    '<',
                    sprintf(
                        '<Messages>
                    <Version Ds_Version="0.0">
                        <Message>
                            <Monitor>
                                <Ds_MerchantCode>%s</Ds_MerchantCode>
                                <Ds_Terminal>%03d</Ds_Terminal>
                                <Ds_Order>%s</Ds_Order>
                            </Monitor>
                        </Message>
                    </Version>
                    <Signature>%s</Signature>
                </Messages>',
                        $this->soap->getMerchant()->getMerchantCode(),
                        $this->soap->getMerchant()->getTerminal(),
                        $order->getNumber(),
                        hash(
                            'sha256',
                            preg_replace(
                                "/(\n|\s*)</",
                                '<',
                                sprintf(
                                    '<Version Ds_Version="0.0">
                                            <Message>
                                                <Monitor>
                                                    <Ds_MerchantCode>%s</Ds_MerchantCode>
                                                    <Ds_Terminal>%03d</Ds_Terminal>
                                                    <Ds_Order>%s</Ds_Order>
                                                </Monitor>
                                            </Message>
                                            </Version>%s',
                                    $this->soap->getMerchant()->getMerchantCode(),
                                    $this->soap->getMerchant()->getTerminal(),
                                    $order->getNumber(),
                                    $this->soap->getMerchant()->getMerchantKey()
                                )
                            )
                        )
                    )
                )
            ),
            $payment
        );
    }
}
