<?php

namespace Uru\BitrixMigrations\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractCommand.
 */
abstract class AbstractCommand extends Command
{
    protected InputInterface $input;

    protected OutputInterface $output;

    /**
     * Configures the current command.
     */
    protected function abort(string $message = ''): \DomainException
    {
        if ($message) {
            $this->error($message);
        }

        $this->error('Abort!');

        throw new \DomainException();
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->input = $input;
        $this->output = $output;

        try {
            $this->fire();

            return 0;
        } catch (\DomainException $e) {
            return 1;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->error('Abort!');

            return $e->getCode();
        }
    }

    /**
     * Echo an error message.
     */
    protected function error(string $message): void
    {
        $this->output->writeln("<error>{$message}</error>");
    }

    /**
     * Echo an info.
     */
    protected function info(string $message): void
    {
        $this->output->writeln("<info>{$message}</info>");
    }

    /**
     * Echo a message.
     */
    protected function message(string $message): void
    {
        $this->output->writeln($message);
    }

    /**
     * Execute the console command.
     */
    abstract protected function fire();
}
