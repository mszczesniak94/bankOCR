<?php declare(strict_types=1);

namespace App\Domain\BankOCR;

use App\Domain\BankOCR\Exception\BadAccountNumberLengthException;
use App\Domain\BankOCR\Exception\CannotFitAnyDigitException;
use App\Infrastructure\BankOCR\Reader\FileAccountNumberReader;

class AccountNumberFactory
{
    private const ILLEGIBLE_CHARACTER      = '?';
    private const ACCOUNT_NUMBER_LENGTH    = 9;
    private const ALLOWED_DIGIT_DIFFERENCE = 1;

    private const ONE = [
        1 => [
            0 => ' ',
            1 => ' ',
            2 => ' ',
        ],
        2 => [
            0 => ' ',
            1 => ' ',
            2 => '|',
        ],
        3 => [
            0 => ' ',
            1 => ' ',
            2 => '|',
        ],
    ];

    private const TWO = [
        1 => [
            0 => ' ',
            1 => '_',
            2 => ' ',
        ],
        2 => [
            0 => ' ',
            1 => '_',
            2 => '|',
        ],
        3 => [
            0 => '|',
            1 => '_',
            2 => ' ',
        ],
    ];

    private const THREE = [
        1 => [
            0 => ' ',
            1 => '_',
            2 => ' ',
        ],
        2 => [
            0 => ' ',
            1 => '_',
            2 => '|',
        ],
        3 => [
            0 => ' ',
            1 => '_',
            2 => '|',
        ],
    ];

    private const FOUR = [
        1 => [
            0 => ' ',
            1 => ' ',
            2 => ' ',
        ],
        2 => [
            0 => '|',
            1 => '_',
            2 => '|',
        ],
        3 => [
            0 => ' ',
            1 => ' ',
            2 => '|',
        ],
    ];

    private const FIVE = [
        1 => [
            0 => ' ',
            1 => '_',
            2 => ' ',
        ],
        2 => [
            0 => '|',
            1 => '_',
            2 => ' ',
        ],
        3 => [
            0 => ' ',
            1 => '_',
            2 => '|',
        ],
    ];

    private const SIX = [
        1 => [
            0 => ' ',
            1 => '_',
            2 => ' ',
        ],
        2 => [
            0 => '|',
            1 => '_',
            2 => ' ',
        ],
        3 => [
            0 => '|',
            1 => '_',
            2 => '|',
        ],
    ];

    private const SEVEN = [
        1 => [
            0 => ' ',
            1 => '_',
            2 => ' ',
        ],
        2 => [
            0 => ' ',
            1 => ' ',
            2 => '|',
        ],
        3 => [
            0 => ' ',
            1 => ' ',
            2 => '|',
        ],
    ];

    private const EIGHT = [
        1 => [
            0 => ' ',
            1 => '_',
            2 => ' ',
        ],
        2 => [
            0 => '|',
            1 => '_',
            2 => '|',
        ],
        3 => [
            0 => '|',
            1 => '_',
            2 => '|',
        ],
    ];

    private const NINE = [
        1 => [
            0 => ' ',
            1 => '_',
            2 => ' ',
        ],
        2 => [
            0 => '|',
            1 => '_',
            2 => '|',
        ],
        3 => [
            0 => ' ',
            1 => '_',
            2 => '|',
        ],
    ];

    private const ZERO = [
        1 => [
            0 => ' ',
            1 => '_',
            2 => ' ',
        ],
        2 => [
            0 => '|',
            1 => ' ',
            2 => '|',
        ],
        3 => [
            0 => '|',
            1 => '_',
            2 => '|',
        ],
    ];

    public const DIGITS = [
        1 => self::ONE,
        2 => self::TWO,
        3 => self::THREE,
        4 => self::FOUR,
        5 => self::FIVE,
        6 => self::SIX,
        7 => self::SEVEN,
        8 => self::EIGHT,
        9 => self::NINE,
        0 => self::ZERO,
    ];

    public function create(array $originalAccountNumber): AccountNumber
    {
        $realDigits        = [];
        $anyIllegibleDigit = false;
        foreach ($originalAccountNumber as $digit) {
            if (false !== $realDigitValue = array_search($digit, self::DIGITS, true)) {
                $realDigits[] = $realDigitValue;
                continue;
            }

            $realDigits[]      = self::ILLEGIBLE_CHARACTER;
            $anyIllegibleDigit = true;
        }

        $realDigitCount = count($realDigits);
        if (self::ACCOUNT_NUMBER_LENGTH !== $realDigitCount) {
            throw new BadAccountNumberLengthException($realDigitCount);
        }

        if (true === $anyIllegibleDigit) {
            return new IllegibleAccountNumber($originalAccountNumber, $realDigits);
        }

        return new AccountNumber($originalAccountNumber, array_map('intval', $realDigits));
    }

    public function createFormIllegible(IllegibleAccountNumber $illegibleAccountNumber): AccountNumber
    {
        $previousIllegibleDigitIndex = null;
        $parsedAccountNumber         = $illegibleAccountNumber->getParsedAccountNumber();
        $originalAccountNumber       = $illegibleAccountNumber->getOriginalDigitNumber();
        while (false !== $illegibleDigitIndex = array_search(self::ILLEGIBLE_CHARACTER, $parsedAccountNumber, true)) {
            if ($previousIllegibleDigitIndex === $illegibleDigitIndex) {
                throw new CannotFitAnyDigitException($originalAccountNumber[$illegibleDigitIndex]);
            }

            $previousIllegibleDigitIndex = $illegibleDigitIndex;
            foreach (self::DIGITS as $digitRealNumber => $digit) {
                $originalDigitToCheck = $originalAccountNumber[$illegibleDigitIndex];
                $digitsDifference     = count(array_diff_assoc($originalDigitToCheck[1], $digit[1]))
                                        + count(array_diff_assoc($originalDigitToCheck[2], $digit[2]))
                                        + count(array_diff_assoc($originalDigitToCheck[3], $digit[3]));
                if (self::ALLOWED_DIGIT_DIFFERENCE === $digitsDifference) {
                    $parsedAccountNumber[$illegibleDigitIndex] = $digitRealNumber;
                    break;
                }
            }

        }

        return new AccountNumber($illegibleAccountNumber->getOriginalDigitNumber(), $parsedAccountNumber);
    }
}
