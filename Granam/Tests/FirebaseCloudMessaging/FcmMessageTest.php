<?php
namespace Granam\Tests\FirebaseCloudMessaging;

use Granam\FirebaseCloudMessaging\AndroidFcmNotification;
use Granam\FirebaseCloudMessaging\FcmMessage;
use Granam\FirebaseCloudMessaging\FcmNotification;
use Granam\FirebaseCloudMessaging\IosFcmNotification;
use Granam\FirebaseCloudMessaging\JsFcmNotification;
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
        $message->setNotification(new JsFcmNotification('test', 'a nice testing notification'));
        $this->assertSame($body, \json_encode($message));
    }

    /**
     * @test
     */
    public function I_can_simply_convert_whole_message_for_device_to_json(): void
    {
        $notification = new JsFcmNotification('test', 'a nice testing notification');
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
     * @test
     */
    public function I_can_set_notification_when_message_is_silent(): void
    {
        $message = new FcmMessage(new FcmDeviceTarget('123'));
        $message->setSilent()
            ->setNotification(new IosFcmNotification('foo'));
        self::assertSame(
            ['to' => '123', 'notification' => ['title' => 'foo', 'content-available' => 1]],
            $message->jsonSerialize()
        );
    }

    /**
     * @test
     */
    public function I_can_set_message_silent_when_contains_silenceable_notification(): void
    {
        $message = new FcmMessage(new FcmDeviceTarget('123'));
        $message
            ->setNotification(new IosFcmNotification('foo'))
            ->setSilent();
        self::assertSame(
            ['to' => '123', 'notification' => ['title' => 'foo', 'content-available' => 1]],
            $message->jsonSerialize()
        );
    }

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\CanNotMakeSilentMessageWithLoudNotification
     */
    public function I_can_not_set_loud_notification_when_message_is_silent(): void
    {
        $message = new FcmMessage(new FcmDeviceTarget('123'));
        $message->setSilent()
            ->setNotification(new AndroidFcmNotification());
    }

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\CanNotMakeSilentMessageWithLoudNotification
     */
    public function I_can_not_set_message_silent_when_has_loud_notification(): void
    {
        $message = new FcmMessage(new FcmDeviceTarget('123'));
        $message
            ->setNotification(new AndroidFcmNotification())
            ->setSilent();
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
        $message = new FcmMessage(new FcmTopicTarget('qux'));
        self::assertSame('', $message->getCollapseKey());
        $message->setCollapseKey('foo');
        self::assertSame('foo', $message->getCollapseKey());
        self::assertSame(['to' => '/topics/qux', 'collapse_key' => 'foo'], $message->jsonSerialize());
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

    /**
     * @test
     */
    public function I_can_set_delay_while_idle(): void
    {
        $message = new FcmMessage(new FcmDeviceTarget('foo'));
        self::assertSame(['to' => 'foo'], $message->jsonSerialize());
        $message->enableDelayWhileIdle();
        self::assertSame(['to' => 'foo', 'delay_while_idle' => true], $message->jsonSerialize());
        $message->disableDelayWhileIdle();
        self::assertSame(['to' => 'foo', 'delay_while_idle' => false], $message->jsonSerialize());
    }

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\UnknownTargetType
     */
    public function I_can_not_add_unknown_target_type(): void
    {
        $message = new FcmMessage(new FcmDeviceTarget('foo'));
        /** @var FcmTarget $fcmTarget */
        $fcmTarget = $this->mockery(FcmTarget::class);
        $message->addTarget($fcmTarget);
    }

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfDevices
     */
    public function I_can_not_add_more_than_allowed_targets(): void
    {
        $message = new FcmMessage($target = new FcmDeviceTarget('foo'));
        try {
            for ($number = 1; $number < FcmMessage::MAX_DEVICES; $number++) {
                $message->addTarget($target);
            }
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage());
        }
        $message->addTarget($target);
    }

    /**
     * @test
     */
    public function I_can_ad_multiple_targets_at_once(): void
    {
        $message = new FcmMessage(new FcmDeviceTarget('foo'));
        $message->addTargets([new FcmDeviceTarget('bar'), new FcmDeviceTarget('baz')]);
        self::assertSame(['registration_ids' => ['foo', 'bar', 'baz']], $message->jsonSerialize());
    }

    /**
     * @test
     */
    public function I_can_set_priority(): void
    {
        $message = new FcmMessage(new FcmDeviceTarget('foo'));
        $message->setPriority('high');
        self::assertSame(['to' => 'foo', 'priority' => 'high'], $message->jsonSerialize());
    }

    /**
     * @test
     */
    public function I_can_set_data(): void
    {
        $message = new FcmMessage(new FcmDeviceTarget('foo'));
        $message->setData(['bank', 'bang']);
        self::assertSame(['to' => 'foo', 'data' => ['bank', 'bang']], $message->jsonSerialize());
    }

    /**
     * @test
     */
    public function I_can_set_time_to_live(): void
    {
        $message = new FcmMessage(new FcmDeviceTarget('foo'));
        $message->setTimeToLive(123);
        self::assertSame(['to' => 'foo', 'time_to_live' => 123], $message->jsonSerialize());
    }

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfTopics
     */
    public function I_can_not_add_more_than_allowed_topic_targets(): void
    {
        $message = new FcmMessage(new FcmTopicTarget('foo'));
        for ($number = 1; $number < FcmMessage::MAX_TOPICS; $number++) {
            try {
                $message->addTarget(new FcmTopicTarget('bar'));
            } catch (\Exception $exception) {
                self::fail('No exception expected so far: ' . $exception->getMessage());
            }
        }
        $message->addTarget(new FcmTopicTarget('qux'));
    }

    /**
     * @test
     */
    public function I_can_use_single_topic_target_even_if_condition_wants_more(): void
    {
        $message = new FcmMessage(new FcmTopicTarget('foo'));
        $message->setCondition('%s || %s');
        self::assertSame(['to' => '/topics/foo'], $message->jsonSerialize());
    }

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\CountOfTopicsDoesNotMatchConditionPattern
     */
    public function I_can_not_use_more_topic_targets_than_condition_requires(): void
    {
        $message = new FcmMessage(new FcmTopicTarget('foo'));
        $message->addTarget(new FcmTopicTarget('bar'));
        $message->addTarget(new FcmTopicTarget('baz'));
        $message->setCondition('%s || %s');
        $message->jsonSerialize();
    }

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\CountOfTopicsDoesNotMatchConditionPattern
     */
    public function I_can_not_use_less_topic_targets_than_condition_requires(): void
    {
        $message = new FcmMessage(new FcmTopicTarget('foo'));
        $message->addTarget(new FcmTopicTarget('bar'));
        $message->setCondition('%s || (%s && %s)');
        $message->jsonSerialize();
    }
}