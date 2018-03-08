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

    private $jsonData = [];
    private $notification;
    private $collapseKey;
    private $priority;
    private $data;
    /** @var array|FcmTarget[] */
    private $targets = [];
    private $targetType;
    private $condition;

    /**
     * @param FcmTarget $target
     * @return \Granam\FirebaseCloudMessaging\FcmMessage
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CanNotMixRecipientTypes
     */
    public function addTarget(FcmTarget $target): FcmMessage
    {
        $this->targets[] = $target;
        $this->targetType = $this->targetType ?? \get_class($target);
        if ($this->targetType !== \get_class($target)) {
            $givenRecipientClass = \get_class($target);
            throw new Exceptions\CanNotMixRecipientTypes(
                "Mixed target types are not supported by FCM, firstly set is '{$this->targetType}'"
                . ", but currently given is '$givenRecipientClass'"
            );
        }

        return $this;
    }

    public function setNotification(FcmNotification $notification): FcmMessage
    {
        $this->notification = $notification;

        return $this;
    }

    public function setCollapseKey(string $collapseKey): FcmMessage
    {
        $this->collapseKey = $collapseKey;

        return $this;
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

    /**
     * Set root message data via key
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setJsonDataValue(string $key, $value): FcmMessage
    {
        $this->jsonData[$key] = $value;

        return $this;
    }

    /**
     * Unset root message data via key
     *
     * @param string $key
     * @return $this
     */
    public function deleteJsonDataItem(string $key): FcmMessage
    {
        unset($this->jsonData[$key]);

        return $this;
    }

    /**
     * Get root message data via key
     *
     * @param string $key
     * @return mixed
     */
    public function getJsonDataItem(string $key)
    {
        return $this->jsonData[$key];
    }

    /**
     * Get root message data
     *
     * @return array
     */
    public function getJsonData(): array
    {
        return $this->jsonData;
    }

    /**
     * Set root message data
     *
     * @param array $array
     * @return $this
     */
    public function setJsonData(array $array): FcmMessage
    {
        $this->jsonData = $array;

        return $this;
    }

    public function enableDelayWhileIdle(): FcmMessage
    {
        $this->setJsonDataValue('delay_while_idle', true);

        return $this;
    }

    public function disableDelayWhileIdle(): FcmMessage
    {
        $this->setJsonDataValue('delay_while_idle', false);

        return $this;
    }

    public function setTimeToLive(int $value): FcmMessage
    {
        $this->setJsonDataValue('time_to_live', $value);

        return $this;
    }

    /**
     * @return array
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\MissingTargets
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfTopics
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\MissingMultipleTopicsCondition
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CountOfTopicsDoesNotMatchConditionPattern
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfDevices
     */
    public function jsonSerialize(): array
    {
        $target = $this->createTargetForJson();
        $jsonData = $this->jsonData;
        if (\count($this->targets) === 1) {
            $jsonData['to'] = $target;
        } elseif ($this->targetType === FcmDeviceTarget::class) {
            $jsonData['registration_ids'] = $target;
        } else {
            $jsonData['condition'] = $target;
        }

        if ($this->collapseKey) {
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

    /**
     * @return array|null|string
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\MissingTargets
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfTopics
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\MissingMultipleTopicsCondition
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\CountOfTopicsDoesNotMatchConditionPattern
     * @throws \Granam\FirebaseCloudMessaging\Exceptions\ExceededLimitOfDevices
     */
    private function createTargetForJson()
    {
        $targetCounts = \count($this->targets);
        if ($targetCounts === 0) {
            throw new Exceptions\MissingTargets('Message must have at least one target set');
        }
        switch ($this->targetType) {
            case FcmTopicTarget::class :
                if ($targetCounts === 1) {
                    /** @var FcmTopicTarget $target */
                    $target = \current($this->targets);

                    return '/topics/' . $target->getTopicName();
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

                return \vsprintf($this->condition, $names);
            case FcmDeviceTarget::class :
                if ($targetCounts === 1) {
                    /** @var FcmDeviceTarget $device */
                    $device = \current($this->targets);

                    return $device->getToken();
                }
                if ($targetCounts > self::MAX_DEVICES) {
                    throw new Exceptions\ExceededLimitOfDevices(
                        'Message device limit exceeded. Firebase supports a maximum of ' . self::MAX_DEVICES . ' devices'
                        . ", got {$targetCounts} devices"
                    );
                }
                $tokens = [];
                /** @var FcmDeviceTarget $target */
                foreach ($this->targets as $target) {
                    $tokens[] = $target->getToken();
                }

                return $tokens;
            default:
                break;
        }

        return null;
    }
}