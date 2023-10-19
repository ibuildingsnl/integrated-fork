<?php

namespace Integrated\Bundle\ImportBundle\Import\Converter;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage\Metadata as StorageMetadata;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ImportBundle\Import\Create\Create;
use Integrated\Bundle\StorageBundle\Storage\Reader\MemoryReader;

class WP
{
    public static function processContent($content, $importType, $importDefinition)
    {
        $imgIds = [];

        $content = str_ireplace('alt=" width', 'alt="" width', $content);

        // TODO: Add support for gallery.
        $content = preg_replace_callback(
            '/\[gallery ids\="(.+?)".*?\]/',
            function ($matches) use (&$imgIds) {
                $imgIds = array_merge($imgIds, explode(',', $matches[1]));

                return '';
            },
            $content
        );

        $youtubeRexEg = '/(?:https?:\/\/)?(?:www\.)?youtu\.?be(?:\.com)?\/?.*(?:watch|embed)?(?:.*v=|v\/|\/)([\w\-_]+)/';
        $content = preg_replace_callback($youtubeRexEg, function ($matches) {
            if (\strlen(trim($matches[1])) == 11) {
                return '[object type="youtube" id="'.trim($matches[1]).'"]';
            }

            return $matches[0];
        }, $content);
        //TODO: Fix support for caption with anchor that has space in it.
        $content = preg_replace_callback(
            '/\[caption.*?\](?:<a href="[^"]+"[^>]*>)?(<img[^>]+>)(?:<\/a>)?\s*(.*?)\[\/caption\]/',
            function ($matches) {
                $imageContent = $matches[1];  // Extracts the img tag
                $captionText = trim($matches[2]);  // Extracts the caption text and trims any whitespace

                // Convert the caption text into a format suitable for an HTML attribute
                $captionAttribute = htmlspecialchars($captionText, ENT_QUOTES);

                // Add or replace the description attribute in the img tag with the new caption text
                if (strpos($imageContent, 'caption=') !== false) {
                    // If description attribute already exists, replace its value
                    $imageContent = preg_replace('/caption="[^"]*"/', 'caption="' . $captionAttribute . '"', $imageContent);
                } else {
                    // If no description attribute exists, add one
                    $imageContent = preg_replace('/<img/', '<img caption="' . $captionAttribute . '"', $imageContent);
                }

                // Remove the size attribute from the src URL of the img tag
                $imageContent = preg_replace(
                    '/(<img.*?src=")([^"]+)-\d+x\d+(\.[a-zA-Z]+)(".*?>)/',
                    '$1$2$3$4',
                    $imageContent
                );

                return $imageContent;  // return the updated image content with the updated alt attribute
            },
            $content
        );

        $content = preg_replace_callback(
            '/<a href="([^"]+)"><img(.*?)src="([^"]+)-\d+x\d+\.([a-zA-Z]+)"(.*?)\/><\/a>/',
            function ($matches) {
                $imgAttributes = $matches[2] . ' src="' . $matches[3] . '.' . $matches[4] . '"' . $matches[5];
                return '<img' . $imgAttributes . '>';
            },
            $content
        );

        if ($importDefinition->getRemoveFirstImage()) {
            $content = preg_replace('/<img[^>]+>/', '', $content, 1);
        }

        $content = preg_replace('/<a[^>]*>\s*<\/a>/', '', $content);
        $content = preg_replace('/\[caption.*?\]/', '', $content);
        $content = str_ireplace('[/caption]', '', $content);
        $content = str_ireplace(' ', ' ', $content);
        $content = str_ireplace('<h4>Wil je meer te weten komen over woningaanpassingen? <a href="https://supportmagazine.nl/abonneren/" target="_blank" rel="noopener">Neem dan nu extra voordelig een abonnement op Support Magazine!</a></h4>', '', $content);
        $content = str_ireplace('IK WORD ABONNEE[/su_button]', '[/su_button]', $content);
        $content = preg_replace('/\[(\/)?su_.*?\]/', '', $content); // Strip shortcodes
        $content = str_ireplace('<p>&nbsp;</p>', '', $content);
        $content = str_ireplace('<p> </p>', '', $content);

        $content = str_ireplace('<div class="well">', '<div class="frame-general">', $content);

        $newHtml = '';

        if ($importType === 'WordPress') {
            $newHtml = self::formatContentLines($content);
        } else { // content as text
            foreach (explode("\n", $content) as $line) {
                $line = trim($line);
                $line = '<p>'.$line.'</p>';
                $newHtml .= $line."\n";
            }
        }

        return $newHtml;
    }

    public static function formatContentLines(string $content): string
    {
        $contentLines = [];
        $prevLine = '';
        $newHtml = '';

        foreach (explode("\n", $content) as $contentLine) {
            if (str_replace('-', '', $contentLine) == '') {
                $contentLine = '';
            }
            if (trim($contentLine) != '') {
                $contentLines[] = $contentLine;
            }
        }

        foreach ($contentLines as $lineKey => $line) {
            $line = trim($line);

            $nextCouldBeLi = false;
            if (isset($contentLines[$lineKey + 1])) {
                $nextLine = $contentLines[$lineKey + 1];
                $nextLine = trim($nextLine);
                if (substr($nextLine, -1, 1) != '.'
                    && substr($nextLine, -1, 1) != '?'
                    && trim(str_ireplace('&nbsp;', '', $nextLine)) != ''
                    && (substr($nextLine, -1, 1) != '>' || substr($nextLine, -3, 3) == '/a>')) {
                    $nextCouldBeLi = true;
                }
            }

            if (trim(strip_tags(str_replace('&nbsp;', '', $line))) != '' || $line == '<ul>' || $line == '</ul>') {
                if (substr($line, -3, 3) == 'h1>'
                    || substr($line, -3, 3) == 'h2>'
                    || substr($line, -3, 3) == 'h3>'
                    || substr($line, -3, 3) == 'li>'
                    || substr($line, -3, 3) == 'ul>'
                    || substr($line, 0, 3) == '<li'
                    || substr($line, 0, 3) == '<ul'
                    || (substr($line, 0, 1) == '[' && substr($line, -1, 1) == ']')
                ) {
                    // niks mee doen
                    if ($prevLine == 'li') {
                        $newHtml .= '</ul>';
                    }
                    $prevLine = '';
                } elseif ((\strlen(strip_tags($line)) < 90 || $prevLine == 'li')
                          && substr($line, -1, 1) != '.'
                          && substr($line, -1, 1) != '?'
                          && (substr($line, -1, 1) != '>' || substr($line, -3, 3) == '/a>')
                          && ($nextCouldBeLi || $prevLine == 'li')
                ) {
                    if ($prevLine != 'li') {
                        $newHtml .= '<ul>';
                    }
                    if (strpos($line, '- ') === 0) {
                        $line = substr($line, 2);
                    }
                    $line = '<li>'.$line.'</li>';
                    $prevLine = 'li';
                } else {
                    if ($prevLine == 'li') {
                        $newHtml .= '</ul>';
                    }
                    $line = '<p>'.$line.'</p>';
                    $prevLine = 'p';
                }
            } else {
                if ($prevLine == 'li') {
                    $newHtml .= '</ul>';
                }
            }
            $newHtml .= $line."\n";
        }

        if ($prevLine == 'li') {
            $newHtml .= '</ul>';
        }

        return $newHtml;
    }

    public static function processWpMetadata(
        $row,
        $newData,
        $newObject,
        $importDefinition,
        $documentManager,
        $storageManager
    ) {
        $result = ExecuteImporter::initializeResult();

        if (isset($row['wp:post_id'])) {
            $newObject->getMetadata()->set('wpPostId', $row['wp:post_id']);
            $newObject->getMetadata()->set(
                'wpUrl',
                (isset($row['wp:attachment_url'])) ? $row['wp:attachment_url'] : $row['link']
            );
            $newObject->getMetadata()->set('importDate', date('Ymd'));
            $newObject->getMetadata()->set('importWebsiteBaseUrl', $importDefinition->getWebsiteBaseUrl());
        }

        if (isset($row['id'])) {
            $newObject->getMetadata()->set('PostId', $row['id']);
            $newObject->getMetadata()->set('wpUrl', (isset($row['Permalink'])) ? $row['Permalink'] : '');
            $newObject->getMetadata()->set('importDate', date('Ymd'));
            $newObject->getMetadata()->set('importWebsiteBaseUrl', $importDefinition->getWebsiteBaseUrl());
        }

        if (isset($row['meta_yoast_wpseo_canonical']) && $newObject instanceof Article) {
            $newObject->setSourceUrl($row['meta_yoast_wpseo_canonical']);
        }

        if (isset($row['wp:attachment_url']) && $newObject instanceof File) {
            $result = self::processAttachment($row, $newObject, $storageManager);
            if (isset($result['message'])) {
                $result['messages'][] = $result['message'];
            }
        }

        if (isset($row['wp:comments'])) {
            //TODO: Warning is not given.
            $result['messages'][] = '[WARNING] There are comments that are not processed.';
        }

        if (!\array_key_exists('featured_image', $newData)) {
            if (isset($row['meta_thumbnail_id'])) {
                $href = $importDefinition->getWebsiteBaseUrl().'?attachment_id='.$row['meta_thumbnail_id'];
                $checkResult = Create::createFileFromUrl(
                    $href,
                    $newObject,
                    $newData,
                    $importDefinition,
                    $storageManager,
                    $documentManager,
                    false,
                    true
                );

                $result['messages'] = array_merge($result['messages'], $checkResult['result']['messages']);
            }
        }

        return [
            'result' => $result,
            'newObject' => $newObject,
        ];
    }

    // TODO: Make use of file creation by URL
    public static function processAttachment($row, $newObject, $storageManager)
    {
        if (isset($row['wp:attachment_url']) && $newObject instanceof File) {
            $tmpBaseFile = tempnam('/tmp/', 'img');
            $tmpfile = $tmpBaseFile.'.'.pathinfo($row['wp:attachment_url'], \PATHINFO_EXTENSION);
            rename($tmpBaseFile, $tmpfile);
            file_put_contents($tmpfile, @file_get_contents($row['wp:attachment_url']));
            if (filesize($tmpfile) == 0) {
                $errorMessage = '[ERROR] Attachment '.$row['wp:post_id'].' has 0 bytes';
                unlink($tmpfile);

                return ['message' => $errorMessage];
            }

            $storage = $storageManager->write(
                new MemoryReader(
                    file_get_contents($tmpfile),
                    new StorageMetadata(
                        pathinfo($row['wp:attachment_url'], \PATHINFO_EXTENSION),
                        mime_content_type($tmpfile),
                        new ArrayCollection(),
                        new ArrayCollection()
                    )
                )
            );

            $newObject->setFile($storage);
            unlink($tmpfile);

            return ['success' => true];
        }

        return ['message' => 'Invalid attachment or object type.'];
    }
}
