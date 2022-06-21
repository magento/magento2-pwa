<?php
/**
 * Black list for the @see \Magento\Test\Integrity\DependencyTest::testUndeclared()
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'app/code/Magento/PageBuilderPwa/Plugin/UiInputTypePageBuilder.php' => ['Magento\Eav'],
    'app/code/Magento/SalesGraphQlAux/Model/Resolver/OrderState.php' => ['Magento\Sales']
];
