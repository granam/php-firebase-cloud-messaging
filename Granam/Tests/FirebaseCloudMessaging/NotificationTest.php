<?php
namespace sngrl\Granam\Tests;

use Granam\FirebaseCloudMessaging\FcmNotification;
use Granam\Tests\Tools\TestWithMockery;

class NotificationTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_get_minimal_notification_in_json(): void
    {
        $fcmNotification = new FcmNotification('foo', 'bar');
        $this->assertSame(['title' => 'foo', 'body' => 'bar'], $fcmNotification->jsonSerialize());
        $this->assertEquals(\json_encode(['title' => 'foo', 'body' => 'bar']), \json_encode($fcmNotification));
    }

    /**
     * @test
     */
    public function I_can_get_notification_with_ios_badge_in_json(): void
    {
        $fcmNotification = new FcmNotification('foo', 'bar');
        $fcmNotification->setIosBadge(123);
        $expectedValues = ['title' => 'foo', 'body' => 'bar', 'badge' => 123];
        $this->assertSame($expectedValues, $fcmNotification->jsonSerialize());
        $this->assertEquals(\json_encode($expectedValues), \json_encode($fcmNotification));
    }

    /**
     * @test
     */
    public function I_can_get_notification_with_android_icon_in_json(): void
    {
        $fcmNotification = new FcmNotification('foo', 'bar');
        $fcmNotification->setAndroidIcon('baz');
        $expectedValues = ['title' => 'foo', 'body' => 'bar', 'icon' => 'baz'];
        $this->assertSame($expectedValues, $fcmNotification->jsonSerialize());
        $this->assertEquals(\json_encode($expectedValues), \json_encode($fcmNotification));
    }

    /**
     * @test
     */
    public function I_can_get_notification_with_content_available_in_json(): void
    {
        $fcmNotification = new FcmNotification('foo', 'bar');
        $fcmNotification->enableContentAvailable();
        $expectedValues = ['title' => 'foo', 'body' => 'bar', 'content_available' => true];
        $this->assertSame($expectedValues, $fcmNotification->jsonSerialize());
        $this->assertEquals(\json_encode($expectedValues), \json_encode($fcmNotification));
    }
}
