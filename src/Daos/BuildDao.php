<?php
namespace App\Daos;


use BMorais\Database\Crud;
use Error;

class BuildDao extends Crud{


    public function execute(string $sql)
    {
        $result = $this->executeSQL($sql);
        if (!$result)
            return [];
        $colCount = $result->columnCount();
        $return = array();
        for($i=0;$i<$colCount;$i++){
            $meta = $result->getColumnMeta($i);
            $return[] = $meta['name'];
        }


        return $return;
    }
}