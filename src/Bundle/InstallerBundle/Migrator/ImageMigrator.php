<?php

namespace Integrated\Bundle\InstallerBundle\Migrator;

use Integrated\Bundle\ContentBundle\Document\Content\Image;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Database;
use Ramsey\Uuid\Uuid;

class ImageMigrator
{
    private UTCDateTime $timeNow;
    private UTCDateTime $timeMax;

    public function __construct(
        private readonly Database $db
    ) {
        $this->timeNow = new UTCDateTime((new \DateTime())->getTimestamp() * 1000);
        $this->timeMax = new UTCDateTime(253402214400000);
    }

    public function move(string $class, string $field): void
    {
        $images = [];

        foreach ($this->getImages($class, $field) as $image) {
            $images[$image['_id']] = $image;

            $images[$image['_id']]['_id'] = Uuid::uuid4()->getHex()->toString();
        }

        if (0 === \count($images)) {
            return;
        }

        $this->db->selectCollection('content')->insertMany(array_values($images));

        $this->updateItems($class, $images);
    }

    private function updateItems(string $class, array $images): void
    {
        foreach ($images as $id => $image) {
            $this->db->selectCollection('content')->updateOne(['_id' => $id], [
                '$set' => [
                    'logo' => [
                        '$ref' => 'content',
                        '$id' => $image['_id'],
                    ],
                ],
            ]);
        }
    }

    private function getImages(string $class, string $field): \Traversable
    {
        return $this->db->selectCollection('content')->aggregate([
            [
                '$match' => [
                    'class' => $class,
                    $field => ['$exists' => true],
                    $field.'.identifier' => ['$exists' => true],
                    $field.'.pathname' => ['$exists' => true],
                ],
            ],
            [
                '$project' => [
                    '_id' => 1,
                    'title' => ['$concat' => [['$ifNull' => ['$name', ['$ifNull' => ['$title', '$_id']]]], ' image']],
                    'contentType' => 'image',
                    'file' => ['$ifNull' => ['$'.$field, null]],
                    'channels' => ['$ifNull' => ['$channels', null]],
                    'createdAt' => $this->timeNow,
                    'updateAt' => $this->timeNow,
                    'published' => ['$toBool' => true],
                    'disabled' => ['$toBool' => false],
                    'publishTime' => [
                        'startDate' => $this->timeNow,
                        'endDate' => $this->timeMax,
                    ],
                    'class' => Image::class,
                ],
            ],
        ]);
    }
}
