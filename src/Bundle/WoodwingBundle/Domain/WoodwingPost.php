<?php

namespace Integrated\Bundle\WoodwingBundle\Domain;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
//use TD\Bundle\PublishingBundle\Domain\Publishable;
use Integrated\Common\Form\Mapping\Attributes as Type;

#[Type\Document('Woodwing Post')]
class WoodwingPost extends Content
{
    public string $article_id = '';
    public Taxonomy $edition;
    public Taxonomy $layout;
    public ?\DateTime $pickedUpAt = null;

    public string $title = '';
    public string $subtitle = '';
    public string $intro = '';
    public string $originalBody = '';
    public string $body = '';
    public array $kaders = [];
    public array $author = [];
    public array $featuredImage = [];
    public array $images = [];
    public array $quotes = [];

    public function populate(): void
    {
        $original = $this->getOriginal();
        $this->article_id = $original->getId();
        $this->originalBody = $original->getContent() ?: '';
        $this->title = $original->getTitle() ?: '';
        $this->subtitle = $original->getSubtitle() ?: '';
        $this->intro = $this->getIntroFromArticle();
        $this->kaders = $this->getKadersFromArticle();
        $this->featuredImage = $this->getFeaturedImageFromArticle();
        $this->images = $this->getImagesFromArticle();
        if (count($this->featuredImage) > 0) {
            array_unshift($this->images, $this->featuredImage);
        }
        $this->quotes = $this->getQuotesFromArticle();
        $this->body = $this->cleanBody();
        $this->setAuthor();
    }

    public function getTitle(): string {
        return $this->title;
    }

    private function getDomain(): string {
        return $this->getOriginal()->getChannels()[0]->getPrimaryDomain();
    }

    private function getImagesFromArticle(): array
    {
        $domain = $this->getDomain();
        $relationImages = $this->getOriginal()->getReferencesByRelationType('embedded');
        $fileToCopyrightMapping = [];
        foreach ($relationImages as $relationImage) {
            $fileToCopyrightMapping[$relationImage->getFile() . ""] = $relationImage->getCopyrightRestrictions();
        }

        $images = [];
        /* [0] = full selection
         * [1] = class
         * [2] = title
         * [3] = src
         * [4] = alt
         * [5] = data-integrated-id
         */
        $pattern = '/<img\s*(?:class\s*\=\s*[\'\"](.*?)[\'\"].*?\s*|title\s*\=\s*[\'\"](.*?)[\'\"].*?\s*|src\s*\=\s*[\'\"](.*?)[\'\"].*?\s*|alt\s*\=\s*[\'\"](.*?)[\'\"].*?\s*|data-integrated-id\s*\=\s*[\'\"](.*?)[\'\"].*?\s*\s*)+.*?>/si';
        preg_match_all($pattern, $this->getOriginal()->getContent(), $matches);

        foreach ($matches[5] as $key => $value) {
            $images[] = [
                "url" => 'https://' . $domain . $matches[3][$key],
                "copyright" => $fileToCopyrightMapping[$matches[3][$key]] ?? '',
                "description" => $matches[4][$key],
                "unique_identifier" => $matches[3][$key],
            ];
        }

        return $images;
    }

    private function getFeaturedImageFromArticle(): array {
        $featuredImage = $this->getOriginal()->getFeaturedImage();
        if ($featuredImage === null) {
            return [];
        }

        $domain = $this->getDomain();
        return [
            "url" => 'https://' . $domain . $featuredImage->getFile(),
            "copyright" => $featuredImage->getCopyrightRestrictions(),
            "description" => $featuredImage->getTitle(),
            "unique_identifier" => $domain . $featuredImage->getFile(),
        ];
    }

    private function getQuotesFromArticle(): array
    {
        $pattern = '/<blockquote class="quote">\s*([^*]+?)\s*<\/blockquote>/si'; //excluding
        preg_match_all($pattern, $this->getOriginal()->getContent(), $matches);

        $quotes = [];
        foreach ($matches[0] as $key => $value) {
            preg_match('/<cite class="quote">([^<]+)<\/cite>/', $value, $nameMatch);
            $name = $nameMatch[1] ?? "Unknown";

            $quote = $matches[1][$key];
            $quote = preg_replace('/<cite[^>]*>(.*?)<\/cite>/', '', $quote); // Remove <cite> element
            $quote = str_replace('<br>', '', $quote); // Remove <br> tag
            $quote = str_replace('</br>', '', $quote); // Remove <br> tag

            $quotes[] = [
                "quote" => $quote,
                "name" => $name,
                "layout_type" => "inline"
            ];
        }

        return $quotes;
    }

    public function getKadersFromArticle(): array
    {
        $content = $this->getOriginal()->getContent();

        $allowed_tags = "<p><a><h1><h2><h3><h4><h5><h6><em><strong><i><li><b><br><br/><br /><sub><sup>";

        $content = str_replace('frame-general', 'kader', $content);

        $pattern = '/<div class="kader">\s*([^*]+?)\s*<\/div>/si'; //excluding
        preg_match_all($pattern, $content, $matches);

        $kaders = [];
        foreach ($matches[0] as $key => $value) {
            $kaders[] = strip_tags($matches[1][$key], $allowed_tags);
        }

        return $kaders;
    }

    private function cleanBody(): string
    {
        $content = $this->getOriginal()->getContent();
        //links:
        $content =  preg_replace("/<a[^>]+\>/i", "", $content);
        $content =  str_replace("</a>", "", $content);
        //images:
        $content =  preg_replace("/<img[^>]+\>/i", "", $content);
        //quotes:
        $content =  preg_replace("/<blockquote class=\"quote\">\s*[^*]+?\s*<\/blockquote>/si", "", $content);
        //intro:
        $content =  preg_replace("/<div class=\"(?:frame-intro|intro)\">\s*([^*]+?)\s*<\/div>/si", "", $content);
        //kaders:
        $content =  preg_replace("/<div class=\"frame-general\">\s*([^*]+?)\s*<\/div>/si", "", $content);
        $content =  preg_replace("/<div class=\"kader\">\s*[^*]+?\s*<\/div>/i", "", $content);
        //empty paragraphs?
        $content = str_replace("<p></p>", "", $content);
        $content = str_replace("<p>&nbsp;</p>", "", $content);
        //double enters?
        $content = str_replace("\r\n\r\n", "", $content);

        $content = $this->removeTables($content);

        return $content;
    }

    private function getPositions($content, $needle): array {
        $lastPos = 0;
        $startPositions = [];
        while (($lastPos = strpos($content, $needle, $lastPos)) !== false) {
            $startPositions[] = $lastPos;
            $lastPos = $lastPos + strlen($needle);
        }

        return $startPositions;
    }

    private function containsOtherTable($content, $startPosition, $endPosition): bool {
        $length = $endPosition - $startPosition;
        $subContent = substr($content, $startPosition + 8, $length);
        return str_contains($subContent, "<table ");
    }

    private function removeTableFromContent($content, $startPosition, $endPosition): string {
        return substr($content, 0, $startPosition) . substr($content, $endPosition + 8);
    }

    private function removeTables($content) {
        $startPositions = $this->getPositions($content, "<table ");
        $endPositions = $this->getPositions($content, "</table>");

        //We are not going to parse invalid html
        if (count($startPositions) !== count($endPositions)) {
            return $content;
        }

        $loopingMax = 50; //Failsafe for invalid HTML
        $counter = 0;
        $newContent = $content;

        //Loop until no more tables, or content isnt changed
        //The counter is a failsafe
        while ($counter < $loopingMax) {
            $startPositions = $this->getPositions($newContent, "<table ");

            //If there are no more tables we are done:
            if (count($startPositions) === 0) {
                break;
            }

            foreach ($startPositions as $startPosition) {
                $endPosition = strpos($content, "</table>", $startPosition);
                if (!$this->containsOtherTable($content, $startPosition, $endPosition)) {
                    $newContent = $this->removeTableFromContent($content, $startPosition, $endPosition);
                    break; //We remove max 1 instance, because after removing, the positions change.
                }
            }

            //If we have the same content, we are done:
            if ($content === $newContent) {
                break;
            }

            $content = $newContent;
            $counter++;
        }

        return $content;
    }

    private function getIntroFromArticle(): string
    {
        //First we try to get the intro from the content:
        $pattern = '/<div class="(?:frame-intro|intro)">\s*([^*]+?)\s*<\/div>/si'; //excluding
        preg_match($pattern, $this->getOriginal()->getContent(), $matches);

        if (array_key_exists(1, $matches)) {
            return '<p>' . $matches[1] . '</p>';
        }

        return '<p>' . $this->getOriginal()->getIntro() . '</p>' ?: '';
    }

    private function setAuthor(): WoodwingPost
    {
        if (!$this->getOriginal()) {
            return $this;
        }

        $authors = [];
        foreach($this->getOriginal()->getAuthors() as $author) {
            $authors[] = $author->getPerson()->__toString();
        }

        $this->author = $authors;
        return $this;
    }

    /** @return Article */
    public function getOriginal(): Content
    {
        $original = parent::getOriginal();
        assert($original instanceof Article); // @todo check if valid assumption
        return $original;
    }

    public function show(): array
    {
        return [
            'article_identifier' => $this->getId(),
            'edition' => $this->edition->getTitle(),
            'layout' => $this->layout->getTitle(),
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'intro' => $this->intro,
            'body' => $this->body,
            'kaders' => $this->kaders,
            'author' => $this->author,
            'images' => $this->images,
            'quotes' => $this->quotes,
        ];
    }
}
