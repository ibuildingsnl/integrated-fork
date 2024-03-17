<?php

namespace Integrated\Bundle\ContentBundle\Controller;

use Embed\Embed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

class OEmbedController extends AbstractController
{
    public function __construct(
        private readonly string $fbAppId,
        private readonly string $fbSecret,
        private readonly string $xAppId,
        private readonly string $xSecret,
    )
    {
    }

    public function oEmbed(Request $request): JsonResponse
    {
        parse_str($request->getQueryString(), $parsedArray);

        $decodedUrl = '';

        if (isset($parsedArray['url'])) {
            $url = $parsedArray['url'];
            $decodedUrl = urldecode($url);
        } else {
            echo "URL parameter is missing.";
        }

        $url = $decodedUrl;

        try {
            $embed = new Embed();
            $embed->setSettings(
                [
                    'facebook:token' => $this->fbAppId . '|' . $this->fbSecret, // oEmbed Read rights are needed, app needs to be verified
                    'instagram:token' => $this->fbAppId . '|' . $this->fbSecret, // oEmbed Read rights are needed, app needs to be verified
                    'twitter:token' => $this->xAppId . '|' . $this->xSecret,
                ]
            );

            $info = $embed->get($url);

            $response = [
                'title' => $info->title, //The page title
                'description' => $info->description, //The page description
                'url' => $info->url, //The canonical url
                'keywords' => $info->keywords, //The page keywords (tags)

                'image' => $info->image, //The image choosen as main image

                'code' => $info->code->html, //The code to embed the image, video, etc
                'width' => $info->code->width, //The width of the embed code
                'height' => $info->code->height, //The height of the embed code
                'ratio' => $info->code->ratio, //The aspect ratio (width/height)

                'author_name' => $info->authorName, //The resource author
                'author_url' => $info->authorUrl, //The author url

                'provider_name' => $info->providerName, //The provider name of the page (Youtube, Twitter, Instagram, etc)
                'provider_url' => $info->providerUrl, //The provider url
                'provider_icon' => $info->icon, //All provider icons found in the page
                'favicon' => $info->favicon, //The icon choosen as main icon

                'published_date' => $info->publishedTime, //The published date of the resource
                'license' => $info->license, //The license url of the resource
                'feeds' => $info->feeds, //The RSS/Atom feeds
            ];

            return new JsonResponse($response);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to retrieve embed data'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
