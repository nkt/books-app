<?php

use Silex\Application as BaseApplication;
use Silex\ControllerCollection;
use Silex\Provider;

require __DIR__ . '/../vendor/autoload.php';

class Application extends BaseApplication
{
    use BaseApplication\TwigTrait;
    use BaseApplication\UrlGeneratorTrait;

    public function __construct($debug = false)
    {
        parent::__construct([
            'debug' => $debug
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
            'pdo.dsn'        => 'mysql:host=localhost;dbname=books',
            'pdo.username'   => 'root',
            'pdo.class_name' => 'Flame\\Connection'
        ]);
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
