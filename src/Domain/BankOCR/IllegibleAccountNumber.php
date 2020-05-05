<?php declare(strict_types=1);

namespace App\Domain\BankOCR;

use App\Infrastructure\BankOCR\Reader\FileAccountNumberReader;

class IllegibleAccountNumber extends AccountNumber
{
    protected string $status = 'ILL';

    public function validChecksum(): void
    {
    }
}
