<?php

namespace Tito10047\EverythingYamlBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Tito10047\EverythingYamlBundle\Exception\InvalidArgumentException;
use Tito10047\EverythingYamlBundle\GreeterInterface;
use Twig\Environment;

/**
 * Greets people on a page rendered from the "@Tito10047EverythingYaml/hello.html.twig" template.
 *
 * Routed by the "tito10047_everything_yaml_hello" route.
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

        return new Response($this->twig->render('@Tito10047EverythingYaml/hello.html.twig', [
            'greeting' => $greeting,
        ]));
    }
}
