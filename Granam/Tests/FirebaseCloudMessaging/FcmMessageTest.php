<?php
namespace sngrl\Granam\Tests;

use Granam\FirebaseCloudMessaging\FcmMessage;
use Granam\FirebaseCloudMessaging\FcmNotification;
use Granam\FirebaseCloudMessaging\Target\FcmDeviceTarget;
use Granam\FirebaseCloudMessaging\Target\FcmTarget;
use Granam\FirebaseCloudMessaging\Target\FcmTopicTarget;
use Granam\Tests\Tools\TestWithMockery;

class FcmMessageTest extends TestWithMockery
{
    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\CanNotMixRecipientTypes
     */
    public function I_can_not_mix_recipient_types(): void
    {
        (new FcmMessage($this->createTarget()))->addTarget(new FcmTopicTarget('breaking-news'))
            ->addTarget(new FcmDeviceTarget('NA Palm OS'));
    }

    /**
     * @return FcmTarget|\Mockery\MockInterface
     */
    private function createTarget(): FcmTarget
    {
        return $this->mockery(FcmTarget::class);
    }

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\MissingMultipleTopicsCondition
     */
    public function I_can_not_get_it_in_json_with_multiple_topics_but_without_condition_pattern(): void
    {
        $message = new FcmMessage(new FcmTopicTarget('breaking-news'));
        $message->addTarget(new FcmTopicTarget('fixing news'));

        $message->jsonSerialize();
    }

    /**
     * @test
     */
    public function I_can_simply_convert_whole_message_for_topic_to_json(): void
    {
        $body = '{"to":"\/topics\/breaking-news","notification":{"title":"test","body":"a nice testing notification"}}';

        $message = new FcmMessage(new FcmTopicTarget('breaking-news'));
        $message->setNotification(new FcmNotification('test', 'a nice testing notification'));
        $this->assertSame($body, \json_encode($message));
    }

    /**
     * @test
     */
    public function I_can_simply_convert_whole_message_for_device_to_json(): void
    {
        $body = '{"to":"deviceId","notification":{"title":"test","body":"a nice testing notification"}}';

        $notification = new FcmNotification('test', 'a nice testing notification');
        $message = new FcmMessage(new FcmDeviceTarget('deviceId'));
        $message->setNotification($notification);

        $this->assertSame(
            $body,
            json_encode($message)
        );
    }

    /**
     * @test
     */
    public function I_can_set_notification_and_get_it_back(): void
    {
        $message = new FcmMessage($this->createTarget());
        self::assertNull($message->getNotification());
        $message->setNotification($notification = $this->createNotification());
        self::assertSame($notification, $message->getNotification());
    }

    /**
     * @return \Mockery\MockInterface|FcmNotification
     */
    private function createNotification(): FcmNotification
    {
        return $this->mockery(FcmNotification::class);
    }

    /**
     * @test
     */
    public function I_can_set_collapse_key_and_get_it_back(): void
    {
        $message = new FcmMessage($this->createTarget());
        self::assertSame('', $message->getCollapseKey());
        $message->setCollapseKey('foo');
        self::assertSame('foo', $message->getCollapseKey());
    }

    /**
     * @test
     */
    public function I_can_set_every_parameter(): void
    {
        $message = new FcmMessage($this->createTarget());
        $message->jsonSerialize();
    }

}