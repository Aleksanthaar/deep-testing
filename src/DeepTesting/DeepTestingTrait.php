<?php

namespace Aleksanthaar\DeepTesting;

use ReflectionGenerator;
use ReflectionMethod;
use ReflectionProperty;

trait DeepTestingTrait
{
    /**
     * Shortcut to test proptected methods.
     *
     * @param object $subject    Object containing the method
     * @param string $methodName Name of method to run
     * @param mixed  $args       variadic parameters to pass the tested method
     *
     * @return ?mixed
     */
    public function execInternalMethod(object $subject, string $methodName, ...$args)
    {
        $method = $this->getOpenMethod($subject, $methodName);

        return $method->invokeArgs($subject, $args);
    }

    /**
     * Sets non-public property to given value for given object.
     *
     * @param object $subject      Object which property to define
     * @param string $propertyName Property name
     * @param mixed  $value        Value to set to the property
     */
    public function setInternalProperty($subject, $propertyName, $value): void
    {
        $property = $this->getOpenProperty($subject, $propertyName);

        $property->setValue($subject, $value);
    }

    /**
     * Gets non-public property for given value from given object.
     *
     * @param object $subject      Object which property to define
     * @param string $propertyName Property name
     *
     * @return ?mixed
     */
    public function getInternalProperty($subject, $propertyName)
    {
        $property = $this->getOpenProperty($subject, $propertyName);

        return $property->getValue($subject);
    }

    /**
     * Result execution of a generator.
     *
     * @param \Generator $generator
     *
     * @return mixed
     */
    public function execGenerator(object $subject, string $methodName, ...$args)
    {
        $method        = $this->getOpenMethod($subject, $methodName);
        $generator     = $method->invokeArgs($subject, $args);
        $reflectionGen = new ReflectionGenerator($generator);
        $execGen       = $reflectionGen->getExecutingGenerator();

        return $execGen->current();
    }

    /**
     * Makes method public and returns its reflection.
     *
     * @param object $subject    Object containing the method
     * @param string $methodName Name of method to get
     *
     * @return ReflectionMethod
     */
    protected function getOpenMethod(object $subject, string $methodName)
    {
        $method = new ReflectionMethod(get_class($subject), $methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Makes property public and returns its reflection.
     *
     * @param object $subject     Object containing the method
     * @param string $propetyName Name of property to get
     *
     * @return ReflectionProperty
     */
    protected function getOpenProperty(object $subject, string $propertyName)
    {
        $property = new ReflectionProperty(get_class($subject), $propertyName);
        $property->setAccessible(true);

        return $property;
    }
}
