<?php

namespace Integrated\Bundle\ContentBundle\Controller;

use Embed\Embed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

class OEmbedController extends AbstractController
{
    public function oEmbed(Request $request): JsonResponse
    {
        $validator = Validation::createValidator();

        parse_str($request->getQueryString(), $parsedArray);

        $decodedUrl = '';

        if (isset($parsedArray['url'])) {
            $url = $parsedArray['url'];
            // If you need to decode the URL
            $decodedUrl = urldecode($url);
        } else {
            echo "URL parameter is missing.";
        }

        $url = $decodedUrl;

        try {
            // Creating an Embed instance and extracting the info
            $embed = new \Embed\Embed();
            $info = $embed->get($url);

            // Constructing the response based on available data
            $response = [
                'title' => $info->title, //The page title
                'description' => $info->description, //The page description
                'url' => $info->url, //The canonical url
                'type' => $info->type, //The page type (link, video, image, rich)
                'tags' => $info->tags, //The page keywords (tags)

                'images' => $info->images, //List of all images found in the page
                'image' => $info->image, //The image choosen as main image
                'image_width' => $info->imageWidth, //The width of the main image
                'image_height' => $info->imageHeight, //The height of the main image

                'code' => $info->code, //The code to embed the image, video, etc
                'width' => $info->width, //The width of the embed code
                'height' => $info->height, //The height of the embed code
                'aspect_ratio' => $info->aspectRatio, //The aspect ratio (width/height)

                'author_name' => $info->authorName, //The resource author
                'author_url' => $info->authorUrl, //The author url

                'provider_name' => $info->providerName, //The provider name of the page (Youtube, Twitter, Instagram, etc)
                'provider_url' => $info->providerUrl, //The provider url
                'provider_icons' => $info->providerIcons, //All provider icons found in the page
                'provider_icon' => $info->providerIcon, //The icon choosen as main icon

                'published_date' => $info->publishedDate, //The published date of the resource
                'license' => $info->license, //The license url of the resource
                'linked_data' => $info->linkedData, //The linked-data info (http://json-ld.org/)
                'feeds' => $info->feeds, //The RSS/Atom feeds
            ];

            dd($response);

            return $this->json($response);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to retrieve embed data'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
