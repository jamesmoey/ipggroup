<?php

namespace IPGGroup;

use Silex\Application\MonologTrait;
use Silex\Application\SwiftmailerTrait;
use Silex\Application\TwigTrait;
use Silex\Provider\CsrfServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Security\Csrf\CsrfToken;

class Application extends \Silex\Application {

    use TwigTrait;
    use MonologTrait;
    use SwiftmailerTrait;

    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->register(new CsrfServiceProvider());

        $this->register(new TwigServiceProvider(), [
            'twig.path' => __DIR__.'/views',
        ]);

        $this->register(new MonologServiceProvider(), [
            'monolog.logfile' => 'php://stdout',
        ]);

        $this->register(new SwiftmailerServiceProvider(), [
            'swiftmailer.use_spool' => false,
        ]);

        $this->register(new ValidatorServiceProvider());
    }

    public function generateToken(string $name) {
        return $this['csrf.token_manager']->getToken($name);
    }

    public function validateToken(string $name, string $token) {
        return $this['csrf.token_manager']->isTokenValid(new CsrfToken($name, $token));
    }
}