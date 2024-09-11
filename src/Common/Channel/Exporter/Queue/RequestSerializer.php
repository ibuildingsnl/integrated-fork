<?php

namespace Integrated\Common\Channel\Exporter\Queue;

use Integrated\Common\Channel\ChannelManagerInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\Security\Acl\Util\ClassUtils;
use Symfony\Component\Serializer\SerializerInterface;

class RequestSerializer implements RequestSerializerInterface
{
    public const CONTENT_REMOVED = 'removed';

    /**
     * @var SerializerInterface
     */
    protected $serializer = null;

    /**
     * @var ChannelManagerInterface
     */
    protected $manager = null;

    /**
     * Constructor.
     */
    public function __construct(SerializerInterface $serializer, ChannelManagerInterface $manager)
    {
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * @return SerializerInterface
     */
    protected function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @return ChannelManagerInterface
     */
    protected function getManager()
    {
        return $this->manager;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(Request $data)
    {
        return json_encode(
            [
                'content' => [
                    'data' => $this->getSerializer()->serialize($data->content, 'json'),
                    'type' => ClassUtils::getRealClass($data->content),
                ],
                'state' => $data->state,
                'channel' => $data->channel instanceof ChannelInterface ? $data->channel->getId() : null,
                'settings' => $data->settings ?? [],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data)
    {
        $data = json_decode($data, true);

        if (!\is_array(
            $data
        ) || empty($data['content']) || empty($data['content']['data']) || empty($data['content']['type']) || empty($data['state']) || empty($data['channel'])) {
            return null;
        }

        $request = new Request();

        try {
            $request->content = $this->getSerializer()->deserialize(
                $data['content']['data'],
                $data['content']['type'],
                'json'
            );
            $request->state = (string) $data['state'];
            $request->channel = $this->getManager()->find($data['channel']);
            $request->settings = $data['settings'] ?? [];
        } catch (\Exception $e) {
            return null;
        }

        // let the exporter know if content is removed from the database
        if (!$request->content) {
            return self::CONTENT_REMOVED;
        }

        // only return a valid none empty request object
        if ($request->content && $request->channel instanceof ChannelInterface) {
            return $request;
        }

        return null;
    }
}
