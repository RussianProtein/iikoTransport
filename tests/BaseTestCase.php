<?php
/**
 * 02/09/2020
 * @author Sergey Borguronov <multiatlast@gmail.com>
 */

namespace Tests;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @param $class
     * @param $method
     * @return \ReflectionMethod
     * @throws \ReflectionException
     */
    protected function makeMethodAccessible($class, $method)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return $method;
    }
}