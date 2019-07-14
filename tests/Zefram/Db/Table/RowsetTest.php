<?php

class Zefram_Db_Table_Rowset_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zefram_Db_Table_Rowset
     */
    protected $_rowset;

    protected function setUp()
    {
        $this->_rowset = new Zefram_Db_Table_Rowset(array(
            'data' => array(
                array(
                    'id' => 1,
                    'name' => 'Optimus Prime',
                    'faction' => 'Autobot',
                    'subGroup' => null,
                ),
                array(
                    'id' => 2,
                    'name' => 'Megatron',
                    'faction' => 'Decepticon',
                    'subGroup' => null,
                ),
                array(
                    'id' => 3,
                    'name' => 'Grimlock',
                    'faction' => 'Autobot',
                    'subGroup' => 'Dinobots',
                ),
                array(
                    'id' => 4,
                    'name' => 'Long Haul',
                    'faction' => 'Decepticon',
                    'subGroup' => 'Constructicons',
                ),
            ),
        ));
    }

    public function testCollectColumn()
    {
        $this->assertEquals(
            array(
                'Optimus Prime',
                'Megatron',
                'Grimlock',
                'Long Haul',
            ),
            $this->_rowset->collectColumn('name')
        );

        $this->assertEquals(
            array(
                1 => 'Optimus Prime',
                2 => 'Megatron',
                3 => 'Grimlock',
                4 => 'Long Haul',
            ),
            $this->_rowset->collectColumn('name', 'id')
        );

        $this->assertEquals(
            array(
                $this->_rowset->getRow(0),
                $this->_rowset->getRow(1),
                $this->_rowset->getRow(2),
                $this->_rowset->getRow(3),
            ),
            $this->_rowset->collectColumn(null)
        );

        $this->assertEquals(
            array(
                1 => $this->_rowset->getRow(0),
                2 => $this->_rowset->getRow(1),
                3 => $this->_rowset->getRow(2),
                4 => $this->_rowset->getRow(3),
            ),
            $this->_rowset->collectColumn(null, 'id')
        );


        $this->assertEquals(
            array(
                'Autobot' => 'Grimlock',
                'Decepticon' => 'Long Haul',
            ),
            $this->_rowset->collectColumn('name', 'faction')
        );
    }
}
