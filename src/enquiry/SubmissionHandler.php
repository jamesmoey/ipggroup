<?php

namespace IPGGroup\enquiry;

use Symfony\Component\Validator\Constraints as Assert;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SubmissionHandler
{
    public function __construct(
        string $email,
        \Swift_Mailer $mailer,
        Logger $log,
        ValidatorInterface $validator,
        \Twig_Environment $renderer
    ) {
        $this->email = $email;
        $this->mailer = $mailer;
        $this->logger = $log;
        $this->validator = $validator;
        $this->renderer = $renderer;

        $this->modelContrait = new Assert\Collection([
            'name' => new Assert\NotBlank(),
            'email' => [new Assert\NotBlank(), new Assert\Email()],
            'phone' => [new Assert\NotNull(), new Assert\Callback([ $this, 'phoneValidate' ])],
            'preferred_method' => [new Assert\NotBlank(), new Assert\Choice(['phone', 'email'])],
            'enquiry' => new Assert\NotBlank(),
            'invoice' => [new Assert\NotNull(), new Assert\Length([ 'min' => 10, 'max' => 10])],
        ]);
    }

    public function phoneValidate($value, ExecutionContextInterface $context, $payload) {
        if ($payload['preferred_method'] === "phone" && !$payload['phone']) {
            $context->buildViolation('Phone is mandatory if preferred contact method is phone')
                ->atPath('phone')
                ->addViolation();
        }
    }

    public function validate(Request $request) {
        $validation = $this->validator->validate($request->request->all(), $this->modelContrait);
        if ($validation->count() !== 0) {
            return JsonResponse::create(['message' => 'Invalid Input', 'errors' => $validation ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function handle(Request $req) {
        $csvResource = fopen(__DIR__.'/../../enquiry.csv', 'a');
        if ($csvResource) {
            //lock csv to avoid corruption
            if (flock($csvResource, LOCK_EX)) {
                // so that they are always in the same order in the CSV
                $content = array_map(function($index) use ($req) {
                    return $req->request->get($index);
                }, array_keys($this->modelContrait->fields));
                fputcsv($csvResource, $content);
                fclose($csvResource);
                return JsonResponse::create([ 'message' => 'Your enquiry is saved' ]);
            }
            $this->logger->addCritical('Can not lock enquiry.csv');
            fclose($csvResource);
        }
        $this->logger->addCritical('Can not open enquiry.csv');
        return JsonResponse::create([ 'message' => 'Failed to save the enquiry' ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function sendMail(Request $request, Response $response) {
        //do not send email if there is client side error, 4xx
        if ($response->getStatusCode() < 400 || $response->getStatusCode() >= 500) {
            $message = new \Swift_Message(
                'Site Enquiry',
                $this->renderer->render('enquiry-mail.html.twig', $request->request->all())
            );
            $message
                ->setFrom(array($request->request->get('email')))
                ->setTo(array($this->email));

            try {
                if ($this->mailer->send($message) === 0) {
                    $this->logger->addError('Failed to send enquiry mail', [
                        'from' => $request->request->get('email'),
                    ]);
                    return $response;
                } else {
                    return JsonResponse::create(['message' => 'Your enquiry is send']);
                }
            } catch (\Exception $e) {
                $this->logger->addError('Exception when trying to sent email', [
                    'exception' => $e->getMessage(),
                ]);
                return $response;
            }
        }
    }
}