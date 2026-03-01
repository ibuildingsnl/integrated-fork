<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\PageBuilder\V2\Validation\LayoutPayloadValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'pagebuilder:v2:verify')]
final class PageBuilderV2VerifyCommand extends Command
{
    protected static $defaultName = 'pagebuilder:v2:verify';

    private DocumentManager $documentManager;
    private LayoutPayloadValidator $validator;

    public function __construct(DocumentManager $documentManager, LayoutPayloadValidator $validator)
    {
        $this->documentManager = $documentManager;
        $this->validator = $validator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Verify pagebuilder v2 migration completeness');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $total = 0;
        $valid = 0;
        $invalid = 0;
        $pending = 0;

        foreach ($this->loadPages() as $page) {
            $total++;

            if ($page->getLayoutVersion() !== 2) {
                $pending++;

                continue;
            }

            $errors = $this->validator->validate($page->getLayoutPayload(), 'default');
            if (\count($errors) > 0) {
                $invalid++;

                continue;
            }

            $valid++;
        }

        $output->writeln(sprintf(
            'pagebuilder:v2:verify summary total=%d valid=%d invalid=%d pending=%d',
            $total,
            $valid,
            $invalid,
            $pending
        ));

        if ($invalid > 0 || $pending > 0) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, AbstractPage>
     */
    private function loadPages(): array
    {
        $pages = [];

        foreach ((array) $this->documentManager->getRepository(Page::class)->findAll() as $page) {
            if ($page instanceof AbstractPage) {
                $pages[] = $page;
            }
        }

        foreach ((array) $this->documentManager->getRepository(ContentTypePage::class)->findAll() as $page) {
            if ($page instanceof AbstractPage) {
                $pages[] = $page;
            }
        }

        return $pages;
    }
}
