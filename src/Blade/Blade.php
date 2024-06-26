<?php

namespace Uru\BitrixBlade;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;

class Blade
{
    /**
     * Array of view base directories.
     */
    protected array $viewPaths;

    /**
     * Local path to blade cache storage.
     */
    protected string $cachePath;

    /**
     * Service container instance.
     */
    protected Container $container;

    /**
     * View factory instance.
     */
    protected Factory $viewFactory;

    /**
     * Constructor.
     */
    public function __construct(array $viewPaths, string $cachePath, Container $container)
    {
        $this->viewPaths = $viewPaths ?: [];
        $this->cachePath = $cachePath;
        $this->container = $container;

        $this->registerFilesystem();
        $this->registerEvents();
        $this->registerEngineResolver();
        $this->registerViewFinder();
        $this->registerFactory();
    }

    /**
     * Getter for view factory.
     */
    public function view(): Factory
    {
        return $this->viewFactory;
    }

    /**
     * Register filesystem in container.
     */
    public function registerFilesystem(): void
    {
        $this->container->singleton('files', function () {
            return new Filesystem();
        });
    }

    /**
     * Register events in container.
     */
    public function registerEvents(): void
    {
        $this->container->singleton('events', function () {
            return new Dispatcher();
        });
    }

    /**
     * Register the engine resolver instance.
     */
    public function registerEngineResolver(): void
    {
        $me = $this;

        $this->container->singleton('view.engine.resolver', function () use ($me) {
            $resolver = new EngineResolver();

            $me->registerPhpEngine($resolver);
            $me->registerBladeEngine($resolver);

            return $resolver;
        });
    }

    /**
     * Register the PHP engine implementation.
     */
    public function registerPhpEngine(EngineResolver $resolver): void
    {
        $resolver->register('php', function () {
            return new PhpEngine();
        });
    }

    /**
     * Register the Blade engine implementation.
     */
    public function registerBladeEngine(EngineResolver $resolver): void
    {
        $me = $this;
        $app = $this->container;

        $this->container->singleton('blade.compiler', function ($app) use ($me) {
            $cache = $me->cachePath;

            return new BladeCompiler($app['files'], $cache);
        });

        $resolver->register('blade', function () use ($app) {
            return new CompilerEngine($app['blade.compiler']);
        });
    }

    /**
     * Register the view factory.
     */
    public function registerFactory(): void
    {
        $resolver = $this->container['view.engine.resolver'];

        $finder = $this->container['view.finder'];

        $factory = new Factory($resolver, $finder, $this->container['events']);
        $factory->setContainer($this->container);

        // $factory->share('app', $this->container);
        $this->viewFactory = $factory;
    }

    /**
     * Register the view finder implementation.
     */
    public function registerViewFinder(): void
    {
        $me = $this;
        $this->container->singleton('view.finder', function ($app) use ($me) {
            $paths = $me->viewPaths;

            return new ViewFinder($app['files'], $paths);
        });
    }
}
