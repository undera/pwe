<?php

namespace PWE\Utils;

require_once dirname(__FILE__) . '/../../PWEUnitTests.php';

/**
 * Test class for PWEXMLFunctions.
 * Generated by PHPUnit on 2011-09-12 at 01:32:33.
 */
class PWEXMLFunctionsTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var PWEXMLFunctions
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        //$this->object = new PWEXMLFunctions;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @todo Implement testFindNodeWithAttributeValue().
     */
    public function testFindNodeWithAttributeValue() {
        $nodes = array('!a'=>array());
        PWEXMLFunctions:: findNodeWithAttributeValue($nodes, 'test', 'test');
    }

}

?>
