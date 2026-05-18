# PHireScript Architecture

## Source Tree

```
src/
├── Compiler.php                        # Main entry point — orchestrates compilation
├── Transpiler.php                      # Runs the full pipeline for a single file
├── SymbolTable.php                     # Global symbol registry (types, classes, functions)
├── DependencyGraphBuilder.php          # Topological sort for inter-file compile order
│
├── Core/
│   ├── CompileMode.php                 # Enum: BUILD, TEST, DEBUG, SNAPSHOT, WATCH, CHECK
│   └── CompilerContext.php             # Runtime context passed through the entire pipeline
│
├── Compiler/
│   ├── Scanner.php                     # Lexical analysis — source text → token stream
│   ├── Parser.php                      # Syntactic analysis — tokens → AST
│   ├── Binder.php                      # Orchestrator: instantiates all Binder impls, walks AST
│   ├── Checker.php                     # Orchestrator: instantiates all Checker impls, walks AST
│   ├── Emitter.php                     # Orchestrator: instantiates EmitterDispatcher, all NodeEmitters
│   ├── FileManager.php                 # File I/O, project config, watch loop (delegates to FileManager/)
│   │
│   ├── Parser/
│   │   ├── Ast/
│   │   │   ├── Context/                # Scope limiters — one class per language construct
│   │   │   │   ├── AbstractContext.php # Base: handle(), validation(), canClose(), onClose(), afterClose()
│   │   │   │   ├── Declarations/       # ClassContext, InterfaceContext, TraitContext, MethodDeclarationContext,
│   │   │   │   │   │                   # PropertyDeclarationContext, ArrowFunctionDeclarationContext,
│   │   │   │   │   │                   # VariableDeclarationContext, ParamsDeclarationContext,
│   │   │   │   │   │                   # ParamsConsumptionContext, FunctionDeclarationContext,
│   │   │   │   │   │                   # ValidateContext, EnumContext
│   │   │   │   │   └── Class/          # ClassBodyContext, ExtendsContext, ImplementsContext,
│   │   │   │   │       Interface/      # WithContext, DependencyInjectionContext
│   │   │   │   │                       # Interface/: InterfaceBodyContext, ExtendsContext,
│   │   │   │   │                       #             MethodDeclarationContext
│   │   │   │   ├── Expressions/        # AssignmentContext, BinaryExpressionContext, BinaryOperationContext,
│   │   │   │   │   │                   # CallExpressionContext, FunctionCallContext, LiteralContext,
│   │   │   │   │   │                   # MemberAccessContext, ExpressionContext, ArrayContext,
│   │   │   │   │   │                   # ArrayKeyContext, ArrayLiteralContext, ObjectLiteralContext,
│   │   │   │   │   │                   # ObjectKeyContext, PrimitiveCastingContext, SuperTypeCastingContext
│   │   │   │   │   └── Types/          # ListContext, MapContext, QueueContext, StackContext
│   │   │   │   ├── Root/               # ProgramContext, PackageContext, UseContext, GroupUseContext,
│   │   │   │   │                       # ExternalContext
│   │   │   │   ├── Scopes/             # MethodScopeContext, ClassScopeContext, FunctionScopeContext,
│   │   │   │   │                       # BlockScopeContext, ScopeContext, IfConditionContext, IfScopeContext,
│   │   │   │   │                       # ElseIfScopeContext, ElseScopeContext, TryScopeContext,
│   │   │   │   │                       # HandleContext, HandleScopeContext, AlwaysContext, AlwaysScopeContext
│   │   │   │   ├── Signatures/         # ModifiersContext, ParameterListContext, ParameterArgumentContext,
│   │   │   │   │                       # ArgumentAssignmentContext, ReturnTypeContext
│   │   │   │   │   └── Validate/       # ValidateBodyContext
│   │   │   │   ├── Statements/         # IfContext, ElseIfContext, ReturnContext, TryContext,
│   │   │   │   │                       # CommentContext, BlockStatementContext, LoopContext,
│   │   │   │   │                       # SwitchStatementContext
│   │   │   │   └── Types/              # UnionTypeContext, GenericTypeContext, TypeInstantiationContext
│   │   │   │
│   │   │   ├── Nodes/                  # AST node data classes — plain data, one per construct
│   │   │   │   ├── Node.php            # Base node interface / abstract
│   │   │   │   ├── Expression.php      # Base for expression nodes
│   │   │   │   ├── Statement.php       # Base for statement nodes
│   │   │   │   ├── Collections/        # ListNode, MapNode, QueueNode, StackNode
│   │   │   │   ├── Declarations/       # ClassNode, InterfaceNode, TraitNode, ArrowFunctionNode,
│   │   │   │   │                       # FunctionNode, PackageNode, UseNode, GroupUseNode,
│   │   │   │   │                       # ExternalNode, ValidateNode
│   │   │   │   ├── Expressions/        # ArrayLiteralNode, BinaryExpressionNode, BoolNode,
│   │   │   │   │                       # ExplicitTypedNode, GlobalConstNode, KeyValuePairNode,
│   │   │   │   │                       # LiteralNode, MetaTypeNode, NewExceptionNode,
│   │   │   │   │                       # NullExpressionNode, NullNode, NumberNode,
│   │   │   │   │                       # ObjectLiteralNode, PrimitiveCastingNode,
│   │   │   │   │                       # PropertyAccessNode, RangeNode, StringNode,
│   │   │   │   │                       # SuperTypeNode, ThisExpressionNode, VoidExpressionNode
│   │   │   │   ├── Meta/               # CommentNode
│   │   │   │   ├── OOP/                # ClassBodyNode, ClassExtendsNode, ConstructorDefinitionNode,
│   │   │   │   │                       # DependencyInjectionNode, ImplementsNode, InterfaceBodyNode,
│   │   │   │   │                       # InterfaceExtendsNode, InterfaceMethodDeclarationNode,
│   │   │   │   │                       # MethodDeclarationNode, PropertyNode, ValidateBodyNode, WithNode
│   │   │   │   ├── Scopes/             # AlwaysScopeNode, ElseIfScopeNode, ElseScopeNode,
│   │   │   │   │                       # HandleScopeNode, IfConditionNode, IfScopeNode,
│   │   │   │   │                       # MethodScopeNode, TryScopeNode
│   │   │   │   ├── Signatures/         # ParamArgumentNode, ParamHandleNode, ParamsListNode,
│   │   │   │   │                       # ParamsNode, ReturnTypeNode
│   │   │   │   └── Statements/         # AlwaysNode, AssignmentNode, DependencyStatementNode,
│   │   │   │                           # ElseIfNode, ExpressionStatementNode, GlobalStatementNode,
│   │   │   │                           # HandleNode, IfNode, IssetOperatorNode, NamespaceNode,
│   │   │   │                           # NotOperatorNode, PackageDependencyNode, ReturnNode,
│   │   │   │                           # ThrowStatementNode, TryNode, VariableDeclarationNode,
│   │   │   │                           # VariableNode, VariableReferenceNode
│   │   │   │
│   │   │   └── Resolver/               # Pattern matchers — one per language construct
│   │   │       ├── ContextTokenResolver.php  # Root resolver that chains all others
│   │   │       ├── Declaration/        # ClassResolver, InterfaceResolver, TraitResolver,
│   │   │       │   │                   # MethodDeclarationResolver, PropertyDeclarationResolver, ...
│   │   │       │   └── Interface/      # Interface-specific method declaration resolvers
│   │   │       ├── Expressions/        # ArrowFunctionResolver, FunctionCallResolver,
│   │   │       │   │                   # AssignmentResolver, BinaryExpressionResolver, ...
│   │   │       │   ├── CastingConsumptionParams/
│   │   │       │   ├── ConsumptionParams/
│   │   │       │   └── Types/          # ListResolver, MapResolver, QueueResolver, StackResolver
│   │   │       ├── Root/               # PackageResolver, UseResolver, GroupUseResolver,
│   │   │       │   │                   # ExternalResolver
│   │   │       │   ├── Class/
│   │   │       │   ├── ComplexObjects/
│   │   │       │   ├── External/
│   │   │       │   ├── Interface/
│   │   │       │   └── Use/
│   │   │       ├── Scopes/             # MethodScopeResolver, IfScopeResolver, TryScopeResolver, ...
│   │   │       ├── Signatures/         # ModifiersResolver, ParamResolver, ReturnTypeResolver, ...
│   │   │       │   └── Validate/
│   │   │       └── Statements/         # IfResolver, ReturnResolver, TryResolver, CommentResolver, ...
│   │   │
│   │   ├── Managers/
│   │   │   ├── ContextManager.php      # Context stack: enter(), exit(), exitUntil(), isIn(), handle()
│   │   │   ├── TokenManager.php        # Token cursor: advance(), walk(), peek(), sequence(),
│   │   │   │                           # getNextToken(), matchSequence(), getLeftTokens()
│   │   │   ├── SymbolTableManager.php  # Parser-local type-method registry; auto-loads
│   │   │   │                           # DefaultOverrideMethods/Types/*.php via reflection;
│   │   │   │                           # resolves getFunction(name) and getFunctionFromLastExecution()
│   │   │   │                           # so Resolvers can look up callable methods on typed variables
│   │   │   ├── VariableManager.php     # Tracks variables and class properties in scope during
│   │   │   │                           # parsing; maintains a "focus" pointer to the last accessed
│   │   │   │                           # variable — used by Resolvers that need to know the type
│   │   │   │                           # of the variable a method is being called on
│   │   │   ├── Builder/
│   │   │   │   └── SequenceBuilder.php # Fluent API for multi-token pattern matching:
│   │   │   │                           # lookAhead / lookBehind direction; rules: once(), then(),
│   │   │   │                           # separated(), optional(), group(), or(), around(),
│   │   │   │                           # skipUntil(); execute with match() or test()
│   │   │   ├── Context/
│   │   │   │   ├── Context.php         # Enum of active context labels (ClassType, Interface,
│   │   │   │   │                       # Trait, Method, Variable, Queue, Map, List, etc.)
│   │   │   │   │                       # used by VariableManager to know what construct is active
│   │   │   │   └── ContextState.php    # Tree node pairing a Context enum + Node + parent/children;
│   │   │   │                           # lightweight snapshot of the current parse state
│   │   │   └── Token/
│   │   │       └── Token.php           # Token data class: type, value, line, column, processedBy;
│   │   │                               # semantic helpers: isKeyword(), isPrimitive(), isSuperType(),
│   │   │                               # isMagicMethod(), isMathOperator(), isBooleanOperator(),
│   │   │                               # isVariable(), isModifier(), isRange(), isAccessor(), ...
│   │   └── Transformers/
│   │       └── ModifiersTransform.php  # Maps PHireScript modifier symbols to PHP keywords
│   │
│   ├── Binder/
│   │   ├── Binder.php                  # Interface: mustBind(Node): bool + bind(Node, Binder): void
│   │   ├── Root/
│   │   │   ├── TypeRegistrationBinder.php   # First pass: registers ClassNode/InterfaceNode names
│   │   │   │                                # into SymbolTable for cross-file type resolution
│   │   │   └── ProgramBinder.php            # Second pass: walks all top-level AST statements,
│   │   │                                    # dispatches each to all registered binders
│   │   ├── Declaration/
│   │   │   ├── ClassBinder.php              # Walks ClassNode body children, dispatches members
│   │   │   │                                # to all registered binders
│   │   │   ├── ClassBodyBinder.php          # Walks class body, dispatches all members
│   │   │   ├── InterfaceBinder.php          # Walks InterfaceNode body, dispatches to child binders
│   │   │   ├── PropertyBinder.php           # Processes PropertyNode: resolves getter/setter
│   │   │   │                                # modifiers, normalizes type list
│   │   │   ├── PropertyTypeResolutionBinder.php # Categorizes each type in PropertyNode and
│   │   │   │                                    # ParamArgumentNode as primitive / supertype /
│   │   │   │                                    # metatype / custom / unknown; stores result
│   │   │   │                                    # in resolvedTypeInfo[]
│   │   │   ├── Class/
│   │   │   │   ├── MagicMethodDeclarationBinder.php  # Resolves which MagicMethods spec applies
│   │   │   │   │                                     # to a magic-method MethodDeclarationNode;
│   │   │   │   │                                     # stores spec on $node->implements
│   │   │   │   └── MethodParamResolutionBinder.php   # Resolves method-call parameters for
│   │   │   │                                         # FunctionNode-style calls (type-method
│   │   │   │                                         # consumption)
│   │   │   └── Interface/
│   │   │       └── MethodDeclarationBinder.php  # Binds interface method signatures (optional/
│   │   │                                        # required markers)
│   │   └── Signatures/
│   │       └── ModifiersBinder.php     # Translates *, +, # → public, protected, private;
│   │                                   # filters to PHP-allowed set; updates $node->modifiers
│   │
│   ├── Checker/
│   │   ├── Checker.php                 # Abstract base: mustCheck() + check() + willCheck() helper
│   │   │                               # to recursively dispatch sub-nodes to child checkers
│   │   ├── Root/
│   │   │   └── ProgramChecker.php      # Walks all top-level AST statements, dispatches to checkers
│   │   ├── Declaration/
│   │   │   ├── ClassChecker.php        # Validates non-abstract, non-trait classes have a lifecycle
│   │   │   │                           # declaration (as scoped / singleton / transient / newable)
│   │   │   ├── ClassBodyChecker.php    # Validates class body properties: readonly/immutable cannot
│   │   │   │                           # have defaults; abstract properties only in abstract classes
│   │   │   └── Class/
│   │   │       ├── MagicMethodsChecker.php  # Validates magic method return type and parameter
│   │   │       │                            # types exactly match their MagicMethods spec
│   │   │       └── MethodReturnChecker.php  # Enforces naming conventions: method? → exclusively
│   │   │                                    # Bool; method! → exclusively Void; validates array
│   │   │                                    # literal returns against declared return type
│   │   └── Expression/
│   │       ├── MethodConsumptionChecker.php # Validates method-call FunctionNode: required params
│   │       │                                # present; subtype params match generic type constraints
│   │       └── Types/
│   │           └── QueueChecker.php         # Queue<T> type usage validation
│   │
│   ├── Emitter/
│   │   ├── Base/
│   │   │   ├── NodeEmitter.php         # Interface: supports(object, EmitContext): bool
│   │   │   │                           #            emit(object, EmitContext): string
│   │   │   ├── NodeEmitterAbstract.php # Helper base: removeEndPunctuation() strips ! / ? suffixes
│   │   │   ├── EmitterDispatcher.php   # Iterates all NodeEmitters; calls the first whose
│   │   │   │                           # supports() returns true; throws CompileException if none
│   │   │   ├── EmitContext.php         # Shared emission context passed to every emitter:
│   │   │   │                           # dev flag, UseRegistry, PhpTypeResolver,
│   │   │   │                           # DependencyGraphBuilder, EmitterDispatcher;
│   │   │   │                           # state flags: insideInterface, insideClass,
│   │   │   │                           # insideMethodSignature, currentMethodReturnType
│   │   │   ├── UseRegistry.php         # Accumulates PHP `use` statements during emission;
│   │   │   │                           # add(fqcn), render() → sorted `use` block
│   │   │   └── Type/
│   │   │       └── PhpTypeResolver.php # Resolves PHireScript property types to PHP equivalents:
│   │   │                               # primitive → native PHP, supertype → string,
│   │   │                               # metatype/custom → class name; generates union types
│   │   │                               # and constructor assignment code (with UnionType::cast
│   │   │                               # for multi-type props)
│   │   ├── Collections/                # ListEmitter, MapEmitter, QueueEmitter, StackEmitter
│   │   ├── Declarations/               # ClassEmitter, InterfaceEmitter, TraitEmitter,
│   │   │                               # ArrowFunctionEmitter, FunctionEmitter, PackageEmitter,
│   │   │                               # UseEmitter, ExternalEmitter
│   │   ├── Expressions/                # ArrayLiteralEmitter, BinaryExpressionEmitter, BoolEmitter,
│   │   │                               # CastingEmitter, KeyValuePairEmitter, LiteralEmitter,
│   │   │                               # NullEmitter, NumberEmitter, ObjectLiteralEmitter,
│   │   │                               # PropertyAccessEmitter, RangeEmitter, StringEmitter,
│   │   │                               # SuperTypeEmitter, ThisExpressionEmitter, VoidExpressionEmitter
│   │   ├── OOP/                        # ClassBodyEmitter, ConstructorEmitter, InterfaceBodyEmitter,
│   │   │                               # InterfaceMethodEmitter, MethodEmitter, MethodScopeEmitter,
│   │   │                               # PropertyDeclarationEmitter, PropertyEmitter,
│   │   │                               # ReturnTypeEmitter, WithEmitter
│   │   ├── Root/                       # ProgramEmitter
│   │   ├── Signatures/                 # ParamArgumentEmitter, ParameterEmitter, ParamsListEmitter
│   │   └── Statements/                 # AlwaysEmitter, AssignmentEmitter, CommentStatementEmitter,
│   │                                   # GlobalConstEmitter, GlobalStatementEmitter, HandleEmitter,
│   │                                   # IfStatementEmitter, IssetOperatorEmitter, NewExceptionEmitter,
│   │                                   # NotOperatorEmitter, ReturnEmitter, ThrowStatementEmitter,
│   │                                   # TryEmitter, VariableDeclarationEmitter, VariableEmitter,
│   │                                   # VariableReferenceAssignEmitter
│   │
│   ├── FileManager/
│   │   ├── ClassScanner.php            # Scans source dirs for .ps/.pst files
│   │   ├── ErrorRenderer.php           # Formats compile errors for CLI / HTML output
│   │   ├── FileCompiler.php            # Compiles a single file through the full pipeline
│   │   └── FileWatcher.php             # Inotify/polling loop for WATCH mode
│   │
│   ├── DependencyGraphBuilder/
│   │   ├── Node.php                    # Dependency graph node (file + its dependencies)
│   │   └── DependencyTree/
│   │       └── Parser.php              # Parses `use` statements to build inter-file dependency edges
│   │
│   └── Processors/                     # Post-emission: nikic/php-parser integration
│       ├── PreprocessorInterface.php   # Interface for all post-emission processors
│       ├── PhpFileGeneratorHandler.php # Orchestrates all processors; parses pre-PHP with
│       │                               # nikic/php-parser, applies them in order, writes final .php
│       ├── PhpFileHandler.php          # Wraps nikic/php-parser AST manipulation helpers
│       ├── SemicolonHandler.php        # Inserts missing semicolons
│       ├── ReturnTypeHandler.php       # Normalises return type annotations
│       ├── AccessorHandler.php         # Rewrites getter/setter property access syntax
│       ├── VariablesHandler.php        # Normalises variable declarations
│       ├── VariablesBeforeInitializationHandler.php  # Hoists variable declarations
│       ├── NativeTypesHandler.php      # Maps PHireScript types to PHP native types
│       ├── FunctionsHandler.php        # Post-processes function/method nodes
│       ├── FunctionBodyProcessor.php   # Processes function body statements
│       └── ObjectsHandler.php          # Post-processes object literal and instantiation nodes
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
│   ├── TypeResolver.php                # Classifies a type name as primitive/supertype/metatype
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
  Builds an EmitterDispatcher with all NodeEmitters. Walks the AST; for each
  Node, the dispatcher calls the first emitter whose supports() returns true.
  Produces pre-PHP output string.
  │
  ▼
PhpFileGeneratorHandler         src/Compiler/Processors/
  Uses nikic/php-parser: parses pre-PHP, applies all Processor passes
  (semicolons, types, accessors, variables, etc.), formats the result.
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

Complex multi-token patterns are detected using `TokenManager::sequence()`, which returns a `SequenceBuilder` with a fluent API: `lookAhead()`, `once()`, `then()`, `separated()`, `optional()`, `group()`, `or()`, `around()`, `skipUntil()`, `match()`.

```
src/Compiler/Parser/Ast/Resolver/
```

### Context (AbstractContext)

A scope limiter. Wraps a Node and manages what is valid inside its boundaries. Contains its own Resolvers. Stays on the stack until `canClose()` returns true.

Key methods to implement:
```php
handle(Token $token, ParseContext $ctx): ?Node   // receives every token while active
validation(Token $token, ParseContext $ctx): void // called after handle() on every token
canClose(Token $token, ParseContext $ctx): bool  // return true to pop off the stack
onClose(Token $token, ParseContext $ctx): void   // called just before exiting
afterClose(Token $token, ParseContext $ctx): void // called after exiting, in parent context
```

```
src/Compiler/Parser/Ast/Context/
```

### Node

Plain data class. Holds the parsed information for a single construct (name, type, children, modifiers, etc.). Passed into its Context; later consumed by the Emitter.

Nodes mirror the Context category hierarchy:

| Category | Subdirectory | Examples |
|---|---|---|
| Top-level declarations | `Declarations/` | ClassNode, InterfaceNode, TraitNode, UseNode |
| Value-producing expressions | `Expressions/` | LiteralNode, BinaryExpressionNode, ArrayLiteralNode |
| OOP structure | `OOP/` | ClassBodyNode, MethodDeclarationNode, PropertyNode |
| Block scopes | `Scopes/` | MethodScopeNode, IfScopeNode, TryScopeNode |
| Signatures | `Signatures/` | ParamsNode, ReturnTypeNode, ParamArgumentNode |
| Control flow statements | `Statements/` | IfNode, ReturnNode, AssignmentNode, TryNode |
| Generic collections | `Collections/` | ListNode, MapNode, QueueNode, StackNode |
| Metadata | `Meta/` | CommentNode |

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

## Parser Managers

### SymbolTableManager

Parser-local type-method registry. On construction it auto-loads all classes from `Runtime/DefaultOverrideMethods/Types/` via reflection and maps each type name (e.g. `StringMethods`) to its `BaseMethods` instances. Resolvers use this to look up whether a method call is valid for a given variable type.

Key methods:
```php
from(string $rawType): self                              // set current type context
getFunction(string $functionName): ?BaseMethods          // look up method on current type
getFunctionFromLastExecution(string $name): ?BaseMethods // chain: look up on return type
                                                         // of last resolved method call
```

### VariableManager

Tracks variables and class properties in scope during parsing. Maintains a "focus" pointer to the last accessed variable, which lets method-call Resolvers know the type of the receiver without having to search the whole scope.

```php
addVariable(VariableDeclarationNode|VariableReferenceNode $v)
addProperty(PropertyNode $p)
getVariable(string $name): ?VariableDeclarationNode
getProperty(string $name): ?PropertyNode
getVariableOnFocus(): mixed   // the last variable set/accessed
setVirtualVariable($v)        // temporarily set focus without adding to scope
```

### SequenceBuilder

Fluent API for multi-token pattern matching used inside Resolvers. Built via `TokenManager::sequence()`.

```php
// example: detect "identifier ( ... )" — a function call
$match = $ctx->tokens->sequence()
    ->lookAhead()
    ->once(fn($t) => $t->isIdentifier())
    ->then(fn($t) => $t->isOpeningParenthesis())
    ->match();
```

Available rule methods:

| Method | Behaviour |
|---|---|
| `once($match)` / `then($match)` | Require exactly one token matching the callable |
| `separated($match, $sep)` | One or more matching tokens with a separator between them |
| `optional($builder)` | Run a sub-builder; succeed whether or not it matches |
| `group($builder)` | Run a sub-builder; fail if it does not match |
| `or(...$builders)` | Try each sub-builder in order; succeed on first match |
| `around($back, $fwd)` | Match backward pattern AND forward pattern from current position |
| `skipUntil($callback)` | Advance until the callback returns true |
| `until($callback)` | Set a stop condition for all subsequent rules |

Direction: `lookAhead()` (default, offset+1) or `lookBehind()` (offset-1).

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

The final translation from symbol → keyword happens in `ModifiersBinder`, which runs during the Binder pass.

---

## Binder — Symbol Binding Pass

`Binder.php` is the orchestrator. It instantiates all `Binder` implementations in order and calls `bind(Program)`, which invokes `ProgramBinder` first (the walker), which then dispatches every node to every registered binder.

Execution order (defined in `Binder.php`):

| Order | Class | What it does |
|---|---|---|
| 1 | `TypeRegistrationBinder` | Registers class/interface names in SymbolTable (first pass, needed by later binders) |
| 2 | `ProgramBinder` | Walks top-level statements, dispatches each to all binders |
| 3 | `ClassBodyBinder` | Walks class body children |
| 4 | `InterfaceBinder` | Walks interface body children |
| 5 | `ClassBinder` | Walks ClassNode body, dispatches members |
| 6 | `MagicMethodDeclarationBinder` | Attaches MagicMethods spec to magic method nodes |
| 7 | `MethodDeclarationBinder` (Interface) | Binds interface method signature markers |
| 8 | `MethodParamResolutionBinder` | Resolves method-call parameters for type-method consumption |
| 9 | `PropertyBinder` | Normalises property modifiers and type list |
| 10 | `PropertyTypeResolutionBinder` | Categorises all types in `PropertyNode.types[]` and `ParamArgumentNode.types[]` into `resolvedTypeInfo[]` |
| 11 | `ModifiersBinder` | Translates `*`, `+`, `#` → `public`, `protected`, `private` on every node that has `$modifiers` |

---

## Checker — Semantic Validation Pass

`Checker.php` is the orchestrator. Instantiates all `Checker` implementations. Each checker extends the abstract `Checker` base which provides `willCheck(array $nodes, Checker $ctx)` to recursively dispatch sub-nodes.

Registered checkers and what they validate:

| Class | Triggers on | Validates |
|---|---|---|
| `QueueChecker` | Queue-related nodes | Queue<T> type usage correctness |
| `MethodConsumptionChecker` | `FunctionNode` (method call) | Required params present; param subtypes match generic constraints |
| `MagicMethodsChecker` | `MethodDeclarationNode` (magic token) | Return type and each param type match the MagicMethods spec exactly |
| `ProgramChecker` | `Program` | Walks all top-level statements, dispatches to child checkers |
| `ClassChecker` | `ClassNode` | Non-abstract, non-trait classes must have a lifecycle declaration (`as scoped/singleton/transient` or `newable`) |
| `ClassBodyChecker` | `ClassNode` | Readonly/immutable classes cannot have property defaults; abstract properties only in abstract classes |
| `MethodReturnChecker` | `MethodDeclarationNode` | `method?` → exclusively `Bool`; `method!` → exclusively `Void`; array literal return types match declared type |

---

## Emitter — NodeEmitter Dispatch

`Emitter.php` builds an `EmitterDispatcher` with all registered `NodeEmitter` implementations. The dispatcher walks the AST and for each Node iterates all emitters, calling the first whose `supports()` returns true.

```php
// Each NodeEmitter implements the NodeEmitter interface:
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

### EmitContext

Passed to every emitter. Contains:

| Field | Purpose |
|---|---|
| `$dev` | Whether dev mode is active (affects generated comments/debug output) |
| `$uses` | `UseRegistry` — accumulates `use` statements added during emission |
| `$types` | `PhpTypeResolver` — resolves PHireScript types to PHP type strings and constructor assignments |
| `$dependencyManager` | `DependencyGraphBuilder` — used when emitting `use`/`external` to register dependency edges |
| `$emitter` | `EmitterDispatcher` — allows emitters to delegate sub-node emission |
| `$insideInterface` | Flag: current scope is an interface body |
| `$insideClass` | Flag: current scope is a class body |
| `$insideMethodSignature` | Flag: currently emitting a method signature |
| `$currentMethodReturnType` | The return type string of the method being emitted |

### PhpTypeResolver

Resolves PHireScript property types to their PHP equivalents during emission:

- `primitive` → native PHP type (`string`, `int`, `bool`, etc.)
- `supertype` → `string` (supertypes are validated strings at runtime)
- `metatype` / `custom` → class name as-is
- multiple types → PHP union type (`string|int|null`)
- `null` always appended last for consistent ordering

Also generates constructor assignment code: for supertypes uses `SuperType::cast($v)`, for metatypes uses `new MetaType($v)`, for union types uses `UnionType::cast($v, [...classes...])`.

Emitters can delegate sub-parts of their structure to other emitters via `$ctx->emitter->emit(subNode, $ctx)`.

```
src/Compiler/Emitter/
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

`SymbolTableManager` auto-loads these files via reflection during parser startup, making all method descriptors available to Resolvers at parse time.

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
src/Compiler/Parser/Ast/Nodes/<category>/YourFeatureNode.php
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
If the feature introduces new names into scope or needs type resolution:
```
src/Compiler/Binder/<category>/YourFeatureBinder.php
```
Register it in `src/Compiler/Binder.php`.

### 4. Checker — semantic validation (if applicable)
If the feature has type constraints or semantic rules:
```
src/Compiler/Checker/<category>/YourFeatureChecker.php
```
Register it in `src/Compiler/Checker.php`.

### 5. Emitter — PHP code generation
Create a class implementing the `NodeEmitter` interface:
```
src/Compiler/Emitter/<category>/YourFeatureEmitter.php
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
3. `SymbolTableManager` auto-discovers it at parse startup — no manual wiring needed
