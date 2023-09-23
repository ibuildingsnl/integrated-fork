<?php

namespace Integrated\Bundle\ImportBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Doctrine\ORM\EntityManager;
use Integrated\Bundle\ChannelBundle\Model\Config;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Connector;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage\Metadata as StorageMetadata;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\FormTypeBundle\Form\Type\FormActionsType;
use Integrated\Bundle\ImportBundle\Document\Embedded\ImportField;
use Integrated\Bundle\ImportBundle\Document\ImportDefinition;
use Integrated\Bundle\ImportBundle\Form\Type\ImportDefinitionType;
use Integrated\Bundle\ImportBundle\Import\Create\Create;
use Integrated\Bundle\ImportBundle\Import\ImportProcessor;
use Integrated\Bundle\ImportBundle\Import\Converter\WP;
use Integrated\Bundle\ImportBundle\Import\Converter\BaseConverter;
use Integrated\Bundle\ImportBundle\Import\Provider\Doctrine;
use Integrated\Bundle\ImportBundle\Import\Provider\File as ImportFile;
use Integrated\Bundle\ImportBundle\Serializer\InitializedObjectConstructor;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\StorageBundle\Storage\Manager;
use Integrated\Bundle\StorageBundle\Storage\Reader\MemoryReader;
use Integrated\Common\Content\Form\ContentFormType;
use JMS\Serializer\Construction\UnserializeObjectConstructor;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Sunra\PhpSimple\HtmlDomParser;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class ImportController extends AbstractController
{
    /**
     * @var ContentTypeManager
     */
    protected $contentTypeManager;

    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ImportFile
     */
    protected $importFile;

    /**
     * @var Manager
     */
    protected $storageManager;

    /**
     * @var ImportProcessor
     */
    private $processor;

    /**
     * @var Doctrine
     */
    private $doctrine;

    public function __construct(
        ContentTypeManager $contentTypeManager,
        DocumentManager $documentManager,
        EntityManager $entityManager,
        ImportFile $importFile,
        Doctrine $doctrine,
        Manager $storageManager,
        ImportProcessor $processor
    ) {
        $this->contentTypeManager = $contentTypeManager;
        $this->documentManager = $documentManager;
        $this->entityManager = $entityManager;
        $this->importFile = $importFile;
        $this->doctrine = $doctrine;
        $this->storageManager = $storageManager;
        $this->processor = $processor;
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

        $form = $this->createCreateImportDefinitionForm($importDefinition);

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
        $form = $this->createCreateImportDefinitionForm($importDefinition);

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
        ini_set('max_execution_time', 3600);
        ini_set('memory_limit', '4G');

        try {
            if ($importDefinition->getConnectionUrl() && $importDefinition->getConnectionQuery()) {
                $data = $this->doctrine->toArray($importDefinition);
            } else {
                $data = $this->importFile->toArray($importDefinition);
            }


            $contentType = $this->documentManager->find(
                ContentType::class,
                $importDefinition->getContentType()
            );


            $context = new SerializationContext();
            $context->setSerializeNull(true);

            $serializer = SerializerBuilder::create()
                                           ->addMetadataDir(
                                               realpath(__DIR__ . '/../../ContentBundle/Resources/serializer')
                                           )
                                           ->setObjectConstructor(
                                               new InitializedObjectConstructor(new UnserializeObjectConstructor())
                                           )
                                           ->build();
            $contentTypeFields = json_decode($serializer->serialize($contentType->create(), 'json', $context), true);

            $fields = $this->processor->getFields();

            foreach ($contentTypeFields as $contentTypeField => $contentTypeValue) {
                $contentTypeFields = [];
                if (\is_array($contentTypeValue)) {
                    foreach ($contentTypeValue as $contentTypeField2 => $contentTypeValue2) {
                        $contentTypeFields[] = $contentTypeField . '.' . $contentTypeField2;
                    }
                } else {
                    $contentTypeFields[] = $contentTypeField;
                }
                foreach ($contentTypeFields as $contentTypeField) {
                    $matchCol = false;
                    if (!$importDefinition->getFields()) {
                        if (isset($data[0])) {
                            $col = 1;
                            foreach ($data[0] as $dataValue) {
                                $dataName = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $dataValue));
                                $contentTypeFieldName = strtolower(
                                    preg_replace('/[^A-Za-z0-9]/', '', $contentTypeField)
                                );
                                if ($dataName == $contentTypeFieldName) {
                                    if (!$matchCol) {
                                        $matchCol = [$col];
                                    }
                                }
                                ++$col;
                            }
                        }
                    }
                    // check current field

                    $fields['field-' . $contentTypeField] = ['label' => $contentTypeField, 'matchCol' => $matchCol];
                }
            }

            $fields['author-author'] = ['label' => 'Author', 'matchCol' => false];
            $fields['meta-meta'] = ['label' => 'Metadata', 'matchCol' => false];

            $configs = $this->entityManager->getRepository(Config::class)->findAll();
            foreach ($configs as $config) {
                $fields['connector-' . $config->getId()] = [
                    'label' => 'ID for ' . $config->getName(),
                    'matchCol' => false
                ];
            }

            $relations = $this->documentManager->getRepository(Relation::class)->findAll();
            foreach ($relations as $relation) {
                $fields['relation-' . $relation->getId()] = [
                    'label' => 'Relation ' . $relation->getName(),
                    'matchCol' => false
                ];
            }

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

            // do some checks to generate some warnings
            if ($importDefinition->getFields()) {
                foreach ($importDefinition->getFields() as $field) {
                    if (isset($fields[$field->getMappedField()])) {
                        if ($field->getSourceField()) {
                            $column = array_search($field->getSourceField(), $data[0]);
                            if ($column === false) {
                                $this->addFlash(
                                    'warning',
                                    'Warning: field ' . $field->getSourceField(
                                    ) . ' is not available in the import any more will be ignored'
                                );
                                continue;
                            }
                            if ($column != $field->getColumn()) {
                                $this->addFlash(
                                    'warning',
                                    'Warning: column ' . $field->getSourceField() . ' is on another position now'
                                );
                            }
                        } else {
                            $column = $field->getColumn();
                        }
                        if ($fields[$field->getMappedField()]['matchCol'] == false) {
                            $fields[$field->getMappedField()]['matchCol'] = [];
                        }
                        $fields[$field->getMappedField()]['matchCol'][] = $column;
                    } else {
                        $this->addFlash(
                            'warning',
                            'Warning: mapped field is not available and will be ignored: ' . $field->getMappedField()
                        );
                    }
                }
            }

            $columnItemCount = [];
            foreach ($data[0] as $columnName) {
                $columnItemCount[$columnName] = 0;
            }

            // display at least 2 sample rows for each column and minimum 20 rows, don't display the rest
            $rowNumber = 0;
            foreach ($data as $index => $row) {
                if ($rowNumber >= 1) {
                    $showThisRow = false;
                    if ($rowNumber <= 20) {
                        $showThisRow = true;
                    }
                    foreach ($row as $column => $value) {
                        if (!isset($columnItemCount[$column])) {
                            $columnItemCount[$column] = 0;
                        }
                        if ($columnItemCount[$column] >= 2) {
                            continue;
                        }
                        if (\is_array($value)) {
                            if (\count($value) > 0) {
                                ++$columnItemCount[$column];
                                $showThisRow = true;
                            }
                        } elseif ($value != '') {
                            ++$columnItemCount[$column];
                            $showThisRow = true;
                        }
                    }
                    if (!$showThisRow) {
                        unset($data[$index]);
                    }
                }
                ++$rowNumber;
            }

            // todo: id for connector
            // todo: relations match

            // prepare data for display
            foreach ($data as $index => $row) {
                foreach ($row as $index2 => $value2) {
                    if (\is_array($value2)) {
                        $data[$index][$index2] = implode(', ', $value2);
                    }
                }
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
        ini_set('max_execution_time', 3600);
        ini_set('memory_limit', '4G');


        // close session to prevent session locking for other connections
        $session = new Session();
        $session->save();

        $start = $request->get('start', 1);

        $result = [];
        $result['done'] = true;
        $result['success'] = [];
        $result['warnings'] = [];
        $result['errors'] = [];

        if ($importDefinition->getConnectionUrl() && $importDefinition->getConnectionQuery()) {
            $data = $this->doctrine->toArray($importDefinition);
        } else {
            $data = $this->importFile->toArray($importDefinition);
        }

        $totalRowNumber = \count($data);
        $rowsPerRequest = max(20, min(500, (int)$totalRowNumber / 20));
        if ($start <= 1) {
            $start = 1;
            $rowsPerRequest = 3;
        }

        $rowNumber = -1;

        $contentType = $this->documentManager->find(
            ContentType::class,
            $importDefinition->getContentType()
        );

        $fieldMapping = [];
        foreach ($importDefinition->getFields() as $field) {
            $fieldMapping[$field->getSourceField()] = $field->getMappedField();
        }

        $newStart = $start;
        foreach ($data as $row) {
            ++$rowNumber;
            if ($rowNumber <= 0 || $rowNumber < $start) {
                // skip heading row and processed rows
                continue;
            }

            if (($newStart - $start) > $rowsPerRequest) {
                // max 20 items
                $result['start'] = $newStart;
                $result['done'] = false;
                continue;
            }

            $newStart = $newStart + 1;

            // create record
            $col = 0;

            $newData = BaseConverter::fieldMapper($fieldMapping, $row, $data, $col);

            BaseConverter::processDateFields($newData);

            if (\count($newData)) {
                $target = $contentType->create();

                if ($importDefinition->getImageBaseUrl()) {
                    $fields = [
                        'contentitem_id' => 'Item',
                        'wp:post_id' => 'wpPostId'
                    ];

                    foreach ($fields as $field => $dbField) {
                        if (isset($row[$field])) {
                            $doubleArticle = BaseConverter::checkForExistingContent(
                                $field,
                                $dbField,
                                $row[$field],
                                $importDefinition,
                                $this->documentManager
                            );
                            if ($doubleArticle) {
                                $result['warnings'][] = "{$dbField} {$row[$field]} already imported - updating";
                                $target = $doubleArticle;
                            }
                        }
                    }
                }

                $newObject = $target;

                BaseConverter::setPublicationDate($row, $newData, $newObject);

                BaseConverter::setPublished($newData, $newObject);

                foreach ($newData as $field => $value) {
                    if ($field == 'created_at' || $field == 'updated_at' || $field == 'publish_time.start_date' || $field == 'publish_time.end_date') {
                        continue;  // Skip processing for the 'created_at' field
                    }

                    $method = str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));

                    // Prefix with 'set' for setter methods, e.g., 'Title' becomes 'setTitle'
                    $setterMethod = 'set' . $method;

                    // Special handling for boolean fields
                    if (in_array($field, ['featured', 'premium'])) {
                        $setterMethod = 'is' . ucfirst($field);
                    }

                    if (method_exists($newObject, $setterMethod)) {
                        call_user_func([$newObject, $setterMethod], $value);
                    }
                }

//                try {
//                    $newObject = $serializer->deserialize(json_encode($newData), $contentType->getClass(), 'json', $context);
//                } catch (RuntimeException $e) {
//                    $result['errors'][] = 'Data error: '.$e->getMessage();
//                    continue;
//                }

                if ($importDefinition->getImageRelation()) {
                    if ($relation = $newObject->getRelation($importDefinition->getImageRelation()->getId())) {
                        $newObject->removeRelation($relation);
                    }
                    if ($relation = $newObject->getRelation('__editor_image')) {
                        $newObject->removeRelation($relation);
                    }
                }

                //Process Person Object
                if ($newObject instanceof Person) {
                    $newObject = BaseConverter::processPersonObject(
                        $newObject,
                        $row,
                        $importDefinition,
                        $this->storageManager
                    );
                }

                try {
                    // todo: move to Wordpress filter

                    foreach ($importDefinition->getChannels() as $channel) {
                        if ($newObject instanceof Person) {
                            if ($row['own_page'] != 1) {
                                continue;
                            }
                        }
                        $newObject->addChannel($channel);
                    }

                    $col = 0;
                    foreach ($row as $name => $value) {
                        if (!isset($fieldMapping[$data[0][$col]])) {
                            ++$col;
                            continue;
                        }
                        $mappedField = $fieldMapping[$data[0][$col]];

                        if (strpos($mappedField, 'author-') === 0) {
                            BaseConverter::processAuthorField($mappedField, $value, $newObject, $importDefinition, $this->documentManager);
                        }

                        if (strpos($mappedField, 'meta-') === 0) {
                            BaseConverter::processMetaField($mappedField, $value, $newObject);
                        }

                        if (strpos($mappedField, 'connector-') === 0) {
                            BaseConverter::processConnectorField($mappedField, $value, $newObject, $this->entityManager);
                        }

                        if (strpos($mappedField, 'relation-') === 0) {
                            BaseConverter::processRelationField($mappedField, $value, $newObject, $importDefinition, $this->documentManager, $this->storageManager);
                        }

                        ++$col;
                    }

                    if ($newObject instanceof Article || $newObject instanceof Person) {
                        if ($newObject instanceof Article) {
                            $content = $newObject->getContent();
                        } else {
                            $content = $newObject->getDescription();
                        }

                        $content = WP::processContent($content);

                        $newHtml = '';

                        if (true) { // todo: more to wordpress filter, only for Wordpress
                            $newHtml = WP::formatContentLines($content);
                        } else { // content as text
                            foreach (explode("\n", $content) as $line) {
                                $line = trim($line);
                                $line = '<p>' . $line . '</p>';
                                $newHtml .= $line . "\n";
                            }
                        }

                        $html = HtmlDomParser::str_get_html($newHtml);

                        if ($html === false) {
                            if ($newHtml != '') {
                                $result['warnings'][] = 'No valid HTML for ' . (string)$newObject . ', content ignored' . $newHtml;
                            }
                            $html = HtmlDomParser::str_get_html('<p></p>');
                        }

                        $html = BaseConverter::processImageElements(
                            $html,
                            $newObject,
                            $importDefinition,
                            $this->documentManager,
                            $this->storageManager
                        );

                        $html = (string)$html;

                        if ($newObject instanceof Article) {
                            $newObject->setContent($html);
                        } else {
                            $newObject->setDescription($html);
                        }
                    }

                    if (isset($row['wp:attachment_url']) && $newObject instanceof File) {
                        $result = $this->processAttachment($row, $newObject, $this->storageManager);
                        if (isset($result['error'])) {
                            $result['errors'][] = $result['error'];
                        }
                    }

                    if (isset($row['meta_thumbnail_id'])) {
                        $href = $importDefinition->getImageBaseUrl() . $row['meta_thumbnail_id'];
                        Create::createFileFromUrl($href, $newObject, $importDefinition, $this->storageManager, $this->documentManager, false,true);
                    }

                    //Process Metadata
                    $newObject = BaseConverter::processMetadata($row, $newObject, $importDefinition);

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

        $startTime = (int)$request->get('startTime', time());
        $duration = time() - $startTime;
        $remaining = ($duration / ($newStart - 1)) * ($totalRowNumber - ($newStart - 1));
        if ($remaining >= 120) {
            $remaining = round($remaining / 60) . ' minutes';
        } else {
            $remaining = round($remaining) . ' seconds';
        }

        if ($result['done']) {
            $result['percentage'] = 100;
            $result['remaining'] = '';
        } else {
            $result['startRow'] = $newStart;
            $result['percentage'] = round((($newStart - 1) / $totalRowNumber) * 100);
            $result['remaining'] = 'Estimated remaining time: ' . $remaining;
        }

        return new JsonResponse($result);
    }

    /**
     * Creates a form to edit an ImportDefinition document.
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createCreateImportDefinitionForm(ImportDefinition $importDefinition)
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
}
