<?php

namespace Uru\BitrixMigrations\Commands;

use DomainException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractCommand
 * @package Uru\BitrixMigrations\Commands
 */
abstract class AbstractCommand extends Command
{
    /**
     * @var InputInterface
     */
    protected InputInterface $input;

    /**
     * @var OutputInterface
     */
    protected OutputInterface $output;

    /**
     * Configures the current command.
     *
     * @param string $message
     */
    protected function abort(string $message = ''): DomainException
    {
        if ($message) {
            $this->error($message);
        }

        $this->error('Abort!');

        throw new DomainException();
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->input = $input;
        $this->output = $output;

        try {
            $this->fire();
            return 0;
        } catch (DomainException $e) {
            return 1;
        } catch (Exception $e) {
            $this->error($e->getMessage());
            $this->error('Abort!');

            return $e->getCode();
        }
    }

    /**
     * Echo an error message.
     *
     * @param string $message
     */
    protected function error(string $message): void
    {
        $this->output->writeln("<error>$message</error>");
    }

    /**
     * Echo an info.
     *
     * @param string $message
     */
    protected function info(string $message): void
    {
        $this->output->writeln("<info>$message</info>");
    }

    /**
     * Echo a message.
     *
     * @param string $message
     */
    protected function message(string $message): void
    {
        $this->output->writeln($message);
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    abstract protected function fire();
}
