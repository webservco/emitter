<?php

declare(strict_types=1);

namespace WebServCo\Emitter\Service;

use OverflowException;
use Override;
use Psr\Http\Message\ResponseInterface;
use WebServCo\Emitter\Contract\EmitterInterface;

use function header;
use function headers_sent;
use function in_array;
use function sprintf;

use const PHP_SAPI;

/**
 * Emit Response using PHP SAPI.
 */
final class SapiEmitter implements EmitterInterface
{
    #[Override]
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
        foreach ($response->getHeaders() as $headerName => $headerValues) {
            /**
             * Send each value as a separate header line, never joined by comma.
             * Some headers (notably `Set-Cookie`) can not be combined into a single line (RFC 9110 5.3, RFC 6265 3);
             * for all other headers, separate lines are equivalent to the combined form.
             */
            foreach ($headerValues as $headerValue) {
                header(
                    sprintf('%s: %s', $headerName, $headerValue),
                    // Do not replace a previous header with the same name.
                    false,
                );
            }
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
