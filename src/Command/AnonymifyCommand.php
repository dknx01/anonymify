<?php

declare(strict_types=1);

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Command;

use App\Anonymify\Column\Anonymizer;
use App\Anonymify\Db\Processor;
use App\Configuration\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'anonymify', description: 'Anonymize data')]
class AnonymifyCommand extends Command
{
    public function __construct(
        private readonly Reader $reader,
        private readonly Processor $processor,
        private readonly Anonymizer $anonymizer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Config file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Anonymify: anonymous data');
        try {
            $config = $this->reader->readConfig($input->getOption('config'));
            $io->section('anonymous whole table data');
            $this->processor->preProcess($config);
            $io->section('anonymous column data');
            $this->anonymizer->obfuscate($config);
        } catch (\JsonException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
