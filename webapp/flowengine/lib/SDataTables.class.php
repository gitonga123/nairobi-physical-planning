<?php

/**
 * Datatables PHP component
 *
 * This class responsible for allowing datatables to fetch information from the
 * web server page by page as opposed to fetching all the information at once 
 * and then showing the table using datatables.
 * 
 * @version   1.3.0
 * @author    Ken Gichia
 */
class SDataTables {
    /**
     * In the event one would like to show return empty data table data, call 
     * this function
     * 
     * @return  array
     */
    public function emptyResultSet() {
        return array (
            "sEcho" => !is_null($this->request->getParameter('sEcho')) ? intval($this->request->getParameter('sEcho')): 1,
            "iTotalRecords" =>0,
            "iTotalDisplayRecords" => 0,
            "aaData" => array ()
        );
    }

    /**
     * Specify the columns that will appear in the final result set.
     *
     * Example
     * <code>
     * $dt->select('r.roleNo, a.name as roleName, r.urlCode, b.name as actionName');
     * </code>
     *
     * @param   string
     * @return  SDataTables
     */
    public function select($select) {
        $this->columnNames = array ();
        $select = $this->trimString($select);
        
        foreach ( explode(",", $select) as $cols ) {
            $cols = explode(" ", trim($cols));
            $this->columnNames[] = $cols[0];
        }
        
        $this->selectParams = $select; 
        return $this;
    }


    /**
     * Specify the table or view where the data is getting its source from.
     *
     * Example
     * <code>
     * $dt->from('table_name', 'r');
     * </code>
     *
     * @param   string   The name of the table or view
     * @param   string   The alias used when referring to the table or view
     * @return  SDataTables
     */
    public function from($table, $alias) {
        $this->fromParams = array (
            'table' => $this->trimString($table),
            'alias' => $this->trimString($alias)
        ); return $this;
    }


    /**
     * Allow one to perform inner joins 
     *
     * Example
     * <code>
     * $dt
     *   ->innerJoin (
     *     'table_a', 'a', 'a.id = r.roleNo'
     *   )
     *   ->innerJoin (
     *     'table_b', 'b', 'b.id = r.defaultAction'
     *   );
     * </code>
     *
     * @param   string   The table or view to join with
     * @param   string   The alias used when referring to this entity
     * @param   string   The condition joining the two entities
     * @return  SDataTables
     */
    public function innerJoin($table, $alias, $condition) {
        $this->innerJoinParams[] = array (
            'table'     => $this->trimString($table),
            'alias'     => $this->trimString($alias),
            'condition' => $condition
        ); return $this;
    }


    /**
     * Allow one to perform left joins 
     *
     * Example
     * <code>
     * $dt
     *   ->leftJoin (
     *     'table_a', 'a', 'a.id = r.roleNo'
     *   )
     *   ->leftJoin (
     *     'table_b', 'b', 'b.id = r.defaultAction'
     *   );
     * </code>
     *
     * @param   string   The entity to join with
     * @param   string   The alias used when referring to this entity
     * @param   string   The condition joining the two entities
     * @return  SDataTables
     */
    public function leftJoin($table, $alias, $condition) {
        $table = $this->trimString($table);
        $alias = $this->trimString($alias);

        if ( strpos(strtolower($table), 'select') !== false )
            $table = "($table)";

        $this->leftJoinParams[] = array (
            'table'     => $table,
            'alias'     => $alias,
            'condition' => $condition
        ); return $this;
    }


    /**
     * Allow one to apply specific filters using doctrine's Expr class
     *
     * Example
     * <code>
     * $dt
     *   ->where ('b.id = :param')
     *   ->setParameter ('param', $_POST['some_var']);
     * </code>
     *
     * @param   string
     * @return  SDataTables
     */
    public function where($where) {
        if ( $this->whereParams != "" ) $this->whereParams .= " AND ";
        $this->whereParams .= "($where)";
        return $this;
    }


    /**
     * Allow one to safely insert one parameter at a time
     *
     * Example
     * <code>
     * $dt
     *   ->where ('b.id = :param')
     *   ->setParameter ('param', $_POST['some_var']);
     * </code>
     * 
     * @param   string
     * @param   mixed
     * @return  SDataTables
     */
    public function setParameter($param, $bindValue) {
        $param = str_replace(':', '', $param);
        $this->params[$param] = $bindValue;
        return $this;
    }


    /**
     * Allow one to safely insert multiple parameters at a time
     *
     * Example
     * <code>
     * $dt
     *   ->where ('b.id = :param_1 AND b.id = :param_2')
     *   ->setParameters ( array (
     *     'param_1' => $_POST['some_var'],
     *     'param_2' => $_POST['another_var']
     *   ));
     * </code>
     * 
     * @param   array
     * @return  SDataTables
     */
    public function setParameters($params) {
        foreach ( $params as $k=>$v ) {
            $k = str_replace(':', '', $k);
            $this->params[$k] = $v;
        }
        return $this;
    }


    /**
     * Allow one to concat the fields specified
     * 
     * This function concatenates columns and uses a single space character to 
     * seperate the values placed in the concat string.
     *
     * Example
     * <code>
     * $dt->concatFields ('b.id, b.name', 'b.id');
     * </code>
     *
     * @param   string
     * @param   string
     * @return  SDataTables
     */
    public function concatFields($fields, $col) {
        $fields = $this->trimString($fields);
        $str = "CONCAT($fields)";

        $this->concatCols[ $this->trimString($col) ] = $str;
        return $this;
    }


    /**
     * Generate the json output that datatables will use to display the table
     * 
     * @return  Array
     */
    public function showTable() {
        // Prepare the data that has been passed
        $this->prepareParams();

        // The object to display data
        $output = array (
            "sEcho" => !is_null($this->request->getParameter('sEcho')) ? intval($this->request->getParameter('sEcho')): 1,
            "iTotalRecords" => $this->getQueryStatement("total")->fetchColumn(),
            "iTotalDisplayRecords" => $this->getQueryStatement("filtered-total")->fetchColumn(),
            "aaData" => array ()
        );

        // Get the data to show in the result set
        $stmt = $this->getQueryStatement();
        while ( $row = $stmt->fetch(\PDO::FETCH_NUM) ) 
            $output['aaData'][] = $row;

        // Display the content
        return $output;
    }


    /**
     * The class constructor
     */
    public function __construct(sfWebRequest $request) {
        $this->selectParams = null;
        $this->fromParams = null;
        $this->innerJoinParams = array ();
        $this->leftJoinParams = array ();
        $this->whereParams = "";
        $this->params = array ();
        $this->concatCols = array ();
        $this->request = $request;
        $this->prepared = false;
        $this->conn = Doctrine_Query::create()->getConnection();
    }

    private $selectParams;    // The columns forming the result set
    private $fromParams;      // The source of the primary result set    
    private $innerJoinParams; // The parameters used to perform an inner join to the primary result set
    private $leftJoinParams;  // The parameters used to perform a left join to the primary result set
    private $whereParams;     // The conditions applied to the result set
    private $params;          // Store the parameters which will be safely inserted to the query
    private $columnNames;     // Specify the column names in the result set
    private $concatCols;      // Specify the columns to concat
    private $request;         // Where the data is 
    private $prepared;        // Whether the parameters defined have been prepared
    private $conn;            // The database connection


    /**
     * This is the function that generates the statement used to display the
     * data consumed by data tables
     */
    private function getQueryStatement($setting=null) {
        // What to select
        $sql = "SELECT COUNT({$this->columnNames[0]}) AS cc";
        if ( $setting!="filtered-total" && $setting!="total" )
            $sql = "SELECT ".$this->selectParams;

        // Where to fetch the data from
        $sql .= $this->fromClause();

        // The filter to apply
        $sql .= $this->setFilter($setting);

        if ( $setting!="filtered-total" && $setting!="total" ) {
            // Set order 
            $sql .= $this->setOrder();

            // Specify the limit
            $sql .= $this->setLimit();
        }

        // Generate the query statement
        $stmt = $this->conn->prepare($sql);

        // Bind params if any
        $p = array ();
        if ( count($this->params) > 0 ) {
            // Ensure that there are paremeters to bind
            foreach ( $this->params as $key=>$v ) {
                if ( strpos($sql, ":$key") !== false )
                    $p[$key] = $v;
            }

            if ( count($p) > 0 )
                $stmt->execute($p);
            else $stmt->execute();
        }
        else {
            $stmt->execute();
        }

        // Return the statement
        return $stmt;
    }


    /**
     * Prepare class parameters that will be used to render the tables
     */
    private function prepareParams() {
        if ( $this->prepared === true ) return;

        // The where params
        $this->whereParams = $this->trimString($this->whereParams);

        // Perform the concat in the various columns
        foreach ( $this->concatCols as $col=>$v ) {
            $this->selectParams = str_replace($col, $v, $this->selectParams);

            $key = array_search($col, $this->columnNames);
            if ( $key !== false ) $this->columnNames[$key] = $v;
        }

        // This step has been completed
        $this->prepared = true;
    }

    /**
     * Allow one to trim excess space characters from the string passed
     *
     * @param   string
     * @return  string
     */
    private function trimString($str) {
        return trim( preg_replace("/[\s]+/S", " ", $str) );
    }


    /**
     * This function specifies which tables the query will fetch information
     * from
     *
     * @return  string
     */
    private function fromClause() {
        // The primary table
        $sql = $this->fromParams['table']." ".$this->fromParams['alias'];

        // The inner joins that will be applied
        if ( count($this->innerJoinParams) > 0 ) {
            foreach ( $this->innerJoinParams as $table )
                $sql .= ", ".$table['table']." ".$table['alias'];
        }

        // The left joins to be applied
        if ( count($this->leftJoinParams) == 0 ) return " FROM $sql";

        // The left join to apply
        $tbl=''; $p='';
        foreach ( $this->leftJoinParams as $table ) {
            if ( $p != '' ) {
                $p .= " AND ";
                $tbl .= ", ";
            }
            $p .= $table['condition'];
            $tbl .= $table['table']." ".$table['alias'];
        }
        $sql .= " LEFT OUTER JOIN ($tbl) ON ($p)";

        // The result set
        return " FROM $sql";
    }

    /**
     * The filter to be applied to the result set
     *
     * @param   mixed
     * @param   string
     */
    private function setFilter($setting) {
        $filter = '';

        if ( $setting != "total" ) {
            $search = $this->request->getParameter('sSearch');
            if ( !is_null($search) && $search != "" ) {
                $orX = "";

                for ( $i=0 ; $i<count($this->columnNames) ; $i++ ) {
                    $bSearchable = $this->request->getParameter("bSearchable_{$i}");

                    if ( !is_null($bSearchable) && $bSearchable == "true" ) {
                        if ( $orX !== "" ) $orX .= " OR ";
                        $orX .= "{$this->columnNames[$i]} LIKE :dtSearch";
                    }
                } 

                if ( $orX !== "" ) {
                    $this->params["dtSearch"] = "%{$search}%";
                    $filter = "({$orX})";
                }
            }

            /* Individual column filtering */
            for ( $i=0 ; $i<count($this->columnNames) ; $i++ ) {
                $bSearchable = $this->request->getParameter("bSearchable_{$i}");
                $sSearch = $this->request->getParameter("sSearch_{$i}");

                if ( !is_null($bSearchable) && $bSearchable == "true" && !is_null($sSearch) && $sSearch != '' ) {
                    if ( $filter !== "" ) $filter .= " AND ";
                    $filter .= "{$this->columnNames[$i]} LIKE :dtSearch_{$i}";

                    $this->params["dtSearch_{$i}"] = "%{$sSearch}%";
                }
            }
        }

        // The where params
        if ( $this->whereParams != "" ) {
            if ( $filter !== "" ) $filter .= " AND ";
            $filter .= "({$this->whereParams})";
        }

        // The inner join params
        foreach ( $this->innerJoinParams as $p ) {
            if ( $filter !== "" ) $filter .= " AND ";
            $filter .= $p['condition'];
        }

        // Return the where clause
        return ( $filter !== "" ) ? " WHERE $filter": "";
    }

    /**
     * This function specifies the order to be followed when retrieving 
     * information from the database
     *
     * @return  string
     */
    private function setOrder() {
        $sOrder = "";
        if ( !is_null( $this->request->getParameter('iSortCol_0') ) ) {
            for ( $i=0 ; $i<intval($this->request->getParameter('iSortingCols')) ; $i++ ) {
                $iSortCol = intval($this->request->getParameter('iSortCol_'.$i));

                if ( $this->request->getParameter( 'bSortable_'.$iSortCol ) == "true" ) {
                    if ( $sOrder != "" ) $sOrder .= ", ";
                    $sOrder .= $this->columnNames[$iSortCol]." ".($this->request->getParameter('sSortDir_'.$i)==='asc' ? 'ASC' : 'DESC');
                }
            }
        }
        return ( $sOrder != "" ) ? " ORDER BY $sOrder": "";
    }

    /**
     * This function is used to define the limit to apply when fetching the data
     *
     * @return  string
     */
    private function setLimit() {
        $iDisplayLength = intval($this->request->getParameter('iDisplayLength'));
        $iDisplayStart = $this->request->getParameter('iDisplayStart');
        $sLimit = " LIMIT 0, 25";

        if ( !is_null($iDisplayStart) && $iDisplayLength >= 10 )
            $sLimit = " LIMIT ".intval($iDisplayStart).", ".$iDisplayLength;
        return $sLimit;
    }
}
