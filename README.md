# webservco/emitter

A PHP component/library. Please see [webservco/sandbox](https://webservco.github.io/sandbox/) for an example project using these types of components.

Sends a PSR-7 response to the client, with its status line, headers and body.

- Related: `webservco/application-runner` (emits the response after the request is handled)

---

## Installation

```shell
composer require webservco/emitter
```

---

## Usage

```php
use WebServCo\Emitter\Service\SapiEmitter;
use WebServCo\Emitter\Service\StackEmitter;

// \WebServCo\Emitter\Contract\EmitterInterface
$emitter = new StackEmitter([new SapiEmitter()]);
$emitter->emit($response);
```

`StackEmitter` tries each emitter in order and stops at the first one that returns `true`. Add your own emitter to the list for environments that `SapiEmitter` doesn't handle.

---

## Contracts

| Interface | Implementation | Purpose |
| --- | --- | --- |
| `Contract\EmitterInterface` | `Service\SapiEmitter` | Sends the response through the PHP SAPI (`header()` and `echo`). |
| | `Service\StackEmitter` | Tries a list of emitters until one succeeds. |

Namespaces are relative to `WebServCo\Emitter\`.

---

## Notes

Under the `cli` and `cgi-fcgi` SAPIs, `SapiEmitter` returns `false` and leaves the response to the next emitter in the stack. If headers were already sent, it throws an `OverflowException`.

`StackEmitter` throws an `UnderflowException` when no emitter handles the response.

A header with several values is sent as one line, with the values joined by `, `.

---

## Development

```shell
# Start project.
ddev start
# Install dependencies.
ddev c u
# Run all validations.
ddev c c
# Run unit tests.
ddev c test:dox
```

---

## License

[MIT](LICENSE.md)
