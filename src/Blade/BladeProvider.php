<?php

namespace Uru\BitrixBlade;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\Factory;
use RuntimeException;

/**
 * Class BladeProvider
 * @package Uru\BitrixBlade
 */
class BladeProvider
{
    /**
     * Path to a folder view common view can be stored.
     *
     * @var string
     */
    protected static string $baseViewPath;

    /**
     * Local path to blade cache storage.
     *
     * @var string
     */
    protected static string $cachePath;

    /**
     * View factory.
     *
     * @var Factory
     */
    protected static Factory $viewFactory;

    /**
     * Service container factory.
     *
     * @var Container
     */
    protected static Container $container;

    /**
     * Register blade engine in Bitrix.
     *
     * @param string $baseViewPath
     * @param string $cachePath
     */
    public static function register(string $baseViewPath = 'local/views', string $cachePath = 'bitrix/cache/blade'): void
    {
        static::$baseViewPath = static::isAbsolutePath($baseViewPath) ? $baseViewPath : $_SERVER['DOCUMENT_ROOT'].'/'.$baseViewPath;
        static::$cachePath = static::isAbsolutePath($cachePath) ? $cachePath : $_SERVER['DOCUMENT_ROOT'].'/'.$cachePath;
        static::instantiateServiceContainer();
        static::instantiateViewFactory();
        static::registerBitrixDirectives();

        global $arCustomTemplateEngines;
        $arCustomTemplateEngines['blade'] = [
            'templateExt' => ['blade'],
            'function'    => 'renderBladeTemplate',
        ];
    }

    /**
     * @param $path
     * @return bool
     */
    protected static function isAbsolutePath($path): bool
    {
        return $path && ($path[0] === DIRECTORY_SEPARATOR || preg_match('~\A[A-Z]:(?![^/\\\\])~i', $path) > 0);
    }

    /**
     * Get view factory.
     *
     * @return Factory
     */
    public static function getViewFactory(): Factory
    {
        return static::$viewFactory;
    }

    /**
     * @return BladeCompiler
     */
    public static function getCompiler(): BladeCompiler
    {
        return static::$container['blade.compiler'];
    }

    /**
     * Update paths where blade tries to find additional views.
     *
     * @param string $templateDir
     */
    public static function addTemplateFolderToViewPaths(string $templateDir): void
    {
        $finder = Container::getInstance()->make('view.finder');

        $currentPaths = $finder->getPaths();
        $newPaths = [$_SERVER['DOCUMENT_ROOT'].$templateDir];

        // Полностью перезаписывать пути нельзя, иначе вложенные компоненты + include перестанут работать.
        $newPaths = array_values(array_unique(array_merge($newPaths, $currentPaths)));
        if (!in_array(static::$baseViewPath, $newPaths)) {
            $newPaths[] = static::$baseViewPath;
        }

        // Необходимо очистить внутренний кэш ViewFinder-а
        // Потому что иначе если в родительском компоненте есть @include('foo'), то при вызове @include('foo') из дочернего,
        // он не будет искать foo в дочернем, а сразу подключит foo из родительского компонента
        $finder->flush();

        $finder->setPaths($newPaths);
    }

    /**
     * Undo addTemplateFolderToViewPaths
     *
     * @param string $templateDir
     * @throws BindingResolutionException
     */
    public static function removeTemplateFolderFromViewPaths(string $templateDir): void
    {
        $finder = Container::getInstance()->make('view.finder');
        $currentPaths = $finder->getPaths();
        $finder->setPaths(array_diff($currentPaths, [$_SERVER['DOCUMENT_ROOT'].$templateDir] ));

        // Необходимо очистить внутренний кэш ViewFinder-а
        // Потому что иначе если в дочернем компоненте есть @include('foo'), то при вызове @include('foo') в родительском
        // после подключения дочернего,
        // он не будет искать foo в родительском, а сразу подключит foo из дочернего компонента
        $finder->flush();
    }

    /**
     * Instantiate service container if it's not instantiated yet.
     */
    protected static function instantiateServiceContainer(): void
    {
        $container = Container::getInstance();

        if (!$container) {
            $container = new Container();
            Container::setInstance($container);
        }

        static::$container = $container;
    }

    /**
     * Instantiate view factory.
     */
    protected static function instantiateViewFactory(): void
    {
        static::createDirIfNotExist(static::$baseViewPath);
        static::createDirIfNotExist(static::$cachePath);

        $viewPaths = [
            static::$baseViewPath,
        ];
        $cache = static::$cachePath;

        $blade = new Blade($viewPaths, $cache, static::$container);

        static::$viewFactory = $blade->view();
        static::$viewFactory->addExtension('blade', 'blade');
    }

    /**
     * Create dir if it does not exist.
     *
     * @param string $path
     */
    protected static function createDirIfNotExist(string $path): void
    {
        if (!file_exists($path)) {
            $mask = umask(0);
            if (!mkdir($path, 0777, true) && !is_dir($path)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
            umask($mask);
        }
    }

    /**
     * Register bitrix directives.
     */
    protected static function registerBitrixDirectives(): void
    {
        $compiler = static::getCompiler();

        $endIf = function () {
            return '<?php endif; ?>';
        };

        $compiler->directive('component', function ($expression) {
            $expression = rtrim($expression, ')');
            $expression = ltrim($expression, '(');

            return '<?php $APPLICATION->IncludeComponent('.$expression.'); ?>';
        });

        $compiler->directive('bxComponent', function ($expression) {
            $expression = rtrim($expression, ')');
            $expression = ltrim($expression, '(');

            return '<?php $APPLICATION->IncludeComponent('.$expression.'); ?>';
        });

        $compiler->directive('block', function ($expression) {
            $expression = rtrim($expression, ')');
            $expression = ltrim($expression, '(');

            return '<?php ob_start(); $__bx_block = ' . $expression . '; ?>';
        });

        $compiler->directive('endblock', function () {
            return '<?php $APPLICATION->AddViewContent($__bx_block, ob_get_clean()); ?>';
        });

        $compiler->directive('lang', function ($expression) {
            return '<?= Bitrix\Main\Localization\Loc::getMessage('.$expression.') ?>';
        });

        $compiler->directive('auth', function () {
            return '<?php if($USER->IsAuthorized()): ?>';
        });
        $compiler->directive('guest', function () {
            return '<?php if(!$USER->IsAuthorized()): ?>';
        });
        $compiler->directive('admin', function () {
            return '<?php if($USER->IsAdmin()): ?>';
        });
        $compiler->directive('csrf', function ($name = 'sessid') {
            $name = !empty($name) ? $name : 'sessid';
            $name = trim($name, '"');
            $name = trim($name, "'");
            return '<input type="hidden" name="'.$name.'" value="<?= bitrix_sessid() ?>" />';
        });

        $compiler->directive('endauth', $endIf);
        $compiler->directive('endguest', $endIf);
        $compiler->directive('endadmin', $endIf);

        static::registerHermitageDirectives($compiler);
    }

    /**
     * @param BladeCompiler $compiler
     */
    private static function registerHermitageDirectives(BladeCompiler $compiler): void
    {
        $simpleDirectives = [
            'actionAddForIBlock' => 'addForIBlock',
        ];
        foreach ($simpleDirectives as $directive => $action) {
            $compiler->directive($directive, function ($expression) use ($action) {
                $expression = rtrim($expression, ')');
                $expression = ltrim($expression, '(');
                return '<?php \Uru\BitrixHermitage\Action::' . $action . '($template, ' . $expression . '); ?>';
            });
        }

        $echoDirectives = [
            'actionEditIBlockElement' => 'editIBlockElement',
            'actionDeleteIBlockElement' => 'deleteIBlockElement',
            'actionEditAndDeleteIBlockElement' => 'editAndDeleteIBlockElement',

            'actionEditIBlockSection' => 'editIBlockSection',
            'actionDeleteIBlockSection' => 'deleteIBlockSection',
            'actionEditAndDeleteIBlockSection' => 'editAndDeleteIBlockSection',

            'actionEditHLBlockElement' => 'editHLBlockElement',
            'actionDeleteHLBlockElement' => 'deleteHLBlockElement',
            'actionEditAndDeleteHLBlockElement' => 'editAndDeleteHLBlockElement',
        ];
        foreach ($echoDirectives as $directive => $action) {
            $compiler->directive($directive, function ($expression) use ($action) {
                $expression = rtrim($expression, ')');
                $expression = ltrim($expression, '(');
                return '<?= \Uru\BitrixHermitage\Action::' . $action . '($template, ' . $expression . '); ?>';
            });
        }
    }
}
