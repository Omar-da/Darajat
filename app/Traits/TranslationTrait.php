<?php

namespace App\Traits;

use Stichoza\GoogleTranslate\GoogleTranslate;

trait TranslationTrait
{
    protected function detectLanguage(string $text): string
    {
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return 'ar';
        }
        return 'en';
    }

    protected function translateContent(string $text, string $sourceLang): array
    {
        $targetLang = ($sourceLang === 'ar') ? 'en' : 'ar';
        $translated = $this->translateText($text, $sourceLang, $targetLang);

        return [
            $sourceLang => $text,
            $targetLang => $translated
        ];
    }

    protected function translateText(string $text, string $source, string $target): string
    {
//        $tr = new GoogleTranslate();
//        $tr->setSource($source);
//        $tr->setTarget($target);
//        return $tr->translate($text);
        return $text;
    }
}
