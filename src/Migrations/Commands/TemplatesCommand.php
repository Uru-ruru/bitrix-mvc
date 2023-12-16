<?php

namespace Uru\BitrixMigrations\Commands;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Uru\BitrixMigrations\TemplatesCollection;

class TemplatesCommand extends AbstractCommand
{
    /**
     * TemplatesCollection instance.
     *
     * @var TemplatesCollection
     */
    protected $collection;

    protected static $defaultName = 'templates';

    /**
     * Constructor.
     */
    public function __construct(TemplatesCollection $collection, ?string $name = null)
    {
        $this->collection = $collection;

        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Show the list of available migration templates');
    }

    /**
     * Execute the console command.
     */
    protected function fire(): void
    {
        $table = new Table($this->output);
        $table->setHeaders(['Name', 'Path', 'Description'])->setRows($this->collectRows());
        $table->setStyle('borderless');
        $table->render();
    }

    /**
     * Collect and return templates from a Migrator.
     */
    protected function collectRows(): array
    {
        $rows = collect($this->collection->all())
            ->filter(function ($template) {
                return false == $template['is_alias'];
            })
            ->sortBy('name')
            ->map(function ($template) {
                $row = [];

                $names = array_merge([$template['name']], $template['aliases']);
                $row[] = implode("\n/ ", $names);
                $row[] = wordwrap($template['path'], 65, "\n", true);
                $row[] = wordwrap($template['description'], 25, "\n", true);

                return $row;
            })
        ;

        return $this->separateRows($rows);
    }

    /**
     * Separate rows with a separator.
     *
     * @param mixed $templates
     */
    protected function separateRows($templates): array
    {
        $rows = [];
        foreach ($templates as $template) {
            $rows[] = $template;
            $rows[] = new TableSeparator();
        }
        unset($rows[count($rows) - 1]);

        return $rows;
    }
}
