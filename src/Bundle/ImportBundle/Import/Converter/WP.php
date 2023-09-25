<?php

namespace Integrated\Bundle\ImportBundle\Import\Converter;

class WP
{
    public static function processContent($content, $wordpress = false) {

        $newHtml = '';
        $prevLine = '';
        $imgIds = [];

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

        $content = preg_replace_callback(
            '/\[caption.*?\].*?<\/a>\s*(.*?)\[\/caption\]/',
            function ($matches) {
                $captionText = $matches[1];  // extract caption text
                // Now find the img tag and update its alt attribute
                $imgTagPattern = '/(<img.*?alt=")(.*?)(".*?>)/';
                $updatedImgTag = preg_replace($imgTagPattern, "$1$captionText$3", $matches[0]);  // update the entire caption content

                // Remove the original caption text from the updated caption content
                $updatedContent = str_replace($captionText, '', $updatedImgTag);
                return $updatedContent;  // return the updated content without the original caption text
            },
            $content
        );

        $content = preg_replace('/\[caption.*?\]/', '', $content);
        $content = str_ireplace('[/caption]', '', $content);
        $content = preg_replace('/\[(\/)?su_.*?\]/', '', $content); //Strip shortcodes

        $content = str_ireplace('<div class="well">', '<div class="frame-general">', $content);

        $newHtml = '';

        if ($wordpress) { // todo: more to wordpress filter, only for Wordpress
            $newHtml = self::formatContentLines($content);
        } else { // content as text
            foreach (explode("\n", $content) as $line) {
                $line = trim($line);
                $line = '<p>' . $line . '</p>';
                $newHtml .= $line . "\n";
            }
        }

        return $newHtml;
    }

    public static function formatContentLines(string $content): string {

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


    //TODO: Make use of file creation by URL
    public static function processAttachment($row, $newObject, $storageManager)
    {
        if (isset($row['wp:attachment_url']) && $newObject instanceof File) {
            $tmpBaseFile = tempnam('/tmp/', 'img');
            $tmpfile = $tmpBaseFile . '.' . pathinfo($row['wp:attachment_url'], \PATHINFO_EXTENSION);
            rename($tmpBaseFile, $tmpfile);
            file_put_contents($tmpfile, @file_get_contents($row['wp:attachment_url']));
            if (filesize($tmpfile) == 0) {
                $errorMessage = 'Attachment ' . $row['wp:post_id'] . ' has 0 bytes';
                unlink($tmpfile);
                return ['error' => $errorMessage];
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
        return ['error' => 'Invalid attachment or object type.'];
    }
}
