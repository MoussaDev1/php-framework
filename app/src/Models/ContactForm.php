<?php

namespace App\Models;

class ContactForm
{
    private string $email;
    private string $subject;
    private string $message;
    private int $dateOfCreation;
    private int $dateOfUpdate;


    public function __construct(string $email, string $subject, string $message, int $dateOfCreation, int $dateOfUpdate)
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->message = $message;
        $this->dateOfCreation = $dateOfCreation;
        $this->dateOfUpdate = $dateOfUpdate;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
    public function getDateOfCreation(): int
    {
        return $this->dateOfCreation;
    }

    public function getDateOfUpdate(): int
    {
        return $this->dateOfUpdate;
    }

    //-------

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    public function setDateOfCreation(int $dateOfCreation)
    {
        $this->dateOfCreation = $dateOfCreation;
    }

    public function setDateOfUpdate(int $dateOfUpdate)
    {
        $this->dateOfUpdate = $dateOfUpdate;
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
            'dateOfCreation' => $this->dateOfCreation,
            'dateOfUpdate' => $this->dateOfUpdate,
        ];
    }
}
