<?php

declare(strict_types=1);

namespace WebServCo\Emitter\Contract;

use Psr\Http\Message\ResponseInterface;

interface EmitterInterface
{
    /**
     * Emit a response.
     *
     * Returns boolean in order to be able to be used as a stack handler.
     */
    public function emit(ResponseInterface $response): bool;
}
