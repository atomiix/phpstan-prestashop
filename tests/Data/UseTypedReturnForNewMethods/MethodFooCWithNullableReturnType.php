<?php
/*
 * Copyright (c) Since 2007 PrestaShop.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPStanForPrestaShopTests\Data\UseTypedReturnForNewMethods;

class MethodFooCWithNullableReturnType
{
    public function foo(): ?string
    {
        return 'hello world';
    }
}
