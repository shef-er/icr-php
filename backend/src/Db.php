<?php
declare(strict_types=1);

namespace App;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\InvalidArgumentException; 
use Doctrine\DBAL\ParameterType;

class Db {
    
    /**
     * @var string
     */
    public const ACTORS = 'svarta.actors';

    /**
     * @var string
     */
    public const USERS = 'svarta.users';

    /**
     * @var string
     */
    public const ROLE_DEFAULT = 'guest';

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;
    
    /**
     * @var string
     */
    protected $database;

    /**
     * @param string $host      Database server address
     * @param string $user      User login
     * @param string $password  User password
     * @param string $database  Database name
     */
    public function __construct(
        string $host,
        string $user,
        string $password,
        string $database,
        $driver = 'pdo_pgsql'
    )
    {
        $this->driver;
        $this->host;
        $this->user;
        $this->password;
        $this->database;
    }

    /*
     * @return Doctrine\DBAL\Connection
     */
    public static function getInstance()
    {
        $connection_params = [
            'driver'    => 'pdo_pgsql',
            'host'      => DB_HOST,
            'user'      => DB_USER,
            'password'  => DB_PASS,
            'dbname'    => DB_NAME,
        ];
        return DriverManager::getConnection($connection_params);
    }

    public static function generateUuid()
    {
        return self::getInstance()->fetchColumn('SELECT uuid_generate_v4()');
    }

    public static function insert(
        $tableExpression,
        array $data,
        array $types = []
    )
    {
        return self::getInstance()->insert(
            DB_SCHEMA .'.'. $tableExpression,
            $data,
            $types
        );
    }

    public static function update(
        $tableExpression,
        array $data,
        array $identifier,
        array $types = []
    )
    {
        return self::getInstance()->update(
            DB_SCHEMA .'.'. $tableExpression, 
            $data, 
            $identifier, 
            $types
        );
    }

    public static function delete(
        $tableExpression, 
        array $identifier, 
        array $types = []
    )
    {
        return self::getInstance()->delete(
            DB_SCHEMA .'.'. $tableExpression, 
            $identifier, 
            $types
        );
    }

    public static function select(
        $tableExpression, 
        array $select, 
        array $identifier, 
        array $types = []
    )
    {
        if (empty($identifier)) {
            throw InvalidArgumentException::fromEmptyCriteria();
        }

        $columns = $values = $conditions = [];

        self::addIdentifierCondition($identifier, $columns, $values, $conditions);

        return self::getInstance()->fetchAssoc(
            'SELECT ' . implode(', ', $select) . ' FROM ' . DB_SCHEMA .'.'. $tableExpression . ' WHERE ' . implode(' AND ', $conditions),
            $values,
            is_string(key($types)) ? self::extractTypeValues($columns, $types) : $types
        );

        //TODO: own select simplification
        // return self::getInstance()->select( DB_SCHEMA .'.'. $tableExpression, $identifier, $types );
    }

    /**
     * Adds identifier condition to the query components
     *
     * @param mixed[]  $identifier Map of key columns to their values
     * @param string[] $columns    Column names
     * @param mixed[]  $values     Column values
     * @param string[] $conditions Key conditions
     *
     * @throws DBALException
     */
    private static function addIdentifierCondition(
        array $identifier,
        array &$columns,
        array &$values,
        array &$conditions
    ) : void {
        $platform = self::getInstance()->getDatabasePlatform();

        foreach ($identifier as $columnName => $value) {
            if ($value === null) {
                $conditions[] = $platform->getIsNullExpression($columnName);
                continue;
            }

            $columns[]    = $columnName;
            $values[]     = $value;
            $conditions[] = $columnName . ' = ?';
        }
    }

    /**
     * Extract ordered type list from an ordered column list and type map.
     *
     * @param int[]|string[] $columnList
     * @param int[]|string[] $types
     *
     * @return int[]|string[]
     */
    private static function extractTypeValues(array $columnList, array $types)
    {
        $typeValues = [];

        foreach ($columnList as $columnIndex => $columnName) {
            $typeValues[] = $types[$columnName] ?? ParameterType::STRING;
        }

        return $typeValues;
    }

}
