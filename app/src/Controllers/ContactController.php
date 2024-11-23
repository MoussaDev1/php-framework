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

        if ($request->getMethod() === 'PATCH') {
            if (isset($params['filename'])) {
                return $this->update($params['filename']);
            }
        }

        if ($request->getMethod() === 'DELETE') {
            if (isset($params['filename'])) {
                return $this->update($params['filename']);
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

    public function update(string $filename): Response
    {
        $filePath = __DIR__ . '/../../var/contact/' . $filename . '.json';

        if (!file_exists($filePath)) {
            return new Response(
                json_encode(["error" => "Contact not found"]),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $goodBody = ['email', 'subject', 'message'];
        $body = json_decode(file_get_contents('php://input'), true);

        foreach ($body as $rows => $value) {
            if (!in_array($rows, $goodBody) || $value = null) {
                return new Response(
                    json_encode(["error" => "Invalid or empty fields"]),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }
        }

        $existingContent = json_decode(file_get_contents($filePath), true);

        foreach ($body as $row => $value) {
            $existingContent[$row] = $value;
        }

        $existingContent['dateOfLastUpdate'] = time();

        file_put_contents($filePath, json_encode($existingContent, JSON_PRETTY_PRINT));

        return new Response(
            json_encode($existingContent),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function delete(Request $request, string $filename): Response
    {
        $filePath = __DIR__ . '/../../var/contact/' . $filename . '.json';

        if (!file_exists($filePath)) {
            return new Response(
                json_encode(["error" => "Contact not found"]),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        if (unlink($filePath)) {
            return new Response('', 204);
        }

        return new Response(
            json_encode(["error" => "Unable to delete the contact"]),
            404,
            ['Content-Type' => 'application/json']
        );
    }
}
