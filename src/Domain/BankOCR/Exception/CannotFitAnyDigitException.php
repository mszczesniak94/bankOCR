<?php declare(strict_types=1);

namespace App\Domain\BankOCR\Exception;

class CannotFitAnyDigitException extends \DomainException
{
    private const MESSAGE = 'Cannot find any digit that fits';
    private array $rawAccountDigit;

    public function __construct(array $rawAccountDigit, ?\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 0, $previous);
        $this->rawAccountDigit = $rawAccountDigit;
    }
}
