<?php

declare(strict_types=1);

namespace WebServCo\Emitter\Service;

use OverflowException;
use Psr\Http\Message\ResponseInterface;
use WebServCo\Emitter\Contract\EmitterInterface;

use function header;
use function headers_sent;
use function implode;
use function in_array;
use function sprintf;

use const PHP_SAPI;

/**
 * Emit Response using PHP SAPI.
 */
final class SapiEmitter implements EmitterInterface
{
    public function emit(ResponseInterface $response): bool
    {
        // Check if we are actually able to emit the response.
        if (in_array(PHP_SAPI, ['cli', 'cgi-fcgi'], true)) {
           /**
            * Simply return false.
            * Ideally a stack implementation is used and another emitter will handle the response.
            */
            return false;
        }

        // Check if output already started.
        if (headers_sent()) {
            throw new OverflowException('Headers have already been sent.');
        }

        // All is in order, go ahead with functionality.

        $this->sendStatusLine($response);

        $this->sendHeaders($response);

        $this->echoBody($response);

        // Since successful return true so that other eventual handlers in stack will not also attempt to handle.
        return true;
    }

    private function echoBody(ResponseInterface $response): void
    {
        // Cast to string because getBody returns a `\Psr\Http\Message\StreamInterface`.
        echo (string) $response->getBody();
    }

    private function sendHeaders(ResponseInterface $response): void
    {
        foreach ($response->getHeaders() as $headerName => $headerData) {
            header(
                sprintf('%s: %s', $headerName, implode(', ', $headerData)),
                // Replace a previous similar header.
                false,
            );
        }
    }

    private function sendStatusLine(ResponseInterface $response): void
    {
        header(
            sprintf(
                'HTTP/%s %d %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            ),
            // Replace a previous similar header.
            true,
        );
    }
}
