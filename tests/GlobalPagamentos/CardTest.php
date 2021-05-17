<?php

namespace GlobalPagamentos;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CardTest extends TestCase
{
    /**
     * @testdox When creating a `Card` instance with the right data, everything should work properly.
     */
    public function testValidCardData(): void
    {
        $pan        = '5276652150489056';
        $holderName = 'Fulano de Tal';
        $year       = (int)date('y') + 2;
        $month      = date('m');
        $cvv        = '123';

        $card = Card::creditCard(
            $pan,
            $year,
            $month,
            $holderName,
            $cvv
        );

        $this->assertEquals($pan, $card->getPan());
        $this->assertEquals($year . $month, $card->getExpirity());
        $this->assertEquals($holderName, $card->getHolderName());
        $this->assertEquals($cvv, $card->getCvv());
    }

    /**
     * @testdox When using `Card::creditCard` or `Card::debitCard`, `accountType` will be set properly.
     */
    public function testAccountType(): void
    {
        $creditCard = Card::creditCard(
            '5276652150489056',
            (int)date('y') + 2,
            date('m'),
            'Fulano de Tal',
            '123'
        );

        $debitCard = Card::debitCard(
            '5276652150489056',
            (int)date('y') + 2,
            date('m'),
            'Fulano de Tal',
            '123'
        );

        $this->assertEquals(Card::CREDITCARD, $creditCard->getAccountType());
        $this->assertEquals(Card::DEBITCARD, $debitCard->getAccountType());
    }

    /**
     * @testdox If using a debit card or setting 3DS, the 3ds MUST BE enabled.
     */
    public function testWhenSettingDebit3DSIsMandatory(): void
    {
        $debitCard = Card::debitCard(
            '5276652150489056',
            (int)date('y') + 2,
            date('m'),
            'Fulano de Tal',
            '123'
        );

        $this->assertEquals(true, $debitCard->isThreeDSecure());

        $creditCard = Card::creditCard(
            '5276652150489056',
            (int)date('y') + 2,
            date('m'),
            'Fulano de Tal',
            '123'
        );

        $creditCard->setThreeDSecure();

        $this->assertEquals(true, $creditCard->isThreeDSecure());
    }

    /**
     * @testdox We are expecting an InvalidArgumentException when passing an invalid PAN.
     */
    public function testInvalidPan(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid PAN provided.');

        Card::creditCard(
            '5276652150489051',
            (int)date('y') + 2,
            date('m'),
            'Fulano de Tal',
            '123'
        );
    }

    /**
     * @testdox We are expecting an InvalidArgumentException if the informed year has more than 2 digits.
     */
    public function testInvalidYear(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('We are expecting the year with only 2 digits.');

        Card::creditCard(
            '5276652150489056',
            (int)date('Y') + 2,
            date('m'),
            'Fulano de Tal',
            '123'
        );
    }

    /**
     * @testdox We are expecting an InvalidArgumentException if the informed month is lesser than 1 or greater than 12.
     */
    public function testInvalidMonth(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Month should be >= than 1 and <= than 12.');

        Card::creditCard(
            '5276652150489056',
            (int)date('y') + 2,
            '-1',
            'Fulano de Tal',
            '123'
        );
    }

    /**
     * @testdox We are expecting an InvalidArgumentException if the card is expired.
     */
    public function testExpiredCard(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Card expiration date MUST BE in future');

        Card::creditCard(
            '5276652150489056',
            date('y'),
            (int)date('m') - 1,
            'Fulano de Tal',
            '123'
        );
    }

    /**
     * @testdox We are expecting an InvalidArgumentException if the holder's name has any special character.
     */
    public function testInvalidHolderName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Holder name MUST NOT HAVE any special character.');

        Card::creditCard(
            '5276652150489056',
            (int)date('y') + 2,
            date('m'),
            'João da Silva',
            '123'
        );
    }

    /**
     * @testdox We are expecting an InvalidArgumentException if CVV has anything different from numbers.
     */
    public function testInvalidCVV(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CVV MUST HAVE only numbers.');

        Card::creditCard(
            '5276652150489056',
            (int)date('y') + 2,
            date('m'),
            'Fulano de Tal',
            'abc'
        );
    }

    /**
     * @testdox We are expecting an InvalidArgumentException if CVV is lesser than 3 or greater than 4 digits.
     */
    public function testCVVWithInvalidLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CVV MUST HAVE between 3 and 4 digits - inclusive.');

        Card::creditCard(
            '5276652150489056',
            (int)date('y') + 2,
            date('m'),
            'Fulano de Tal',
            '12'
        );
    }
}
