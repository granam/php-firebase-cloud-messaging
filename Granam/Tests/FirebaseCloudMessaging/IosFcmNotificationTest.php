<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\Tests\FirebaseCloudMessaging;

class IosFcmNotificationTest extends FcmNotificationTest
{
    /**
     * @test
     * @throws \ReflectionException
     */
    public function I_can_set_badge(): void
    {
        $this->I_can_set_parameter('badge', 5);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function I_can_set_sub_title(): void
    {
        $this->I_can_set_parameter('subTitle', 'bar');
    }
}