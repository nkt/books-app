<?php

use Silex\Application as BaseApplication;
use Silex\ControllerCollection;
use Silex\Provider;

require __DIR__ . '/../vendor/autoload.php';

class Application extends BaseApplication
{
    use BaseApplication\TwigTrait;
    use BaseApplication\UrlGeneratorTrait;

    public function __construct()
    {
        $config = array_replace_recursive(
            json_decode(file_get_contents(__DIR__ . '/config/config.example.json'), true),
            json_decode(file_get_contents(__DIR__ . '/config/config.json'), true)
        );
        parent::__construct([
            'debug'  => $config['debug'],
            'config' => $config
        ]);
        $this->registerServices();
    }

    private function registerServices()
    {
        $this->register(new Provider\UrlGeneratorServiceProvider());
        $this->register(new Provider\TwigServiceProvider(), [
            'twig.path'    => __DIR__ . '/views',
            'twig.options' => [
                'cache'       => __DIR__ . '/cache/twig',
                'auto_reload' => true
            ]
        ]);
        $this->register(new Provider\PDOServiceProvider(), [
            'pdo.dsn'        => $this['config']['database']['dsn'],
            'pdo.username'   => $this['config']['database']['username'],
            'pdo.password'   => $this['config']['database']['password'],
            'pdo.class_name' => 'Flame\\Connection'
        ]);
        $this->register(new Provider\ServiceControllerServiceProvider());
        $this->register(new Provider\WebProfilerServiceProvider(), array(
            'profiler.cache_dir' => __DIR__ . '/cache/profiler',
        ));
    }

    /**
     * @param string $prefix
     *
     * @return ControllerCollection
     */
    public function createController($prefix)
    {
        $controller = $this['controllers_factory'];
        $this->mount($prefix, $controller);

        return $controller;
    }

    /**
     * @return \Flame\Connection
     */
    public function getConnection()
    {
        return $this['db'];
    }
}
