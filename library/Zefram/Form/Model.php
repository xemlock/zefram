<?php

/**
 * @deprecated
 */
class Zefram_Form_Model extends Zefram_Form
{
    const CREATE = 'CREATE';
    const UPDATE = 'UPDATE';

    const SUBMIT = 'SUBMIT'; // id of submit element

    protected $_formMode;
    protected $_modelName;
    protected $_record;
    protected $_spec;
    protected $_driver;
    protected $_types = array();
    protected $_id;

    public function __construct($modelName, $mode, $id = null)
    {
        $this->_driver = Zefram_Db_Driver::get($modelName);

        $this->_id = $id;
        $record = null;
        if ($id !== null) {
            $record = $this->_driver->find($id);
        }

        $this->_formMode = $mode;
        $this->_modelName = $modelName;
        $this->_record = $record;
        $this->_spec = $this->buildElementsSpec($modelName, $mode, $record);

        parent::__construct(array('elements' => $this->_spec['elements']));
    }

    public function render()
    {
        // move hidden fields to the beggining of the form
        $index = 0;
        foreach ($this->getElements() as $name => $element) {
            if ($element->getType() == 'hidden') {
                $element->setOrder($index++);
            }
        }
        foreach ($this->getElements() as $name => $element) {
            if ($element->getType() != 'hidden') {
                $element->setOrder($index++);
            }
        }
        return parent::render();
    }

    public function getRecord()
    {
        return $this->_record;
    }

    // simulate 'reference' inheritance (Django, not Doctrine)
    // visited - zeby sie nie petlic, bo ktos moze zrobic psikusa w definicji tabeli
    public static function inheritance($modelName, $row = null, $visited = array())
    {
        // This is no more valid - Zend_Db rows can easily be Zend_Db_Table_Row
        //if (is_object($row) && get_class($row) != $modelName) {
        //    trigger_error("Row is not an instance of given model class", E_USER_ERROR);
        //    return;
        //}
        $driver = Zefram_Db_Driver::get($modelName);
        $primary = $driver->getIdentifier();
        if (is_array($primary)) {
            throw new Exception('Multicolumn Primary Keys are not supported');
        }

        $visited[$modelName] = true;
        $parent = $driver->getParent($row);
        if ($parent) {
            $inh = self::inheritance(
                $parent['model'],
                $parent['record'],
                $visited
            );
            $inh[] = array('DRIVER' => $driver, 'CLASS' => $modelName, 'PRIMARY' => $primary, 'RECORD' => $row, 'IDENTITY' => false);
            return $inh;
        }
        // root of inheritance hierarchy
        $specs = $driver->getSpecs();
        $autoinc = $specs[$primary]['IDENTITY'];
        return array(array('DRIVER' => $driver, 'CLASS' => $modelName, 'PRIMARY' => $primary, 'RECORD' => $row, 'IDENTITY' => $autoinc));
    }

    public function updateRecord()
    {
        $conn = $this->_driver->getConnection();
        try {
          $conn->beginTransaction();

          $this->_updateRecord();

          $conn->commit();
        } catch(Exception $e) {
            $conn->rollback();
            // TODO exception translation
            throw Zefram_Db_Driver::translateException($e);
        }
    }

    protected function afterSave()
    {



    }

    protected function onUpdate() {}

    protected function _updateRecord()
    {
        $formData = $this->getValues();
        // nullify empty strings
        foreach ($formData as $key => $value) {
            if ($value == '') {
                $formData[$key] = null;
            }
        }

        $rows = $this->_spec['parents'];
        // save
        // TODO owinac to w funkcje i zrobic rollback!!!
        for ($i = 0; $i < count($rows); ++$i) {
            $row = $rows[$i]['RECORD'];
            if (!$row) {
                // $row = new $rows[$i]['CLASS']; // Driver specific (Zend: table->createRow(), Doctrine: new TableClass)
                $row = $rows[$i]['DRIVER']->createRow();
                if (!$this->_record && $i == count($rows) - 1) {
                    // ostatni rekord to jest ten edytowany
                    $this->_record = $row;
                    $this->onUpdate();
                }
            }
            $rows[$i]['DRIVER']->populateRecord($row, $formData);

            // znulifikuj puste kolumny
            if ($this->_formMode == self::CREATE) {
                // nullify primary key when creating root, and duplicate
                // root id for children
                if ($i == 0) {
                    $autoinc = $rows[$i]['IDENTITY'];
                    // be sure no data from form interacts with autoincremented pk
                    if ($autoinc) $row->{$rows[$i]['PRIMARY']} = null;
                } else {
                    // assign identifier to records in descending tables
                    $row->{$rows[$i]['PRIMARY']} = $root_id;
                }
            } else { // mode == self::UPDATE
                if ($i > 0 && $row->{$rows[$i]['PRIMARY']} != $root_id) {
                    throw new Exception('Fuken data corruption!');
                }
            }
            $row->save();
            if ($i == 0) {
                $root_id = $row->{$rows[$i]['PRIMARY']};
            }
            if ($i == count($rows) - 1) {
                $this->afterSave();
            }
        }
        return;
    }

    // FIXME czy ma przyjmowac model czy jego nazwe i sama budowac model???
    public function buildElementsSpec($modelName, $mode, $row = null) {
        $inherited = self::inheritance($modelName, $row);

        $base = array('elements' => array(), 'parents' => $inherited);
        $relField = null;
        $elems = array();
        for ($i = 0; $i < count($inherited); ++$i) {
            // idziemy od korzenia hierarchii w dol, do zadanego modelu
            $model = $inherited[$i]['CLASS'];
            $pk    = $inherited[$i]['PRIMARY'];
            $r     = $inherited[$i]['RECORD'];
            foreach (self::_buildElementsSpec($model, $mode, $r) as $name => $spec) {
                // ignore join column for descending tables (not root!)
                if (($i > 0) && $name == $pk) continue;
                if (isset($elems[$name])) {
                    trigger_error("Duplicate column in descendant table: " . $name, E_USER_NOTICE);
                    echo("Duplicate column in descendant table: " . $name . E_USER_NOTICE);
                }
                $elems[$name] = $spec;
            }
        }
        $base['elements'] = $elems;
        $base['elements'][self::SUBMIT] = array('type'=>'submit');
        $base['elements'][self::SUBMIT]['options'] = array(
            'decorators' => array(
                'ViewHelper',
                'DtDdWrapper',
                array(array('row' => 'HtmlTag'), array('tag' => 'dl')),
            )
        );
        return $base;
    }

    public function _buildElementsSpec($modelName, $mode, $row = null) {
        $driver = Zefram_Db_Driver::get($modelName);
        $specs = $driver->getSpecs();

        // build elements specification based on columns definition
        $pk = $driver->getIdentifier();
        if (is_array($pk)) {
            throw new Exception('Only single-column primary keys are supported');
        }
        $fields = array();
        $autoPk = $specs[$pk]['IDENTITY'];

        foreach ($specs as $name => $spec) {
            $fields[$name] = array(
                'type' => 'text', // TODO string/varchar -> text, text -> textarea, enum -> select
                'options' => array(
                    'label' => $name,
                    /*'decorators' => array(
                        'ViewHelper', 'Description', 'Errors',
                        array(array('data' => 'HtmlTag'), array('tag' => 'dd')),
                        array('Label', array('tag' => 'dt')),
                        array(array('row' => 'HtmlTag'),array('tag' => 'dl')),
                    ),*/
                    'filters' => array(
                        'StringTrim',
                    ),
                ),
            );
            if ($spec['DATA_TYPE'] == 'enum') {
                $fields[$name]['type'] = 'select';
                $values = array();
                foreach ($spec['values'] as $value) {
                  $values[$value] = $value;
                }
                $fields[$name]['options']['multioptions'] = $values;
            }
            $notnull = !$spec['NULLABLE'];
            if ($name == $pk || $notnull) {
                $fields[$name]['options']['required'] = true;
                $fields[$name]['options']['autoInsertNotEmptyValidator'] = true;
            }
            if (isset($this->_types[$name])) {
                $fields[$name]['type'] = $this->_types[$name];
                if ($fields[$name]['type'] == 'hidden') {
                    $fields[$name]['options']['label'] = null;
                    $fields[$name]['options']['decorators'] = array('ViewHelper');
                }
            }
        }

        //
        switch ($mode) {
            case self::UPDATE:
                if (!$row) {
                    throw new Exception("Invalid id: {$this->_id}");
                }

                // id field is readonly
                $fields[$pk]['options']['readonly'] = true;
                $fields[$pk]['options']['ignore'] = true; // won't be updated by GET/POST

                if ($autoPk) {
                    // autoincremented id field is also hidden (no need to show integer id
                    // since they are meaningless to user), unless type is given directly
                    if (!isset($_types[$pk])) {
                        $fields[$pk]['type'] = 'hidden';
                        $fields[$pk]['options']['label'] = null;
                        $fields[$pk]['options']['decorators'] = array('ViewHelper');
                    }
                }
                // load default values
                foreach ($specs as $key => $spec) {
                    if (isset($fields[$key])) {
                        $fields[$key]['options']['value'] = $row->$key;
                    }
                }

                break;

            case self::CREATE:
                if ($autoPk) {
                    // remove autoincremented id field
                    unset($fields[$pk]);
                } else {
                    // prevent overwriting existing record that has the same id
                    // as newly created one
                    $fields[$pk]['options']['validators'][] = new Zefram_Controller_Form_NoRecord($row);
                }
                break;
        }

        return $fields;
    }
}

// vim: et sw=4
