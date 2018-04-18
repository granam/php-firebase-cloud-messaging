<?php
namespace Granam\Tests\FirebaseCloudMessaging;

use Granam\FirebaseCloudMessaging\FcmNotification;
use Granam\String\StringTools;
use Granam\Tests\Tools\TestWithMockery;

abstract class FcmNotificationTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_create_it_empty(): void
    {
        $sutClass = self::getSutClass();
        /** @var FcmNotification $fcmNotification */
        $fcmNotification = new $sutClass();
        self::assertSame([], $fcmNotification->jsonSerialize());
        self::assertSame('[]', \json_encode($fcmNotification->jsonSerialize()));
    }

    /**
     * @test
     */
    public function I_can_get_minimal_notification_in_json(): void
    {
        $sutClass = self::getSutClass();
        /** @var FcmNotification $fcmNotification */
        $fcmNotification = new $sutClass('foo', 'bar');
        $this->assertSame(['title' => 'foo', 'body' => 'bar'], $fcmNotification->jsonSerialize());
        $this->assertEquals(\json_encode(['title' => 'foo', 'body' => 'bar']), \json_encode($fcmNotification));
        $fcmNotification->setBody('baz');
        $this->assertSame(['title' => 'foo', 'body' => 'baz'], $fcmNotification->jsonSerialize());
        $fcmNotification->setTitle('qux');
        $this->assertSame(['title' => 'qux', 'body' => 'baz'], $fcmNotification->jsonSerialize());
        $fcmNotification->setClickAction('https://example.com/action#!');
        $this->assertSame(
            ['title' => 'qux', 'body' => 'baz', 'click_action' => 'https://example.com/action#!'],
            $fcmNotification->jsonSerialize()
        );
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function I_can_set_click_action(): void
    {
        $this->I_can_set_parameter('clickAction', 'https://example.com/action#!');
    }

    /**
     * @param string $parameterName
     * @param mixed $value
     * @throws \ReflectionException
     */
    protected function I_can_set_parameter(string $parameterName, $value): void
    {
        $sutClass = self::getSutClass();
        $reflectionClass = new \ReflectionClass($sutClass);
        $constructor = $reflectionClass->getMethod('__construct');
        $reflectionParameters = $constructor->getParameters();
        $wantedParameterIndex = null;
        $parameters = [];
        foreach ($reflectionParameters as $index => $reflectionParameter) {
            $parameters[$index] = $reflectionParameter->getDefaultValue();
            if ($reflectionParameter->getName() === $parameterName) {
                $wantedParameterIndex = $index;
            }
        }
        self::assertNotNull($wantedParameterIndex, "Missing $parameterName parameter of a $sutClass constructor");
        /** @var FcmNotification $fcmNotification */
        $fcmNotification = new $sutClass;
        $defaultValue = $parameters[$wantedParameterIndex];
        $parameters[$wantedParameterIndex] = $value;
        $constructor->invokeArgs($fcmNotification, $parameters);
        $this->assertSame(
            [StringTools::camelCaseToSnakeCase($parameterName) /* fooBar = foo_bar */ => $value],
            $fcmNotification->jsonSerialize()
        );
        $setterName = StringTools::assembleSetterForName($parameterName);
        self::assertTrue($reflectionClass->hasMethod($setterName), "$sutClass does not have setter $setterName");
        $fcmNotification->$setterName($defaultValue);
        $this->assertSame([], $fcmNotification->jsonSerialize(), "Value of $parameterName can not be reset");
        $fcmNotification->$setterName($value);
        $this->assertSame(
            [StringTools::camelCaseToSnakeCase($parameterName) /* fooBar = foo_bar */ => $value],
            $fcmNotification->jsonSerialize()
        );
    }
}
