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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\Video;
use Symfony\Component\HttpFoundation\Request;
use Integrated\Common\Storage\ManagerInterface;
use Integrated\Bundle\StorageBundle\Storage\Reader\MemoryReader;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage\Metadata;

/**
 * Class MediaGalleryUploadFile.
 *
 * @author Wouter Koppers <wouter@twindigital.com>
 */
class MediaGalleryUploadFile
{
    /**
     * SearchContentReferenced constructor.
     *
     * @param DocumentManager $documentManager
     */
    public function __construct(
        private DocumentManager $documentManager,
        private ManagerInterface $manager,
    ) {
    }

    public function handleUpload(Request $request)
    {
        // check filetype
        $uploadedFileExtension = strtolower($request->files->get('file')->getClientOriginalExtension());
        // QUESTION: What do you guys think about using this as whitelist:
        // https://gist.github.com/tylerlee/53609bff1346cebf8f0a85b6be29a88e
        $uploadedFileMimetype = $request->files->get('file')->getMimeType();
        // TODO: perfect these filetypes, maybe put these in a config file?:
        $image_filetypes = ['jpg', 'jpeg', 'png', 'tif', 'webp'];
        $video_filetypes = ['mp4', 'mov', 'avi', 'flv', 'mkv', 'wmv'];
        $file_filetypes = ['doc', 'docx', 'pdf', 'xls'];

        // Find a matching class with the extension
        if (\in_array($uploadedFileExtension, $image_filetypes)) {
            $file = new Image();
            $file->setContentType('image');
        } elseif (\in_array($uploadedFileExtension, $video_filetypes)) {
            $file = new Video();
            $file->setContentType('video');
        } elseif (\in_array($uploadedFileExtension, $file_filetypes)) {
            $file = new File();
            $file->setContentType('file');
        } else {
            return new JsonResponse(['message' => 'This filetype is not allowed.']);
        }

        // Get file title
        $uploadedFile = $request->files->get('file');
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), \PATHINFO_FILENAME);
        $file->setTitle($originalFilename);

        $storage = $this->manager->write(
            new MemoryReader(
                file_get_contents($uploadedFile),
                new Metadata(
                    $uploadedFileExtension,
                    $uploadedFileMimetype,
                    new ArrayCollection(),
                    new ArrayCollection()
                )
            )
        );

        $file->setFile($storage);

        $this->documentManager->persist($file);
        $this->documentManager->flush();

        return $file;
    }
}
