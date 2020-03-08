<?php

declare(strict_types=1);

namespace Jacanales\StatsD;

interface Connection
{
    public function connect(): void;

    public function send(string $message): void;

    public function close(): void;
}
