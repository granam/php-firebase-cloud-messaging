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
    public function I_have_got_badge_and_sound_removed_if_set_as_silent(): void
    {
        $iosFcmNotification = new IosFcmNotification();
        self::assertSame([], $iosFcmNotification->jsonSerialize());
        $iosFcmNotification->setBadge(123);
        self::assertSame(['badge' => 123], $iosFcmNotification->jsonSerialize());
        $iosFcmNotification->setSound('default');
        self::assertSame(['sound' => 'default', 'badge' => 123], $iosFcmNotification->jsonSerialize());
        $iosFcmNotification->setSilent();
        self::assertSame(['content-available' => 1], $iosFcmNotification->jsonSerialize());
    }

    /**
     * @test
     */
    public function I_can_ask_it_if_is_silent(): void
    {
        $iosFcmNotification = new IosFcmNotification();
        self::assertFalse($iosFcmNotification->isSilent(), 'iOS push notification should not be silent by default');
    }

    /**
     * @test
     */
    public function I_can_ask_it_if_can_be_silenced(): void
    {
        $iosFcmNotification = new IosFcmNotification();
        self::assertTrue($iosFcmNotification->canBeSilenced(), 'iOS notification can be silenced');
    }

    /**
     * @test
     */
    public function I_can_set_it_silent(): void
    {
        $iosFcmNotification = new IosFcmNotification();
        self::assertFalse($iosFcmNotification->isSilent());
        $iosFcmNotification->setSilent();
        self::assertTrue($iosFcmNotification->isSilent(), 'iOS notification should be able to be silenced');
    }

}