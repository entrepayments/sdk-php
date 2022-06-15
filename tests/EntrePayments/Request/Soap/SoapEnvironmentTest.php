<?php

namespace EntrePayments\Request\Soap;

use DOMDocument;
use DOMException;
use EntrePayments\Payment;
use RuntimeException;

class SoapEnvironmentTest extends SoapTestCase
{
    /**
     * @testdox Webservice response SHOULD HAVE a retornoxml node, or we will throw an exception.
     * @throws DOMException
     */
    public function testResponseWithoutRetornoXMLNode(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No RETORNOXML node found.');

        $domResponse = new DOMDocument();
        $domResponse->loadXML('<invalid></invalid>');

        $this->environment->expects($this->once())
                          ->method('sendMessage')
                          ->willReturn($domResponse);

        $this->assertInstanceOf(Payment::class, $this->environment->authorize($this->payment));
    }

    /**
     * * @testdox Webservice response SHOULD HAVE a `CODIGO` node inside retornoxml, or we will throw an exception.
     * @throws DOMException
     */
    public function testResponseWithoutCodigoNode(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No CODIGO node found.');

        $domResponse = new DOMDocument();
        $domResponse->loadXML('<RETORNOXML><invalid></invalid></RETORNOXML>');

        $this->environment->expects($this->once())
                          ->method('sendMessage')
                          ->willReturn($domResponse);

        $this->assertInstanceOf(Payment::class, $this->environment->authorize($this->payment));
    }

    /**
     * * @testdox Webservice response SHOULD HAVE a `CODIGO` node with zero, or we will throw an exception.
     * @throws DOMException
     */
    public function testResponseWithCodigoNodeDifferentThanZero(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GlobalPayments returned the error 123.');

        $domResponse = new DOMDocument();
        $domResponse->loadXML('<RETORNOXML><CODIGO>123</CODIGO></RETORNOXML>');

        $this->environment->expects($this->once())
                          ->method('sendMessage')
                          ->willReturn($domResponse);

        $this->assertInstanceOf(Payment::class, $this->environment->authorize($this->payment));
    }
}
