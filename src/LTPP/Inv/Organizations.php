<?php

namespace LTPP\Inv;

use PDO as PDO;

/**
 * Class Address
 */
class Organizations extends BaseTable
{

    var $table_name = 'organizations';


    var $field_definitions = array(
        'name' => array('size' => 255, 'type' => 'char', 'default' => ''),
        'contact_name' => array('size' => 255, 'type' => 'char', 'default' => ''),
        'city' => array('size' => 24, 'type' => 'char', 'default' => ''),
        'state' => array('size' => 24, 'type' => 'char', 'default' => ''),
        'county' => array('size' => 24, 'type' => 'char', 'default' => ''),
        'zip' => array('size' => 24, 'type' => 'int', 'default' => ''),
        'website_url' => array('size' => 255, 'type' => 'char', 'default' => ''),
        'boundaries_areas' => array('type' => 'text', 'default' => ''),
        
        'created' => array('size' => 24, 'type' => 'date', 'default' => 'NOW()'),
    );

}
