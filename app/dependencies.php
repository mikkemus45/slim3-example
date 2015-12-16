<?php namespace trt\loraweather;

use PDO;

$container = $app->getContainer();

$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig(
        $c['settings']['view']['template_path']
    );

    $view->addExtension(new \Slim\Views\TwigExtension(
        $c->get('router'),
        $c->get('request')->getUri()
    ));

    $view->addExtension(new \Twig_Extension_Debug());

    return $view;
};

$container['pdo'] = function ($c) {
    try {
        $db = new \PDO('sqlite:' . __DIR__ . '/../database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        echo 'Error: ';
        echo $e->getMessage();
        die();
    }

    return $db;
};

$container['trt\loraweather\SensorDataService'] = function ($c) {
    return new SensorDataService(
        $c['pdo']
    );
};

$container['trt\loraweather\Home'] = function ($c) {
    return new Home(
        $c['view'],
        $c['trt\loraweather\SensorDataService']
    );
};

$container['trt\loraweather\ApiController'] = function ($c) {
    return new ApiController(
        $c['trt\loraweather\SensorDataService']
    );
};