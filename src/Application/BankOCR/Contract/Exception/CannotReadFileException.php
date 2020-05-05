<?php declare(strict_types=1);

namespace App\Application\BankOCR\Contract\Exception;

class CannotReadFileException extends \RuntimeException
{
    private const MESSAGE = 'Cannot read the file from path';
    private string $filePath;

    public function __construct(string $filePath, ?\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 0, $previous);
        $this->filePath = $filePath;
    }
}
