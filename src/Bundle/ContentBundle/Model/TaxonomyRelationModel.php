<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Model;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Wouter Koppers <wouter@twindigital.nl>
 */
class TaxonomyRelationModel
{
    private array $mediaId = [];
    private string $categoryIdTarget = '';
    private string $categoryIdOrigin = '';

    /**
     * @param Request $request
     *                         Explanation of fields that can be send:
     *                         "media_id" => "daf99de93f2f3d5e97306bbab4ae5abb"                     REQUIRED, array with one or many
     *                         "category_id_target" => "category_2-1"                               REQUIRED, one
     *                         "category_id_origin" => "3324234"                                    REQUIRED, one
     */
    public function __construct(protected Request $request)
    {
        $this->setup($request);
    }

    private function setup(Request $request): void
    {
        // From the url (when dragging and dropping)
        $params = json_decode($request->getContent(), true);

        // From form parameters (when using Uppy for example)
        if (null === $params) {
            $params = [
                'category_id_target' => $request->get('category_id_target'),
                'media_id' => [$request->get('media_id')],
            ];
        }

        if (\array_key_exists('media_id', $params)) {
            $this->setMediaId($params['media_id']);
        }

        if ($params['category_id_target'] ?? false) {
            $this->setCategoryIdTarget($params['category_id_target']);
        }

        if (\array_key_exists('category_id_origin', $params)) {
            $this->setCategoryIdOrigin($params['category_id_origin']);
        }
    }

    private function hasTarget(): bool
    {
        if ($this->getCategoryIdTarget() === '') {
            return false;
        }

        return true;
    }

    public function isManagableRelation(): bool
    {
        return $this->hasTarget() && !$this->targetEqualsOrigin();
    }

    private function targetEqualsOrigin(): bool
    {
        return $this->getCategoryIdTarget() === $this->getCategoryIdOrigin();
    }

    public function getMediaId(): array
    {
        return $this->mediaId;
    }

    public function setMediaId(array $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getCategoryIdTarget(): string
    {
        return $this->categoryIdTarget;
    }

    public function setCategoryIdTarget(string $categoryIdTarget): void
    {
        $this->categoryIdTarget = $categoryIdTarget;
    }

    public function getCategoryIdOrigin(): string
    {
        return $this->categoryIdOrigin;
    }

    public function setCategoryIdOrigin(string $categoryIdOrigin): void
    {
        $this->categoryIdOrigin = $categoryIdOrigin;
    }
}
