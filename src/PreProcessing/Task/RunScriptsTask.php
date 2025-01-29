<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\PreProcessing\Task;

use App\Configuration\Processing;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

#[WithMonologChannel('obfuscate')]
readonly class RunScriptsTask implements Task
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%/data')] private string $path,
    ) {
    }

    public function run(Processing $config): void
    {
        $this->logger->info('Run scripts');
        $fs = new Filesystem();
        foreach ($config->scripts as $scriptPath) {
            $this->logger->debug(sprintf('Script path: %s', $scriptPath));

            if ($fs->exists(sprintf('%s/%s', $this->path, $scriptPath))) {
                $sql = $fs->readFile(sprintf('%s/%s', $this->path, $scriptPath));
                $this->entityManager->getConnection()->executeQuery($sql);
            }
        }
    }

    public static function getPriority(): int
    {
        return 50;
    }
}
