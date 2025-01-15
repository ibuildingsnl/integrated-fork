<?php

namespace Integrated\Bundle\ContentBundle\Controller;

use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class YoastSeoLanguageProvider extends AbstractController
{
    /**
     * @var array<string, string>
     */
    public const LANGUAGE_LOCALE_MAPPING = [
        'nl' => 'nl_NL',
    ];

    public function __construct(
        private readonly FileLocator $locator,
        private readonly string $location,
    ) {
    }

    /**
     * Returns json data containing the Yoast SEO translations for the current users backend language.
     *
     * @throws InvalidArgumentException
     */
    public function fetchTranslations(Request $request): JsonResponse
    {
        $interfaceLanguage = $request->getLocale();

        $locale = $this->getValidLocale($interfaceLanguage);

        $path = $this->locator->locate(\sprintf('%s/%s.json', $this->location, $locale));

        if (file_exists($path)) {
            return new JsonResponse(file_get_contents($path), Response::HTTP_OK, [], true);
        } else {
            return new JsonResponse(
                ['error' => \sprintf('No translation available for language %s', $interfaceLanguage)]
            );
        }
    }

    /**
     * Returns a locale based on the given interface language.
     */
    protected function getValidLocale(string $interfaceLanguage): string
    {
        if (\array_key_exists($interfaceLanguage, $this::LANGUAGE_LOCALE_MAPPING)) {
            return $this::LANGUAGE_LOCALE_MAPPING[$interfaceLanguage];
        }

        return $interfaceLanguage;
    }
}
