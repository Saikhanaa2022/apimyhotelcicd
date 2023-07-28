<?php

namespace App\Services;

use SimpleXMLElement;

class Common
{
    public static function sxml_append(SimpleXMLElement $to, SimpleXMLElement $from)
    {
        $toDom = dom_import_simplexml($to);
        $fromDom = dom_import_simplexml($from);
        $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
    }

    public static function xml2array($xmlObject)
    {
        $arr = array();

        foreach ($xmlObject->children() as $r) {
            $t = array();
            if (count($r->children()) == 0) {
                $arr[$r->getName()] = trim(strval($r));
            } else {
                $arr[$r->getName()][] = Common::xml2array($r);
            }
        }
        return $arr;
    }
}