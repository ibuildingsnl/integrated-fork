<?php

namespace Integrated\Bundle\InstallerBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use MongoDB\Database;

class Version20230317121211 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Migrate relations in search selections';
    }

    public function up(Database $db)
    {
        $relations = [];

        foreach ($db->selectCollection('relation')->find() as $document) {
            $relations[preg_replace('/[^a-zA-Z]/', '', $document['name'])] = $document['_id'];
        }

        $collection = $db->selectCollection('search_selection');

        foreach ($collection->find() as $document) {
            $filters = $document['filters'];

            foreach ($relations as $old => $new) {
                if (isset($filters[$old])) {
                    $filters['relation'][$new] = $filters[$old];
                }

                unset($filters[$old]);
            }

            $collection->updateOne(['_id' => $document['_id']], ['$set' => ['filters' => $filters]]);
        }
    }

    public function down(Database $db)
    {
        $relations = [];

        foreach ($db->selectCollection('relation')->find() as $document) {
            $relations[$document['_id']] = preg_replace('/[^a-zA-Z]/', '', $document['name']);
        }

        $collection = $db->selectCollection('search_selection');

        foreach ($collection->find() as $document) {
            $filters = $document['filters'];

            foreach ($relations as $new => $old) {
                if (isset($filters['relation'][$new])) {
                    $filters[$old] = $filters['relation'][$new];
                }
            }

            unset($filters['relation']);

            $collection->updateOne(['_id' => $document['_id']], ['$set' => ['filters' => $filters]]);
        }
    }
}
