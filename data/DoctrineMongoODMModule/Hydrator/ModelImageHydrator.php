<?php

namespace DoctrineMongoODMModule\Hydrator;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Hydrator\HydratorInterface;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ODM. DO NOT EDIT THIS FILE.
 */
class ModelImageHydrator implements HydratorInterface
{
    private $dm;
    private $unitOfWork;
    private $class;

    public function __construct(DocumentManager $dm, UnitOfWork $uow, ClassMetadata $class)
    {
        $this->dm = $dm;
        $this->unitOfWork = $uow;
        $this->class = $class;
    }

    public function hydrate($document, $data, array $hints = array())
    {
        $hydratedData = array();

        /** @Field(type="id") */
        if (isset($data['_id'])) {
            $value = $data['_id'];
            $return = $value instanceof \MongoId ? (string) $value : $value;
            $this->class->reflFields['id']->setValue($document, $return);
            $hydratedData['id'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['distance'])) {
            $value = $data['distance'];
            $return = (string) $value;
            $this->class->reflFields['distance']->setValue($document, $return);
            $hydratedData['distance'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['name'])) {
            $value = $data['name'];
            $return = (string) $value;
            $this->class->reflFields['name']->setValue($document, $return);
            $hydratedData['name'] = $return;
        }

        /** @Field(type="file") */
        if (isset($data['file'])) {
            $value = $data['file'];
            $return = $value;
            $this->class->reflFields['file']->setValue($document, $return);
            $hydratedData['file'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['uploadDate'])) {
            $value = $data['uploadDate'];
            $return = (string) $value;
            $this->class->reflFields['uploadDate']->setValue($document, $return);
            $hydratedData['uploadDate'] = $return;
        }

        /** @EmbedOne */
        if (isset($data['attributes'])) {
            $embeddedDocument = $data['attributes'];
            $className = $this->unitOfWork->getClassNameForAssociation($this->class->fieldMappings['attributes'], $embeddedDocument);
            $embeddedMetadata = $this->dm->getClassMetadata($className);
            $return = $embeddedMetadata->newInstance();

            $embeddedData = $this->dm->getHydratorFactory()->hydrate($return, $embeddedDocument, $hints);
            $embeddedId = $embeddedMetadata->identifier && isset($embeddedData[$embeddedMetadata->identifier]) ? $embeddedData[$embeddedMetadata->identifier] : null;

            $this->unitOfWork->registerManaged($return, $embeddedId, $embeddedData);
            $this->unitOfWork->setParentAssociation($return, $this->class->fieldMappings['attributes'], $document, 'attributes');

            $this->class->reflFields['attributes']->setValue($document, $return);
            $hydratedData['attributes'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['length'])) {
            $value = $data['length'];
            $return = (string) $value;
            $this->class->reflFields['length']->setValue($document, $return);
            $hydratedData['length'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['chunkSize'])) {
            $value = $data['chunkSize'];
            $return = (string) $value;
            $this->class->reflFields['chunkSize']->setValue($document, $return);
            $hydratedData['chunkSize'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['md5'])) {
            $value = $data['md5'];
            $return = (string) $value;
            $this->class->reflFields['md5']->setValue($document, $return);
            $hydratedData['md5'] = $return;
        }

        /** @ReferenceOne */
        if (isset($data['owner'])) {
            $reference = $data['owner'];
            if (isset($this->class->fieldMappings['owner']['simple']) && $this->class->fieldMappings['owner']['simple']) {
                $className = $this->class->fieldMappings['owner']['targetDocument'];
                $mongoId = $reference;
            } else {
                $className = $this->unitOfWork->getClassNameForAssociation($this->class->fieldMappings['owner'], $reference);
                $mongoId = $reference['$id'];
            }
            $targetMetadata = $this->dm->getClassMetadata($className);
            $id = $targetMetadata->getPHPIdentifierValue($mongoId);
            $return = $this->dm->getReference($className, $id);
            $this->class->reflFields['owner']->setValue($document, $return);
            $hydratedData['owner'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['folder'])) {
            $value = $data['folder'];
            $return = (string) $value;
            $this->class->reflFields['folder']->setValue($document, $return);
            $hydratedData['folder'] = $return;
        }

        /** @Field(type="int") */
        if (isset($data['isbackground'])) {
            $value = $data['isbackground'];
            $return = (int) $value;
            $this->class->reflFields['isbackground']->setValue($document, $return);
            $hydratedData['isbackground'] = $return;
        }

        /** @Field(type="collection") */
        if (isset($data['randompoint'])) {
            $value = $data['randompoint'];
            $return = $value;
            $this->class->reflFields['randompoint']->setValue($document, $return);
            $hydratedData['randompoint'] = $return;
        }

        /** @EmbedOne */
        if (isset($data['coordinates'])) {
            $embeddedDocument = $data['coordinates'];
            $className = $this->unitOfWork->getClassNameForAssociation($this->class->fieldMappings['coordinates'], $embeddedDocument);
            $embeddedMetadata = $this->dm->getClassMetadata($className);
            $return = $embeddedMetadata->newInstance();

            $embeddedData = $this->dm->getHydratorFactory()->hydrate($return, $embeddedDocument, $hints);
            $embeddedId = $embeddedMetadata->identifier && isset($embeddedData[$embeddedMetadata->identifier]) ? $embeddedData[$embeddedMetadata->identifier] : null;

            $this->unitOfWork->registerManaged($return, $embeddedId, $embeddedData);
            $this->unitOfWork->setParentAssociation($return, $this->class->fieldMappings['coordinates'], $document, 'coordinates');

            $this->class->reflFields['coordinates']->setValue($document, $return);
            $hydratedData['coordinates'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['token'])) {
            $value = $data['token'];
            $return = (string) $value;
            $this->class->reflFields['token']->setValue($document, $return);
            $hydratedData['token'] = $return;
        }
        return $hydratedData;
    }
}