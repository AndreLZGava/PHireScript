# PHireScript

PHireScript is a transpiler that converts `.ps` files into strictly typed PHP code, targeting PHP 8.2 as the minimum version. The goal is to evolve the target range over time (similar to how TypeScript targets different ECMAScript versions), but older PHP versions will never be supported. It implements a full compiler pipeline: Scanner → Validator → Parser → Binder → Checker → Emitter → PhpFileGenerator.

## Setup

```bash
composer install
php bin/init   # creates PHireScript.json interactively (first time only)
```

## Running Tests

```bash
vendor/bin/phpunit
```

Unit tests live in `tests/`. Integration testing is done via the sandbox project (PHire-Script-Sandbox), which runs the orchestrator against sample `.ps` files.

## Quality

A pre-commit hook runs quality checks automatically. To run manually:

```bash
composer quality       # refactor + format + analyse (all in sequence)
composer format        # php-cs-fixer (PSR-12)
composer refactor      # rector (targets PHP 8.2)
composer analyse       # phpstan (level 9) + phpmd
```

PHPStan is configured at level 9. All new code must pass without suppressions.

## CLI Commands

All commands are run from the `phirescript/` directory:

| Command | Description |
|---|---|
| `php bin/build [src] [dist]` | Compile `.ps` → `.php` (production) |
| `php bin/watch [src] [dist]` | Hot reload — recompiles on file save |
| `php bin/debug <file.ps>` | Inspect tokens/AST for a single file |
| `php bin/snapshot [src] [dist]` | Generate `.psc` intermediate files |
| `php bin/validate [src] [dist]` | Compile `.pst` test files only |
| `php bin/validateCompiled` | Run `php -l` + PHPUnit on compiled output |

Source and dist paths default to `PHireScript.json` when not passed as arguments.

## File Extensions

| Extension | Purpose | Compiled to |
|---|---|---|
| `.ps` | PHireScript source | `.php` |
| `.pst` | PHireScript test files | `*Test.php` (PHPUnit-compatible) |
| `.psc` | Snapshot (intermediate state) | debug only |

## PHireScript.json

Configuration file at the project root:

```json
{
  "dev": true,
  "namespace": "App",
  "currency": "USD",
  "resolver": "laravel",
  "paths": {
    "source": "src/ps",
    "dist": "dist/php",
    "test": "dist/tests"
  }
}
```

The `resolver` field controls how `inject {}` blocks are compiled — it sets the DI adapter for the generated PHP (`laravel`, `symfony`, or `custom`).

> Note: `inject {}` is currently a sketch — the field exists in config but the feature is not functional yet.

## Compiler Pipeline

```
.ps source
  → Scanner       (tokenization, regex-based)
  → Validator     (forbidden keywords, syntax guards)
  → Parser        (tokens → AST, context-based recursive descent)
  → Binder        (symbol binding, scope resolution)
  → Checker       (type checking, semantic validation)
  → Emitter       (AST → pre-PHP via specialized NodeEmitters)
  → PhpFileGeneratorHandler  (nikic/php-parser → final formatted PHP)
  → .php output
```

**CompileMode** controls which extensions are processed and whether output is persisted:
- `BUILD` — processes `.ps`, writes `.php`
- `TEST` — processes `.pst`, writes `*Test.php`
- `DEBUG` — processes `.ps` + `.pst`, in-memory only
- `SNAPSHOT` — writes `.psc` (pre-generator intermediate)
- `WATCH` — BUILD in a loop, reacts to file changes
- `CHECK` — validates only, no output

## Language Feature Status

Features are classified into three tiers. When working on the compiler, respect this — do not assume a sketch will produce valid output.

### Functional — fully implemented and covered by `CaseValidation` in the sandbox

- **Package / Use imports** — `pkg`, `use`, grouped `use { ... }`
- **Comments**
- **Interface** — method signatures with optional (`?`) and required (`!`) markers
- **Class** — with scopes (`as scoped`, `as singleton`, `as newable`), `abstract`, `extends`, `implements`
- **Type** (DTO) — `type Name as scoped { ... }`
- **Immutable** — `immutable Name as scoped { ... }`
- **Trait** — `trait Name { ... }` and `with` on classes
- **Magic Methods** — `onCreate`, `onDestroy`, `onGet`, `onSet`, `onHas`, `onUnset`, `onCall`, `onStaticCall`, `toString`, `toSerialize`, `beforeSerialize`, `afterUnserialize`, `onClone`, `toInspect`
- **Primitives** — `String`, `Int`, `Float`, `Bool`, `Null`, `Void`, `Mixed`, `Any`
- **Variables and Object literals**
- **Range**
- **Super Types (all)** — `Email`, `Ipv4`, `Ipv6`, `Uuid`, `Color`, `Url`, `Cron`, `Duration`, `Json`, `Mac`, `Slug`, `CardNumber`, `Cvv`, `ExpiryDate`
- **Try / Handle / Always** — maps to PHP `try / catch / finally`
- **`external` declarations** — `external ClassName [as Alias]` resolves static/instance/constant access via Reflection; validates member existence and accessibility; propagates return types for chained calls; sandbox cases 39, 40, 41
- **Method Chaining** — `.` and `?.` (safe navigation) on variables and literals; inline nested PHP emission; multi-line chains; cross-type chains (String→Array→Int); `ChainConsistencyChecker` enforces type continuity, void termination, nullable guard, Mixed guard; sandbox cases 42–49
- **Getter / Setter on properties** — `<` (getter) and `>` (setter) markers on property lines; optional visibility modifiers (`*`/`+`/`#`) before each marker; combined `T_ACCESSORS` tokens (`#<`, `+>`, `<>`, etc.); explicit method override suppresses generated version; applies to `class`, `type`, `immutable`, `trait`; sandbox cases 55–60

### Partial — syntax parses and compiles, but with known gaps

- **Arrow Functions** — basic cases work; edge cases may not
- **Collections** — `List<T>`, `Map<T>`, `Queue<T>`, `Stack<T>` — type declarations compile; full runtime behavior incomplete
- **Testing / Validate blocks (`.pst`)** — compiles to `*Test.php`, but with limitations

### Sketch — syntax may exist in the parser/emitter, but the feature is not usable

- **Dependency Injection** — `inject {}` block (config field `resolver` is wired but feature is a skeleton)
- **Cache decorator** — `cache { method<Duration(...)> }` is a skeleton
- **Schedule decorator** — `schedule { method<Cron(...)> }` is a skeleton
- **Enum** — context and emitter exist but not functional
- **Foreach / Loop** — context exists, not functional
- **Switch** — context exists, not functional
- **Pattern Matching** — not implemented

## Adding a New Language Feature

Follow the pipeline in order:

1. **Scanner** — add token recognition (regex pattern in `Scanner.php`)
2. **Parser** — add AST node and parsing context (`Parser/Contexts/`)
3. **Binder** — bind symbols if the feature introduces new declarations (`Compiler/Binder/`)
4. **Checker** — add semantic validation if needed (`Compiler/Checker/`)
5. **Emitter** — add a `NodeEmitter` that converts the AST node to PHP (`Compiler/Emitter/`)
6. **Test in sandbox** — add a case under `PHire-Script-Sandbox/samples/` with a `CaseValidation.php`

## Critical Areas

Changes in these areas have wide blast radius across the pipeline — be careful:

- **Scanner / Lexer** — any change to tokenization affects every downstream phase
- **Parser and its Contexts** — context-based parsing has subtle precedence and scope rules; adding or modifying a context can break unrelated constructs
- **SymbolTable and Binder** — symbol resolution and scope rules underpin type checking and emission; incorrect bindings produce silent wrong output

## Key Files

| File | Role |
|---|---|
| `src/Compiler.php` | Main entry point, orchestrates compilation |
| `src/Transpiler.php` | Coordinates the full pipeline per file |
| `src/SymbolTable.php` | Global symbol registry |
| `src/DependencyGraphBuilder.php` | Topological sort for inter-file compilation order |
| `src/Compiler/Scanner.php` | Lexical analysis |
| `src/Compiler/Parser.php` | Syntactic analysis, produces AST |
| `src/Compiler/Binder.php` | Symbol binding |
| `src/Compiler/Checker.php` | Type and semantic validation |
| `src/Compiler/Emitter.php` | Dispatches to NodeEmitters |
| `src/Compiler/Emitter/NodeEmitter.php` | Base class for all emitters |
| `src/Core/CompileMode.php` | Enum of compilation modes |
| `src/Core/CompilerContext.php` | Runtime context passed through the pipeline |
| `src/Runtime/RuntimeClass.php` | Constants (file extensions, defaults) |
| `src/Helper/Messenger.php` | CLI/web output (success, error, warning, info) |

## Exceptions

| Class | Thrown by | Has |
|---|---|---|
| `CompileException` | Scanner, Validator | `$line`, `$column` |
| `CheckerException` | Checker | `$line`, `$column` |
| `FatalErrorException` | Top-level handler | pretty CLI/HTML rendering |

## Commits

Use **Conventional Commits** in **English**. No `Co-Authored-By` trailer.

```
feat: add arrow function context to parser
fix: correct union type emission in property declarations
chore: update phpstan baseline
refactor: extract method emitter into separate class
test: add SuperType validation for Slug
docs: update readme with snapshot command
```

Scopes are optional but encouraged when the change is isolated to a specific pipeline phase:

```
feat(scanner): recognize range token
fix(emitter): handle null return type in method
test(checker): cover abstract method validation
```

**One commit per complete feature.** Do not commit partial/broken states — only commit when the feature compiles and the sandbox case passes (if applicable).

Common types: `feat`, `fix`, `refactor`, `test`, `chore`, `docs`, `perf`.

## Namespace

```
PHireScript\              → src/
PHireScript\Tests\        → tests/
```
