<?php

use \IPGGroup\Application;
use \Symfony\Component\HttpFoundation\Request;

if ('dev' === getenv('environment')) {
    $filename = __DIR__ . '/../public/' . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
    if (php_sapi_name() === 'cli-server' && is_file($filename)) {
        return false;
    }
}

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();
$app['enquiry_forward_email_address'] = getenv('receiver_email');

if ('dev' === getenv('environment')) {
    $app['debug'] = true;
    $app['enquiry_forward_email_address'] = 'jamesmoey@gmail.com';
}

/**
 * Route Controller
 */
$app->get('/', function() use($app) {
    return $app->render('index.html.twig', [
        'nonce' => $app->generateToken('nonce')
    ]);
});

$enquiry = new \IPGGroup\enquiry\SubmissionHandler(
    $app['enquiry_forward_email_address'],
    $app['mailer'],
    $app['monolog'],
    $app['validator'],
    $app['twig']
);
$app->post('/submit', [$enquiry, 'handle'])
    ->before(['\IPGGroup\middleware\Security', 'nonceValidator'])
    ->before([$enquiry, 'validate'])
    ->after([$enquiry, 'sendMail']);

$app->run();