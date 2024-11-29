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
        $filepath = __DIR__ . '/../../src/var/contact/';
        $files = glob($filepath . '*.json');
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

        $contactsArray = array_map(fn($contact) => $contact->toArray(), $contact);

        return new Response(
            json_encode($contactsArray),
            200,
            ['Content-Type' => 'application/json']
        );
    }
}
