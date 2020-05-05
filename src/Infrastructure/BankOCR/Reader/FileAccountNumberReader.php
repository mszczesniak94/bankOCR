<?php declare(strict_types=1);

namespace App\Infrastructure\BankOCR\Reader;

use App\Application\BankOCR\Contract\Exception\CannotReadFileException;
use App\Application\BankOCR\Contract\Exception\FileDoesNotExistsException;
use App\Application\BankOCR\Contract\Reader\AccountNumberReaderInterface;
use App\Domain\BankOCR\AccountNumber;
use App\Domain\BankOCR\AccountNumberFactory;
use App\Domain\BankOCR\Exception\CannotFitAnyDigitException;
use App\Domain\BankOCR\IllegibleAccountNumber;

class FileAccountNumberReader implements AccountNumberReaderInterface
{
    /**
     * @var AccountNumberFactory
     */
    private AccountNumberFactory $accountNumberFactory;

    public function __construct(AccountNumberFactory $accountNumberFactory)
    {
        $this->accountNumberFactory = $accountNumberFactory;
    }

    public const DIGIT_MATRIX_ROW_DIMENSION = 3;
    public const DIGIT_MATRIX_COL_DIMENSION = 3;
    public const FILE_DIGIT_LINE_HEIGHT     = 4;

    public function read(string $pathToFile): iterable
    {
        $rawAccountNumbers = $this->readFromFile($pathToFile);
        $accountNumbers    = [];
        foreach ($rawAccountNumbers as $rawAccountNumber) {
            $accountNumbers[] = $this->accountNumberFactory->create($rawAccountNumber);
        }

        return $accountNumbers;
    }

    public function guessIllegibleAccountNumber(IllegibleAccountNumber $illegibleAccountNumber): AccountNumber
    {
        try {
            return $this->accountNumberFactory->createFormIllegible($illegibleAccountNumber);
        } catch (CannotFitAnyDigitException|\Throwable $exception) {
            return $illegibleAccountNumber;
        }
    }

    private function readFromFile(string $pathToFile): array
    {
        if (false === file_exists($pathToFile)) {
            throw new FileDoesNotExistsException($pathToFile);
        }

        $handle = fopen($pathToFile, 'rb');
        if (false === $handle) {
            throw new CannotReadFileException($pathToFile);
        }

        $lineCount      = 1;
        $accountNumbers = [];
        $digitMatrix    = [];
        while (false !== $line = fgets($handle)) {
            if (0 === $lineCount % self::FILE_DIGIT_LINE_HEIGHT) {
                $lineCount++;
                $accountNumbers[$lineCount / self::FILE_DIGIT_LINE_HEIGHT] = $digitMatrix;
                $digitMatrix                                               = [];
                continue;
            }

            $lineLength = strlen(rtrim($line, "\n"));
            for ($i = 0; $i < $lineLength; $i++) {
                /** matrix: split account number for digits (27/3), col, row */
                $digitMatrix[$i / self::DIGIT_MATRIX_COL_DIMENSION][$lineCount % self::FILE_DIGIT_LINE_HEIGHT][$i % self::DIGIT_MATRIX_ROW_DIMENSION] = $line[$i];
            }

            $lineCount++;
        }

        return $accountNumbers;
    }
}
