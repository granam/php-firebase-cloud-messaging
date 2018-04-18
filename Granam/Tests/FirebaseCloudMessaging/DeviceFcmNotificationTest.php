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
}
