<?php

namespace EntrePayments\Request\Soap;

use BadMethodCallException;
use EntrePayments\Card;
use EntrePayments\Order;
use EntrePayments\Payment;

class SoapRequestTest extends SoapTestCase
{
    /**
     * @testdox The method `SoapRequest::authorize` SHOULD THROW an exception if not implemented yet.
     */
    public function testAuthorize(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method not implemented.');

        $soapAuthorization = new SoapCancel($this->environment);
        $soapAuthorization->authorize($this->payment);
    }

    /**
     * @testdox The method `SoapRequest::capture` SHOULD THROW an exception if not implemented yet.
     */
    public function testCapture(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method not implemented.');

        $soapAuthorization = new SoapAuthorization($this->environment);
        $soapAuthorization->capture($this->payment);
    }

    /**
     * @testdox The method `SoapRequest::consult` SHOULD THROW an exception if not implemented yet.
     */
    public function testConsult(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method not implemented.');

        $soapAuthorization = new SoapAuthorization($this->environment);
        $soapAuthorization->consult($this->payment);
    }

    /**
     * @testdox The method `SoapRequest::cancel` SHOULD THROW an exception if not implemented yet.
     */
    public function testCancel(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method not implemented.');

        $soapAuthorization = new SoapAuthorization($this->environment);
        $soapAuthorization->cancel($this->payment);
    }
}
