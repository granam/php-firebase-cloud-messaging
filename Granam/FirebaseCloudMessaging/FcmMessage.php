<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Granam\FirebaseCloudMessaging;

use Granam\FirebaseCloudMessaging\Target\FcmDeviceTarget;
use Granam\FirebaseCloudMessaging\Target\FcmTarget;
use Granam\FirebaseCloudMessaging\Target\FcmTopicTarget;

class FcmMessage implements \JsonSerializable
{
    /**
     * Maximum topics and devices: https://firebase.google.com/docs/cloud-messaging/http-server-ref#send-downstream
     */
    public const MAX_TOPICS = 3;
    public const MAX_DEVICES = 1000;

    private $notification;
    private $collapseKey = '';
    private $priority;
    private $data;
    /** @var array|FcmTarget[] */
    private $targets = [];
    private $targetType;
    private $condition;
    private $timeToLive;
    private $delayWhileIdle;
    private $silent = false;

    /**
     * @param FcmTarget $target
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\UnknownTargetType
     */
    public function __construct(FcmTarget $target)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->addTarget($target);
    }

    /**
     * @param FcmTarget $target
     * @return \Granam\FirebaseCloudMessaging\FcmMessage
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\UnknownTargetType
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CanNotMixRecipientTypes
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfDevices
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfTopics
     */
    public function addTarget(FcmTarget $target): FcmMessage
    {
        $givenTargetType = \get_class($target);
        if (!\is_a($target, FcmDeviceTarget::class) && !\is_a($target, FcmTopicTarget::class)) {
            throw new Exceptions\UnknownTargetType(
                'Expected target instance as one of ' . FcmDeviceTarget::class . ' or ' . FcmTopicTarget::class
                . ', got ' . $givenTargetType
            );
        }
        if ($this->targetType !== null
            && !\is_a($target, $this->targetType)
            && !\is_a($this->targetType, $givenTargetType, true /* both are just class names, not objects */)
        ) {
            throw new Exceptions\CanNotMixRecipientTypes(
                "Mixed target types are not supported by FCM, firstly set is '{$this->targetType}'"
                . ", but currently given is '$givenTargetType'"
            );
        }
        if (\is_a($target, FcmDeviceTarget::class) && \count($this->targets) === self::MAX_DEVICES) {
            throw new Exceptions\ExceededLimitOfDevices(
                'Message device limit exceeded. Firebase supports a maximum of ' . self::MAX_DEVICES . ' devices'
            );
        }
        if (\is_a($target, FcmTopicTarget::class) && \count($this->targets) === self::MAX_TOPICS) {
            throw new Exceptions\ExceededLimitOfTopics(
                'Message topic limit exceeded. Firebase supports a maximum of ' . self::MAX_TOPICS
            );
        }
        $this->targetType = $givenTargetType;
        $this->targets[] = $target;

        return $this;
    }

    /**
     * @param array $targets
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\UnknownTargetType
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CanNotMixRecipientTypes
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfDevices
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfTopics
     */
    public function addTargets(array $targets): void
    {
        foreach ($targets as $target) {
            $this->addTarget($target);
        }
    }

    /**
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CanNotMakeSilentMessageWithLoudNotification
     * @param FcmNotification $notification
     * @return FcmMessage
     */
    public function setNotification(FcmNotification $notification): FcmMessage
    {
        if ($this->silent) {
            $this->setNotificationSilent($notification);
        }
        $this->notification = $notification;

        return $this;
    }

    /**
     * @return FcmNotification|null
     */
    public function getNotification(): ?FcmNotification
    {
        return $this->notification;
    }

    public function setCollapseKey(string $collapseKey): FcmMessage
    {
        $this->collapseKey = $collapseKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getCollapseKey(): string
    {
        return $this->collapseKey;
    }

    public function setPriority(string $priority): FcmMessage
    {
        $this->priority = $priority;

        return $this;
    }

    public function setData(array $data): FcmMessage
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Specify a condition pattern when sending to combinations of topics
     * https://firebase.google.com/docs/cloud-messaging/topic-messaging#sending_topic_messages_from_the_server
     *
     * Examples:
     * "%s && %s" > Send to devices subscribed to topic 1 and topic 2
     * "%s && (%s || %s)" > Send to devices subscribed to topic 1 and topic 2 or 3
     *
     * @param string $condition
     * @return $this
     */
    public function setCondition(string $condition): FcmMessage
    {
        $this->condition = $condition;

        return $this;
    }

    public function enableDelayWhileIdle(): FcmMessage
    {
        $this->delayWhileIdle = true;

        return $this;
    }

    public function disableDelayWhileIdle(): FcmMessage
    {
        $this->delayWhileIdle = false;

        return $this;
    }

    public function setTimeToLive(int $timeToLive): FcmMessage
    {
        $this->timeToLive = $timeToLive;

        return $this;
    }

    /**
     * https://stackoverflow.com/questions/36555653/push-silent-notification-through-gcm-to-android-ios?utm_medium=organic&utm_source=google_rich_qa&utm_campaign=google_rich_qa
     *
     * @return FcmMessage
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CanNotMakeSilentMessageWithLoudNotification
     */
    public function setSilent(): FcmMessage
    {
        if ($this->getNotification()) {
            $this->setNotificationSilent($this->getNotification());
        }
        $this->silent = true;

        return $this;
    }

    /**
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CanNotMakeSilentMessageWithLoudNotification
     * @param FcmNotification $fcmNotification
     */
    private function setNotificationSilent(FcmNotification $fcmNotification): void
    {
        if (!$fcmNotification->isSilent()) {
            if (!$fcmNotification->canBeSilenced()) {
                throw new Exceptions\CanNotMakeSilentMessageWithLoudNotification(
                    'Notification ' . \get_class($fcmNotification) . ' can not be silenced.'
                    . ' Do not use ' . FcmNotification::class . ' at all, if you want just silent data transfer'
                );
            }
            $fcmNotification->setSilent();
        }
    }

    /**
     * @return array
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\MissingMultipleTopicsCondition
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CountOfTopicsDoesNotMatchConditionPattern
     */
    public function jsonSerialize(): array
    {
        ['targetValue' => $target, 'targetKey' => $targetKey] = $this->createTargetForJson();
        $jsonData = [$targetKey => $target];
        if ($this->timeToLive) {
            $jsonData['time_to_live'] = $this->timeToLive;
        }
        if ($this->delayWhileIdle !== null) {
            $jsonData['delay_while_idle'] = $this->delayWhileIdle;
        }
        if ($this->collapseKey !== '') {
            $jsonData['collapse_key'] = $this->collapseKey;
        }
        if ($this->data) {
            $jsonData['data'] = $this->data;
        }
        if ($this->priority) {
            $jsonData['priority'] = $this->priority;
        }
        if ($this->getNotification()) {
            $jsonData['notification'] = $this->getNotification()->jsonSerialize();
        }

        return $jsonData;
    }

    /**
     * @return array|string[]|string[][]
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\MissingMultipleTopicsCondition
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CountOfTopicsDoesNotMatchConditionPattern
     */
    private function createTargetForJson(): array
    {
        if ($this->targetType === FcmTopicTarget::class) {
            return $this->getFcmTopicTarget();
        }

        return $this->getDeviceTarget();
    }

    /**
     * @return string[]|array
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\MissingMultipleTopicsCondition
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CountOfTopicsDoesNotMatchConditionPattern
     */
    private function getFcmTopicTarget(): array
    {
        $targetCounts = \count($this->targets);
        if ($targetCounts === 1) {
            /** @var FcmTopicTarget $target */
            $target = \current($this->targets);

            return ['targetValue' => '/topics/' . $target->getTopicName(), 'targetKey' => 'to'];
        }
        if (!$this->condition) {
            throw new Exceptions\MissingMultipleTopicsCondition(
                'You must specify a condition pattern when sending to combinations of topics, see https://firebase.google.com/docs/cloud-messaging/topic-messaging#sending_topic_messages_from_the_server'
            );
        }
        if ($targetCounts !== \substr_count($this->condition, '%s')) {
            /** @noinspection IncompleteThrowStatementsInspection */
            throw new Exceptions\CountOfTopicsDoesNotMatchConditionPattern(
                "The number of message topics must match the number of occurrences of '%s' in the condition pattern: '{$this->condition}'"
                . ", got {$targetCounts} topics"
            );
        }
        $names = [];
        /** @var FcmTopicTarget $target */
        foreach ($this->targets as $target) {
            $names[] = "'{$target->getTopicName()}' in topics";
        }

        return ['targetValue' => \vsprintf($this->condition, $names), 'targetKey' => 'condition'];
    }

    /**
     * @return array|string[]|string[][]
     */
    private
    function getDeviceTarget(): array
    {
        $targetCounts = \count($this->targets);
        if ($targetCounts === 1) {
            /** @var FcmDeviceTarget $device */
            $device = \current($this->targets);

            return ['targetValue' => $device->getDeviceToken(), 'targetKey' => 'to'];
        }
        $deviceTokens = [];
        /** @var FcmDeviceTarget $target */
        foreach ($this->targets as $target) {
            $deviceTokens[] = $target->getDeviceToken();
        }

        return ['targetValue' => $deviceTokens, 'targetKey' => 'registration_ids'];
    }
}