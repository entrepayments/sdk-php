<?php

namespace EntrePayments\Request\Soap;

use DOMException;
use EntrePayments\Payment;

class SoapCapture extends SoapRequest
{
    /**
     * Create a capture request.
     *
     * @throws DOMException
     */
    public function capture(
        Payment $payment
    ): Payment {
        $order = $payment->getOrder();

        return $this->soap->parseResponse(
            $this->soap->sendMessage(
                'trataPeticion',
                preg_replace(
                    "/(\n|\s*)</",
                    '<',
                    sprintf(
                        '<DATOSENTRADA>
                <DS_MERCHANT_AMOUNT>%s</DS_MERCHANT_AMOUNT>
                <DS_MERCHANT_ORDER>%s</DS_MERCHANT_ORDER>
                <DS_MERCHANT_MERCHANTCODE>%s</DS_MERCHANT_MERCHANTCODE>
                <DS_MERCHANT_TERMINAL>%s</DS_MERCHANT_TERMINAL>
                <DS_MERCHANT_CURRENCY>%d</DS_MERCHANT_CURRENCY>
                <DS_MERCHANT_TRANSACTIONTYPE>2</DS_MERCHANT_TRANSACTIONTYPE>
                <DS_MERCHANT_MERCHANTDESCRIPTOR>%s</DS_MERCHANT_MERCHANTDESCRIPTOR>
                <DS_MERCHANT_MERCHANTSIGNATURE>%s</DS_MERCHANT_MERCHANTSIGNATURE>
            </DATOSENTRADA>',
                        $order->getAmount(),
                        $order->getNumber(),
                        $this->soap->getMerchant()->getMerchantCode(),
                        $this->soap->getMerchant()->getTerminal(),
                        $order->getCurrency(),
                        $payment->getSoftDescriptor(),
                        hash(
                            'sha256',
                            implode(
                                '',
                                [
                                    $order->getAmount(),
                                    $order->getNumber(),
                                    $this->soap->getMerchant()->getMerchantCode(),
                                    $order->getCurrency(),
                                    '2',
                                    $this->soap->getMerchant()->getMerchantKey(),
                                ]
                            )
                        )
                    ),
                )
            ),
            $payment
        );
    }
}
