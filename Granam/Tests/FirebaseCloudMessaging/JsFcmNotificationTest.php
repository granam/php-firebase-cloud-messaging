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

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\ClickActionForJavascriptFcmNotificationRequiresValidUrl
     * @expectedExceptionMessageRegExp ~Go to bed~
     * @throws \ReflectionException
     */
    public function I_can_not_use_invalid_url_for_click_action(): void
    {
        $this->I_can_set_parameter('clickAction', 'Go to bed');
    }

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\ClickActionForJavascriptFcmNotificationHasToBeOnHttps
     * @expectedExceptionMessageRegExp ~http://~
     * @throws \ReflectionException
     */
    public function I_have_to_use_url_with_https_for_click_action(): void
    {
        try {
            $this->I_can_set_parameter('clickAction', 'https://example.com/?action=go_to_bed');
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage());
        }
        $this->I_can_set_parameter('clickAction', 'http://example.com/?action=go_to_bed');
    }

    /**
     * @test
     */
    public function I_can_ask_it_if_is_silent(): void
    {
        $jsFcmNotification = new JsFcmNotification();
        self::assertFalse($jsFcmNotification->isSilent(), 'JS notification can not be silent (when app is no background)');
    }

    /**
     * @test
     */
    public function I_can_ask_it_if_can_be_silenced(): void
    {
        $jsFcmNotification = new JsFcmNotification();
        self::assertFalse($jsFcmNotification->canBeSilenced(), 'JS notification can not be silenced');
    }

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\JsFcmNotificationCanNotBeSilenced
     */
    public function I_can_set_it_silent(): void
    {
        $jsFcmNotification = new JsFcmNotification();
        $jsFcmNotification->setSilent();
    }

}