<?php

declare(strict_types=1);

/*
 * This file is part of the simplesamlphp-module-authchain.
 *
 * Copyright (C) 2018 by Sergio Gómez <sergio@uco.es>
 *
 * This code was developed by Universidad de Córdoba (UCO https://www.uco.es)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleSAML\Module\authchain\Auth\Source;

use SimpleSAML\Auth\Source;
use SimpleSAML\Error\AuthSource as AuthSourceError;
use SimpleSAML\Error\Error;
use SimpleSAML\Error\Exception as SspException;
use SimpleSAML\Logger;
use SimpleSAML\Module\core\Auth\UserPassBase;

class AuthChain extends UserPassBase
{
    /**
     * @var string[]
     */
    private array $sources;

    /**
     * @param array<string, mixed> $info
     * @param array<string, mixed> $config
     */
    public function __construct(array $info, array $config)
    {
        parent::__construct($info, $config);

        if (!array_key_exists('sources', $config) || !is_array($config['sources'])) {
            throw new SspException('The required "sources" config option was not found');
        }

        $this->sources = $config['sources'];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function login(string $username, #[\SensitiveParameter] string $password): array
    {
        $lastError = null;

        foreach ($this->sources as $authId) {
            $as = Source::getById($authId);

            if ($as === null) {
                throw new SspException("Invalid authentication source: {$authId}");
            }

            if (!$as instanceof UserPassBase) {
                Logger::error("Could not use {$authId} (not a UserPassBase), trying next");
                continue;
            }

            try {
                return $as->login($username, $password);
            } catch (AuthSourceError $e) {
                Logger::error("Could not connect to {$authId}, trying next");
            } catch (Error $e) {
                if ($e->getErrorCode() === 'WRONGUSERPASS') {
                    Logger::debug("Failed {$authId} with WRONGUSERPASS, trying next");
                } else {
                    $lastError = $e;
                }
            }
        }

        if ($lastError !== null) {
            throw $lastError;
        }

        throw new Error('WRONGUSERPASS');
    }
}
