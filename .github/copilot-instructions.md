# Copilot Instructions

## Test/Lint/Analyze
After every code change, run `./project unit`, `./project sniffer`, and `./project analyzer`.

## Architecture
This is a modular, domain-driven PHP platform with multiple bounded contexts that communicate via messaging.

### Bounded Contexts
Every directory in `src/` except `Common` is a bounded context in the gaming platform domain.
`Common` contains shared libraries (Bus, EventStore, Timer, etc.).

Architectural patterns vary per context but must stay consistent within a context. Do not change a context's
pattern unless explicitly asked to refactor.

### UI Composition
The UI is composed using SSI (Server Side Includes) and Custom Elements. SSI renders server-side fragments from
different contexts into a page. Custom Elements encapsulate client-side behavior. Both serve as transclusion
boundaries between contexts.

## Conventions

### Wiring
All services are explicitly wired in `config/` YAML files. There is no autowiring.

### Exceptions
The context/aggregate base exception type (e.g., `ChallengeException`) must extend `DomainException`. Use named
constructors on that base exception rather than individual exception classes:
```php
class ChallengeException extends DomainException
{
    public static function notFound(): self
    {
        return new self(new Violations(new Violation('challenge_not_found')));
    }

    public static function alreadyClosed(): self
    {
        return new self(new Violations(new Violation('challenge_already_closed')));
    }
}
```
When an exception is explicitly caught to drive alternate control flow, use a dedicated exception class that is
`final` and extends the context base (e.g., `final class ChallengeNotFoundException extends ChallengeException`).
Every domain exception must populate `Violations` (and any parameters) so error translation is always available.

### Cleanup
Remove unused code introduced by changes (classes, methods, config, assets) unless explicitly asked to keep it.

### Tests
- Unit tests mirror the `src/` structure in `tests/unit/`
- Use PHPUnit attributes: `#[Test]`
- Test namespace: `Gaming\Tests\Unit\{Context}\...`
- For domain exceptions, assert via `DomainAssert::expectViolation` with the expected identifier,
  violation parameters as `['name' => value]`, and optionally the concrete exception class.
  Use `expectException` only for non-domain exceptions.
- The closure passed to `DomainAssert::expectViolation` must contain only the single action that
  triggers the exception. All setup (creating objects, calling prior methods) belongs before the call.

### Code Style
- PSR-12 coding standard
- PHPStan level 8
- `declare(strict_types=1)` in all PHP files
- Readonly properties and constructor promotion preferred
