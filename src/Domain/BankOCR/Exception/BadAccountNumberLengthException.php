<?php declare(strict_types=1);

namespace App\Domain\BankOCR\Exception;

class BadAccountNumberLengthException extends \Exception
{
    private const MESSAGE = 'Bad account number length';
    private int   $accountNumberLength;

    public function __construct(int $accountNumberLength, \Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 0, $previous);
        $this->accountNumberLength = $accountNumberLength;
    }
}
