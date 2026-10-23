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

namespace Tito10047\BundleMaker\Options;

use Composer\Util\ProcessExecutor;

/**
 * Reads the user's Git configuration to propose sensible defaults (author, vendor name).
 */
final class GitConfig
{
    /** @var array<string, string|null> */
    private array $cache = [];

    public function __construct(private readonly ProcessExecutor $process = new ProcessExecutor())
    {
    }

    /**
     * Returns the value of the given key (e.g. "user.name"), or null when it is not set or Git is not available.
     */
    public function get(string $key): ?string
    {
        if (\array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $output = '';
        try {
            $status = $this->process->execute(['git', 'config', '--get', $key], $output);
        } catch (\RuntimeException) {
            $status = 1;
        }

        $value = 0 === $status && \is_string($output) ? trim($output) : '';

        return $this->cache[$key] = '' === $value ? null : $value;
    }
}
