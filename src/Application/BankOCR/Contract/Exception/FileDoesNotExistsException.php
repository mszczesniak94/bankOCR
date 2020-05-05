<?php declare(strict_types=1);

namespace App\Application\BankOCR\Contract\Exception;

class FileDoesNotExistsException extends \RuntimeException
{
    private const MESSAGE = 'File from path does not exists';
    private string $filePath;

    public function __construct(string $filePath, ?\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 0, $previous);
        $this->filePath = $filePath;
    }
}
