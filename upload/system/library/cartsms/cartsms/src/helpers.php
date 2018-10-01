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

    public static function productsOutOfStock(Database $db, array $output = array())
    {
        $products = $db->execute("SELECT `product_id` FROM `{$db->table('product')}` WHERE `quantity` = 0 AND `subtract` = 1 AND `status` = 1");

        foreach($products as $item)
        {
            $output[] = (int) $item->product_id;
        }

        return $output;
    }

    public static function subStr($s, $start, $length)
    {
        if(extension_loaded('mbstring'))
        {
            return mb_substr((string) $s, (int) $start, (int) $length);
        }
        else
        {
            return substr((string) $s, (int) $start, (int) $length);
        }
    }
}
