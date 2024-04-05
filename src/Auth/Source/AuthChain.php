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

use SimpleSAML\Assert\Assert;
use SimpleSAML\{Auth, Error, Logger};
use SimpleSAML\Module\core\Auth\UserPassBase;

use function is_subclass_of;
use function sprintf;

class AuthChain extends UserPassBase
{
    /**
     * @var array $sources
     */
    private array $sources;

    public function __construct(array $info, array $config)
    {
        parent::__construct($info, $config);

        Assert::keyExists(
            $config,
            'sources',
            'The required "sources" config option was not found',
            Error\Exception::class,
        );

        $this->sources = $config['sources'];
    }

    protected function login(string $username, string $password): array
    {
        $lastError = false;

        foreach ($this->sources as $authId) {
            $as = Auth\Source::getById($authId);

            if (null === $as) {
                throw new Error\Exception("Invalid authentication source: $authId");
            }

            if (!is_subclass_of($as, UserPassBase::class, false)) {
                Logger::error(sprintf("Could not use '%s', trying next", $authId));
                continue;
            }

            try {
                return $as->login($username, $password);
            } catch (Error\AuthSource $e) {
                Logger::error(sprintf("Could not connect to '%s', trying next", $authId));
            } catch (Error\Error $e) {
                if ('WRONGUSERPASS' === $e->getErrorCode()) {
                    Logger::debug('Failed one source, trying next');
                } else {
                    $lastError = $e;
                }
            }
        }

        if ($lastError) {
            throw $lastError;
        }

        throw new Error\Error('WRONGUSERPASS');
    }
}
