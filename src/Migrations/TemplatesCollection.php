<?php

namespace Uru\BitrixMigrations;

/**
 * Class TemplatesCollection.
 */
class TemplatesCollection
{
    /**
     * Path to directory where basic templates are.
     */
    protected string $dir;

    /**
     * Array of available migration file templates.
     */
    protected array $templates = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dir = dirname(__DIR__).'/../templates';

        $this->registerTemplate([
            'name' => 'default',
            'path' => $this->dir.'/default.template',
            'description' => 'Default migration template',
        ]);
    }

    /**
     * Register basic templates.
     */
    public function registerBasicTemplates()
    {
        $templates = [
            [
                'name' => 'add_iblock',
                'path' => $this->dir.'/add_iblock.template',
                'description' => 'Add iblock',
            ],
            [
                'name' => 'add_iblock_type',
                'path' => $this->dir.'/add_iblock_type.template',
                'description' => 'Add iblock type',
            ],
            [
                'name' => 'add_iblock_element_property',
                'path' => $this->dir.'/add_iblock_element_property.template',
                'description' => 'Add iblock element property',
                'aliases' => [
                    'add_iblock_prop',
                    'add_iblock_element_prop',
                    'add_element_prop',
                    'add_element_property',
                ],
            ],
            [
                'name' => 'add_uf',
                'path' => $this->dir.'/add_uf.template',
                'description' => 'Add user field (for sections, users e.t.c)',
            ],
            [
                'name' => 'add_table',
                'path' => $this->dir.'/add_table.template',
                'description' => 'Create table',
                'aliases' => [
                    'create_table',
                ],
            ],
            [
                'name' => 'delete_table',
                'path' => $this->dir.'/delete_table.template',
                'description' => 'Drop table',
                'aliases' => [
                    'drop_table',
                ],
            ],
            [
                'name' => 'query',
                'path' => $this->dir.'/query.template',
                'description' => 'Simple database query',
            ],
        ];

        foreach ($templates as $template) {
            $this->registerTemplate($template);
        }
    }

    /**
     * Register templates for automigrations.
     */
    public function registerAutoTemplates()
    {
        $templates = [
            'add_iblock',
            'update_iblock',
            'delete_iblock',
            'add_iblock_element_property',
            'update_iblock_element_property',
            'delete_iblock_element_property',
            'add_uf',
            'update_uf',
            'delete_uf',
            'add_hlblock',
            'update_hlblock',
            'delete_hlblock',
            'add_group',
            'update_group',
            'delete_group',
        ];

        foreach ($templates as $template) {
            $this->registerTemplate([
                'name' => 'auto_'.$template,
                'path' => $this->dir.'/auto/'.$template.'.template',
            ]);
        }
    }

    /**
     * Getter for registered templates.
     */
    public function all(): array
    {
        return $this->templates;
    }

    /**
     * Dynamically register migration template.
     */
    public function registerTemplate(array $template)
    {
        $template = $this->normalizeTemplateDuringRegistration($template);

        $this->templates[$template['name']] = $template;

        $this->registerTemplateAliases($template, $template['aliases']);
    }

    /**
     * Path to the file where a template is located.
     */
    public function getTemplatePath(string $name): string
    {
        return $this->templates[$name]['path'];
    }

    /**
     * Find out template name from user input.
     */
    public function selectTemplate(?string $template): string
    {
        if (is_null($template)) {
            return 'default';
        }

        if (!array_key_exists($template, $this->templates)) {
            throw new \RuntimeException("Template \"{$template}\" is not registered");
        }

        return $template;
    }

    /**
     * Check template fields and normalize them.
     *
     * @param mixed $template
     */
    protected function normalizeTemplateDuringRegistration($template): array
    {
        if (empty($template['name'])) {
            throw new \InvalidArgumentException('Impossible to register a template without "name"');
        }

        if (empty($template['path'])) {
            throw new \InvalidArgumentException('Impossible to register a template without "path"');
        }

        $template['description'] ??= '';
        $template['aliases'] ??= [];
        $template['is_alias'] = false;

        return $template;
    }

    /**
     * Register template aliases.
     */
    protected function registerTemplateAliases(array $template, array $aliases = []): void
    {
        foreach ($aliases as $alias) {
            $template['is_alias'] = true;
            $template['name'] = $alias;
            $template['aliases'] = [];

            $this->templates[$template['name']] = $template;
        }
    }
}
