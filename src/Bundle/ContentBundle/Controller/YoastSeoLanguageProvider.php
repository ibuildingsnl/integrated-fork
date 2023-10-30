<?php

namespace Integrated\Bundle\ContentBundle\Controller;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Config\FileLocator;

class YoastSeoLanguageProvider
{
    public function __construct()
    {
    }

    /**
     * @var array
     */
    public const LANGUAGE_LOCALE_MAPPING = [
        'da' => 'da_DK',
        'de' => 'de_DE',
        'en' => '',
        'es' => 'es_ES',
        'fi' => 'fi',
        'fr' => 'fr_FR',
        'km' => '',
        'lv' => '',
        'nl' => 'nl_NL',
        'no' => '',
        'pl' => 'pl_PL',
        'pt-BR' => 'pt_BR',
        'ru' => 'ru_RU',
        'zh-CN' => 'zh_CN',
    ];

    /**
     * Returns json data containing the Yoast SEO translations for the current users backend language.
     *
     * @throws InvalidArgumentException
     */
    public function fetchTranslations(): JsonResponse
    {
        $interfaceLanguage = 'nl';
        $locale = $this->getValidLocale($interfaceLanguage);

        $cache = new FilesystemAdapter();

        $translationData = $cache->getItem('translations.'.$locale);

        if (!$translationData->isHit()) {
            $filePath = new FileLocator(__DIR__.'/../YoastSeo/lang');
            // TODO: this is not the nicest way i think to link to the file
            $filePath = $filePath->locate($locale . '.json');

            if (file_exists($filePath)) {
                $rawTranslationData = file_get_contents($filePath);
                $translationData->set(json_decode($rawTranslationData, true));
                $cache->save($translationData);
            } else {
                return new JsonResponse(
                    ['error' => sprintf('No translation available for language %s', $interfaceLanguage)]
                );
            }
        }

        return new JsonResponse($translationData->get());
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
