<?php

namespace MyVendor\EverythingYamlBundle\Controller;

use MyVendor\EverythingYamlBundle\Exception\InvalidArgumentException;
use MyVendor\EverythingYamlBundle\GreeterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Twig\Environment;

/**
 * Greets people on a page rendered from the "@MyVendorEverythingYaml/hello.html.twig" template.
 *
 * Routed by the "my_vendor_everything_yaml_hello" route.
 */
final readonly class HelloController
{
    public function __construct(
        private GreeterInterface $greeter,
        private Environment $twig,
    ) {
    }

    public function __invoke(string $name): Response
    {
        try {
            $greeting = $this->greeter->greet($name);
        } catch (InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        return new Response($this->twig->render('@MyVendorEverythingYaml/hello.html.twig', [
            'greeting' => $greeting,
        ]));
    }
}
