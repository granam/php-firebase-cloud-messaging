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
        (new FcmMessage())->addTarget(new FcmTopicTarget('breaking-news'))
            ->addTarget(new FcmDeviceTarget('NA Palm OS'));
    }

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\MissingTargets
     */
    public function I_can_not_get_it_in_json_without_recipients(): void
    {
        (new FcmMessage())->jsonSerialize();
    }

    /**
     * @test
     * @expectedException \Granam\FirebaseCloudMessaging\Exceptions\MissingMultipleTopicsCondition
     */
    public function I_can_not_get_it_in_json_with_multiple_topics_but_without_condition_pattern(): void
    {
        $message = new FcmMessage();
        $message->addTarget(new FcmTopicTarget('breaking-news'))
            ->addTarget(new FcmTopicTarget('fixing news'));

        $message->jsonSerialize();
    }

    /**
     * @test
     */
    public function I_can_simply_convert_whole_message_for_topic_to_json(): void
    {
        $body = '{"to":"\/topics\/breaking-news","notification":{"title":"test","body":"a nice testing notification"}}';

        $message = new FcmMessage();
        $message->setNotification(new FcmNotification('test', 'a nice testing notification'));
        $message->addTarget(new FcmTopicTarget('breaking-news'));
        $this->assertSame($body, \json_encode($message));
    }

    /**
     * @test
     */
    public function I_can_simply_convert_whole_message_for_device_to_json(): void
    {
        $body = '{"to":"deviceId","notification":{"title":"test","body":"a nice testing notification"}}';

        $notification = new FcmNotification('test', 'a nice testing notification');
        $message = new FcmMessage();
        $message->setNotification($notification);

        $message->addTarget(new FcmDeviceTarget('deviceId'));
        $this->assertSame(
            $body,
            json_encode($message)
        );
    }
}