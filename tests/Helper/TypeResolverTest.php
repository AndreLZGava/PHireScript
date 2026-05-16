<?php

declare(strict_types=1);

namespace PHireScript\Tests\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHireScript\Helper\TypeResolver;

class TypeResolverTest extends TestCase
{
    // -------------------------------------------------------------------------
    // isPrimitive
    // -------------------------------------------------------------------------

    #[DataProvider('primitiveProvider')]
    public function testIsPrimitiveReturnsTrueForAllPrimitives(string $type): void
    {
        $this->assertTrue(TypeResolver::isPrimitive($type));
    }

    #[DataProvider('nonPrimitiveProvider')]
    public function testIsPrimitiveReturnsFalseForNonPrimitives(string $type): void
    {
        $this->assertFalse(TypeResolver::isPrimitive($type));
    }

    // -------------------------------------------------------------------------
    // isMetaType
    // -------------------------------------------------------------------------

    #[DataProvider('metaTypeProvider')]
    public function testIsMetaTypeReturnsTrueForAllMetaTypes(string $type): void
    {
        $this->assertTrue(TypeResolver::isMetaType($type));
    }

    #[DataProvider('nonMetaTypeProvider')]
    public function testIsMetaTypeReturnsFalseForNonMetaTypes(string $type): void
    {
        $this->assertFalse(TypeResolver::isMetaType($type));
    }

    // -------------------------------------------------------------------------
    // isSuperType
    // -------------------------------------------------------------------------

    #[DataProvider('superTypeProvider')]
    public function testIsSuperTypeReturnsTrueForAllSuperTypes(string $type): void
    {
        $this->assertTrue(TypeResolver::isSuperType($type));
    }

    #[DataProvider('nonSuperTypeProvider')]
    public function testIsSuperTypeReturnsFalseForNonSuperTypes(string $type): void
    {
        $this->assertFalse(TypeResolver::isSuperType($type));
    }

    // -------------------------------------------------------------------------
    // isBuiltIn
    // -------------------------------------------------------------------------

    #[DataProvider('primitiveProvider')]
    public function testIsBuiltInReturnsTrueForPrimitive(string $type): void
    {
        $this->assertTrue(TypeResolver::isBuiltIn($type));
    }

    #[DataProvider('metaTypeProvider')]
    public function testIsBuiltInReturnsTrueForMetaType(string $type): void
    {
        $this->assertTrue(TypeResolver::isBuiltIn($type));
    }

    #[DataProvider('superTypeProvider')]
    public function testIsBuiltInReturnsTrueForSuperType(string $type): void
    {
        $this->assertTrue(TypeResolver::isBuiltIn($type));
    }

    public function testIsBuiltInReturnsFalseForCustomType(): void
    {
        $this->assertFalse(TypeResolver::isBuiltIn('MyCustomClass'));
    }

    public function testIsBuiltInReturnsFalseForUnknownType(): void
    {
        $this->assertFalse(TypeResolver::isBuiltIn(''));
    }

    // -------------------------------------------------------------------------
    // nativeType
    // -------------------------------------------------------------------------

    #[DataProvider('primitiveToNativeProvider')]
    public function testNativeTypeReturnsCorrectPhpType(string $phireType, string $phpType): void
    {
        $this->assertSame($phpType, TypeResolver::nativeType($phireType));
    }

    public function testNativeTypeThrowsForMetaType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TypeResolver::nativeType('Date');
    }

    public function testNativeTypeThrowsForSuperType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TypeResolver::nativeType('Email');
    }

    public function testNativeTypeThrowsForUnknownType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TypeResolver::nativeType('MyClass');
    }

    // -------------------------------------------------------------------------
    // fullClassName
    // -------------------------------------------------------------------------

    #[DataProvider('metaTypeClassProvider')]
    public function testFullClassNameReturnsCorrectFqcnForMetaType(string $type, string $expected): void
    {
        $this->assertSame($expected, TypeResolver::fullClassName($type));
    }

    #[DataProvider('superTypeClassProvider')]
    public function testFullClassNameReturnsCorrectFqcnForSuperType(string $type, string $expected): void
    {
        $this->assertSame($expected, TypeResolver::fullClassName($type));
    }

    public function testFullClassNameThrowsForPrimitive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TypeResolver::fullClassName('Int');
    }

    public function testFullClassNameThrowsForUnknownType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TypeResolver::fullClassName('Unknown');
    }

    // -------------------------------------------------------------------------
    // classify
    // -------------------------------------------------------------------------

    #[DataProvider('primitiveToNativeProvider')]
    public function testClassifyReturnsPrimitiveCategoryWithNative(string $phireType, string $phpType): void
    {
        $result = TypeResolver::classify($phireType);

        $this->assertNotNull($result);
        $this->assertSame('primitive', $result['category']);
        $this->assertSame($phpType, $result['native']);
        $this->assertArrayNotHasKey('class', $result);
    }

    #[DataProvider('metaTypeProvider')]
    public function testClassifyReturnsMetatypeCategoryWithClass(string $type): void
    {
        $result = TypeResolver::classify($type);

        $this->assertNotNull($result);
        $this->assertSame('metatype', $result['category']);
        $this->assertStringStartsWith('PHireScript\\Runtime\\Types\\MetaTypes\\', $result['class']);
        $this->assertStringEndsWith($type, $result['class']);
        $this->assertArrayNotHasKey('native', $result);
    }

    #[DataProvider('superTypeProvider')]
    public function testClassifyReturnsSupertypeCategoryWithClass(string $type): void
    {
        $result = TypeResolver::classify($type);

        $this->assertNotNull($result);
        $this->assertSame('supertype', $result['category']);
        $this->assertStringStartsWith('PHireScript\\Runtime\\Types\\SuperTypes\\', $result['class']);
        $this->assertStringEndsWith($type, $result['class']);
        $this->assertArrayNotHasKey('native', $result);
    }

    public function testClassifyReturnsNullForCustomType(): void
    {
        $this->assertNull(TypeResolver::classify('UserRepository'));
    }

    public function testClassifyReturnsNullForEmptyString(): void
    {
        $this->assertNull(TypeResolver::classify(''));
    }

    // -------------------------------------------------------------------------
    // Specific edge-case assertions
    // -------------------------------------------------------------------------

    public function testNullPrimitiveMapsToPhpNull(): void
    {
        $this->assertSame('null', TypeResolver::nativeType('Null'));
    }

    public function testAnyPrimitiveMapsToMixed(): void
    {
        $this->assertSame('mixed', TypeResolver::nativeType('Any'));
    }

    public function testMixedPrimitiveMapsToMixed(): void
    {
        $this->assertSame('mixed', TypeResolver::nativeType('Mixed'));
    }

    public function testCollectionTypesMapeToArray(): void
    {
        foreach (['Queue', 'List', 'Stack', 'Map', 'Struct'] as $type) {
            $this->assertSame('array', TypeResolver::nativeType($type), "$type should map to array");
        }
    }

    public function testCaseSensitivity(): void
    {
        $this->assertFalse(TypeResolver::isPrimitive('string'));
        $this->assertFalse(TypeResolver::isPrimitive('int'));
        $this->assertFalse(TypeResolver::isMetaType('date'));
        $this->assertFalse(TypeResolver::isSuperType('email'));
    }

    // -------------------------------------------------------------------------
    // Data providers
    // -------------------------------------------------------------------------

    public static function primitiveProvider(): array
    {
        return [
            ['String'], ['Int'], ['Float'], ['Bool'], ['Object'], ['Array'],
            ['Void'], ['Null'], ['Mixed'], ['Any'],
            ['Queue'], ['List'], ['Stack'], ['Map'], ['Struct'],
        ];
    }

    public static function nonPrimitiveProvider(): array
    {
        return [
            ['Date'], ['Email'], ['UserService'], [''], ['string'], ['int'],
        ];
    }

    public static function metaTypeProvider(): array
    {
        return [
            ['Card'], ['Currency'], ['Date'], ['DateTime'], ['Password'], ['Phone'], ['Time'],
        ];
    }

    public static function nonMetaTypeProvider(): array
    {
        return [
            ['Int'], ['Email'], ['Custom'], [''], ['date'],
        ];
    }

    public static function superTypeProvider(): array
    {
        return [
            ['Email'], ['Ipv4'], ['Ipv6'], ['Uuid'], ['Color'], ['Url'],
            ['CardNumber'], ['Cron'], ['Cvv'], ['Duration'], ['ExpiryDate'],
            ['Json'], ['Mac'], ['Slug'],
        ];
    }

    public static function nonSuperTypeProvider(): array
    {
        return [
            ['Int'], ['Date'], ['Custom'], [''], ['email'],
        ];
    }

    public static function primitiveToNativeProvider(): array
    {
        return [
            ['String', 'string'],
            ['Int',    'int'],
            ['Float',  'float'],
            ['Bool',   'bool'],
            ['Object', 'object'],
            ['Array',  'array'],
            ['Void',   'void'],
            ['Null',   'null'],
            ['Mixed',  'mixed'],
            ['Any',    'mixed'],
            ['Queue',  'array'],
            ['List',   'array'],
            ['Stack',  'array'],
            ['Map',    'array'],
            ['Struct', 'array'],
        ];
    }

    public static function metaTypeClassProvider(): array
    {
        return [
            ['Date',     'PHireScript\\Runtime\\Types\\MetaTypes\\Date'],
            ['Currency', 'PHireScript\\Runtime\\Types\\MetaTypes\\Currency'],
            ['Phone',    'PHireScript\\Runtime\\Types\\MetaTypes\\Phone'],
            ['Card',     'PHireScript\\Runtime\\Types\\MetaTypes\\Card'],
            ['DateTime', 'PHireScript\\Runtime\\Types\\MetaTypes\\DateTime'],
            ['Password', 'PHireScript\\Runtime\\Types\\MetaTypes\\Password'],
            ['Time',     'PHireScript\\Runtime\\Types\\MetaTypes\\Time'],
        ];
    }

    public static function superTypeClassProvider(): array
    {
        return [
            ['Email',      'PHireScript\\Runtime\\Types\\SuperTypes\\Email'],
            ['Ipv4',       'PHireScript\\Runtime\\Types\\SuperTypes\\Ipv4'],
            ['Url',        'PHireScript\\Runtime\\Types\\SuperTypes\\Url'],
            ['Uuid',       'PHireScript\\Runtime\\Types\\SuperTypes\\Uuid'],
            ['Color',      'PHireScript\\Runtime\\Types\\SuperTypes\\Color'],
            ['CardNumber', 'PHireScript\\Runtime\\Types\\SuperTypes\\CardNumber'],
            ['Cron',       'PHireScript\\Runtime\\Types\\SuperTypes\\Cron'],
            ['Json',       'PHireScript\\Runtime\\Types\\SuperTypes\\Json'],
            ['Slug',       'PHireScript\\Runtime\\Types\\SuperTypes\\Slug'],
        ];
    }
}
