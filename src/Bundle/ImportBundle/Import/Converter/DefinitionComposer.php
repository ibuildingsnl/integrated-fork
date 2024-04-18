<?php

namespace Integrated\Bundle\ImportBundle\Import\Converter;

use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ImportBundle\Serializer\InitializedObjectConstructor;
use JMS\Serializer\Construction\UnserializeObjectConstructor;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;

class DefinitionComposer
{
    public static function initConfig()
    {
        ini_set('max_execution_time', 3600);
        ini_set('memory_limit', '4G');
    }

    public static function fetchData($importDefinition, $doctrine, $importFile)
    {
        if ($importDefinition->getConnectionUrl() && $importDefinition->getConnectionQuery()) {
            return $doctrine->toArray($importDefinition);
        } else {
            return $importFile->toArray($importDefinition);
        }
    }

    public static function initSerializer($contentType)
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $serializer = SerializerBuilder::create()
                                       ->addMetadataDir(
                                           realpath(__DIR__.'/../../../ContentBundle/Resources/serializer')
                                       )->setObjectConstructor(
                                           new InitializedObjectConstructor(new UnserializeObjectConstructor())
                                       )->build();
        $contentTypeFields = json_decode($serializer->serialize($contentType->create(), 'json', $context), true);

        return $contentTypeFields;
    }

    public static function generateFieldMappings($processor, $contentTypeFields, $importDefinition, $entityManager, $documentManager)
    {
        $fields = $processor->getFields();

        foreach ($contentTypeFields as $contentTypeField => $contentTypeValue) {
            $contentTypeFields = [];
            if (\is_array($contentTypeValue)) {
                foreach ($contentTypeValue as $contentTypeField2 => $contentTypeValue2) {
                    $contentTypeFields[] = $contentTypeField.'.'.$contentTypeField2;
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

                $fields['field-'.$contentTypeField] = ['label' => $contentTypeField, 'matchCol' => $matchCol];
            }
        }

        $fields['author-author'] = ['label' => 'Author', 'matchCol' => false];
        $fields['meta-meta'] = ['label' => 'Metadata', 'matchCol' => false];

        $relations = $documentManager->getRepository(Relation::class)->findAll();
        foreach ($relations as $relation) {
            $fields['relation-'.$relation->getId()] = [
                'label' => 'Relation '.$relation->getName(),
                'matchCol' => false,
            ];
        }

        return $fields;
    }

    public static function generateWarnings($importDefinition, $fields, $data)
    {
        $warnings = [];

        if ($importDefinition->getFields()) {
            foreach ($importDefinition->getFields() as $field) {
                if (isset($fields[$field->getMappedField()])) {
                    if ($field->getSourceField()) {
                        $column = array_search($field->getSourceField(), $data[0]);
                        if ($column === false) {
                            $warnings[] = 'Warning: field '.$field->getSourceField(
                            ).' is not available in the import any more and will be ignored';
                            continue;
                        }
                        if ($column != $field->getColumn()) {
                            $warnings[] = 'Warning: column '.$field->getSourceField().' is on another position now';
                        }
                    } else {
                        $column = $field->getColumn();
                    }
                    if ($fields[$field->getMappedField()]['matchCol'] == false) {
                        $fields[$field->getMappedField()]['matchCol'] = [];
                    }
                    $fields[$field->getMappedField()]['matchCol'][] = $column;
                } else {
                    $warnings[] = 'Warning: mapped field is not available and will be ignored: '.$field->getMappedField(
                    );
                }
            }
        }

        return [
            'warnings' => $warnings,
            'fields' => $fields,
        ];
    }

    public static function processColumnData(array $data): array
    {
        $columnItemCount = [];
        foreach ($data[0] as $columnName) {
            $columnItemCount[$columnName] = 0;
        }

        // Display at least 2 sample rows for each column and minimum 20 rows, don't display the rest
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

        // Prepare data for display
        foreach ($data as $index => $row) {
            foreach ($row as $index2 => $value2) {
                if (\is_array($value2)) {
                    $data[$index][$index2] = implode(', ', $value2);
                }
            }
        }

        return $data;
    }
}
