<?php

declare(strict_types=1);

namespace App\Support\StarterFoundation;

use Illuminate\Console\Command;

final class FoundationCommandResponse
{
    /** @param array{success: bool, code: string, message: string, data: array<string, mixed>} $payload */
    public function respond(Command $command, array $payload): int
    {
        if ($command->option('json')) {
            $command->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            foreach ($payload['data']['checks'] ?? [] as $name => $check) {
                $style = $check['status'] === 'passed' ? 'info' : 'error';
                $command->{$style}($name.': '.$check['message']);
            }

            $command->newLine();
            $command->{$payload['success'] ? 'info' : 'error'}($payload['message']);
        }

        return $payload['success'] ? Command::SUCCESS : Command::FAILURE;
    }
}
