<?php

namespace LTPP\Inv;

use \PDO as PDO;

/**
 * Class BaseTable
 */
class BaseTable
{

    var $dbh;
    var $table_name = '';

    var $field_definitions = array();
    var $id_auto_increment = true;              // If id field is auto increment we will skip putting it in INSERT

    var $id_query = null;
    var $find_all_query = null;
    var $add_query = null;

    /**
     * @param $dbh
     */
    function __construct(&$dbh, $debug = false)
    {

        $this->dbh = $dbh;

        if ($debug) {
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    /**
     * Add record's fields.
     *
     * @param $data
     *     array('name' => 'test1', 'city' => 'Kansas City')
     * Use default values from $this->field_definitions
     * Returns id added or record added to the database.
     *
     *
     */
    function add( $data )
    {

        if ( $record = $this->load_and_validate( $data ) ) {

            if (!$this->add_query) {                                                                // Have we already built the query?
                $names = '';
                $values = '';                                                                               // Build it
                $sep = '';
                foreach ($this->field_definitions AS $f => $v) {
                    if ($f == 'id' && $this->id_auto_increment) continue;
                    $names .= $sep . $f;
                    $values .= $sep . ':' . $f;
                    $sep = ', ';
                }

                $sql = 'INSERT INTO ' . $this->table_name . ' (' . $names . ') VALUES (' . $values . ')';

                // sql ends up like
                //  INSERT INTO organizations (name, city, state, county, zip) VALUES (:name, :city, :state, :county, :zip)

                $this->add_query = $this->dbh->prepare("$sql  -- " . __FILE__ . ' ' . __LINE__);
            }

            try {                                                                                           // Now we can add thr record
                $new_rec = array();
                foreach ($this->field_definitions AS $f => $v) {
                    if ($f == 'id' && $this->id_auto_increment) continue;
                    if (array_key_exists($f, $record)) {
                        $value = $record[$f];
                    } else {
                        if ( $v['default'] == 'NOW()' ) {
                            $value = date ( 'Y-m-d H-i-s' );
                        } else {
                            $value = $v['default'];
                        }
                    }
                    $new_rec[':' . $f] = $value;
                }
                // $new_rec looks like
                /**
                 *
                 */
                $ret = $this->add_query->execute($new_rec);

            } catch (PDOException  $e) {

                error_log($e->getMessage() . ' ' . __FILE__ . ' ' . __LINE__);

                $this->add_query->debugDumpParams();
                //throw new Exception('Unable to query database');
                return false;
            }

            $id = $this->dbh->lastInsertId();

            return $id;
        } else {
            return false;
        }

    }


    /**
     * Changes is an array with an element for each field, with has an array with two element, the 'from' or old value, and 'to' new value.
     * This allows us to easily report differences in other sections of the code.
     * @param $id
     * @param $changes
     * @return bool
     */
    public function save_changes($id, $data)
    {

        if ( $changes = $this->load_and_validate( $data ) ) {

            $sep = '';
            $fields = '';
            $values = array();

            foreach ($changes AS $field => $value) {

                $fields .= $sep . $field . " = :$field ";
                $values[":$field"] = $value;
                $sep = ', ';

            }

            $values['id'] = $id;

            $sql = 'UPDATE ' . $this->table_name . ' SET ' . $fields . ', changed = NOW() ' . ' WHERE id = :id -- ' . __FILE__ . ' ' . __LINE__;

            try {
                $query = $this->dbh->prepare("$sql  -- " . __FILE__ . ' ' . __LINE__);
                $ret = $query->execute(
                    $values
                );
            } catch (PDOException  $e) {
                print ($e->getMessage() . ' ' . __FILE__ . ' ' . __LINE__);
                error_log($e->getMessage() . ' ' . __FILE__ . ' ' . __LINE__);
                //throw new Exception('Unable to query database');
                return false;
            }

            return $id;
        } else {
            return false;
        }

    }


    /**
     * @param $id
     * @return false or found record
     */
    function find_by_id($id)
    {
        if (!$this->id_query) {
            $sql = 'SELECT *  FROM ' . $this->table_name . ' WHERE id = :id';
            $this->id_query = $this->dbh->prepare("$sql  -- " . __FILE__ . ' ' . __LINE__);
        }

        try {
            $this->id_query->execute(array(':id' => $id));
        } catch (PDOException  $e) {
            error_log($e->getMessage() . ' ' . __FILE__ . ' ' . __LINE__);
            //throw new Exception('Unable to query database');
            return false;
        }

        return $this->id_query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $id
     * @return false or found record
     */
    function find_all()
    {
        $results = false;

        if (!$this->find_all_query) {
            $sql = 'SELECT *  FROM ' . $this->table_name . ' ORDER BY id';
            $this->find_all_query = $this->dbh->prepare("$sql  -- " . __FILE__ . ' ' . __LINE__);
        }

        try {
            $this->find_all_query->execute();
        } catch (PDOException  $e) {
            error_log($e->getMessage() . ' ' . __FILE__ . ' ' . __LINE__);
            //throw new Exception('Unable to query database');
            return false;
        }

        try {
            $results = $this->find_all_query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException  $e) {
            error_log($e->getMessage() . ' ' . __FILE__ . ' ' . __LINE__);
            //throw new Exception('Unable to query database');
            return false;
        }

        return $results;
    }



    function load_and_validate($data)
    {

        $is_valid = true;
        $this->error_messages = array();
        foreach ($data AS $field => $value) {
            $valid = $this->load_and_validate_field($this->record, $field, $value);

            if (!$valid) {
                $is_valid = false;
            }
        }

        if ( $is_valid ) {
            return $this->record;
        } else {
            return $is_valid;
        }
    }

    /**
     * load_and_validate_field
     * @param $record - an array of fields indexed by name
     * @param $field_name
     * @param $value
     * @return bool
     */
    function load_and_validate_field(&$record, $field_name, $value)
    {

        $valid_record = true;
        if (array_key_exists($field_name, $this->field_definitions)) {
            $size = $this->field_definitions[$field_name]['size'];

            if ($size && $size > 0) {
                if (strlen($value) > $size) {
                    $this->add_to_error_messages($field_name, ' is ' . strlen($value) . ' long, only ' . $size . ' allowed  "' . $value . '""');
                    $valid_record = false;
                }
            }

            $type = $this->field_definitions[$field_name]['type'];

            switch ($type) {
                case 'int':
                    if (!is_numeric($value)) {
                        $this->add_to_error_messages($field_name, ' is not an integer it is ' . $value);
                        $valid_record = false;
                    }
                    break;

                case 'char':

                    break;

                case 'date':

                    break;

                case 'bool':
                    if (!is_int(( int)$value)) {
                        $this->add_to_error_messages($field_name, ' is not an bool it is ' . $value);
                        $valid_record = false;
                    }
                    break;

                case 'lookup':

                    if (array_key_exists($field_name, $this->field_value_lookup)) {
                        if (!array_key_exists($value, $this->field_value_lookup[$field_name])) {
                            $this->add_to_error_messages($field_name, $value . ' is not a valid value');
                            $valid_record = false;
                        }
                    } else {
                        $this->add_to_error_messages($type, ' is not valid is not a valid LOOKUP field type');
                        $valid_record = false;
                    }
                    break;

                case 'YN':

                    if (!($value == 'Y' || $value == 'N')) {
                        $this->add_to_error_messages($field_name, ' is not Y/N it is ' . $value);
                        $valid_record = false;
                    }

                    break;

                default:
                    $this->add_to_error_messages($type, ' is not valid is not a valid field type');
                    $valid_record = false;
                    break;

            }

            if ($valid_record) {
                $record[$field_name] = $value;
            }

        } else {
            $this->add_to_error_messages($field_name, ' is not a defined field');
            $valid_record = false;
        }


        return $valid_record;
    }

    function add_to_error_messages($field_name, $msg)
    {
        if (!array_key_exists($field_name, $this->error_messages)) {
            $this->error_messages[$field_name] = array();
        }
        $this->error_messages[$field_name][] = $msg;
    }

}
