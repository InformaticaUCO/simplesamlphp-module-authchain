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

namespace Tests\SimpleSAML\Modules\AuthChain\Auth\Source;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Modules\AuthChain\Auth\Source\AuthChain;

class AuthChainTest extends TestCase
{
    /**
     * @test
     */
    public function it_does_chained_login()
    {
        \SimpleSAML_Configuration::setConfigDir(__DIR__.'/../../fixtures/config');

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
     * @test
     */
    public function it_tries_all_auth_sources()
    {
        \SimpleSAML_Configuration::setConfigDir(__DIR__.'/../../fixtures/config');

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
     * @test
     * @expectedException \SimpleSAML_Error_Error
     * @expectedExceptionMessage WRONGUSERPASS
     */
    public function it_launch_exception_if_all_auth_sources_fail()
    {
        \SimpleSAML_Configuration::setConfigDir(__DIR__.'/../../fixtures/config');

        $authChain = new AuthChain([
            'AuthId' => 'chained',
        ], [
            'sources' => ['failure-as', 'failure-as'],
        ]);

        $login = function ($username, $password) {
            return $this->login($username, $password);
        };
        $bindedAuthChain = $login->bindTo($authChain, $authChain);
        $bindedAuthChain('username', 'password');
    }
}
