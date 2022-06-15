<?php
namespace EntrePayments\Request\Soap;

use DOMDocument;
use DOMException;
use EntrePayments\Card;
use EntrePayments\Order;
use EntrePayments\Payment;
use RuntimeException;

class SoapAuthorizationTest extends SoapTestCase
{
    /**
     * @throws DOMException
     */
    public function testAuthorizationWithSomeRequestError(): void
    {
        $this->payment = new Payment(
            new Order(
                '123test',
                1
            ),
            Card::creditCard(
                '4548812049400004',
                '22',
                '12',
                'Fulano de tal',
                '626'
            )
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GlobalPayments returned the error 123.');

        $requestMessage  = '<DATOSENTRADA><DS_MERCHANT_AMOUNT>100</DS_MERCHANT_AMOUNT><DS_MERCHANT_ORDER>123test</DS_MERCHANT_ORDER><DS_MERCHANT_MERCHANTCODE>123</DS_MERCHANT_MERCHANTCODE><DS_MERCHANT_TERMINAL>123</DS_MERCHANT_TERMINAL><DS_MERCHANT_CURRENCY>986</DS_MERCHANT_CURRENCY><DS_MERCHANT_PAN>4548812049400004</DS_MERCHANT_PAN><DS_MERCHANT_EXPIRYDATE>2212</DS_MERCHANT_EXPIRYDATE><DS_MERCHANT_CVV2>626</DS_MERCHANT_CVV2><DS_MERCHANT_TRANSACTIONTYPE>A</DS_MERCHANT_TRANSACTIONTYPE><DS_MERCHANT_ACCOUNTTYPE>01</DS_MERCHANT_ACCOUNTTYPE><DS_MERCHANT_PLANTYPE>01</DS_MERCHANT_PLANTYPE><DS_MERCHANT_PLANINSTALLMENTSNUMBER>1</DS_MERCHANT_PLANINSTALLMENTSNUMBER><DS_MERCHANT_MERCHANTSIGNATURE>76f6ca81ba5470ffd81b20824b5e2c8d3d66fa51e91a71c1ef81634f049ce854</DS_MERCHANT_MERCHANTSIGNATURE></DATOSENTRADA>';
        $responseMessage = '<RETORNOXML><CODIGO>123</CODIGO><RECIBIDO>' . $requestMessage . '</RECIBIDO></RETORNOXML>';
        $domResponse     = new DOMDocument();
        $domResponse->loadXML($responseMessage);

        $this->environment->expects($this->once())
                          ->method('sendMessage')
                          ->with('trataPeticion', $requestMessage)
                          ->willReturn($domResponse);

        $this->assertInstanceOf(Payment::class, $this->environment->authorize($this->payment));
    }

    /**
     * @throws DOMException
     */
    public function testAuthorizationWithOneInstallment(): void
    {
        $this->payment = (new Payment(
            new Order(
                '123test',
                1
            ),
            Card::creditCard(
                '4548812049400004',
                '22',
                '12',
                'Fulano de tal',
                '626'
            )
        ))->setInstallments(1);

        $requestMessage  = '<DATOSENTRADA><DS_MERCHANT_AMOUNT>100</DS_MERCHANT_AMOUNT><DS_MERCHANT_ORDER>123test</DS_MERCHANT_ORDER><DS_MERCHANT_MERCHANTCODE>123</DS_MERCHANT_MERCHANTCODE><DS_MERCHANT_TERMINAL>123</DS_MERCHANT_TERMINAL><DS_MERCHANT_CURRENCY>986</DS_MERCHANT_CURRENCY><DS_MERCHANT_PAN>4548812049400004</DS_MERCHANT_PAN><DS_MERCHANT_EXPIRYDATE>2212</DS_MERCHANT_EXPIRYDATE><DS_MERCHANT_CVV2>626</DS_MERCHANT_CVV2><DS_MERCHANT_TRANSACTIONTYPE>A</DS_MERCHANT_TRANSACTIONTYPE><DS_MERCHANT_ACCOUNTTYPE>01</DS_MERCHANT_ACCOUNTTYPE><DS_MERCHANT_PLANTYPE>01</DS_MERCHANT_PLANTYPE><DS_MERCHANT_PLANINSTALLMENTSNUMBER>1</DS_MERCHANT_PLANINSTALLMENTSNUMBER><DS_MERCHANT_MERCHANTSIGNATURE>76f6ca81ba5470ffd81b20824b5e2c8d3d66fa51e91a71c1ef81634f049ce854</DS_MERCHANT_MERCHANTSIGNATURE></DATOSENTRADA>';
        $responseMessage = '<RETORNOXML><CODIGO>0</CODIGO><OPERACION><Ds_Amount>100</Ds_Amount><Ds_Currency>986</Ds_Currency><Ds_Order>22559031test</Ds_Order><Ds_Signature>76f6ca81ba5470ffd81b20824b5e2c8d3d66fa51e91a71c1ef81634f049ce854</Ds_Signature><Ds_MerchantCode>123</Ds_MerchantCode><Ds_Terminal>1</Ds_Terminal><Ds_Response>0000</Ds_Response><Ds_AuthorisationCode>600202</Ds_AuthorisationCode><Ds_TransactionType>A</Ds_TransactionType><Ds_SecurePayment>0</Ds_SecurePayment><Ds_Language>9</Ds_Language><Ds_Card_Type>C</Ds_Card_Type><Ds_MerchantData/><Ds_Card_Country>724</Ds_Card_Country><Ds_Nsu>600202</Ds_Nsu><Ds_Card_Brand>1</Ds_Card_Brand><Ds_ProcessedPayMethod>3</Ds_ProcessedPayMethod></OPERACION></RETORNOXML>';
        $domResponse     = new DOMDocument();
        $domResponse->loadXML($responseMessage);

        $this->environment->expects($this->once())
                          ->method('sendMessage')
                          ->with('trataPeticion', $requestMessage)
                          ->willReturn($domResponse);

        $payment = $this->environment->authorize($this->payment);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertExpectations($this->payment, $payment);

        $this->assertNotEmpty($payment->getAuthorizationCode());
        $this->assertNotEmpty($payment->getNsu());
        $this->assertEquals('0000', $payment->getResponse());
    }

    /**
     * @throws DOMException
     */
    public function testAuthorizationWithTwoInstallments(): void
    {
        $this->payment = (new Payment(
            new Order(
                '123test',
                1
            ),
            Card::creditCard(
                '4761120000000148',
                '22',
                '12',
                'Fulano de tal',
                '626'
            ),
            'Test'
        ))->setInstallments(2);

        $requestMessage  = '<DATOSENTRADA><DS_MERCHANT_AMOUNT>100</DS_MERCHANT_AMOUNT><DS_MERCHANT_ORDER>123test</DS_MERCHANT_ORDER><DS_MERCHANT_MERCHANTCODE>123</DS_MERCHANT_MERCHANTCODE><DS_MERCHANT_TERMINAL>123</DS_MERCHANT_TERMINAL><DS_MERCHANT_CURRENCY>986</DS_MERCHANT_CURRENCY><DS_MERCHANT_PAN>4761120000000148</DS_MERCHANT_PAN><DS_MERCHANT_EXPIRYDATE>2212</DS_MERCHANT_EXPIRYDATE><DS_MERCHANT_CVV2>626</DS_MERCHANT_CVV2><DS_MERCHANT_TRANSACTIONTYPE>A</DS_MERCHANT_TRANSACTIONTYPE><DS_MERCHANT_ACCOUNTTYPE>01</DS_MERCHANT_ACCOUNTTYPE><DS_MERCHANT_PLANTYPE>02</DS_MERCHANT_PLANTYPE><DS_MERCHANT_PLANINSTALLMENTSNUMBER>2</DS_MERCHANT_PLANINSTALLMENTSNUMBER><DS_MERCHANT_MERCHANTSIGNATURE>85e1c79421e52ccd95f29fe342a90036c871d83acdbd3633b97971f12b6ad0de</DS_MERCHANT_MERCHANTSIGNATURE><DS_MERCHANT_MERCHANTDESCRIPTOR>Test</DS_MERCHANT_MERCHANTDESCRIPTOR></DATOSENTRADA>';
        $responseMessage = '<RETORNOXML><CODIGO>0</CODIGO><OPERACION><Ds_Amount>100</Ds_Amount><Ds_Currency>986</Ds_Currency><Ds_Order>22559031test</Ds_Order><Ds_Signature>76f6ca81ba5470ffd81b20824b5e2c8d3d66fa51e91a71c1ef81634f049ce854</Ds_Signature><Ds_MerchantCode>123</Ds_MerchantCode><Ds_Terminal>1</Ds_Terminal><Ds_Response>0000</Ds_Response><Ds_AuthorisationCode>600202</Ds_AuthorisationCode><Ds_TransactionType>A</Ds_TransactionType><Ds_SecurePayment>0</Ds_SecurePayment><Ds_Language>9</Ds_Language><Ds_Card_Type>C</Ds_Card_Type><Ds_MerchantData/><Ds_Card_Country>724</Ds_Card_Country><Ds_Nsu>600202</Ds_Nsu><Ds_Card_Brand>1</Ds_Card_Brand><Ds_ProcessedPayMethod>3</Ds_ProcessedPayMethod></OPERACION></RETORNOXML>';
        $domResponse     = new DOMDocument();
        $domResponse->loadXML($responseMessage);

        $this->environment->expects($this->once())
                          ->method('sendMessage')
                          ->with('trataPeticion', $requestMessage)
                          ->willReturn($domResponse);

        $payment = $this->environment->authorize($this->payment);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertExpectations($this->payment, $payment);

        $this->assertNotEmpty($payment->getAuthorizationCode());
        $this->assertNotEmpty($payment->getNsu());
        $this->assertEquals('0000', $payment->getResponse());
    }
}
