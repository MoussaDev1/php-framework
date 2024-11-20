<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

class ContactController extends AbstractController
{
    public function process(Request $request): Response
    {
        return $this->handleRequest($request);
    }


    public function handleRequest(Request $request): Response
    {
        if ($request->getMethod() !== 'POST') {
            return (new Response())
                ->setStatus(400)
                ->setContent('Method Not Allowed');
        }

        $body = json_decode(file_get_contents('php://input'), true);

        if (!isset($body['email'], $body['subject'], $body['message'])) {
            return (new Response())
                ->setStatus(400)
                ->setContent('Invalid Request');
        }

        $timestamp = time();
        $filename = sprintf('%s_%s.json', $timestamp, $body['email']);
        $filepath = __DIR__ . '/../../var/contact/' . $filename;

        $ContactFrom = [
            'email' => $body['email'],
            'subject' => $body['subject'],
            'message' => $body['message'],
            'dateOfCreation' => $timestamp,
            'dateOfUpdate' => $timestamp,
        ];

        if (!file_put_contents($filepath, json_encode($ContactFrom))) {
            return (new Response())
                ->setStatus(400)
                ->setContent('Failed to save contact form');
        }

        $responseContent = json_encode([
            'file' => date('Y-m-d_H-i-s', $timestamp) . '_' . $body['email']
        ]);

        return (new Response())
            ->setStatus(201)
            ->setContent($responseContent);
    }
}
