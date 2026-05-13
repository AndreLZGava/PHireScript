# PHireScript Architecture

## Source Tree

```
src/
├── Compiler.php                        # Main entry point — orchestrates compilation
├── Transpiler.php                      # Runs the full pipeline for a single file
├── SymbolTable.php                     # Global symbol registry (types, classes, functions)
├── DependencyGraphBuilder/             # Topological sort for inter-file compile order
│
├── Core/
│   ├── CompileMode.php                 # Enum: BUILD, TEST, DEBUG, SNAPSHOT, WATCH, CHECK
│   └── CompilerContext.php             # Runtime context passed through the entire pipeline
│
├── Compiler/
│   ├── Scanner.php                     # Lexical analysis — source text → token stream
│   ├── Parser.php                      # Syntactic analysis — tokens → AST
│   ├── Binder.php                      # Symbol binding and scope resolution
│   ├── Checker.php                     # Type checking and semantic validation
│   ├── Emitter.php                     # Iterates all NodeEmitters, dispatches by supports()
│   ├── FileManager.php                 # File I/O, project config, watch loop
│   │
│   ├── Parser/
│   │   ├── Ast/
│   │   │   ├── Context/                # Scope limiters — one class per language construct
│   │   │   │   ├── AbstractContext.php # Base: handle(), canClose(), onClose(), afterClose()
│   │   │   │   ├── Declarations/       # class, interface, trait, method, property, ...
│   │   │   │   ├── Expressions/        # assignment, binary op, call, literal, ...
│   │   │   │   ├── Root/               # program, use, package
│   │   │   │   ├── Scopes/             # block, class, method, if, try, handle, always
│   │   │   │   ├── Signatures/         # params, return type, modifiers
│   │   │   │   ├── Statements/         # if, return, loop, switch, try, comment
│   │   │   │   └── Types/              # union, generic, type instantiation
│   │   │   ├── Nodes/                  # AST node data classes (one per construct)
│   │   │   └── Resolver/               # Pattern matchers — one per language construct
│   │   │       ├── ContextTokenResolver.php  # Root resolver that chains all others
│   │   │       ├── Declaration/        # ClassResolver, MethodDeclarationResolver, ...
│   │   │       ├── Expressions/        # ArrowFunctionResolver, FunctionCallResolver, ...
│   │   │       ├── Root/               # UseResolver, PackageResolver, ...
│   │   │       ├── Scopes/             # Scope-aware resolvers
│   │   │       ├── Signatures/         # ParamResolver, ReturnTypeResolver, ...
│   │   │       └── Statements/         # IfResolver, ReturnResolver, ...
│   │   ├── Managers/
│   │   │   ├── ContextManager.php      # Manages the active context stack
│   │   │   ├── TokenManager.php        # Cursor and navigation over the token stream
│   │   │   ├── Builder/                # AST builder utilities
│   │   │   ├── Context/                # Context coordination helpers
│   │   │   └── Token/                  # Token data class and token types
│   │   └── Transformers/
│   │       └── ModifiersTransform.php  # Maps PHireScript modifier symbols to PHP keywords
│   │
│   ├── Binder/
│   │   ├── Declaration/                # ClassBinder, PropertyBinder, MethodBinder, ...
│   │   ├── Root/                       # ProgramBinder
│   │   └── Signatures/                 # ModifiersBinder, ReturnTypeBinder
│   │
│   ├── Checker/
│   │   ├── Declaration/Class/          # ClassChecker, MagicMethodsChecker, ...
│   │   ├── Expression/                 # Expression-level type checkers
│   │   └── Root/                       # ProgramChecker
│   │
│   ├── Emitter/
│   │   ├── NodeEmitters/               # One emitter per AST node type
│   │   │   └── NodeEmitterAbstract.php # Base: supports() + emit()
│   │   ├── Internal/                   # Shared emitter utilities (ConstructorEmitter, ...)
│   │   └── Type/                       # Type-specific emission helpers
│   │
│   └── Processors/                     # Post-emission: nikic/php-parser integration
│
├── Runtime/
│   ├── RuntimeClass.php                # Constants: file extensions, modifier maps, defaults
│   ├── Types/
│   │   ├── SuperTypes/                 # Email, Ipv4, Uuid, Url, ... (validated strings)
│   │   └── MetaTypes/                  # Date, DateTime, Currency, Password, ...
│   ├── DefaultOverrideMethods/         # Method mappings for built-in types (see below)
│   │   ├── BaseMethods.php             # Descriptor for a single method mapping
│   │   ├── BaseParams.php              # Descriptor for a method parameter
│   │   ├── BaseRegistryFunctions.php   # (sketch — not ready, ignore for now)
│   │   ├── GeneralType.php             # Methods available on ALL types
│   │   ├── Types/                      # ArrayMethods, StringMethods, IntMethods, ...
│   │   └── SuperTypes/                 # EmailMethods, UuidMethods, UrlMethods, ...
│   ├── CustomClasses/
│   │   ├── MagicMethods.php            # Maps PHireScript magic methods → PHP __magic
│   │   └── MagicBaseMethods.php        # Base descriptors, reuses DefaultOverrideMethods logic
│   ├── Exceptions/                     # CompileException, CheckerException, FatalErrorException
│   └── Registry/                       # (sketch — not ready, ignore for now)
│
├── Helper/
│   ├── Messenger.php                   # CLI/web output (success, error, warning, info)
│   └── Debug/                          # Debug utilities
│
├── Lexer/                              # Low-level lexer primitives
└── Visitor/                            # AST visitors (VariableResolver, TypeCollector, ...)
```

---

## Compiler Pipeline

Each `.ps` file passes through all stages in order.

```
Source text (.ps)
  │
  ▼
Scanner                         src/Compiler/Scanner.php
  Regex-based tokenizer.
  Produces a flat array of typed Token objects.
  │
  ▼
Validator                       src/Compiler/Parser/Transformers/
  Rejects forbidden PHP constructs before parsing begins.
  ModifiersTransform runs here — maps modifier symbols to PHP keywords.
  │
  ▼
Parser                          src/Compiler/Parser.php
  Token-by-token. Each token is handed to ContextManager.handle().
  ContextManager passes it to the current Context, which runs its Resolvers.
  Produces a Program AST (tree of Node objects).
  │
  ▼
Binder                          src/Compiler/Binder.php
  Walks the AST, binds each symbol to its declaration, populates SymbolTable.
  │
  ▼
Checker                         src/Compiler/Checker.php
  Validates semantic correctness: method signatures, return types, class rules.
  Throws CheckerException with line/column on failure.
  │
  ▼
Emitter                         src/Compiler/Emitter.php
  Walks the AST. For each Node, iterates all NodeEmitters and calls the first
  whose supports() returns true. Produces pre-PHP output.
  │
  ▼
PhpFileGeneratorHandler         src/Compiler/Processors/
  Uses nikic/php-parser: parses pre-PHP, applies formatting and cleanup.
  Produces the final .php file content.
  │
  ▼
FileManager::persist()          src/Compiler/FileManager.php
  Writes the .php file to the dist directory.
```

---

## Parser Internals — Resolver → Node → Context

Every language construct requires three types of files working together.

### How parsing works

The parser feeds one token at a time to `ContextManager.handle(token)`. The ContextManager delegates to the **current active Context**, which runs a chain of **Resolvers** against the token. The first Resolver that recognises the pattern calls `resolve()`, which typically creates a **Node** and a new **Context**, and calls `contextManager.enter(newContext)`.

This continues recursively — each Context has its own set of Resolvers active within it. Contexts are stacked: entering a new one sets the previous as its parent. When `canClose()` returns true the ContextManager calls `exit()`, returning to the parent.

```
Token stream →  ContextManager.handle(token)
                  └─ currentContext.handle(token)
                       └─ runs Resolvers in order
                            Resolver.matches(token)?
                              yes → Resolver.resolve() → creates Node + Context
                                    ContextManager.enter(newContext)
                              no  → next Resolver
                            (no resolver matched → CompileException)

                  └─ currentContext.canClose(token)?
                       yes → onClose() → ContextManager.exit() → afterClose()
```

### Example: parsing `class User implements Entity { }`

```
1. ProgramContext active
2. token "class"      → ClassResolver matches → creates ClassNode + ClassContext
                         ContextManager.enter(ClassContext)
3. token "User"       → inside ClassContext: IdentifierResolver sets node.name = "User"
4. token "implements" → ImplementsResolver → creates ImplementsContext
                         ContextManager.enter(ImplementsContext)
5. token "Entity"     → inside ImplementsContext: adds "Entity" to implements list
6. token "{"          → ImplementsContext.canClose() = true → exit() ← back to ClassContext
7. token "{"          → ClassBodyResolver → creates ClassBodyContext
                         ContextManager.enter(ClassBodyContext)
   ... members parsed inside ClassBodyContext ...
8. token "}"          → ClassBodyContext.canClose() = true → exit() → ClassContext.canClose()
                         = true → exit() ← back to ProgramContext
```

### Resolver

Checks whether the incoming token (or token sequence) matches a construct. If it matches, `resolve()` creates the Node and Context and enters the stack. If not, returns without action (the next Resolver in the chain is tried).

```
src/Compiler/Parser/Ast/Resolver/
```

### Context (AbstractContext)

A scope limiter. Wraps a Node and manages what is valid inside its boundaries. Contains its own Resolvers. Stays on the stack until `canClose()` returns true.

Key methods to implement:
```php
handle(Token $token, ParseContext $ctx): ?Node   // receives every token while active
canClose(Token $token, ParseContext $ctx): bool  // return true to pop off the stack
onClose(Token $token, ParseContext $ctx): void   // called just before exiting
afterClose(Token $token, ParseContext $ctx): void // called after exiting, in parent context
```

```
src/Compiler/Parser/Ast/Context/
```

### Node

Plain data class. Holds the parsed information for a single construct (name, type, children, modifiers, etc.). Passed into its Context; later consumed by the Emitter.

```
src/Compiler/Parser/Ast/Nodes/
```

### ContextManager

Manages the context stack.

```php
enter(AbstractContext $ctx)       // push — sets parent link
exit()                            // pop — returns to parent
exitUntil(string $class)          // unwind stack to first instance of $class
isIn(string $class): bool         // true if $class appears anywhere in the stack ancestry
handle(Token $token, $ctx)        // dispatches token to current context, checks canClose()
```

---

## Modifiers and ModifiersTransform

Modifiers (`+`, `#`, `*`, `abstract`, `static`, `readonly`, `async`) are ambiguous when encountered — the parser cannot immediately know whether they precede a class, method, property, or something else. They are stored temporarily and resolved when the following construct is identified.

`ModifiersTransform` maps PHireScript symbols to PHP keywords:

| PHireScript | PHP |
|---|---|
| `+` | `protected` |
| `#` | `private` |
| `*` (or getter/setter context) | `public` |
| `abstract` | `abstract` |
| `static` | `static` |
| `readonly` | `readonly` |
| `async` | `async` |

```
src/Compiler/Parser/Transformers/ModifiersTransform.php
```

---

## Emitter — NodeEmitter Dispatch

`Emitter.php` walks the AST and for each Node iterates all registered NodeEmitters. The first emitter whose `supports()` returns true handles that node.

```php
// Each NodeEmitter implements:
public function supports(object $node, EmitContext $ctx): bool
{
    return $node instanceof SomeNode;   // type-based dispatch
}

public function emit(object $node, EmitContext $ctx): string
{
    // ... generate PHP string ...
    // delegate sub-nodes to other emitters:
    $code .= $ctx->emitter->emit($node->body, $ctx);
    return $code;
}
```

Emitters can delegate sub-parts of their structure to other emitters via `$ctx->emitter->emit(subNode, $ctx)`. See `ClassEmitter` for a concrete example.

```
src/Compiler/Emitter/NodeEmitters/
```

---

## Magic Methods — CustomClasses

`src/Runtime/CustomClasses/` contains the mapping between PHireScript magic method names and their PHP `__magic` counterparts. The logic reuses the `DefaultOverrideMethods` descriptors to validate parameter types and signatures.

| PHireScript | PHP |
|---|---|
| `onCreate` | `__construct` |
| `onDestroy` | `__destruct` |
| `onGet` | `__get` |
| `onSet` | `__set` |
| `onHas` | `__isset` |
| `onUnset` | `__unset` |
| `onCall` | `__call` |
| `onStaticCall` | `__callStatic` |
| `toString` | `__toString` |
| `toSerialize` | `__serialize` |
| `toUnserialize` | `__unserialize` |
| `beforeSerialize` | `__sleep` |
| `afterUnserialize` | `__wakeup` |
| `onClone` | `__clone` |
| `toInspect` | `__debugInfo` |

---

## Type Methods — DefaultOverrideMethods

In PHireScript, methods can be called on variables based on their declared type:

```
myArray.last()         →  empty($myArray) ? null : $myArray[array_key_last($myArray)]
myString.toUpperCase() →  mb_strtoupper($myString, 'UTF-8')
myEmail.domain()       →  explode('@', $myEmail)[1]
```

These mappings live in `src/Runtime/DefaultOverrideMethods/`.

### File map

| PHireScript type | Methods class |
|---|---|
| All types (base) | `Types/GeneralType.php` |
| `Array` | `Types/ArrayMethods.php` |
| `String` | `Types/StringMethods.php` |
| `Int` | `Types/IntMethods.php` |
| `Float` | `Types/FloatMethods.php` |
| `Bool` | `Types/BoolMethods.php` |
| `Object` | `Types/ObjectMethods.php` |
| `List<T>` | `Types/ListMethods.php` |
| `Map<T>` | `Types/MapMethods.php` |
| `Queue<T>` | `Types/QueueMethods.php` |
| `Stack<T>` | `Types/StackMethods.php` |
| `Email` | `SuperTypes/EmailMethods.php` |
| `Uuid` | `SuperTypes/UuidMethods.php` |
| `Url` | `SuperTypes/UrlMethods.php` |
| `Color` | `SuperTypes/ColorMethods.php` |
| `Cron` | `SuperTypes/CronMethods.php` |
| `Duration` | `SuperTypes/DurationMethods.php` |
| `Json` | `SuperTypes/JsonMethods.php` |
| `Mac` | `SuperTypes/MacMethods.php` |
| `Slug` | `SuperTypes/SlugMethods.php` |
| `Ipv4` | `SuperTypes/Ipv4Methods.php` |
| `Ipv6` | `SuperTypes/Ipv6Methods.php` |

`GeneralType` is the base class for all `Types/` classes — its methods (`destroy!`, `defined?`, `getClass`, `show!`, `display!`) are available on every type.

### Method name conventions

| Suffix | Meaning |
|---|---|
| *(none)* | Returns a value — may or may not mutate |
| `?` | Returns `Bool` |
| `!` | Executes only, no return value — may or may not mutate |

### BaseMethods — anatomy of a method mapping

```php
new BaseMethods(
    name: 'split',
    phpCodeForConversion: '\explode(@separator, @self, @limit)',
    returnOfPhpExecution: ['Array'],            // PHireScript return type(s)
    subTypes: ['String'],                       // inner type for generics (Array<String>)
    params: [
        new BaseParams('@separator', 'string', true),          // required
        new BaseParams('@limit', 'int', false, PHP_INT_MAX),   // optional with default
    ],
    overridesSelfParam: true,                   // whether @self is reassigned
)
```

**Placeholders in `phpCodeForConversion`:**
- `@self` — the variable the method is called on
- `@paramName` — replaced by the argument passed by the caller (must match a `BaseParams::$name`)

**Multi-line conversions** — pass an array of strings:
```php
phpCodeForConversion: [
    '$__pos = \mb_strpos(@self, @search, 0, "UTF-8");',
    'return $__pos === false ? -1 : $__pos;'
]
```

### BaseParams — anatomy of a parameter

```php
new BaseParams(
    name: '@key',           // placeholder used in phpCodeForConversion
    type: 'mixed',          // PHP type (for internal reference)
    required: true,         // whether the caller must pass this
    defaultValue: null,     // used when required: false
    relatedKeyParam: false, // marks this as an array key param (special handling)
)
```

---

## Adding a New Language Feature

Minimum required files, in pipeline order:

### 1. Scanner — token recognition
If the feature introduces a new keyword or operator, add a regex pattern:
```
src/Compiler/Scanner.php
```

### 2. Parser — Resolver + Node + Context (the trinity)

**Resolver** — detects the token/pattern, creates Node + Context:
```
src/Compiler/Parser/Ast/Resolver/<category>/YourFeatureResolver.php
```

**Node** — plain data class holding the parsed information:
```
src/Compiler/Parser/Ast/Nodes/YourFeatureNode.php
```

**Context** — scope limiter, extends `AbstractContext`, manages tokens while active:
```
src/Compiler/Parser/Ast/Context/<category>/YourFeatureContext.php
```

Place files in the subdirectory that matches the construct:

| Construct type | Subdirectory |
|---|---|
| class, method, property, interface, trait | `Declarations/` |
| value-producing expressions | `Expressions/` |
| package, use | `Root/` |
| block delimiters with own scope | `Scopes/` |
| parameter lists, return types, modifiers | `Signatures/` |
| if, return, loop, switch | `Statements/` |
| union, generic, type instantiation | `Types/` |

### 3. Binder — symbol binding (if applicable)
If the feature introduces new names into scope:
```
src/Compiler/Binder/<category>/YourFeatureBinder.php
```

### 4. Checker — semantic validation (if applicable)
If the feature has type constraints or semantic rules:
```
src/Compiler/Checker/<category>/YourFeatureChecker.php
```

### 5. Emitter — PHP code generation
Create a NodeEmitter implementing `supports()` and `emit()`:
```
src/Compiler/Emitter/NodeEmitters/YourFeatureEmitter.php
```
Register it in `src/Compiler/Emitter.php`.

### 6. Sandbox case
```
PHire-Script-Sandbox/samples/success/case_N/
  ├── YourFeature.ps         # source exercising the new syntax
  └── CaseValidation.php     # asserts expected compilation output
```

### Adding a method to an existing type
1. Open `src/Runtime/DefaultOverrideMethods/Types/YourTypeMethods.php`
2. Add a public method returning a `BaseMethods` instance
3. Define `phpCodeForConversion` using `@self` and `@paramName` placeholders
4. List parameters via `BaseParams`

### Adding methods for a new type
1. Create `src/Runtime/DefaultOverrideMethods/Types/YourTypeMethods.php` extending `GeneralType`
2. Register it so the compiler maps the PHireScript type to your class
