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

namespace Tests\SimpleSAML\Module\authchain\Auth\Source;

use PHPUnit\Framework\TestCase;
use SimpleSAML\{Configuration, Error};
use SimpleSAML\Module\authchain\Auth\Source\AuthChain;

class AuthChainTest extends TestCase
{
    /**
     */
    public function itDoeschainedLogin(): void
    {
        Configuration::setConfigDir(__DIR__ . '/../../fixtures/config');

        $authChain = new AuthChain([
            'AuthId' => 'chained',
        ], [
            'sources' => ['dummy-as', 'success-as'],
        ]);

        $login = function ($username, $password) {
            return $this->login($username, $password);
        };
        $bindedAuthChain = $login->bindTo($authChain, $authChain);

        $this->assertArraySubset(['uid' => ['username']], $bindedAuthChain('username', 'password'));
    }

    /**
     */
    public function itTriesAllAuthSources(): void
    {
        Configuration::setConfigDir(__DIR__ . '/../../fixtures/config');

        $authChain = new AuthChain([
            'AuthId' => 'chained',
        ], [
            'sources' => ['failure-as', 'success-as'],
        ]);

        $login = function ($username, $password) {
            return $this->login($username, $password);
        };
        $bindedAuthChain = $login->bindTo($authChain, $authChain);

        $this->assertArraySubset(['uid' => ['username']], $bindedAuthChain('username', 'password'));
    }

    /**
     */
    public function itThrowsExceptionIfAllAuthSourcesFail(): void
    {
        Configuration::setConfigDir(__DIR__ . '/../../fixtures/config');

        $authChain = new AuthChain([
            'AuthId' => 'chained',
        ], [
            'sources' => ['failure-as', 'failure-as'],
        ]);

        $login = function ($username, $password) {
            return $this->login($username, $password);
        };
        $bindedAuthChain = $login->bindTo($authChain, $authChain);

        $this->expectException(Error\Error::class);
        $this->expectExceptionMessage('WRONGUSERPASS');
        $bindedAuthChain('username', 'password');
    }
}
