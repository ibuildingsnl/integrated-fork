<?php

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RenameContentTypeCommand extends Command
{
    protected static $defaultName = 'app:rename-content-type';
    private $connection;

    public function __construct(DocumentManager $documentManager)
    {
        parent::__construct();
        $this->connection = $documentManager->getConnection();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $this->connection->selectDatabase('my_database');
        $collection = $db->selectCollection('my_collection');
        $query = ['contentType' => 'bb_news'];
        $update = ['$set' => ['contentType' => 'news']];
        $options = ['multiple' => true];
        $result = $collection->updateMany($query, $update, $options);
        $output->writeln(sprintf('%d documents were updated.', $result->getMatchedCount()));
    }
}
