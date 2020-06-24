<?php
declare(strict_types=1);

namespace App;

use App\Db;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use DI\Container;
use Doctrine\DBAL\Connection;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpBadRequestException;

class Api 
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var Container
     */
    protected $c;

    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var string
     */
    protected $schema_name;

    /**
     * @param App       $app
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->c = $container;
        $this->app = $this->c->get(App::class);
        $this->db = $this->c->get(Connection::class);
        // $this->db = Db::getInstance();

        $this->schema_name = DB_SCHEMA;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param string   $table_name
     * @return array
     */
    public function selectTable(
        Request $request,
        Response $response,
        string $table_name
    ) : array 
    {
        $table_path = $this->schema_name .".". $table_name;

        $table_columns = $this->selectTableColumns($request, $table_name);

        $request_params_get = $request->getQueryParams();
        if( !empty($request_params_get) ) {
            $data_columns = array_intersect(
                array_keys($request_params_get), 
                $table_columns
            );
        }
        if( 
            !isset($data_columns) 
            || empty($data_columns) 
        ) {
            $data_columns = array('*');
        }

        $sql_select = "
            SELECT ". implode(',', $data_columns) ." 
            FROM $table_path
        ";
        $data = $this->db->executeQuery($sql_select)->fetchAll();

        return $data;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param string   $proc_name
     * @return array
     */
    public function selectProcedure(
        Request $request,
        Response $response,
        string $proc_name
    ) : array 
    {
        $proc_path = $this->schema_name .".". $proc_name;

        $proc_params = $this->selectProcedureParams($request, $proc_name);

        $request_params_get = $request->getQueryParams();

        // filter request parameters according to procedure definition
        if( !empty($request_params_get) ) {
            $data_params = array_intersect_key(
                $request->getQueryParams(), 
                array_flip($proc_params)
            );
            $params_num = count($data_params);
            $sql_part_params = implode(',', array_fill(0, $params_num, '?' ) );
        } else {
            $data_params = array();
            $sql_part_params = "";
        }

        $sql_select = "
            SELECT *
            FROM $proc_path($sql_part_params)
        ";
        $data = $this->db->executeQuery(
                    $sql_select,
                    array_values($data_params)
                )->fetchAll();

        // decoding json string values including one level nested values
        array_walk_recursive($data, function (&$value) {
            if (
                is_string($value)
                && strstr($value, '{')
            ) {
                $value = json_decode($value);
            }
        });

        return $data;
    }

    /**
     * @param Request $request
     * @param string $table_name
     * @return array
     * @throws HttpNotFoundException
     */
    private function selectTableColumns( 
        Request $request, 
        string $table_name 
    ) : array 
    {
        $sql_select = "
            SELECT column_name
            FROM information_schema.columns
            WHERE table_schema = '$this->schema_name'
            AND table_name   = '$table_name';
        ";
        $result_select = $this->db->executeQuery($sql_select)->fetchAll();

        if ( empty($result_select) ) {
            throw new HttpNotFoundException(
                $request,
                "relation \"$this->schema_name.$table_name\" does not exist"
            );
        }

        $table_columns = array_column(
            $result_select,
            'column_name'
        );
        return (array) $table_columns;
    }

    /**
     * @param string $proc_name
     * @return array
     * @throws HttpNotFoundException
     */
    private function selectProcedureParams(Request $request, string $proc_name ) : array
    {
        $sql_select = "
            SELECT proargnames
            FROM pg_proc
            WHERE proname = '$proc_name';
        ";
        $result_select = $this->db->fetchColumn($sql_select);

        if ( empty($result_select) ) {
            throw new HttpNotFoundException(
                $request,
                "procedure \"$this->schema_name.$proc_name\" does not exist"
            );
        }

        // removes '{}' from  parameters list in $result_select 
        // and explodes to array values
        if ( 
            is_string($result_select)
            && strstr($result_select, "{")
            && strstr($result_select, "}")
        ) {
            $proc_params = explode(',', trim( $result_select, "{}" ) );
        } else {
            $proc_params = array();
        }

        return (array) $proc_params;
    }
    

    /**
     * @param Request  $request
     * @param Response $response
     * @throws HttpBadRequestException
     */
    public function upsertWorkerReport(
        Request $request,
        Response $response
    ) {
        $table_name = 'worker_report';
        $key_columns = [
            'date',
            'worker'
        ];

        $table_path = $this->schema_name .".". $table_name;

        $request_params_post = $request->getParsedBody();

        if( !empty($request_params_post) ) {
            $table_columns = $this->selectTableColumns($request, $table_name);

            $data_values = (array) array_intersect_key(
                $request_params_post[0], 
                array_flip( $table_columns )
            );
        }

        $api_token = $request_params_post['api_token'] ?? '';

        //TODO: finish permissions system
        // $permissons = $this->apiTokenVerify($api_token, $table_name);
        // if ( strpos($permissons, 'w') === false ) {
        //     throw new HttpBadRequestException($request, 'Wrong parameters.');
        // }

        if ( 
            !is_array($data_values) 
            || empty($data_values)
        ) {
            throw new HttpBadRequestException($request, 'Wrong parameters.');
        }

        // building record query WHERE part
        $where_columns = [];
        $where_values = [];
        foreach ($key_columns as $column) {
            if (array_key_exists($column, $data_values)) {
                $where_columns[] = " $column = ? ";
                $where_values[] = $data_values[$column];
            }
        }
        $sql_part_where = " WHERE ". implode(' AND ', $where_columns);

        // Checking is record already exists
        // building SELECT query
        $sql_select = "
            SELECT *
            FROM $table_path
            $sql_part_where
            ;
        ";
        $query_select = $this->db->executeQuery(
            $sql_select, 
            $where_values
        );
        $result_select = $query_select->fetchAll();

        if( 
            is_array($result_select)
            && !empty($result_select)
            && is_array($result_select[0]) 
        ) {
            $values_to_insert = array_diff_assoc($data_values, $result_select[0]);

            if (
                is_array($values_to_insert) 
                && count($values_to_insert) > 0 
            ) {

                $is_hours = array_key_exists('hours', $values_to_insert);
                $is_status = array_key_exists('status', $values_to_insert);

                if ( $is_hours && !$is_status ) {
                    $values_to_insert['status'] = null;
                }
                elseif ( $is_status && !$is_hours ){
                    $values_to_insert['hours'] = null;
                }

                $sql_set_keys = array_keys($values_to_insert);
                $sql_set_values = array_values($values_to_insert);
                foreach ($sql_set_keys as $key => $value) {
                    $sql_set_keys[$key] = "$value = ?";
                }
                $sql_part_set = implode(',', $sql_set_keys);

                $sql_update = "
                    UPDATE $table_path
                    SET $sql_part_set
                    $sql_part_where
                    ;
                ";
                $result = $this->db->executeUpdate(
                    $sql_update,
                    array_merge(
                        $sql_set_values,
                        $where_values
                    )
                );
            }
        } 
        else {
            $sql_insert = " 
                INSERT INTO $table_path (". implode(',', array_keys($data_values)) .")
                VALUES (". implode(",", array_fill(0, count($data_values), '?') ) .")
                ;
            ";
            $result = $this->db->executeUpdate(
                $sql_insert,
                array_values($data_values)
            );
        }
    }

    public function apiTokenVerify(string $api_token, string $endpoint): string
    {
        $actor_role = $this->getActorRoleByApiToken($api_token);
        $config = (array)$this->c->get('settings')['permissions'];

        if(
            !array_key_exists($endpoint, $config) 
            || !array_key_exists($actor_role, $config[$endpoint])
        ) {
            return "";
        }

        return $config[$endpoint][$actor_role];
    }

    private function getActorRoleByApiToken(string $api_token): string
    {
        $sql_select = "
            SELECT a.role FROM actors AS a
            JOIN api_tokens AS at 
            ON at.user_id = a.id
            WHERE table_schema = '$this->schema_name'
            AND at.api_token = '$api_token';
        ";
        $result_select = $this->db->fetchColumn($sql_select);
        return (string) $result_select;
    }

}