<?php

namespace Integrated\Bundle\ImportBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Doctrine\ORM\EntityManager;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\FormTypeBundle\Form\Type\FormActionsType;
use Integrated\Bundle\ImportBundle\Document\Embedded\ImportField;
use Integrated\Bundle\ImportBundle\Document\ImportDefinition;
use Integrated\Bundle\ImportBundle\Form\Type\ImportDefinitionType;
use Integrated\Bundle\ImportBundle\Import\Converter\BaseConverter;
use Integrated\Bundle\ImportBundle\Import\Converter\DefinitionComposer;
use Integrated\Bundle\ImportBundle\Import\Converter\ExecuteImporter;
use Integrated\Bundle\ImportBundle\Import\Converter\WP;
use Integrated\Bundle\ImportBundle\Import\Create\Create;
use Integrated\Bundle\ImportBundle\Import\ImportProcessor;
use Integrated\Bundle\ImportBundle\Import\Provider\Doctrine;
use Integrated\Bundle\ImportBundle\Import\Provider\File as ImportFile;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\StorageBundle\Storage\Manager;
use Integrated\Common\Content\Form\ContentFormType;
use KubAT\PhpSimple\HtmlDomParser;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class ImportController extends AbstractController
{
    public function __construct(
        private ContentTypeManager $contentTypeManager,
        private DocumentManager $documentManager,
        private EntityManager $entityManager,
        private ImportFile $importFile,
        private PublicationRepositoryInterface $publications,
        private Doctrine $doctrine,
        private Manager $storageManager,
        private ImportProcessor $processor
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $contentTypes = $this->contentTypeManager->getAll();

        $documents = $this->documentManager->getRepository(ImportDefinition::class)->findBy([], ['name' => 'asc']);

        return $this->render(
            '@IntegratedImport/index.html.twig',
            [
                'contentTypes' => $contentTypes,
                'documents' => $documents,
            ]
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cloneImport(ImportDefinition $importDefinition)
    {
        $copiedImportDefinition = clone $importDefinition;
        $copiedImportDefinition->setName('Copy of ' . $copiedImportDefinition->getName());
        $copiedImportDefinition->setFileId(null);

        $this->documentManager->persist($copiedImportDefinition);
        $this->documentManager->flush();

        return $this->redirectToRoute('integrated_import_edit', ['importDefinition' => $copiedImportDefinition->getId()]
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newImport(Request $request, ContentType $type)
    {
        $importDefinition = new ImportDefinition();
        $importDefinition->setContentType($type->getId());

        $form = $this->createImportDefinitionForm($importDefinition);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->documentManager->persist($importDefinition);
            $this->documentManager->flush();

            return $this->redirect(
                $this->generateUrl('integrated_import_file', ['importDefinition' => $importDefinition->getId()])
            );
        }

        return $this->render(
            '@IntegratedImport/new.html.twig',
            [
                'contentType' => $type,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editImport(Request $request, ImportDefinition $importDefinition)
    {
        $form = $this->editImportDefinitionForm($importDefinition);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->documentManager->flush();

            return $this->redirect(
                $this->generateUrl('integrated_import_index')
            );
        }

        return $this->render(
            '@IntegratedImport/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function chooseFile(Request $request, ImportDefinition $importDefinition)
    {
        $contentTypeFile = $this->importFile->getContentType();

        $file = false;
        $method = 'PUT';

        if ($importDefinition->getConnectionUrl() && $importDefinition->getConnectionQuery()) {
            return $this->redirect(
                $this->generateUrl('integrated_import_definition', ['importDefinition' => $importDefinition->getId()])
            );
        }

        if ($importDefinition->getFileId()) {
            $file = $this->documentManager->find(File::class, $importDefinition->getFileId());
        }

        if (!$file) {
            // file not yet uploaded, create a new one
            $file = new File();
            $file->setContentType('import_file');
            $method = 'POST';
        }

        if ($method == 'POST') {
            $form = $this->createForm(ContentFormType::class, $file, [
                'action' => $this->generateUrl(
                    'integrated_import_file',
                    ['importDefinition' => $importDefinition->getId()]
                ),
                'method' => $method,
                'content_type' => $contentTypeFile->getId(),
            ]);
        } else {
            $form = $this->createForm(ContentFormType::class, $file, [
                'action' => $this->generateUrl(
                    'integrated_import_file',
                    ['importDefinition' => $importDefinition->getId()]
                ),
                'method' => $method,
                'content_type' => $contentTypeFile->getId(),
                'attr' => ['class' => 'content-form', 'data-content-id' => $file->getId()],
            ]);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($method == 'POST') {
                $this->documentManager->persist($file);
            }
            $this->documentManager->flush();

            $importDefinition->setFileId($file->getId());
            $this->documentManager->flush();

            return $this->redirect(
                $this->generateUrl('integrated_import_definition', ['importDefinition' => $importDefinition->getId()])
            );
        }

        return $this->render(
            '@IntegratedImport/chooseFile.html.twig',
            [
                'importDefinition' => $importDefinition,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function composeDefinition(Request $request, ImportDefinition $importDefinition)
    {
        DefinitionComposer::initConfig();

        try {
            $data = DefinitionComposer::fetchData($importDefinition, $this->doctrine, $this->importFile);
            $contentType = $this->documentManager->find(ContentType::class, $importDefinition->getContentType());
            $contentTypeFields = DefinitionComposer::initSerializer($contentType);
            $fields = DefinitionComposer::generateFieldMappings(
                $this->processor,
                $contentTypeFields,
                $importDefinition,
                $this->entityManager,
                $this->documentManager
            );
            $result = DefinitionComposer::generateWarnings($importDefinition, $fields, $data);

            foreach ($result['warnings'] as $warning) {
                $this->addFlash('warning', $warning);
            }

            $fields = $result['fields'];

            $data = DefinitionComposer::processColumnData($data);

            if ($request->request->get('action') == 'go') {
                if (isset($data[0])) {
                    $cols = \count($data[0]);
                    $fields2 = [];
                    for ($col = 0; $col < $cols; ++$col) {
                        $mappedField = $request->request->get('col' . $col, null);
                        if ($mappedField) {
                            $field = new ImportField();
                            $field->setColumn($col);
                            $field->setSourceField($data[0][$col]);
                            $field->setMappedField($mappedField);
                            $fields2[] = $field;
                        }
                    }

                    $importDefinition->setFields($fields2);
                    $this->documentManager->flush();
                }

                return $this->redirectToRoute(
                    'integrated_import_summary',
                    ['importDefinition' => $importDefinition->getId()]
                );
            }
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Unable to read import file: ' . $e->getMessage() . $e->getTraceAsString());
            $fields = [];
            $data = [];
        }

        return $this->render(
            '@IntegratedImport/composeDefinition.html.twig',
            [
                'importDefinition' => $importDefinition,
                'fields' => $fields,
                'data' => $data,
            ]
        );
    }

    public function summary(ImportDefinition $importDefinition)
    {
        ini_set('max_execution_time', 3600);
        ini_set('memory_limit', '4G');

        if ($importDefinition->getConnectionUrl() && $importDefinition->getConnectionQuery()) {
            $data = $this->doctrine->toArray($importDefinition);
        } else {
            $data = $this->importFile->toArray($importDefinition);
        }

        // found out which fields are not in the definition
        $ignoredFields = $data[0];
        foreach ($importDefinition->getFields() as $field) {
            $index = array_search($field->getSourceField(), $ignoredFields);
            if ($index !== false) {
                unset($ignoredFields[$index]);
            }
        }

        return $this->render(
            '@IntegratedImport/summary.html.twig',
            [
                'records' => \count($data),
                'importDefinition' => $importDefinition,
                'ignoredFields' => $ignoredFields,
            ]
        );
    }

    public function run(ImportDefinition $importDefinition)
    {
        return $this->render(
            '@IntegratedImport/run.html.twig',
            [
                'importDefinition' => $importDefinition,
                'startTime' => time(),
            ]
        );
    }

    public function runExecute(Request $request, ImportDefinition $importDefinition)
    {
        ExecuteImporter::configureExecutionEnvironment();

        $session = new Session();
        $session->save();

        $start = $request->get('start', 1);

        if (!$session->isStarted()) {
            $session->start();
        }

        if ($start <= 1) {
            $session->remove('import_data');
            $session->remove('import_type');
            $session->remove('content_type');
            $session->remove('field_mapping');
        }

        if ($start < 1 || !$session->has('import_data')) {
            $data = ExecuteImporter::getData($importDefinition, $this->doctrine, $this->importFile);
            $session->set('import_data', $data);
        } else {
            $data = $session->get('import_data');
        }

        if ($start < 1 || !$session->has('import_type')) {
            $importType = ExecuteImporter::getImportType($importDefinition, $data, $this->importFile);
            $session->set('import_type', $importType);
        } else {
            $importType = $session->get('import_type');
        }

        if ($start < 1 || !$session->has('content_type')) {
            $contentType = $this->documentManager->find(ContentType::class, $importDefinition->getContentType());
            $session->set('content_type', $contentType);
        } else {
            $contentType = $session->get('content_type');
        }

        if ($start < 1 || !$session->has('field_mapping')) {
            $fieldMapping = [];
            foreach ($importDefinition->getFields() as $field) {
                $fieldMapping[$field->getSourceField()] = $field->getMappedField();
            }
            $session->set('field_mapping', $fieldMapping);
        } else {
            $fieldMapping = $session->get('field_mapping');
        }

        $result = ExecuteImporter::initializeResult();

        $totalRowNumber = \count($data);
        $rowsPerRequest = min(25, max(10, min(500, (int)$totalRowNumber / 10)));

        $rowNumber = -1;

        if ($start <= 1) {
            $start = 0;
            $rowsPerRequest = 10;
            $result['messages'][] = "[STARTING IMPORT] New import, recognized format is {$importType}";
        }

        $newStart = $start;
        foreach ($data as $row) {
            ++$rowNumber;
            if ($rowNumber <= 0 || $rowNumber < $start) {
                // skip heading row and processed rows
                continue;
            }

            if (($newStart - $start) > $rowsPerRequest) {
                // max 2 items
                $result['start'] = $newStart;
                $result['done'] = false;
                continue;
            }

            $newStart = $newStart + 1;

            $col = 0;

            $newData = BaseConverter::fieldMapper($fieldMapping, $row, $data, $col);

            BaseConverter::processDateFields($newData);

            if (\count($newData)) {
                $updating = false;
                $checkResult = BaseConverter::checkForExistingContent(
                    $importDefinition,
                    $row,
                    $newData,
                    $this->documentManager
                );
                $result['messages'] = array_merge($result['messages'], $checkResult['result']['messages']);
                if ($checkResult['target'] != null) {
                    $newObject = $checkResult['target'];
                    $updating = true;
                    if (!$importDefinition->getUpdateExisting()) {
                        $result['messages'][] = "[EXISTING ITEM] Existing item found, skipping: {$newData['title']}";
                        continue;
                    }
                } else {
                    $newObject = $contentType->create();
                    if (\array_key_exists('title', $newData)) {
                        $result['messages'][] = "[NEW ITEM] New item found, creating: {$newData['title']}";
                    }
                }

                BaseConverter::setPublicationDate($row, $newData, $newObject);

//TODO: This does not yet work as expected due to ContentSubscriber.php
//                $checkResult = BaseConverter::setPublished($newData, $newObject, $importDefinition, $this->documentManager, $this->entityManager);
//
//                $result['messages'] = array_merge($result['messages'], $checkResult['messages']);

                BaseConverter::setObjectProperties(
                    $newData,
                    $newObject,
                    $importDefinition,
                    $this->storageManager,
                    $this->documentManager,
                    $importType
                );

                if ($importDefinition->getImageRelation()) {
                    if ($relation = $newObject->getRelation($importDefinition->getImageRelation()->getId())) {
                        $newObject->removeRelation($relation);
                    }
                    if ($relation = $newObject->getRelation('__editor_image')) {
                        $newObject->removeRelation($relation);
                    }
                }

                if ($relation = $newObject->getRelation('__editor_image')) {
                    $newObject->removeRelation($relation);
                }

                // Process Person Object
                if ($newObject instanceof Person) {
                    $newObject = BaseConverter::processPersonObject(
                        $newObject,
                        $row,
                        $importDefinition,
                        $this->storageManager
                    );
                }

                if ($newObject instanceof Taxonomy) {
                    if (!array_key_exists('parent_id', $newData)) {
                        $newData['parent_id'] = '';
                    }
                    $checkResult = Create::maybeCreateParent(
                        $newData['parent_id'],
                        $contentType,
                        $this->documentManager,
                        $importDefinition
                    );
                    if ($checkResult['parent'] !== false) {
                        $newObject->setParentId($checkResult['parent']->getId());
                    } else {
                        $newObject->setParentId($checkResult['brandParent']->getId());
                    }
                    $result['messages'] = array_merge($result['messages'], $checkResult['result']['messages']);
                }

                try {
                    foreach ($importDefinition->getChannels() as $channel) {
                        if ($newObject instanceof Person) {
                            if ($row['own_page'] != 1) {
                                continue;
                            }
                        }
                        $newObject->addChannel($channel);
                        $result['messages'][] = "[INFO] Linked to channel: {$channel->getName()}";
                    }

                    $col = 0;
                    foreach ($row as $name => $value) {
                        $currentColValue = $data[0][$col];
                        if (!isset($fieldMapping[$currentColValue])) {
                            ++$col;
                            continue;
                        }
                        $mappedField = $fieldMapping[$currentColValue];

                        $checkResult = BaseConverter::fieldProcessor(
                            $mappedField,
                            $value,
                            $newObject,
                            $importDefinition,
                            $this->documentManager,
                            $this->storageManager
                        );

                        $result['messages'] = array_merge($result['messages'], $checkResult['messages']);

                        ++$col;
                    }

//                    if (!in_array('author-author', $fieldMapping) && $newObject instanceof Article) {
//                        $checkResult = BaseConverter::authorProcessor(
//                            $row,
//                            $newObject,
//                            $importDefinition,
//                            $this->documentManager
//                        );
//                        $result['messages'] = array_merge($result['messages'], $checkResult['messages']);
//                    }


                    if ($newObject instanceof Article || $newObject instanceof Person || $newObject instanceof Taxonomy) {
                        if ($newObject instanceof Article) {
                            $content = $newObject->getContent();
                        } else {
                            $content = $newObject->getDescription();
                        }

                        $content = WP::processContent(
                            $content,
                            $importType,
                            $importDefinition,
                            $newObject,
                            $newData,
                            $this->storageManager,
                            $this->documentManager
                        );

                        $result['messages'] = array_merge($result['messages'], $content['result']['messages']);

                        $html = HtmlDomParser::str_get_html($content['newHtml']);

                        $checkResult = BaseConverter::processImageElements(
                            $html,
                            $newObject,
                            $newData,
                            $importDefinition,
                            $this->documentManager,
                            $this->storageManager
                        );

                        $result['messages'] = array_merge($result['messages'], $checkResult['result']['messages']);

                        $html = (string)$checkResult['html'];

                        if ($html === '') {
                            $result['messages'][] = "[WARNING] No valid HTML for {(string)$newObject}, content ignored";
                            $html = HtmlDomParser::str_get_html('<p></p>');
                        }

                        if ($newObject instanceof Article) {
                            $newObject->setContent($html);
                        } else {
                            $newObject->setDescription($html);
                        }
                    }

                    // Process Metadata
                    $checkResult = BaseConverter::processMetadata(
                        $row,
                        $newData,
                        $newObject,
                        $importDefinition,
                        $importType,
                        $this->documentManager,
                        $this->storageManager
                    );
                    $result['messages'] = array_merge($result['messages'], $checkResult['result']['messages']);
                    $newObject = $checkResult['newObject'];

                    if ($this->documentManager->getUnitOfWork()->getDocumentState(
                            $newObject
                        ) !== UnitOfWork::STATE_MANAGED) {
                        $this->documentManager->persist($newObject);
                    }
                    $this->documentManager->flush();

                    $import_id = $newObject->getId();
                    if (isset($row['wp:post_id'])) {
                        $import_id = $row['wp:post_id'];
                    }

                    if (!count($this->publications->forContent($newObject)) > 0) {
                        foreach ($newObject->getChannels() as $channel) {
                            $time = $newObject->getPublishTime();
                            $this->publications->add(
                                new Publication($newObject, $channel, $time, [])
                            );
                        }
                    }

                    $result['messages'][] = '[SUCCES] Item ' . $import_id . ' (' . (string)$newObject . ') ' . ($updating ? 'updated' : 'created');
                    $result['messages'][] = '---NEW IMPORT---';
                    $result['success'][] = 'Item ' . $import_id . ' (' . (string)$newObject . ') imported';
                } catch (\Exception $e) {
                    $result['errors'][] = 'Item ' . (string)$newObject . ' failed: ' . $e->getMessage() . ' ' . nl2br(
                            $e->getTraceAsString()
                        ) . ' ' . $e->getFile() . ' ' . $e->getLine();
                } catch (\Throwable $e) {
                    $result['errors'][] = 'Item ' . (string)$newObject . ' fatal: ' . $e->getMessage() . ' ' . nl2br(
                            $e->getTraceAsString()
                        ) . ' ' . $e->getFile() . ' ' . $e->getLine();
                }
            }
        }

        $remainingTime = ExecuteImporter::calculateRemainingTime($request, $newStart, $totalRowNumber);

        $result = array_merge($result, $remainingTime);

        return new JsonResponse($result);
    }

    /**
     * Creates a form to edit an ImportDefinition document.
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createImportDefinitionForm(ImportDefinition $importDefinition)
    {
        $form = $this->createForm(
            ImportDefinitionType::class,
            $importDefinition,
            [
                'method' => 'POST',
            ]
        );

        $form->add('actions', FormActionsType::class, [
            'buttons' => [
                'create' => [
                    'type' => SubmitType::class,
                    'options' => [
                        'label' => 'Create',
                        'button_class' => 'orange no-icon',
                    ],
                ],
                'cancel' => [
                    'type' => SubmitType::class,
                    'options' => [
                        'label' => 'Back',
                        'button_class' => 'white icon-left',
                        'attr' => [
                            'formnovalidate' => 'formnovalidate',
                            'data-dismiss' => 'modal',
                            'icon' => 'arrow-left',
                        ],
                    ],
                ],
            ],
        ]);

        return $form;
    }

    /**
     * Creates a form to edit an ImportDefinition document.
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function editImportDefinitionForm(ImportDefinition $importDefinition)
    {
        $form = $this->createForm(
            ImportDefinitionType::class,
            $importDefinition,
            [
                'method' => 'POST',
            ]
        );

        $form->add('actions', FormActionsType::class, [
            'buttons' => [
                'create' => [
                    'type' => SubmitType::class,
                    'options' => [
                        'label' => 'Update',
                        'button_class' => 'green no-icon',
                    ],
                ],
                'cancel' => [
                    'type' => SubmitType::class,
                    'options' => [
                        'label' => 'Back',
                        'button_class' => 'white icon-left',
                        'attr' => [
                            'formnovalidate' => 'formnovalidate',
                            'data-dismiss' => 'modal',
                            'icon' => 'arrow-left',
                        ],
                    ],
                ],
            ],
        ]);

        return $form;
    }
}
