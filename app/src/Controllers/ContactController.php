<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;

class ContactController extends AbstractController
{
    public function process(Request $request, array $params = []): Response
    {
        if ($request->getMethod() === 'POST') {
            return $this->create($request);
        }

        if ($request->getMethod() === 'GET') {
            if (isset($params['filename'])) {
                return $this->fetchone($params['filename']);
            } else {
                return $this->fetch();
            }
        }
        return new Response('Methode now Allowed', 405);
    }
    public function create(Request $request): Response
    {
        if ($request->getMethod() !== 'POST' && $request->getHeaders()['Content-Type'] !== 'application/json') {
            return new Response(
                json_encode(["error" => "Invalid Method or Content-Type"]),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        $body = json_decode(file_get_contents('php://input'), true);

        if (!isset($body['email'], $body['subject'], $body['message'])) {
            return new Response(
                json_encode(["error" => "Invalid Enter Data"]),
                400,
                ['Content-Type' => 'application/json']
            );
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
            return new Response(
                json_encode(["error" => "Invalid JSON"]),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        return new Response(
            json_encode(['file' => date('Y-m-d_H-i-s', $timestamp) . '_' . $body['email']]),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function fetch(): Response
    {
        $directory = __DIR__ . '/../../var/contact/';
        $files = glob($directory . '*.json');
        $contact = [];

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $contact[] = $data;
            }
        }

        return new Response(
            json_encode($contact),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function fetchone(string $filename): Response
    {
        $filePath = __DIR__ . '/../../var/contact/' . $filename . '.json';

        if (!file_exists($filePath)) {
            return new Response(
                json_encode(["error" => "Contact not found"]),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $content = file_get_contents($filePath);
        return new Response($content, 200, ['Content-Type' => 'application/json']);
    }

    private function response(array $body, int $status): void
    {
        header('Content-Type: application/json', true, $status);
        echo json_encode($body);
    }
}
