<?php

declare(strict_types=1);

namespace WebServCo\Emitter\Service;

use Psr\Http\Message\ResponseInterface;
use UnderflowException;
use WebServCo\Emitter\Contract\EmitterInterface;

/**
 * Emit reponse by using a stack of emitters.
 *
 * Since expected use case implies only a few emitters,
 * the stack is initialized as part of the constructor and not with separate methods.
 */
final class StackEmitter implements EmitterInterface
{
    /**
     * @param array<int,\WebServCo\Emitter\Contract\EmitterInterface> $emitters
     */
    public function __construct(private array $emitters)
    {
    }

    public function emit(ResponseInterface $response): bool
    {
        // Call all emitters in stack until one of them returns true.

        foreach ($this->emitters as $emitter) {
            $result = $emitter->emit($response);
            if ($result) {
                return $result;
            }
        }

        throw new UnderflowException('No emitter was able to emit the response.');
    }
}
