<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\Tests\FirebaseCloudMessaging;

use Granam\FirebaseCloudMessaging\JsFcmNotification;

class JsFcmNotificationTest extends FcmNotificationTest
{
    /**
     * @test
     */
    public function I_can_set_icon(): void
    {
        $jsFcmNotification = new JsFcmNotification('', '', 'favicon');
        self::assertSame(['icon' => 'favicon'], $jsFcmNotification->jsonSerialize());
        $jsFcmNotification->setIcon('madona');
        self::assertSame(['icon' => 'madona'], $jsFcmNotification->jsonSerialize());
    }
}