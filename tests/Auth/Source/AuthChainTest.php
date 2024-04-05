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
    public function testItDoeschainedLogin(): void
    {
        Configuration::setConfigDir(__DIR__ . '/../../fixtures/config');

        $authChain = new AuthChain([
            'AuthId' => 'chained',
        ], [
            'sources' => ['dummy-as', 'success-as'],
        ]);

        $login = function ($authChain, $username, $password) {
            return $authChain->login($username, $password);
        };
        $bindedAuthChain = $login->bindTo($authChain, $authChain);

        $result = $bindedAuthChain($authChain, 'username', 'password');
        $this->assertArrayHasKey('uid', $result);
        $this->assertSame([0 => 'username'], $result['uid']);
    }


    /**
     */
    public function testItTriesAllAuthSources(): void
    {
        Configuration::setConfigDir(__DIR__ . '/../../fixtures/config');

        $authChain = new AuthChain([
            'AuthId' => 'chained',
        ], [
            'sources' => ['failure-as', 'success-as'],
        ]);

        $login = function ($authChain, $username, $password) {
            return $authChain->login($username, $password);
        };
        $bindedAuthChain = $login->bindTo($authChain, $authChain);

        $result = $bindedAuthChain($authChain, 'username', 'password');
        $this->assertArrayHasKey('uid', $result);
        $this->assertSame([0 => 'username'], $result['uid']);
    }


    /**
     */
    public function testItThrowsExceptionIfAllAuthSourcesFail(): void
    {
        Configuration::setConfigDir(__DIR__ . '/../../fixtures/config');

        $authChain = new AuthChain([
            'AuthId' => 'chained',
        ], [
            'sources' => ['failure-as', 'failure-as'],
        ]);

        $login = function ($authChain, $username, $password) {
            return $authChain->login($username, $password);
        };
        $bindedAuthChain = $login->bindTo($authChain, $authChain);

        $this->expectException(Error\Error::class);
        $this->expectExceptionMessage('WRONGUSERPASS');

        $bindedAuthChain($authChain, 'username', 'password');
    }
}
