<?php

use \Delight\I18n\Locale;

class Language{

    static function getLanguageCode(){
        global $i18n;

        return Locale::toLanguageCode($i18n->getLocale());
    }

    static function getLocale(){
        global $i18n;

        return $i18n->getLocale();
    }
}