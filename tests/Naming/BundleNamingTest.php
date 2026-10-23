<?php

declare(strict_types=1);

/*
 * This file is part of the tito10047/symfony-bundle-maker package.
 *
 * (c) Jozef Môstka <jozef@mostka.sk>
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tito10047\BundleMaker\Tests\Naming;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Tito10047\BundleMaker\Naming\BundleNaming;

final class BundleNamingTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, string, string, string, string, string}>
     */
    public static function provideValidPackageNames(): iterable
    {
        // package name => namespace, class, alias, short name (domain/Twig), slug, public assets dir
        yield 'conventional' => ['acme/blog-bundle', 'Acme\\BlogBundle', 'AcmeBlogBundle', 'acme_blog', 'AcmeBlog', 'acme-blog', 'bundles/acmeblog'];
        yield 'multi-word name' => ['acme/foo-bar-bundle', 'Acme\\FooBarBundle', 'AcmeFooBarBundle', 'acme_foo_bar', 'AcmeFooBar', 'acme-foo-bar', 'bundles/acmefoobar'];
        yield 'vendor with dash' => ['my-vendor/foo-bar-bundle', 'MyVendor\\FooBarBundle', 'MyVendorFooBarBundle', 'my_vendor_foo_bar', 'MyVendorFooBar', 'my-vendor-foo-bar', 'bundles/myvendorfoobar'];
        yield 'without -bundle suffix' => ['acme/blog', 'Acme\\BlogBundle', 'AcmeBlogBundle', 'acme_blog', 'AcmeBlog', 'acme-blog', 'bundles/acmeblog'];
        yield 'glued bundle suffix' => ['acme/blogbundle', 'Acme\\BlogBundle', 'AcmeBlogBundle', 'acme_blog', 'AcmeBlog', 'acme-blog', 'bundles/acmeblog'];
        yield 'underscore suffix' => ['acme/blog_bundle', 'Acme\\BlogBundle', 'AcmeBlogBundle', 'acme_blog', 'AcmeBlog', 'acme-blog', 'bundles/acmeblog'];
        yield 'digits glued' => ['acme/blog2-bundle', 'Acme\\Blog2Bundle', 'AcmeBlog2Bundle', 'acme_blog2', 'AcmeBlog2', 'acme-blog2', 'bundles/acmeblog2'];
        yield 'digit segment' => ['acme/foo-2fa-bundle', 'Acme\\Foo2faBundle', 'AcmeFoo2faBundle', 'acme_foo2fa', 'AcmeFoo2fa', 'acme-foo2fa', 'bundles/acmefoo2fa'];
        yield 'digit-only segment' => ['acme/foo-2-bar-bundle', 'Acme\\Foo2BarBundle', 'AcmeFoo2BarBundle', 'acme_foo2_bar', 'AcmeFoo2Bar', 'acme-foo2-bar', 'bundles/acmefoo2bar'];
        yield 'vendor with digits' => ['acme42/blog-bundle', 'Acme42\\BlogBundle', 'Acme42BlogBundle', 'acme42_blog', 'Acme42Blog', 'acme42-blog', 'bundles/acme42blog'];
        yield 'name repeats the vendor' => ['acme/acme-blog-bundle', 'Acme\\AcmeBlogBundle', 'AcmeBlogBundle', 'acme_blog', 'AcmeBlog', 'acme-blog', 'bundles/acmeblog'];
        yield 'name equals the vendor' => ['acme/acme-bundle', 'Acme\\AcmeBundle', 'AcmeBundle', 'acme', 'Acme', 'acme', 'bundles/acme'];
        yield 'name starts with the vendor word' => ['acme/acmeblog-bundle', 'Acme\\AcmeblogBundle', 'AcmeAcmeblogBundle', 'acme_acmeblog', 'AcmeAcmeblog', 'acme-acmeblog', 'bundles/acmeacmeblog'];
        yield 'dotted name' => ['acme/foo.bar-bundle', 'Acme\\FooBarBundle', 'AcmeFooBarBundle', 'acme_foo_bar', 'AcmeFooBar', 'acme-foo-bar', 'bundles/acmefoobar'];
        yield 'double dash' => ['acme/foo--bar-bundle', 'Acme\\FooBarBundle', 'AcmeFooBarBundle', 'acme_foo_bar', 'AcmeFooBar', 'acme-foo-bar', 'bundles/acmefoobar'];
    }

    #[DataProvider('provideValidPackageNames')]
    public function testDerivesNames(string $packageName, string $namespace, string $className, string $alias, string $shortName, string $slug, string $publicAssetsDirectory): void
    {
        $naming = new BundleNaming($packageName);

        self::assertSame($packageName, $naming->packageName);
        self::assertSame($namespace, $naming->namespace);
        self::assertSame($namespace.'\\Tests', $naming->testsNamespace);
        self::assertSame($className, $naming->className);
        self::assertSame($namespace.'\\'.$className, $naming->fqcn);
        self::assertSame($alias, $naming->alias);
        self::assertSame($shortName, $naming->shortName);
        self::assertSame($shortName, $naming->translationDomain);
        self::assertSame($shortName, $naming->twigNamespace);
        self::assertSame($slug, $naming->slug);
        self::assertSame($publicAssetsDirectory, $naming->publicAssetsDirectory);
        self::assertSame(explode('/', $packageName)[0], $naming->vendor);
        self::assertSame(explode('/', $packageName)[1], $naming->project);
        self::assertSame(explode('\\', $namespace)[0], $naming->vendorNamespace);
    }

    /**
     * The alias must be exactly what AbstractBundle derives at runtime from the class name.
     */
    #[DataProvider('provideValidPackageNames')]
    public function testAliasMatchesAbstractBundleAtRuntime(string $packageName): void
    {
        $naming = new BundleNaming($packageName);
        // a unique namespace per data set, so classes never collide
        $namespace = 'BundleMakerAliasTest\\'.$naming->vendorNamespace.ucfirst(hash('xxh3', $packageName));
        $file = sys_get_temp_dir().'/bundle-maker-alias-'.hash('xxh3', $packageName).'.php';
        file_put_contents($file, \sprintf("<?php\nnamespace %s;\nfinal class %s extends \\%s {}\n", $namespace, $naming->className, AbstractBundle::class));

        try {
            require_once $file;
        } finally {
            unlink($file);
        }

        /** @var class-string<AbstractBundle> $class */
        $class = $namespace.'\\'.$naming->className;
        $extension = (new $class())->getContainerExtension();

        self::assertNotNull($extension);
        self::assertSame($naming->alias, $extension->getAlias());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideInvalidPackageNames(): iterable
    {
        yield 'empty' => ['', 'must be lowercase, in the "vendor/name" format'];
        yield 'no vendor' => ['blog-bundle', 'must be lowercase, in the "vendor/name" format'];
        yield 'uppercase' => ['Acme/Blog-bundle', 'must be lowercase'];
        yield 'too many slashes' => ['acme/blog/bundle', 'vendor/name'];
        yield 'vendor starts with a digit' => ['1acme/blog-bundle', 'must start with a letter'];
        yield 'name starts with a digit' => ['acme/2fa-bundle', 'must start with a letter'];
        yield 'trailing dash' => ['acme/blog-', 'vendor/name'];
        yield 'spaces' => ['acme/blog bundle', 'vendor/name'];
        yield 'only bundle' => ['acme/bundle', 'not only be "bundle"'];
    }

    #[DataProvider('provideInvalidPackageNames')]
    public function testRejectsInvalidPackageNames(string $packageName, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new BundleNaming($packageName);
    }

    /**
     * @return iterable<array{string, bool, string}>
     */
    public static function provideSuggestedPackageNames(): iterable
    {
        yield ['acme/blog-bundle', true, 'acme/blog-bundle'];
        yield ['acme/blog', false, 'acme/blog-bundle'];
        yield ['acme/blogbundle', false, 'acme/blog-bundle'];
        yield ['acme/blog_bundle', false, 'acme/blog-bundle'];
        yield ['acme/blog.bundle', false, 'acme/blog-bundle'];
        yield ['acme/foo-bar', false, 'acme/foo-bar-bundle'];
    }

    #[DataProvider('provideSuggestedPackageNames')]
    public function testSuggestsTheBundleSuffix(string $packageName, bool $hasSuffix, string $suggestion): void
    {
        $naming = new BundleNaming($packageName);

        self::assertSame($hasSuffix, $naming->hasBundleSuffix());
        self::assertSame($suggestion, $naming->suggestedPackageName());
    }
}
