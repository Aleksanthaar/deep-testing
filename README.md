# Deep Testing

PhpUnit in depth testing utilities.

##Usage 

In any class extending **PHPUnit\Framework\TestCase**, use **Aleksanthaar\DeepTesting\DeepTestingTrait**:

```php

use Aleksanthaar\DeepTesting\DeepTestingTrait;
use PHPUnit\Framework\TestCase;

class FooTest extends TestCase
{
    use DeepTestingTrait;

    // ...
}
```

### execInternalMethod

This method eases testing for protected and private methods. Assuming the any guven User class has the following signatures:
```php
class User
{
    public function setSomethingPublic(string $value, bool $anotherValue);
    protected function setSomethingProtected(string $value, bool $anotherValue);
    private function setSomethingPrivate(string $value, bool $anotherValue);
}
```

If you want to test these methods in _real_ isolation, you would need have to do some Reflecion ; and it's precisely what the trait does under the hood. The test for the public method would look like this:
```php
class UserTest extends TestCase
{
    // We'll ignore the data providers here
    public function testSetSomethingPublic($input1, $input2, $expect)
    {
        $user   = // instanciate or mock the user
        $result = $user->setSomethingPublic($input1, $input2);

        $this->assertEquals($expect, $result);
    }
}
```

The test for the protected method is quite similar:
```php
class UserTest extends TestCase
{
    use DeepTestingTrait; // Don't forget this

    // We'll ignore the data providers here
    public function testSetSomethingProtected($input1, $input2, $expect)
    {
        // instanciate or mock the user
        $user   = $this->defaultUser;

        // $result = $user->setSomethingProtected($input1, $input1); // This wouldn't work

        $result = $this->execInternalMethod($user, 'setSomethingProtected', $input1, $input2);

        $this->assertEquals($expect, $result);
    }
}
```

First, pass the object you are testing, then the name of the method, and finally the method parameters (if any) **variadically**. The signature of *execInternalMethod* goes as follow:
```php
    public function execInternalMethod(object $subject, string $methodName, ...$args);
```

The code to test the private method is same, just change the tested method name to *setSomethingPrivate*:

```php
class UserTest extends TestCase
{
    use DeepTestingTrait; // Don't forget this

    // We'll ignore the data providers here
    public function testSetSomethingPrivate($input1, $input2, $expect)
    {
        // **INSTANCIATE** the user
        $user   = new User();

        // $result = $user->setSomethingPrivate($input1, $input1); // This wouldn't work

        $result = $this->execInternalMethod($user, 'setSomethingPrivate', $input1, $input2);

        $this->assertEquals($expect, $result);
    }
}
```

If the method you're testing is a generator (regardless its visibility), you should use **execGenerator** instead. The signature is the same:
```php
    public function execGenerator(object $subject, string $methodName, ...$args)
```

**Warning**: Private methods (generators or not) cannot be tested from mock objects.

### get/setInternalProperty

Likewise, you may want to manually set protected and private properties. Imagine for instance you are working with a mock, which constructor accepts 20 objects, but the method you're testing involves only one of these 20 objects. You can disable the original constructor and manually set the object that actually interests you.

```php
class Foo
{
    protected $lorem;

    public function __construct(Lorem $lorem, /* imagine the 19 other required arguments here */)
    {
        $this->lorem = $lorem; // this is the one object that interest us

        // ... and 19 more
    }

    public function somethingWithLorem()
    {
        $this->lorem->doStuff();
    }
}

class FooTest extends TestCase
{
    use DeepTestingTrait;

    public function testSomethingWithLorem()
    {
        $lorem = // mock Lorem
        $foo   = // mock Foo

        $this->setInternalProperty($foo, 'lorem', $lorem);


        // Now you can execute somethingWithLorem() in isolation without mocking 19 useless depenedencies
    }
}

```

The signatures of these two methods go as follow:
```php
    public function setInternalProperty($subject, $propertyName, $value): void;
    public function getInternalProperty($subject, $propertyName);
```

**Warning**: private properties cannot be get or set when working with mock objects!
