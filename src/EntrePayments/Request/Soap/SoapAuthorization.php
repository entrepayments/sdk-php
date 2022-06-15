<?php

namespace EntrePayments\Request\Soap;

use DOMException;
use EntrePayments\Card;
use EntrePayments\Payment;

class SoapAuthorization extends SoapRequest
{
    /**
     * Create an authorization request.
     *
     * @throws DOMException
     */
    public function authorize(
        Payment $payment,
        bool $capture = true,
        bool $recurring = false,
        bool $tokenize = false
    ): Payment {
        $order = $payment->getOrder();
        $card = $payment->getCard();
        $isV2 = $card->isThreeDSecure() && $card->getThreeDSecureVersion() == Card::THREE_D_SECURE_VERSION_2;
        $identifier = $tokenize ? '<DS_MERCHANT_IDENTIFIER>REQUIRED</DS_MERCHANT_IDENTIFIER>' : '';

        if ($isV2) {
            $datoEntrada = preg_replace(
                "/(\n|\s*)</",
                '<',
                sprintf(
                    '<DATOSENTRADA>
                <DS_MERCHANT_AMOUNT>%s</DS_MERCHANT_AMOUNT>
                <DS_MERCHANT_ORDER>%s</DS_MERCHANT_ORDER>
                <DS_MERCHANT_MERCHANTCODE>%s</DS_MERCHANT_MERCHANTCODE>
                <DS_MERCHANT_CURRENCY>%d</DS_MERCHANT_CURRENCY>
                <DS_MERCHANT_PAN>%s</DS_MERCHANT_PAN>
                <DS_MERCHANT_EXPIRYDATE>%s</DS_MERCHANT_EXPIRYDATE>
                <DS_MERCHANT_CVV2>%s</DS_MERCHANT_CVV2>
                <DS_MERCHANT_TRANSACTIONTYPE>0</DS_MERCHANT_TRANSACTIONTYPE>
                <DS_MERCHANT_TERMINAL>%s</DS_MERCHANT_TERMINAL>
                <DS_MERCHANT_ACCOUNTTYPE>02</DS_MERCHANT_ACCOUNTTYPE>
                <DS_MERCHANT_PLANTYPE>1</DS_MERCHANT_PLANTYPE>
                <DS_MERCHANT_EMV3DS>{\'threeDSInfo\':\'CardData\'}</DS_MERCHANT_EMV3DS>
            </DATOSENTRADA>',
                    $order->getAmount(),
                    $order->getNumber(),
                    $this->soap->getMerchant()->getMerchantCode(),
                    $order->getCurrency(),
                    $card->getPan(),
                    $card->getExpiration(),
                    $card->getCvv(),
                    $this->soap->getMerchant()->getTerminal(),
                )
            );

            $message = sprintf(
                '<REQUEST>%s<DS_SIGNATURE>%s</DS_SIGNATURE><DS_SIGNATUREVERSION>T23V1</DS_SIGNATUREVERSION></REQUEST>',
                $datoEntrada,
                hash(
                    'sha256',
                    implode(
                        '',
                        [
                            $datoEntrada,
                            $this->soap->getMerchant()->getMerchantKey(),
                        ]
                    )
                )
            );
        } else {
            if (! $tokenize) {
                if (empty($card->getPan())) {
                    $signature = hash(
                        'sha256',
                        implode(
                            '',
                            [
                                $order->getAmount(),
                                $order->getNumber(),
                                $this->soap->getMerchant()->getMerchantCode(),
                                $order->getCurrency(),
                                $card->isThreeDSecure() ? '0' : ($capture ? 'A' : '1'),
                                $card->getIdentifier(),
                                $this->soap->getMerchant()->getMerchantKey(),
                            ]
                        )
                    );
                } else {
                    $signature = hash(
                        'sha256',
                        implode(
                            '',
                            [
                                $order->getAmount(),
                                $order->getNumber(),
                                $this->soap->getMerchant()->getMerchantCode(),
                                $order->getCurrency(),
                                $card->getPan(),
                                $card->getCvv(),
                                $card->isThreeDSecure() ? '0' : ($capture ? 'A' : '1'),
                                $this->soap->getMerchant()->getMerchantKey(),
                            ]
                        )
                    );
                }
            } else {
                $signature = hash(
                    'sha256',
                    implode(
                        '',
                        [
                            $order->getAmount(),
                            $order->getNumber(),
                            $this->soap->getMerchant()->getMerchantCode(),
                            $order->getCurrency(),
                            $card->getPan(),
                            $card->getCvv(),
                            $card->isThreeDSecure() ? '0' : ($capture ? 'A' : '1'),
                            'REQUIRED',
                            $this->soap->getMerchant()->getMerchantKey(),
                        ]
                    )
                );
            }

            if (empty($card->getPan())) {
                $cardData = '<DS_MERCHANT_IDENTIFIER>' . $card->getIdentifier() . '</DS_MERCHANT_IDENTIFIER>';
            } else {
                $cardData = preg_replace(
                    "/(\n|\s*)</",
                    '<',
                    sprintf(
                        '<DS_MERCHANT_PAN>%s</DS_MERCHANT_PAN>
                <DS_MERCHANT_EXPIRYDATE>%s</DS_MERCHANT_EXPIRYDATE>
                <DS_MERCHANT_CVV2>%s</DS_MERCHANT_CVV2>',
                        $card->getPan(),
                        $card->getExpiration(),
                        $card->getCvv(),
                    )
                );
            }

            $message = preg_replace(
                "/(\n|\s*)</",
                '<',
                sprintf(
                    '<DATOSENTRADA>
                <DS_MERCHANT_AMOUNT>%s</DS_MERCHANT_AMOUNT>
                <DS_MERCHANT_ORDER>%s</DS_MERCHANT_ORDER>
                <DS_MERCHANT_MERCHANTCODE>%s</DS_MERCHANT_MERCHANTCODE>
                <DS_MERCHANT_TERMINAL>%s</DS_MERCHANT_TERMINAL>
                <DS_MERCHANT_CURRENCY>%d</DS_MERCHANT_CURRENCY>
                %s
                <DS_MERCHANT_TRANSACTIONTYPE>%s</DS_MERCHANT_TRANSACTIONTYPE>
                <DS_MERCHANT_ACCOUNTTYPE>%02d</DS_MERCHANT_ACCOUNTTYPE>
                <DS_MERCHANT_PLANTYPE>%s</DS_MERCHANT_PLANTYPE>
                <DS_MERCHANT_PLANINSTALLMENTSNUMBER>%d</DS_MERCHANT_PLANINSTALLMENTSNUMBER>
                %s
                <DS_MERCHANT_MERCHANTSIGNATURE>%s</DS_MERCHANT_MERCHANTSIGNATURE>%s%s%s
            </DATOSENTRADA>',
                    $order->getAmount(),
                    $order->getNumber(),
                    $this->soap->getMerchant()->getMerchantCode(),
                    $this->soap->getMerchant()->getTerminal(),
                    $order->getCurrency(),
                    $cardData,
                    $card->isThreeDSecure() ? '0' : ($capture ? 'A' : '1'),
                    $card->getAccountType(),
                    $payment->getInstallments() > 1 ? '02' : '01',
                    $payment->getInstallments(),
                    $identifier,
                    $signature,
                    $recurring ? "\n<DS_MERCHANT_RECURRINGPAYMENT>Y</DS_MERCHANT_RECURRINGPAYMENT>" : '',
                    ! empty($payment->getSoftDescriptor()) ?
                        sprintf(
                            '<DS_MERCHANT_MERCHANTDESCRIPTOR>%s</DS_MERCHANT_MERCHANTDESCRIPTOR>',
                            $payment->getSoftDescriptor()
                        ) : '',
                    $card->isThreeDSecure() ?
                        sprintf(
                            "<DS_MERCHANT_ACCEPTHEADER>
                                        text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
                                    </DS_MERCHANT_ACCEPTHEADER>
                                    <DS_MERCHANT_USERAGENT>%s</DS_MERCHANT_USERAGENT>",
                            $this->soap->getUserAgent()
                        ) : ''
                )
            );
        }

        return $this->soap->parseResponse(
            $this->soap->sendMessage(
                $isV2 ? 'iniciaPeticion' : 'trataPeticion',
                $message,
                $isV2
            ),
            $payment
        );
    }
}
