<?php declare(strict_types=1);

namespace App\Domain\BankOCR\Exception;

class CannotCreateAccountNumberException extends \Exception
{
    private const MESSAGE = 'Cannot find any digit that fits';
    private array $rawAccountDigit;

    public function __construct(\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 0, $previous);
        $this->rawAccountDigit = $rawAccountDigit;
    }
}
