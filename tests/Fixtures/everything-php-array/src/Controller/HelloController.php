<?php

namespace Acme\EverythingPhpArrayBundle\Controller;

use Acme\EverythingPhpArrayBundle\Exception\InvalidArgumentException;
use Acme\EverythingPhpArrayBundle\GreeterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Twig\Environment;

/**
 * Greets people on a page rendered from the "@AcmeEverythingPhpArray/hello.html.twig" template.
 *
 * Routed by the "acme_everything_php_array_hello" route.
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

        return new Response($this->twig->render('@AcmeEverythingPhpArray/hello.html.twig', [
            'greeting' => $greeting,
        ]));
    }
}
