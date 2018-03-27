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
    private $ttl;
    private $delayWhileIdle;

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
        if (\is_a($target, FcmDeviceTarget::class) && (\count($this->targets) + 1) > self::MAX_DEVICES) {
            throw new Exceptions\ExceededLimitOfDevices(
                'Message device limit exceeded. Firebase supports a maximum of ' . self::MAX_DEVICES . ' devices'
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
     */
    public function addTargets(array $targets): void
    {
        foreach ($targets as $target) {
            $this->addTarget($target);
        }
    }

    public function setNotification(FcmNotification $notification): FcmMessage
    {
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
        $this->delayWhileIdle = true;

        return $this;
    }

    public function setTimeToLive(int $ttl): FcmMessage
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * @return array
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfTopics
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\MissingMultipleTopicsCondition
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CountOfTopicsDoesNotMatchConditionPattern
     */
    public function jsonSerialize(): array
    {
        $jsonData = $this->getJsonData();
        ['value' => $target, 'key' => $targetKey] = $this->createTargetForJson();
        $jsonData[$targetKey] = $target;
        if ($this->collapseKey !== '') {
            $jsonData['collapse_key'] = $this->collapseKey;
        }
        if ($this->data) {
            $jsonData['data'] = $this->data;
        }
        if ($this->priority) {
            $jsonData['priority'] = $this->priority;
        }
        if ($this->notification) {
            $jsonData['notification'] = $this->notification;
        }

        return $jsonData;
    }

    protected function getJsonData(): array
    {
        $jsonData = [];
        if ($this->ttl) {
            $jsonData['time_to_live'] = $this->ttl;
        }
        if ($this->delayWhileIdle !== null) {
            $jsonData['delay_while_idle'] = $this->delayWhileIdle;
        }

        return $jsonData;
    }

    /**
     * @return array|string[]|string[][]
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfTopics
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
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfTopics
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\MissingMultipleTopicsCondition
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CountOfTopicsDoesNotMatchConditionPattern
     */
    private function getFcmTopicTarget(): array
    {
        $targetCounts = \count($this->targets);
        if ($targetCounts === 1) {
            /** @var FcmTopicTarget $target */
            $target = \current($this->targets);

            return ['value' => '/topics/' . $target->getTopicName(), 'key' => 'to'];
        }
        if ($targetCounts > self::MAX_TOPICS) {
            throw new Exceptions\ExceededLimitOfTopics(
                'Message topic limit exceeded. Firebase supports a maximum of ' . self::MAX_TOPICS
                . " topics for a single message, got {$targetCounts} topics"
            );
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

        return ['value' => \vsprintf($this->condition, $names), 'key' => 'condition'];
    }

    /**
     * @return array|string[]|string[][]
     */
    private function getDeviceTarget(): array
    {
        $targetCounts = \count($this->targets);
        if ($targetCounts === 1) {
            /** @var FcmDeviceTarget $device */
            $device = \current($this->targets);

            return ['value' => $device->getDeviceToken(), 'key' => 'to'];
        }
        $deviceTokens = [];
        /** @var FcmDeviceTarget $target */
        foreach ($this->targets as $target) {
            $deviceTokens[] = $target->getDeviceToken();
        }

        return ['value' => $deviceTokens, 'key' => 'registration_ids'];
    }
}