<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\ContentRepository;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;

/**
 * Class MediaGalleryEditFile.
 *
 * @author Wouter Koppers <wouter@twindigital.com>
 */
class MediaGalleryEditFile
{
    /**
     * SearchContentReferenced constructor.
     */
    public function __construct(
        private DocumentManager $documentManager,
        private MediaGalleryUploadFile $mediaGalleryUploadFile,
        private ContentRepository $contentRepository,
    ) {
    }

    /* This function replaces the link in the database to the file on the storage
     * We dont touch the actual files on the storage.
     */
    public function replaceImage($request, $image): File
    {
        $oldImageFile = $image->getFile();
        $newFileStorage = $this->mediaGalleryUploadFile->getContentFromUploadedFile($request);
        $this->changeImageLinksInContent($oldImageFile, $image, $newFileStorage);
        $image->setFile($newFileStorage);
        $this->documentManager->persist($image);

        return $image;
    }

    public function createCopy($request): Image
    {
        $original = $this->documentManager->getRepository(File::class)->find($request->get('id'));
        $file = $this->copyImage($original);
        $storage = $this->mediaGalleryUploadFile->getContentFromUploadedFile($request);
        $file->setFile($storage);
        $this->documentManager->persist($file);

        return $file;
    }

    private function copyImage($originalImage): Image
    {
        $class = new \ReflectionClass($originalImage);
        $copy = new Image();
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        $excludedSetters = ['setUpdatedAt', 'setCreatedAt', 'setId', 'setFile'];

        foreach ($methods as $method) {
            $methodName = $method->name;

            // Check if it's a setter and not excluded
            if (strncasecmp($methodName, 'set', 3) === 0 && !\in_array($methodName, $excludedSetters, true)) {
                $getterName = 'get'.substr($methodName, 3);

                if ($class->hasMethod($getterName)) {
                    $copy->{$methodName}($originalImage->{$getterName}());
                }
            }
        }

        return $copy;
    }

    /* Loop over usedBy results.
     * If we find an embedded image, we replace the old pathname with the new pathname.
     */
    public function changeImageLinksInContent($oldImageFile, $image, $newFileStorage): void
    {
        $linkedItemsQuery = $this->contentRepository->getUsedBy(new ArrayCollection([$image]), null, null, false);
        foreach ($linkedItemsQuery->getQuery()->execute() as $item) {
            $relatedArticleContent = $item->getContent();
            foreach ($item->getReferencesByRelationType('embedded') as $embeddedImage) {
                if ($embeddedImage instanceof Image) {
                    $relatedArticleContent = str_replace($oldImageFile->getPathname(), $newFileStorage->getPathname(), $relatedArticleContent);
                }
            }
            $item->setContent($relatedArticleContent);
        }
    }
}
