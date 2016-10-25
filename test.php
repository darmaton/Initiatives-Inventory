<?php

require 'vendor/autoload.php';
require 'config/config.php';			// Has database credentials

/**
 * Test Organization
 * Start by adding a record
 */

if ( $pdo = connect_to_LTPP_database() ) {

    $Organization = new \LTPP\Inv\Organizations( $pdo, true );

    print "Bad Insert\n-----------------------------\n";

    $record = array('name' => 'test1', 'city' => 'Kansas City', 'zip' => 'MO', 'barham' => 'not');

    $id = $Organization->add( $record );

    if ( $id == false ) {
        print_r( $Organization->error_messages );
    } else {
        print "New id=$id\n";
    }

    print "\n\nGOOD Insert\n-----------------------------\n";

    $record = array('name' => 'test1', 'city' => 'Kansas City', 'state' => 'MO');

    $id = $Organization->add( $record );

    if ( $id == false ) {
        print_r( $Organization->error_messages );
    } else {
        print "New id=$id\n";
    }

    print "\n\nCHANGE \n-----------------------------\n";

    $record = array('name' => 'Newname', 'city' => 'Greenwood', 'state' => 'MO');

    $id = $Organization->save_changes( $id, $record );

    $record = $Organization->find_by_id( $id );

    print_r( $record );

    print "\n\nFind All \n-----------------------------\n";


    $records = $Organization->find_all( );

    print_r( $records );
}




/**
 * Get database connection
 * @return PDO
 * @throws Exception
 */
function connect_to_LTPP_database()
{

    global $DB_NAME;
    global $DB_USER;
    global $DB_PASS;
    global $DB_HOST;

    try {

        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PASS);

    } catch (PDOException $e) {
        error_log($e->getMessage() . ' ' . __FILE__ . ' ' . __LINE__);
        return false;
    }

    return $pdo;
}




