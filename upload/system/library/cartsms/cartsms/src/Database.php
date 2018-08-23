<?php
namespace BulkGate\CartSms;

use BulkGate;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Database extends BulkGate\Extensions\Strict implements BulkGate\Extensions\Database\IDatabase
{
    /** @var \DB */
    private $db;

    private $sql = array();

    public function __construct(\DB $db)
    {
        $this->db = $db;
    }

    public function execute($sql)
    {
        $output = array();

        $this->sql[] = $sql;

        $result = $this->db->query($sql);

        if(isset($result->rows) && is_array($result->rows) && count($result->num_rows) > 0)
        {
            foreach ($result->rows as $key => $item)
            {
                $output[$key] = (object) $item;
            }
        }
        return new BulkGate\Extensions\Database\Result($output);
    }

    public function prepare($sql, array $params = array())
    {
        foreach($params as $param)
        {
            $sql = preg_replace("/%s/", "'".$this->db->escape((string) $param)."'", $sql, 1);
        }
        return $sql;
    }

    public function lastId()
    {
        return $this->db->getLastId();
    }

    public function escape($string)
    {
        return $this->db->escape($string);
    }

    public function prefix()
    {
        return DB_PREFIX;
    }

    public function table($table)
    {
        return DB_PREFIX.$table;
    }

    public function getSqlList()
    {
        return $this->sql;
    }
}
