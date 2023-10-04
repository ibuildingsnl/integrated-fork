<?php

namespace Integrated\Bundle\ContentBundle\Controller;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;

class YoastSeoLanguageProvider
{
    public function __construct()
    {
    }

    /**
     * @var array
     */
    protected $languageToLocaleMapping = [
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
            $reflection = new \ReflectionClass('Integrated\Bundle\ContentBundle\Document\Content\Article');
            // TODO: this is not the nicest way i think to link to the file
            $filePath = sprintf('%s/../../YoastSeo/lang/%s.json', \dirname($reflection->getFilename()), $locale);

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
        if (\array_key_exists($interfaceLanguage, $this->languageToLocaleMapping)) {
            return $this->languageToLocaleMapping[$interfaceLanguage];
        }

        return $interfaceLanguage;
    }
}
