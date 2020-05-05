<?php declare(strict_types=1);

namespace App\Domain\BankOCR;

class AccountNumber
{
    private const SIMILAR_DIGITS = [
        1 => [7],
        3 => [9],
        5 => [6, 9],
        6 => [5, 8],
        7 => [1],
        8 => [0, 6, 9],
        9 => [3, 5, 8],
        0 => [8],
    ];

    public const STATUS_OK        = '';
    public const STATUS_ERROR     = 'ERR';
    public const STATUS_AMBIGUOUS = 'AMB';

    protected string $status                     = self::STATUS_OK;
    protected array  $originalDigitNumber;
    protected array  $parsedAccountNumber;
    protected array  $similarValidAccountNumbers = [];

    public function __construct(array $originalAccountNumber, array $parsedAccountNumber)
    {
        $this->originalDigitNumber = $originalAccountNumber;
        $this->parsedAccountNumber = $parsedAccountNumber;
    }

    public function getOriginalDigitNumber(): array
    {
        return $this->originalDigitNumber;
    }

    public function getParsedAccountNumber(): array
    {
        return $this->parsedAccountNumber;
    }

    public function getParsedAccountNumberAsString(): string
    {
        return implode('', $this->parsedAccountNumber);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function validChecksum(): void
    {
        if (true === $this->isChecksumValid($this->parsedAccountNumber)) {
            return;
        }

        $this->status = self::STATUS_ERROR;
    }

    private function isChecksumValid(array $number): bool
    {
        $checksum = 0;
        foreach ($number as $key => $digit) {
            $checksum += $digit * (9 - $key);
        }

        return 0 === $checksum % 11;
    }

    public function checkSimilarNumbers(): void
    {
        $similarAccountNumbers   = [];
        $parsedAccountNumberCopy = $this->parsedAccountNumber;
        foreach ($parsedAccountNumberCopy as $digitPosition => $digit) {
            if (isset(self::SIMILAR_DIGITS[$digit]) && true === is_array(self::SIMILAR_DIGITS[$digit])) {
                foreach (self::SIMILAR_DIGITS[$digit] as $similarDigit) {
                    $parsedAccountNumberCopy[$digitPosition] = $similarDigit;
                    $similarAccountNumbers[]                 = $parsedAccountNumberCopy;
                    $parsedAccountNumberCopy                 = $this->parsedAccountNumber;
                }
            }
        }

        foreach ($similarAccountNumbers as $number) {
            if (true === $this->isChecksumValid($number)) {
                $this->similarValidAccountNumbers[] = $number;
                $this->status                       = self::STATUS_AMBIGUOUS;
            }
        }

        if (1 === count($this->similarValidAccountNumbers)) {
            $this->parsedAccountNumber        = $this->similarValidAccountNumbers[0];
            $this->status                     = self::STATUS_OK;
            $this->similarValidAccountNumbers = [];
        }
    }

    public function getSimilarValidAccountNumbersAsString(): string
    {
        if (true === empty($this->similarValidAccountNumbers)) {
            return '';
        }

        $similarValidNumbers = array_map(static function ($item) {
            return implode('', $item);
        }, $this->similarValidAccountNumbers);

        return '[' . implode(', ', $similarValidNumbers) . ']';
    }
}
