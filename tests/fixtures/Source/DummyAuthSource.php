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

namespace Tests\SimpleSAML\Modules\AuthChain\fixtures\Source;

use SimpleSAML\Auth;

class DummyAuthSource extends Auth\Source
{
    public function authenticate(&$state)
    {
    }
}
