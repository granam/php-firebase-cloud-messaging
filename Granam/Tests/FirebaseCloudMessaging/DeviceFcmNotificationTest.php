<?php
namespace Granam\Tests\FirebaseCloudMessaging;

abstract class DeviceFcmNotificationTest extends FcmNotificationTest
{
    /**
     * @test
     * @throws \ReflectionException
     */
    public function I_can_set_sound(): void
    {
        $this->I_can_set_parameter('sound', 'foo');
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function I_can_set_body_loc_key(): void
    {
        $this->I_can_set_parameter('bodyLocKey', 'foo');
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function I_can_set_body_loc_args(): void
    {
        $this->I_can_set_parameter('bodyLocArgs', ['foo', 'bar']);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function I_can_set_title_loc_key(): void
    {
        $this->I_can_set_parameter('titleLocKey', 'foo');
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function I_can_set_title_loc_args(): void
    {
        $this->I_can_set_parameter('titleLocArgs', ['foo', 'bar', 123]);
    }
}
