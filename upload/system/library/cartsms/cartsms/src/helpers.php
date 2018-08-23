<?php
namespace BulkGate\CartSms;

use BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Helpers extends Extensions\Strict
{
    public static function fixUrl($url)
    {
        return str_replace('&amp;', '&', $url);
    }

    public static function getLanguageId($locale, Database $db)
    {
        if($locale !== null)
        {
            $row = $db->execute($db->prepare("SELECT `language_id` FROM `{$db->table('language')}` WHERE `code` = %s AND `status` = 1 LIMIT 1", array($locale)))->getRow();

            if($row)
            {
                return (int) $row->language_id;
            }
        }

        $row = $db->execute("SELECT `language_id` FROM `{$db->table('language')}` WHERE `status` = 1 LIMIT 1")->getRow();

        if($row)
        {
            return (int) $row->language_id;
        }
        return 0;
    }
}
