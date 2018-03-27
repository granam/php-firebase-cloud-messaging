<?php
namespace sngrl\Granam\Tests;

use Granam\FirebaseCloudMessaging\FcmMessage;
use Granam\FirebaseCloudMessaging\FcmNotification;
use Granam\FirebaseCloudMessaging\Target\FcmDeviceTarget;
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
        (new FcmMessage(new FcmDeviceTarget('NA Palm OS')))
            ->addTarget(new FcmTopicTarget('breaking-news'));
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
        $notification = new FcmNotification('test', 'a nice testing notification');
        $message = new FcmMessage(new FcmDeviceTarget('deviceId'));
        $message->setNotification($notification);

        $expectedBody = '{"to":"deviceId","notification":{"title":"test","body":"a nice testing notification"}}';
        $this->assertSame($expectedBody, \json_encode($message));
    }

    /**
     * @test
     */
    public function I_can_set_notification_and_get_it_back(): void
    {
        $message = new FcmMessage($this->createDeviceTarget());
        self::assertNull($message->getNotification());
        $message->setNotification($notification = $this->createNotification());
        self::assertSame($notification, $message->getNotification());
    }

    /**
     * @param string $deviceToken = null
     * @return FcmTopicTarget|\Mockery\MockInterface
     */
    private function createDeviceTarget(string $deviceToken = null): FcmTopicTarget
    {
        $fcmTopicTarget = $this->mockery(FcmTopicTarget::class);
        if ($deviceToken !== null) {
            $fcmTopicTarget->shouldReceive('getDeviceToken')
                ->atLeast()->once()
                ->andReturn($deviceToken);
        }

        return $fcmTopicTarget;
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
        $message = new FcmMessage($this->createDeviceTarget());
        self::assertSame('', $message->getCollapseKey());
        $message->setCollapseKey('foo');
        self::assertSame('foo', $message->getCollapseKey());
    }

    /**
     * @test
     */
    public function I_can_use_topic_targets(): void
    {
        $message = new FcmMessage(new FcmTopicTarget('foo'));
        self::assertSame(['to' => '/topics/foo'], $message->jsonSerialize()); // single topic target
        $message->setCondition('%s || %s');
        $message->addTarget(new FcmTopicTarget('bar'));
        self::assertSame(['condition' => "'foo' in topics || 'bar' in topics"], $message->jsonSerialize()); // multiple topic targets
        $message->addTarget(new FcmTopicTarget('baz'));
        $message->setCondition('%s || (%s && %s)');
        self::assertSame(['condition' => "'foo' in topics || ('bar' in topics && 'baz' in topics)"], $message->jsonSerialize()); // even more topic targets
    }

    /**
     * @test
     */
    public function I_can_use_device_targets(): void
    {
        $message = new FcmMessage(new FcmDeviceTarget('foo'));
        self::assertSame(['to' => 'foo'], $message->jsonSerialize()); // single device target
        $message->addTarget(new FcmDeviceTarget('bar'));
        self::assertSame(['registration_ids' => ['foo', 'bar']], $message->jsonSerialize()); // multiple device targets
        $message->addTarget(new FcmDeviceTarget('baz'));
        self::assertSame(['registration_ids' => ['foo', 'bar', 'baz']], $message->jsonSerialize()); // even more device targets
    }

}