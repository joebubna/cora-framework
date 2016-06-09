<?php
namespace Cora;

class Database
{
    protected $tables;
    protected $selects;
    protected $updates;
    protected $delete;
    protected $distinct;
    protected $wheres;
    protected $ins;
    protected $limit;
    protected $offset;
    protected $groupBys;
    protected $orderBys;
    protected $havings;
    protected $joins;
    protected $inserts;
    protected $values;
    protected $create;
    protected $primaryKeys;
    protected $foreignKeys;
    protected $query;
    protected $queryDisplay;
    
    public function __construct()
    {
        $this->reset();
    }
    
    
    public function table($tables)
    {
        $this->storeValue('tables', $tables);
        return $this;
    }
    
    
    // Alias of $this->table
    public function from($tables)
    {
        $this->table($tables);
        return $this;
    }
    
    
    public function select($fields)
    {
        $this->storeValue('selects', $fields);
        return $this;
    }
    
    
    // Alias of $this->table
    public function update($tables)
    {
        $this->table($tables);
        return $this;
    }
    
    
    public function set($field, $value)
    {
        $this->store('keyValue', 'updates', $field, $value);
        return $this;
    }
    
    
    public function delete()
    {
        $this->delete = true;
        return $this;
    }
    
    
    public function distinct()
    {
        $this->distinct = true;
        return $this;
    }
    
    /**
     *  WHERE array format:
        [
            [
                [
                    ['amount', '>', '1000'],
                    ['savings', '>', '100']
                ],
                'AND'
            ]
        ]
    */
    public function where($conditions, $value = false, $comparison = '=')
    {
        $this->store('condition', 'wheres', $conditions, $value, $comparison);
        return $this;
    }
    
    
    public function orWhere($conditions, $value = false, $comparison = '=')
    {
        $this->store('condition', 'wheres', $conditions, $value, $comparison, 'OR');
        return $this;
    }
    
    
    //public function in($column, $fields)
    public function in($conditions, $value = false, $comparison = 'IN')
    {
        $val = $value;
        if (!is_array($value)) {
            $val = explode(',', $val);
        }
        $this->store('condition', 'wheres', $conditions, $val, $comparison);
        return $this;
    }
    
    
    public function limit($num)
    {
        $this->limit = $num;
        return $this;
    }
    
    
    public function offset($num)
    {
        $this->offset = $num;
        return $this;
    }
    
    
    public function groupBy($fields)
    {
        $this->storeValue('groupBys', $fields);
        return $this;
    }
    
    
    public function orderBy($field, $direction = 'DESC')
    {
        $this->store('keyValue', 'orderBys', $field, $direction, '');
        return $this;
    }
    
    
    public function having($conditions, $value = false, $comparison = '=')
    {
        $this->store('condition', 'havings', $conditions, $value, $comparison);
        return $this;
    }
    
    
    public function orHaving($conditions, $value = false, $comparison = '=')
    {
        $this->store('condition', 'havings', $conditions, $value, $comparison, 'OR');
        return $this;
    }
    
    
    public function insert($columns)
    {
        $val = $columns;
        if (!is_array($val)) {
            $val = explode(',', $val);
        }
        $this->storeValue('inserts', $val);
        return $this;
    }
    
    
    // Alias of $this->table
    public function into($table)
    {
        $this->table($table);
        return $this;
    }
    
    
    public function values($values)
    {
        $this->storeValue('values', $values);
        return $this;
    }
    
    
    public function create($table)
    {
        $this->create = $table;
        return $this;
    }
    
    
    public function field($name, $type, $attributes = ' ')
    {
        $this->store('keyValue', 'fields', $name, $attributes, $type);
        return $this;
    }
    
    
    public function primaryKey($columns) {
        $this->storeValue('primaryKeys', $columns);
        return $this;
    }
    
    
    public function foreignKey($column, $foreignTable, $foreignColumn)
    {
        $this->store('keyValue', 'foreignKeys', $column, $foreignColumn, $foreignTable);
        return $this;
    }
    
    
    public function getQuery()
    {
        $this->calculate();
        $this->queryDisplay = $this->query;
        $this->query = '';
        return $this->queryDisplay;
    }
    
    
    public function reset()
    {
        $this->tables   = array();
        $this->selects  = array();
        $this->updates  = array();
        $this->wheres   = array();
        $this->ins      = array();
        $this->groupBys = array();
        $this->orderBys = array();
        $this->havings  = array();
        $this->joins    = array();
        $this->inserts  = array();
        $this->values   = array();
        $this->fields   = array();
        $this->primaryKeys  = array();
        $this->foreignKeys  = array();
        
        $this->distinct = false;
        $this->delete   = false;
        $this->limit    = false;
        $this->offset   = false;
        $this->create   = false;
        $this->query    = '';
        $this->queryDisplay = '';
    }
    
    
    /**
        JOIN array format:
        [
            [
                table,
                [
                    conditions
                ],
                type
            ]
        ]
     */
    public function join($table, $conditions, $type = 'INNER')
    {
        $dataMember = &$this->joins;
        $item = [$table, $conditions, $type];
        array_push($dataMember, $item);
        return $this;
    }
    
    
    protected function store($type, $dataMember, $fields, $value = false, $comparison = '=', $conjunction = 'AND')
    {
        
        // If the data being stored DOES need its key.
        // E.g. adding WHERE field = value pairs to wheres array.
        if ($type == 'keyValue') {
            if ($value) {
                $key = $fields;
                $this->storeKeyValue($dataMember, $value, $key, $comparison);
            }
            else {
                $this->storeKeyValue($dataMember, $fields);
            }
        }
        
        // If the data being stored is condition statements (WHERE, HAVING)
        else if($type == 'condition') {
            if ($value) {
                $key = $fields;
                $this->storeCondition($dataMember, $value, $key, $comparison, $conjunction);
            }
            else {
                $this->storeCondition($dataMember, $fields, $conjunction);
            }
        }
    }
    
    /**
     *  For storing a single value or flat list of values.
     *  STORAGE FORMAT:
     *  [item1, item2, item3]
     */
    protected function storeValue($type, $data)
    {
        $dataMember = &$this->$type;
        // If array or object full of data was passed in, add all data
        // to appropriate data member.
        if (is_array($data) || is_object($data)) {
            foreach ($data as $value) {
                array_push($dataMember, $value);
            }
        }
        
        // Add singular data item to data member.
        else {
            array_push($dataMember, $data);
        }
    }
    
    
    /**
     *  For storing an array of data that represents an item.
     *  STORAGE FORMAT:
     *  [
     *      [column, operator, value],
     *      [name, LIKE, %s],
     *      [price, >, 100]
     *  ]
     */
    protected function storeKeyValue($type, $data, $key = false, $comparison = false)
    {
        $dataMember = &$this->$type;
        // If array or object full of data was passed in, add all data
        // to appropriate data member.
        if (is_array($data) || is_object($data)) {
            foreach ($data as $item) {
                array_push($dataMember, $item);
            }
        }
        
        // Add singular data item to data member.
        else {
            $item = array($key, $comparison, $data);
            array_push($dataMember, $item);
        }
    }
    
    /**
     *  For storing an array of data that represents an item which needs a custom conjunction connecting them.
     *  STORAGE FORMAT:
     *  [
     *      [
     *          [
     *              [column, operator, value],
     *              [name, LIKE, %s],
     *              [price, >, 100]
     *          ],
     *          AND
     *      ]
     *  ]
     */
    protected function storeCondition($type, $data, $key = false, $comparison = false, $conjunction = false)
    {
        $dataMember = &$this->$type;
        // If array or object full of data was passed in, add all data
        // to appropriate data member.
        if ($comparison != 'IN' && (is_array($data) || is_object($data))) {
            $conj = $key;
            $condition = array($data, $conj);
            array_push($dataMember, $condition);
        }
        
        // Add singular data item to data member.
        else {
            $item = [array($key, $comparison, $data)];
            $condition = array($item, $conjunction);
            array_push($dataMember, $condition);
        }
    }
    
    
    public function exec()
    {
        // To be implemented by specific DB adaptor.
        throw new Exception('exec() needs to be implemented by a specific database adaptor!');
    }
    
    
    protected function calculate()
    {
        // To be implemented by specific DB adaptor.
        throw new Exception('getQuery() calls calculate(), which needs to be implemented by a specific database adaptor!');
    }
    
}