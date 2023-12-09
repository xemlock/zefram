<?php

class Zefram_Db_Table_RowTest extends PHPUnit_Framework_TestCase
{
    /** @var Zend_Db */
    protected $db;

    function setUp()
    {
        $dbname = ':memory:';
        $this->db = Zend_Db::factory('PDO_SQLITE', compact('dbname'));
    }

    function tearDown()
    {
        $this->db->closeConnection();
        $this->db = null;
    }

    function testRows()
    {
        $this->db->query('CREATE TABLE a (a_id INTEGER NOT NULL PRIMARY KEY, a_val VARCHAR(32) NOT NULL, b_id INTEGER)');
        $this->db->query('CREATE TABLE b (b_id INTEGER NOT NULL PRIMARY KEY, b_val VARCHAR(32) NOT NULL, a_id INTEGER REFERENCES a (a_id))');

        $tableProvider = new Zefram_Db_TableProvider($this->db);

        /** @var ATable $aTable */
        $aTable = $tableProvider->getTable('ATable');

        /** @var BTable $bTable */
        $bTable = $tableProvider->getTable('BTable');

        $a = $aTable->createRow();
        $a->a_val = md5(mt_rand());
        $a->save();

        $a2 = $aTable->createRow();
        $a2->a_val = md5(mt_rand());
        $a2->save();

        $b = $bTable->createRow();
        $b->A = $a;

        // find by primary key
        $this->assertEquals($a->toArray(), $aTable->find($a->a_id)->current()->toArray(), 'Test find');
        $this->assertEquals($a->toArray(), $aTable->find(array($a->a_id))->current()->toArray(), 'Test find');

        // find multiple rows
        $this->assertEquals($a->toArray(), $aTable->find(array($a->a_id, $a2->a_id))->offsetGet(0)->toArray(),
            'Test find array($a->a_id, $a2->a_id)[0]'
        );
        $this->assertEquals($a2->toArray(), $aTable->find(array($a->a_id, $a2->a_id))->offsetGet(1)->toArray(),
            'Test find array($a->a_id, $a2->a_id)[1]'
        );

        $this->assertSame($a, $b->A, 'Parent row assignment ($b->A === $a)');
        $this->assertSame($a->a_id, $b->a_id, 'Reference columns must match ($b->a_id == $a->a_id)');

        $b2 = $bTable->createRow();
        $b2->b_val = md5(mt_rand());
        $b2->save();

        $this->assertNull($b2->A, 'Referenced parent is empty ($b2->A === null)');
        $this->assertNull($b2->a_id, 'Referencing column is empty ($b2->a_id === null)');

        $b2->A = $a;
        $b2->save();

        $b2->A = $a2;
        $b2->save();

        $b2->A = null;
        $b2->save();

        $this->assertNull($b2->A, 'NULL parent row assignment ($b2->A === null)');
        $this->assertNull($b2->a_id);

        $b2->A = null;
        $b2->a_id = $a2->a_id;
        $this->assertNotNull($b2->A);
        $this->assertEquals($b2->A->a_id, $b2->a_id,
            'Parent row access following unsetting parent row and setting parent by column');

        $b3 = $bTable->createRow(array('b_val' => 'b3'));
        $a3 = $aTable->createRow(array('a_val' => 'a3'));

        $b3->A = $a3;
        $b3->save();

        $this->assertNotNull($a3->a_id, 'Parent row was persisted by child row');
        $this->assertSame($a3, $b3->A, 'Child row retained reference to parent row after save');
        $this->assertEquals($a3->a_id, $b3->a_id, 'Child row has correct parent ID value');

        $a4 = $aTable->createRow(array('a_val' => 'a4'));
        $a4->save();

        $b4 = $bTable->createRow(array('b_val' => 'b4'));
        $b4->A = $a4;
        $b4->save();

        $a4->a_id = 128;
        $a4->save(); // persist modified primary key in database

        // What if refresh() is called instead of save()?
        // new a_id value will be loaded from db,
        // row corresponding to old a_id is in _parentRows and wont be detected upon
        // access, so new a4 will be fetched.
        // Conclusion: refresh() called explicitly may break connections between row
        // objects.
        $b4->save();
        $this->assertEquals(128, $b4->a_id, 'Parent row ID was updated');
        $this->assertSame($a4, $b4->A, 'Parent row with modified primary key was retained');

        $a5 = $aTable->createRow(array('a_val' => 'a5'));
        $b5 = $bTable->createRow(array('b_val' => 'b5'));

        $a5->B = $b5;
        $b5->A = $a5;

        $a5->save();

        $this->assertTrue($a5->isStored(), 'Cyclic references are stored');
        $this->assertTrue($b5->isStored());
        $this->assertSame($b5, $a5->B, 'Cyclically referenced objects are retained');
        $this->assertSame($a5, $b5->A);

        // check if modified detached rows are not saved when referencing row is saved
        $a6 = $aTable->createRow(array('a_val' => 'a6'));
        $b6 = $bTable->createRow(array('b_val' => 'b6'));

        $a6->B = $b6;
        $a6->save();

        $b6->b_val = 'b.vi';

        $a6->b_id = null;
        $a6->save();

        $this->assertTrue($b6->isModified(), 'Detached rows are not saved');
        $this->assertNull($a6->b_id,'Detached rows are not referenced after save()');

        // check if _postLoad is triggered whenever necessary
        BTableRow::clearPostLoadLog();
        $b7 = $bTable->createRow(array('b_val' => 'b7'));
        $this->assertEquals(array(), BTableRow::getPostLoadLog(), 'Post-load logic is not triggered when a not stored row is created');

        BTableRow::clearPostLoadLog();
        $b7->save();
        $this->assertEquals(array(BTableRow::postLoadLogEntry($b7)), BTableRow::getPostLoadLog(), 'Post-load logic is executed upon save()');

        BTableRow::clearPostLoadLog();
        $b8 = $bTable->findRow($b7->b_id);
        $this->assertEquals(array(BTableRow::postLoadLogEntry($b8)), BTableRow::getPostLoadLog(), 'Post-load logic is executed when row is fetched');
    }
}

/**
 * @method Zefram_Db_Table_Row createRow(array $data = array(), string $defaultSource = null)
 */
class ATable extends Zefram_Db_Table
{
    protected $_name = 'a';

    protected $_primary = 'a_id';

    protected $_sequence = true;

    protected $_referenceMap = array(
        'B' => array(
            'columns'       => 'b_id',
            'refTableClass' => 'BTable',
            'refColumns'    => 'b_id',
        ),
    );
}

/**
 * @method Zefram_Db_Table_Row createRow(array $data = array(), string $defaultSource = null)
 */
class BTable extends Zefram_Db_Table
{
    protected $_name = 'b';

    protected $_primary = 'b_id';

    protected $_sequence = true;

    protected $_referenceMap = array(
        'A' => array(
            'columns'       => 'a_id',
            'refTableClass' => 'ATable',
            'refColumns'    => 'a_id',
        ),
    );

    protected $_rowClass = 'BTableRow';
}

class BTableRow extends Zefram_Db_Table_Row
{
    protected $_tableClass = 'BTable';

    protected function _postLoad()
    {
        self::$_postLoadLog[] = self::postLoadLogEntry($this);
    }

    protected static $_postLoadLog = array();

    public static function postLoadLogEntry(BTableRow $row)
    {
        return __METHOD__ . '(' . $row->b_id . ')';
    }

    public static function getPostLoadLog()
    {
        return (array) self::$_postLoadLog;
    }

    public static function clearPostLoadLog()
    {
        self::$_postLoadLog = array();
    }
}
