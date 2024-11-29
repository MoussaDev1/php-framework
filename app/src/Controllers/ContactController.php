<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\ContactForm;

class ContactController extends AbstractController
{
    public function process(Request $request): Response
    {

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
        $ContactForm = new ContactForm(
            $body['email'],
            $body['subject'],
            $body['message'],
            $timestamp,
            $timestamp
        );

        $filename = sprintf('%s_%s.json', $timestamp, $ContactForm->getEmail());
        $filepath = __DIR__ . '/../../src/var/contact/' . $filename;


        if (!file_put_contents($filepath, json_encode($ContactForm->toArray()))) {
            return new Response(
                json_encode(["error" => "Invalid JSON"]),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        return new Response(
            json_encode(['file' => date('Y-m-d_H-i-s', $timestamp) . '_' . $ContactForm->getEmail()]),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function fetch(): Response
    {
        $repositorypath = __DIR__ . '/../../src/var/contact/';
        $files = glob($repositorypath . '*.json');
        $contact = [];

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);

            if (isset($data['email'], $data['subject'], $data['message'], $data['dateOfCreation'], $data['dateOfUpdate'])) {
                $contact[] = new ContactForm(
                    $data['email'],
                    $data['subject'],
                    $data['message'],
                    $data['dateOfCreation'],
                    $data['dateOfUpdate']
                );
            }
        }

        $contactsArray = [];
        foreach ($contact as $contacts) {
            $contactsArray[] = $contacts->toArray();
        }

        return new Response(
            json_encode($contactsArray),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function fetchOne(string $filename): Response
    {
        $filepath = __DIR__ . '/../../src/var/contact/' . $filename . '.json';

        if (!file_exists($filepath)) {
            return new Response(
                json_encode(["error" => "Contact not found"]),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $content = file_get_contents($filepath);
        $data = json_decode($content, true);

        $contactForm = new ContactForm(
            $data['email'],
            $data['subject'],
            $data['message'],
            $data['dateOfCreation'],
            $data['dateOfUpdate']
        );
        return new Response(json_encode($contactForm->toArray()), 200, ['Content-Type' => 'application/json']);
    }

    public function update(string $filename): Response
    {
        $filePath = __DIR__ . '/../../src/var/contact/' . $filename . '.json';

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

        $contactForm = new ContactForm(
            $existingContent['email'],
            $existingContent['subject'],
            $existingContent['message'],
            $existingContent['dateOfCreation'],
            $existingContent['dateOfUpdate']
        );

        foreach ($body as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($contactForm, $setter)) {
                $contactForm->$setter($value);
            }
        }

        $contactForm->setDateOfUpdate(time());

        file_put_contents($filePath, json_encode($existingContent, JSON_PRETTY_PRINT));

        return new Response(
            json_encode($existingContent),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function delete(string $filename): Response
    {
        $filePath = __DIR__ . '/../../src/var/contact/' . $filename . '.json';

        if (!file_exists($filePath)) {
            return new Response(
                json_encode(["error" => "Contact not found"]),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        if (isset($filePath)) {
            unlink($filePath);
        }
        return new Response($filename, '204', ['Content-Type' => 'application/json']);
    }
}
