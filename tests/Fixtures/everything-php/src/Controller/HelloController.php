<?php

namespace Zeta\EverythingPhpBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Twig\Environment;
use Zeta\EverythingPhpBundle\Exception\InvalidArgumentException;
use Zeta\EverythingPhpBundle\GreeterInterface;

/**
 * Greets people on a page rendered from the "@ZetaEverythingPhp/hello.html.twig" template.
 *
 * Routed by the "zeta_everything_php_hello" route.
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

        return new Response($this->twig->render('@ZetaEverythingPhp/hello.html.twig', [
            'greeting' => $greeting,
        ]));
    }
}
