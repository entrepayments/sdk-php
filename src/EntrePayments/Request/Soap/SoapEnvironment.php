<?php

namespace EntrePayments\Request\Soap;

use DOMDocument;
use DOMElement;
use DOMException;
use EntrePayments\Card;
use EntrePayments\EntrePayments;
use EntrePayments\Merchant;
use EntrePayments\Payment;
use EntrePayments\Request\Environment;
use RuntimeException;

class SoapEnvironment implements Environment
{
    /**
     * Production webservice transaction namespace
     */
    private const WS_NAMESPACE = 'http://webservice.sis.sermepa.es';

    /**
     * Production webservice transaction endpoint
     */
    private const PRODUCTION_ENDPOINT = 'https://sisw.redsys.es/sis/services/SerClsWSEntradaV2';

    /**
     * Test webservice transaction endpoint
     */
    private const TEST_ENDPOINT = 'https://sis-t.redsys.es:25443/sis/services/SerClsWSEntradaV2';

    /**
     * Production webservice consultation namespace
     */
    private const WS_CONSULT_NAMESPACE = 'http://webservices.apl02.redsys.es';

    /**
     * Test webservice consultation endpoint
     */
    private const TEST_CONSULT_ENDPOINT = 'https://sis-t.redsys.es:25443/apl02/services/SerClsWSConsulta';

    /**
     * Production webservice consultation endpoint
     */
    private const PRODUCTION_CONSULT_ENDPOINT = 'https://sisw.globalpaybrasil.com.br/apl02/services/SerClsWSConsulta';

    /**
     * Merchant data
     *
     * @var Merchant
     */
    private Merchant $merchant;

    /**
     * Will use production endpoint
     *
     * @var bool
     */
    private bool $production;

    /**
     * Is a consultation request?
     *
     * @var bool
     */
    private bool $consult = false;

    /**
     * Creates the environment defining the merchant and the production/test environment.
     *
     * @param Merchant $merchant
     * @param bool     $production
     */
    public function __construct(Merchant $merchant, bool $production)
    {
        $this->merchant   = $merchant;
        $this->production = $production;
    }

    /**
     * Creates an instance for production environment
     *
     * @param Merchant $merchant
     *
     * @return Environment
     */
    public static function production(Merchant $merchant): Environment
    {
        return new SoapEnvironment($merchant, true);
    }

    /**
     * Creates an instance for test environment
     *
     * @param Merchant $merchant
     *
     * @return Environment
     */
    public static function test(Merchant $merchant): Environment
    {
        return new SoapEnvironment($merchant, false);
    }

    /**
     * Sets this environment as consultation
     *
     * @return SoapEnvironment
     */
    public function setConsult(): SoapEnvironment
    {
        $this->consult = true;

        return $this;
    }

    /**
     * Is a production environment?
     *
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->production;
    }

    /**
     * Gets the merchant of this environment
     *
     * @return Merchant
     */
    public function getMerchant(): Merchant
    {
        return $this->merchant;
    }

    /**
     * Creates an authorization
     *
     * @param Payment $payment
     * @param bool    $capture
     * @param bool    $recurring
     * @param bool    $tokenize
     *
     * @return Payment
     * @throws DOMException
     */
    public function authorize(
        Payment $payment,
        bool $capture = true,
        bool $recurring = false,
        bool $tokenize = false
    ): Payment {
        return (new SoapAuthorization($this))->authorize(
            $payment,
            $capture,
            $recurring,
            $tokenize
        );
    }

    /**
     * Parses a consultation response
     *
     * @param DOMDocument $dom
     * @param Payment     $payment
     *
     * @return Payment
     */
    public function parseResponseConsult(DOMDocument $dom, Payment $payment): Payment
    {
        $retornoXmlNodeList = $dom->getElementsByTagName('Message');

        if ($retornoXmlNodeList->length < 1) {
            throw new RuntimeException('No Message node found.');
        }

        $retornoXml = $retornoXmlNodeList->item(0);

        if ($retornoXml !== null && isset($retornoXml->childNodes[0])) {
            foreach ($retornoXml->childNodes[0]->childNodes as $node) {
                switch ($node->nodeName) {
                    case 'Ds_Order':
                        $payment->getOrder()->setNumber($node->nodeValue);
                        break;
                    case 'Ds_Amount':
                        $payment->getOrder()->setAmount((float)$node->nodeValue);
                        break;
                    case 'Ds_Currency':
                        $payment->getOrder()->setCurrency($node->nodeValue);
                        break;
                    case 'Ds_Response':
                        $payment->setResponse($node->nodeValue);
                        break;
                    case 'Ds_ResponseInt':
                        $payment->setRespondeInt($node->nodeValue);
                        break;
                    case 'Ds_SecurePayment':
                        $payment->setSecurePayment($node->nodeValue != '0');
                        break;
                    case 'Ds_State':
                        $payment->setState($node->nodeValue);
                        break;
                    case 'Ds_TransactionType':
                        $payment->setTransactionType($node->nodeValue);
                        break;
                }
            }
        } else {
            throw new RuntimeException('No response message found');
        }

        return $payment;
    }

    /**
     * Parses a transaction response
     *
     * @param DOMDocument $dom
     * @param Payment     $payment
     *
     * @return Payment
     */
    public function parseResponse(DOMDocument $dom, Payment $payment): Payment
    {
        $retornoXmlNodeList = $dom->getElementsByTagName('RETORNOXML');

        if ($retornoXmlNodeList->length < 1) {
            throw new RuntimeException('No RETORNOXML node found.');
        }

        $retornoXml = $retornoXmlNodeList->item(0);

        if (! $retornoXml instanceof DOMElement) {
            throw new RuntimeException('Something happened when reading the response');
        }

        $codigo = $retornoXml->getElementsByTagName('CODIGO');

        if ($codigo->length < 1) {
            throw new RuntimeException('No CODIGO node found.');
        }

        foreach ($retornoXml->childNodes as $node) {
            switch ($node->nodeName) {
                case 'CODIGO':
                    if ($node->nodeValue !== '0') {
                        throw new RuntimeException(
                            sprintf(
                                'GlobalPayments returned the error %s.',
                                $node->nodeValue
                            )
                        );
                    }

                    break;
                case 'OPERACION':
                    foreach ($node->childNodes as $operacionNode) {
                        $nodeName = strtolower($operacionNode->nodeName);

                        switch ($nodeName) {
                            case 'ds_amount':
                                $payment->getOrder()->setAmount(floatval($operacionNode->nodeValue));
                                break;
                            case 'ds_currency':
                                $payment->getOrder()->setCurrency(intval($operacionNode->nodeValue));
                                break;
                            case 'ds_order':
                                $payment->getOrder()->setNumber($operacionNode->nodeValue);
                                break;
                            case 'ds_md':
                                $payment->setMd($operacionNode->nodeValue);
                                break;
                            case 'ds_authorisationcode':
                                $payment->setAuthorizationCode($operacionNode->nodeValue);
                                break;
                            case 'ds_securepayment':
                                $payment->setSecurePayment(! empty($operacionNode->nodeValue));
                                break;
                            case 'ds_language':
                                $payment->setLanguage($operacionNode->nodeValue);
                                break;
                            case 'ds_nsu':
                                $payment->setNsu($operacionNode->nodeValue);
                                break;
                            case 'ds_parequest':
                                $payment->setPaRequest($operacionNode->nodeValue);
                                break;
                            case 'ds_acsurl':
                                $payment->setAuthenticationUrl($operacionNode->nodeValue);
                                break;
                            case 'ds_response':
                                $payment->setResponse($operacionNode->nodeValue);
                                break;
                            case 'ds_response_int':
                                $payment->setRespondeInt($operacionNode->nodeValue);
                                break;
                            case 'ds_card_type':
                                $payment->getCard()->setAccountType(
                                    $operacionNode->nodeValue == 'C' ? Card::CREDITCARD : Card::DEBITCARD
                                );
                                break;
                            case 'ds_merchantdata':
                                $payment->setMerchantData($operacionNode->nodeValue);
                                break;
                            case 'ds_card_country':
                                $payment->getCard()->setCardCountry(intval($operacionNode->nodeValue));
                                break;
                            case 'ds_card_brand':
                                $payment->getCard()->setCardBrand(intval($operacionNode->nodeValue));
                                break;
                            case 'ds_processedpaymethod':
                                $payment->setProcessedPayMethod($operacionNode->nodeValue);
                                break;
                            case 'ds_merchant_identifier':
                                $payment->getCard()->setIdentifier($operacionNode->nodeValue);
                                break;
                        }
                    }
            }
        }

        return $payment;
    }

    /**
     * Sent the request
     *
     * @throws DOMException
     */
    public function sendMessage(
        string $messageElementName,
        string $dataContent,
        bool $v2 = false
    ): DOMDocument {
        $headers = [
            'User-Agent: ' . $this->getUserAgent(),
            'Accept: text/xml',
            'SOAPAction: ' . $messageElementName,
            'Content-Type: text/xml; charset=utf-8',
        ];

        $domRequest = new DOMDocument();
        $envelope   = $domRequest->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Envelope');
        $body       = $domRequest->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Body');
        $message    = $domRequest->createElement('web:' . $messageElementName);
        $data       = $domRequest->createElement($this->consult ? 'web:cadenaXML' : 'web:datoEntrada');

        if ($this->consult) {
            $curl = curl_init($this->production ? self::PRODUCTION_CONSULT_ENDPOINT : self::TEST_CONSULT_ENDPOINT);
        } else {
            $curl = curl_init($this->production ? self::PRODUCTION_ENDPOINT : self::TEST_ENDPOINT);
        }

        if ($curl === false) {
            throw new RuntimeException('Error creating a curl resource.');
        }

        if ($this->consult) {
            $envelope->setAttribute('xmlns:web', self::WS_CONSULT_NAMESPACE);
        } else {
            $envelope->setAttribute('xmlns:web', self::WS_NAMESPACE);
        }

        $data->appendChild($domRequest->createCDATASection($dataContent));
        $message->appendChild($data);
        $body->appendChild($message);
        $envelope->appendChild($body);
        $domRequest->appendChild($envelope);

        $domExib = new DOMDocument();
        $domExib->loadXML($dataContent);

        $domExib->preserveWhiteSpace = false;
        $domExib->formatOutput       = true;

        error_log($domExib->saveXML());

        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $domRequest->saveXML());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);

        if (! is_string($response)) {
            throw new RuntimeException('Something happened when calling the webservice.');
        }

        $soapResponse = new DOMDocument();
        $soapResponse->loadXML($response);

        $soapResponse->preserveWhiteSpace = false;
        $soapResponse->formatOutput       = true;

        if ($this->consult) {
            $soapMessageReturn = $soapResponse->getElementsByTagNameNS(
                SoapEnvironment::WS_CONSULT_NAMESPACE,
                'consultaOperacionesResponse'
            )->item(0);
        } else {
            $soapMessageReturn = $soapResponse->getElementsByTagNameNS(
                SoapEnvironment::WS_NAMESPACE,
                $v2 ? 'iniciaPeticionReturn' : 'trataPeticionReturn'
            )->item(0);
        }

        if ($soapMessageReturn === null) {
            throw new RuntimeException('Something happened.');
        }

        $actionReturn                     = new DOMDocument();
        $actionReturn->preserveWhiteSpace = false;
        $actionReturn->formatOutput       = true;
        $actionReturn->loadXML($soapMessageReturn->nodeValue);

        error_log($actionReturn->saveXML());

        curl_close($curl);

        return $actionReturn;
    }

    /**
     * Gets the SDK user agent
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        return sprintf(
            'SOAP PHP SDK %s (PHP: %s - %s)',
            EntrePayments::VERSION,
            phpversion(),
            php_uname(),
        );
    }

    /**
     * Creates a consultation
     *
     * @param Payment $payment
     * @param string  $transactionType
     *
     * @return Payment
     * @throws DOMException
     */
    public function consult(Payment $payment, string $transactionType = '1'): Payment
    {
        return (new SoapConsult($this))->consult(
            $payment,
            $transactionType
        );
    }

    /**
     * Creates a capture
     *
     * @param Payment $payment
     *
     * @return Payment
     * @throws DOMException
     */
    public function capture(Payment $payment): Payment
    {
        return (new SoapCapture($this))->capture(
            $payment
        );
    }

    /**
     * Creates a cancellation
     *
     * @param Payment $payment
     * @param bool    $preAuthorization
     *
     * @return Payment
     * @throws DOMException
     */
    public function cancel(Payment $payment, bool $preAuthorization = false): Payment
    {
        return (new SoapCancel($this))->cancel(
            $payment,
            $preAuthorization
        );
    }
}
