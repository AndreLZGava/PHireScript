# PHireScript

> **A modern programming language that compiles to PHP.**
>
> *Inspired by PHP, TypeScript, Ruby and modern language design.*

> **⚠️ Experimental Project**
>
> PHireScript is a personal study project focused on compiler construction, language design and transpilation.
>
> It is **not production ready** and should **not** be used for real-world applications.

---

# What is PHireScript?

PHireScript is an experimental programming language that transpiles into native PHP.

Rather than extending PHP, PHireScript explores what PHP development could look like if the language were designed today, without decades of backwards compatibility constraints.

Its goal is to provide a cleaner syntax, stronger typing, richer language features and better compile-time validation while remaining fully compatible with the PHP runtime through transpilation.

Think of it as asking:

> **"What if PHP could be redesigned from scratch today?"**

---

# Why another language?

PHP is one of the most successful programming languages ever created.

Its stability, ecosystem and backwards compatibility are some of its greatest strengths.

However, those same strengths make radical changes extremely difficult.

Removing `$` from variables.

Removing mandatory semicolons.

Redesigning namespaces.

Introducing richer native types.

Changing how loops work.

Improving object-oriented APIs.

These kinds of changes would break millions of existing applications.

PHireScript exists because it doesn't have that limitation.

It is a playground where new ideas can be explored freely while still compiling into valid PHP.

The purpose isn't to replace PHP.

The purpose is to experiment with what PHP development *could become* if backwards compatibility were no longer a concern.

---

# Philosophy

PHireScript follows a few simple principles.

* Everything should have a type.
* Strong compile-time validation is better than runtime surprises.
* Objects should expose behavior instead of relying on global helper functions.
* Boilerplate should disappear whenever possible.
* Generated PHP should remain clean and readable.
* Modern language ideas should be explored without being constrained by legacy syntax.

---

# Current Status

PHireScript is currently an **experimental language**.

Many features are:

* ✅ Implemented
* 🚧 Partially implemented
* 💡 Planned

The primary goals of this project are learning and experimentation.

It is a place to study:

* language design
* parser implementation
* compiler architecture
* transpilers
* static analysis
* type systems
* developer tooling

---

# Features

## Clean Syntax

One of the goals is reducing unnecessary syntax inherited from PHP.

### Variables

Instead of

```php
$name = "John";
```

PHireScript simply uses

```phs
name = "John"
```

No `$`.

No mandatory semicolons.

---

## Automatic Type Inference

Types are inferred whenever possible.

```phs
name = "John"
```

The compiler understands that `name` is a `String`.

Types always use PascalCase.

Examples:

```text
String
Bool
Int
Float
Array
Object
Email
Uuid
Duration
Color
Json
```

---

## Simplified Method Signatures

Instead of writing

```php
public function exists(): bool
```

PHireScript allows

```phs
exists?(): Bool
```

Where `?` represents `Bool`, making it easy to identify what that method returns.

Likewise,

```php
public function save(): void
```

becomes

```phs
save!(): Void
```

Where `!` represents `Void`.

The goal is to reduce verbosity while keeping code expressive.

---

## Visibility

Instead of keywords like

```php
public
protected
private
```

PHireScript uses symbols.

| Symbol      | Visibility |
| ----------- | ---------- |
| *(default)* | Public     |
| `*`         | Public     |
| `+`         | Protected  |
| `#`         | Private    |

This dramatically reduces visual noise when declaring classes, methods and attributes.

---

## Automatic Getters and Setters

Declaring getters and setters should not require writing repetitive code.

Instead of manually writing methods, PHireScript provides attribute modifiers.

Example:

```phs
<> # String name
```

The compiler automatically generates PHP equivalent to:

```php
private string $name;

public function getName(): string
{
    return $this->name;
}

public function setName(string $name): void
{
    $this->name = $name;
}
```

---

## Everything is Object-Oriented

A long-term goal of PHireScript is avoiding large collections of standalone helper functions.

Instead, behavior should belong to typed objects whenever possible.

This improves:

* discoverability
* IDE autocompletion
* readability
* refactoring

---

## Rich Type System

PHireScript distinguishes between primitive types, supertypes and metatypes.

Examples include:

```text
String
Int
Bool
Float
Array
Object
Email
Uuid
Json
IPv4
IPv6
Slug
Duration
Cron
Color
CardNumber
CVV
```

Some compile directly to PHP primitive types.

Others become dedicated runtime objects with additional behavior.

The intention is to move domain validation closer to the type system itself.

---

## Compile-Time Validation

One of the primary objectives is catching as many errors as possible during compilation.

Examples include:

* incompatible assignments
* invalid method calls
* invalid literal values
* type violations
* domain-specific validations

Moving errors from runtime to compile time is one of the core ideas behind the language.

---

## Namespaces

Namespaces are inspired more by Java than PHP.

Instead of manually matching folder structures with long namespace declarations, PHireScript introduces packages.

Example:

```phs
pkg Example.Test.Database
```

During compilation, packages are converted into valid PHP namespaces.

This allows projects to reorganize directory structures without rewriting namespace declarations throughout the codebase.

---

## Collections

Traditional language constructs like `foreach` are planned to become methods available only on iterable objects.

Instead of

```php
foreach ($users as $index => $user) {

}
```

the intended syntax is

```phs
users.each((User user, Int index) => {

})
```

This follows the philosophy that behavior should belong to objects rather than language keywords whenever possible.

---

## Specialized Object Types

Besides traditional classes, PHireScript is designed to support additional language constructs.

Examples include:

* Classes
* Immutables
* Types
* Validation classes
* Exception objects
* Attributes
* Etc

Each construct exists to solve a specific problem while generating standard PHP underneath.

Some of these features are still under development.

---

## Testing

PHireScript plans to support dedicated validation/test files.

Example extension:

```text
.pht
```

These files are intended to provide language-level constructs specifically designed for testing.

This feature is still experimental.

---

## Dependency Injection is planned as a first-class language feature.

Rather than enforcing a single dependency injection strategy, PHireScript aims to give developers the freedom to choose the approach that best fits their project.

The long-term goal is to support multiple dependency injection providers, including:

Native PHireScript dependency injection
Symfony Dependency Injection
Laravel Service Container

This allows developers to take advantage of the ecosystems they already use while keeping dependency injection integrated into the language itself.

The architecture is being designed with extensibility in mind, making it possible to support additional dependency injection containers in the future.

---

## Compiler

The compiler is written in PHP.

Current work includes:

* Parser
* Symbol table
* Type analysis
* Static validation
* PHP code generation
* Internal compiler cache
* Other features

The compiler architecture continues to evolve as new language features are added.

---

# Example

```phs
pkg Example.Application

@Entity
class Person as scoped {

    @Field(min: 3, max: 255)
    <> # String name
    
    new(String name): Void {
        this.name = name
    }

    greet(String other): String {
        return "Hello ".join(other)
    }

    exists?(): Bool {
        return true
    }

    save!(): Void {
        ...
    }
}
```

The compiler generates valid PHP while preserving the intended behavior.

---

# Roadmap

Some planned ideas include:

* Generics
* Better diagnostics
* Richer type inference
* Immutable objects
* Native dependency injection
* Reflection improvements
* Better IDE support
* Faster incremental compilation
* Compile-time optimizations
* Additional metatypes
* More expressive collection APIs

This roadmap is intentionally flexible.

Since PHireScript is an experimental language, ideas are expected to evolve over time.

---

# # Contributing

Contributions, discussions and suggestions are always welcome.

PHireScript is primarily a learning and research project, so discussions about language design are just as valuable as code contributions.

If you have an idea, a suggestion, or want to challenge a design decision, feel free to open an issue or submit a pull request.

## Development Environment

Although there isn't a formal contribution guide yet, you can start developing using the following projects.

### VS Code Extension

A development version of the PHireScript VS Code extension is available here:

* https://github.com/AndreLZGava/PHire-Script-Extension

The extension provides language support and is recommended when developing or testing PHireScript features.

### Sandbox

The official development sandbox is available at:

* https://github.com/AndreLZGava/PHire-Script-Sandbox

The sandbox contains:

* Working PHireScript examples
* Documentation
* Sample projects
* Test scenarios
* Playground code for new language features

## Pull Requests

When contributing new language features or compiler improvements, contributors are expected to:

* Add or update examples in the Sandbox repository demonstrating the new functionality.
* Ensure all existing tests continue to pass.
* Add new tests whenever appropriate.
* Keep the generated PHP output consistent with the language specification.

The Sandbox serves as both the primary documentation and the reference implementation for language features. Every significant language addition should include practical examples that demonstrate how the feature is intended to be used.

As the project evolves, a dedicated contribution guide and coding standards document will be added.

---

# Goals

PHireScript is **not** trying to become "PHP 2.0".

It is an experiment.

A laboratory for language design.

A place to explore ideas that would be extremely difficult—or impossible—to introduce into PHP itself because of backwards compatibility.

Some experiments will succeed.

Some will fail.

Both outcomes are valuable.

---

# License

Released under the MIT License.
