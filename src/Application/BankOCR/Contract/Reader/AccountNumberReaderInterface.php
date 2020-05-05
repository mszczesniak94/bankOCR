<?php declare(strict_types=1);

namespace App\Application\BankOCR\Contract\Reader;

use App\Domain\BankOCR\AccountNumber;
use App\Domain\BankOCR\IllegibleAccountNumber;

interface AccountNumberReaderInterface
{
    /**
     * @param string $pathToFile
     * @return iterable|AccountNumber[]
     */
    public function read(string $pathToFile): iterable;

    public function guessIllegibleAccountNumber(IllegibleAccountNumber $illegibleAccountNumber): AccountNumber;
}
