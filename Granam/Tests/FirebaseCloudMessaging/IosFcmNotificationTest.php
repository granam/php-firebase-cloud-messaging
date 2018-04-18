<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\Tests\FirebaseCloudMessaging;

use Granam\FirebaseCloudMessaging\IosFcmNotification;

class IosFcmNotificationTest extends DeviceFcmNotificationTest
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

    /**
     * @test
     */
    public function I_can_ask_it_if_is_silent(): void
    {
        $iosFcmNotification = new IosFcmNotification();
        self::assertFalse($iosFcmNotification->isSilent(), 'iOS pusp notification should not be silent by default');
        $iosFcmNotification->setSilent(true);
        self::assertTrue($iosFcmNotification->isSilent(), 'Could not set iOS push notification as silent');
        $iosFcmNotification->setSilent(false);
        self::assertFalse($iosFcmNotification->isSilent(), 'Could not set iOS push notification as not silent');
    }

    /**
     * @test
     */
    public function I_have_got_badge_and_sound_removed_if_set_as_silent(): void
    {
        $iosFcmNotification = new IosFcmNotification();
        self::assertSame([], $iosFcmNotification->jsonSerialize());
        $iosFcmNotification->setBadge(123);
        self::assertSame(['badge' => 123], $iosFcmNotification->jsonSerialize());
        $iosFcmNotification->setSilent(true);
        self::assertSame(['content-available' => 1], $iosFcmNotification->jsonSerialize());
        $iosFcmNotification->setSilent(false);
        $iosFcmNotification->setSound('default');
        self::assertSame(['sound' => 'default', 'badge' => 123], $iosFcmNotification->jsonSerialize());
        $iosFcmNotification->setSilent(true);
        self::assertSame(['content-available' => 1], $iosFcmNotification->jsonSerialize());
    }

}