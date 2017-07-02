<?php

namespace IPGGroup\middleware;

use Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Security
{
    public static function nonceValidator(Request $request, \IPGGroup\Application $app)
    {
        $valid = $app->validateToken('nonce', $request->request->get('nonce'));
        if (!$valid) {
            $app->log('Invalid nonce', [
                'request_ip' => $request->getClientIp(),
                'nonce' => $request->request->get('nonce')
            ], Logger::WARNING);
            return JsonResponse::create([ 'message' => 'Invalid nonce' ], Response::HTTP_UNAUTHORIZED);
        }
        $request->request->remove('nonce');
    }
}