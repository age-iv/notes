<?php

namespace App\DTO\Api;

class ErrorResponse
{
    public string $error;
    public ?array $details;
    public string $timestamp;

    public function __construct(string $error, ?array $details = null)
    {
        $this->error = $error;
        $this->details = $details;
        $this->timestamp = (new \DateTimeImmutable())->format('Y-m-d\TH:i:s\Z');
    }

    public function toArray(): array
    {
        $data = [
            'error' => $this->error,
            'timestamp' => $this->timestamp,
        ];

        if ($this->details !== null) {
            $data['details'] = $this->details;
        }

        return $data;
    }
}