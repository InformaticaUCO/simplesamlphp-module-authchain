<?php

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

namespace SimpleSAML\Modules\AuthChain\Auth\Source;

use Webmozart\Assert\Assert;

class AuthChain extends \SimpleSAML\Module\core\Auth\UserPassBase
{
    /**
     * @var array
     */
    private $sources;

    public function __construct(array $info, array $config)
    {
        parent::__construct($info, $config);

        if (!array_key_exists('sources', $config)) {
            throw new \SimpleSAML\Error\Exception('The required "sources" config option was not found');
        }

        $this->sources = $config['sources'];
    }

    protected function login($username, $password)
    {
        Assert::string($username, 'username must be a string');
        Assert::string($password, 'password must be a string');

        $lastError = false;

        foreach ($this->sources as $authId) {
            $as = \SimpleSAML\Auth\Source::getById($authId);

            if (null === $as) {
                throw new \SimpleSAML\Error\Exception("Invalid authentication source: $authId");
            }

            if (!method_exists($as, 'login')) {
                \SimpleSAML\Logger::error('Could not use {$authId}, trying next');
                continue;
            }

            try {
                return $as->login($username, $password);
            } catch (\SimpleSAML\Error\AuthSource $e) {
                \SimpleSAML\Logger::error("Could not connect to {$authId}, trying next");
            } catch (\SimpleSAML\Error\Error $e) {
                if ('WRONGUSERPASS' === $e->getErrorCode()) {
                    \SimpleSAML\Logger::debug('Failed one source, trying next');
                } else {
                    $lastError = $e;
                }
            }
        }

        if ($lastError) {
            throw $lastError;
        }

        throw new \SimpleSAML\Error\Error('WRONGUSERPASS');
    }
}
