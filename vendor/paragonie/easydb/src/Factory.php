<?php
declare(strict_types=1);

namespace ParagonIE\EasyDB;

use \ParagonIE\EasyDB\Exception as Issues;

/**
 * Class Factory
 *
 * @package ParagonIE\EasyDB
 */
abstract class Factory
{
    /**
     * Create a new EasyDB object based on PDO constructors
     *
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $options
     * @return \ParagonIE\EasyDB\EasyDB
     * @throws Issues\ConstructorFailed
     */
    public static function create(
        string $dsn,
        string $username = null,
        string $password = null,
        array $options = []
    ): EasyDB {
        return static::fromArray([$dsn, $username, $password, $options]);
    }
    
    /**
     * Create a new EasyDB object from array of parameters
     *
     * @param array $config
     * @return \ParagonIE\EasyDB\EasyDB
     * @throws Issues\ConstructorFailed
     */
    public static function fromArray(array $config): EasyDB
    {

        /** @var string $dsn */
        $dsn      = $config[0];
        /** @var string|null $username */
        $username = $config[1] ?? null;
        /** @var string|null $password */
        $password = $config[2] ?? null;
        /** @var array $options */
        $options  = $config[3] ?? [];

        $dbEngine = '';
        $post_query = null;

        if (!\is_string($username)) {
            $username = '';
        }
        if (!\is_string($password)) {
            $password = '';
        }

        // Let's grab the DB engine
        if (strpos($dsn, ':') !== false) {
            $dbEngine = explode(':', $dsn)[0];
        }

        /** @var string $post_query */
        $post_query = '';

        // If no charset is specified, default to UTF-8
        switch ($dbEngine) {
            case 'mysql':
                if (\strpos($dsn, ';charset=') === false) {
                    $dsn .= ';charset=utf8mb4';
                }
                break;
            case 'pgsql':
                $post_query = "SET NAMES 'UNICODE'";
                break;
        }

        try {
            $pdo = new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            if (\strpos((string) $e->getMessage(), 'could not find driver') !== false) {
                throw (new Issues\ConstructorFailed(
                    'Could not create a PDO connection. Is the driver installed/enabled?'
                ))->setRealException($e);
            }
            
            if (\strpos((string) $e->getMessage(), 'unknown database') !== false) {
                throw (new Issues\ConstructorFailed(
                    'Could not create a PDO connection. Check that your database exists.'
                ))->setRealException($e);
            }
            
            // Don't leak credentials directly if we can.
            throw (new Issues\ConstructorFailed(
                'Could not create a PDO connection. Please check your username and password.'
            ))->setRealException($e);
        }

        if (!empty($post_query)) {
            $pdo->query($post_query);
        }

        return new EasyDB($pdo, $dbEngine, $options);
    }
}
