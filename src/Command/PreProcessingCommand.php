<?php

declare(strict_types=1);

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Command;

use App\Configuration\Reader;
use App\PreProcessing\PreProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'obfuscate:pre-processing', description: 'Pre-Processing data')]
class PreProcessingCommand extends Command
{
    public function __construct(
        private readonly Reader $reader,
        private readonly PreProcessor $preProcessor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Pre-processing config file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Pre-Processing data');
        try {
            $config = $this->reader->readConfig($input->getOption('config'));
            $this->preProcessor->preProcess($config);
        } catch (\JsonException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
