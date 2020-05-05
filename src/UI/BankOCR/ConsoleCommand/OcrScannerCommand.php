<?php declare(strict_types=1);

namespace App\UI\BankOCR\ConsoleCommand;

use App\Application\BankOCR\Contract\Exception\CannotReadFileException;
use App\Application\BankOCR\Contract\Exception\FileDoesNotExistsException;
use App\Application\BankOCR\Contract\Reader\AccountNumberReaderInterface;
use App\Domain\BankOCR\AccountNumber;
use App\Domain\BankOCR\IllegibleAccountNumber;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class OcrScannerCommand extends Command
{
    public const SUCCESS_CODE      = 0;
    public const INVALID_DATA_CODE = 1;
    public const ERROR_CODE        = 9;

    public const PATH_ARGUMENT = 'path';

    protected static $defaultName = 'ocr:scan:account_numbers';
    /**
     * @var AccountNumberReaderInterface
     */
    private AccountNumberReaderInterface $accountNumberReader;

    public function __construct(AccountNumberReaderInterface $accountNumberReader)
    {
        parent::__construct();
        $this->accountNumberReader = $accountNumberReader;
    }

    protected function configure(): void
    {
        $this
            ->addArgument(self::PATH_ARGUMENT, InputArgument::REQUIRED, 'Path to file with account numbers');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $pathToFile = $input->getArgument(self::PATH_ARGUMENT);
        $this->showQuestionOptions($output);
        try {
            $accountNumbers = $this->accountNumberReader->read($pathToFile);
        } catch (FileDoesNotExistsException|CannotReadFileException $exception) {
            $output->writeln($exception->getMessage());

            return self::INVALID_DATA_CODE;
        } catch (\Throwable $exception) {
            $output->writeln('Something went wrong');

            return self::ERROR_CODE;
        };

        $helper         = $this->getHelper('question');
        $question       = new Question("Which test case do you want to run?\n", 0);
        $testCaseAnswer = -1;
        while (0 !== $testCaseAnswer) {
            $testCaseAnswer = (int) $helper->ask($input, $output, $question);
            $this->showResult($accountNumbers, $testCaseAnswer);
        }

        return self::SUCCESS_CODE;
    }

    private function showQuestionOptions(OutputInterface $output): void
    {
        $questionChoiceOptions = [
            1 => 'Test case 1 - read account numbers',
            3 => 'Test case 3 - show illegible characters, valid checksum',
            4 => 'Test case 4 - guess illegible characters, find account number if checksum is not valid',
        ];
        foreach ($questionChoiceOptions as $key => $description) {
            $output->writeln("  $key: $description");
        }

        $output->writeln('  0: Exit');
    }

    private function showResult(array $accountNumbers, int $testCase): void
    {
        switch ($testCase) {
            case 1:
                $this->showTestCaseOne(...$accountNumbers);
                break;
            case 3:
                $this->showTestCaseThree(...$accountNumbers);
                break;
            case 4:
                $this->showTestCaseFour(...$accountNumbers);
                break;
            default:
                break;
        }
    }

    private function showTestCaseOne(AccountNumber ...$accountNumbers): void
    {
        echo "\nTest case 1 - read account numbers \n\n";
        foreach ($accountNumbers as $accountNumber) {
            $accountNumber->validChecksum();

            echo $accountNumber->getParsedAccountNumberAsString() . "\n";
        }

        echo "\nTest case ended\n\n";
    }

    private function showTestCaseThree(AccountNumber ...$accountNumbers): void
    {
        echo "\nTest case 3 - show illegible characters, valid checksum\n\n";

        foreach ($accountNumbers as $accountNumber) {
            $accountNumber->validChecksum();

            echo $accountNumber->getParsedAccountNumberAsString() . "\t" . $accountNumber->getStatus() . "\n";
        }

        echo "\nTest case ended\n\n";
    }

    private function showTestCaseFour(AccountNumber ...$accountNumbers): void
    {
        echo "\nTest case 4 - guess illegible characters, find account number if checksum is not valid\n\n";
        foreach ($accountNumbers as $accountNumber) {
            if (true === $accountNumber instanceof IllegibleAccountNumber) {
                $accountNumber = $this->accountNumberReader->guessIllegibleAccountNumber($accountNumber);
            }

            $accountNumber->validChecksum();
            if (AccountNumber::STATUS_ERROR === $accountNumber->getStatus()) {
                $accountNumber->checkSimilarNumbers();
            }

            echo $accountNumber->getParsedAccountNumberAsString() . "\t" . $accountNumber->getStatus() . "\t" . $accountNumber->getSimilarValidAccountNumbersAsString() . "\n";
        }

        echo "\nTest case ended\n\n";
    }
}
