<?php
/**
 * Subscriptions
 *
 * @author edgebal
 */

namespace Minds\Core\Channels;

use Minds\Core\Data\Cassandra\Client as CassandraClient;
use Minds\Core\Data\Cassandra\Prepared\Custom;
use Minds\Core\Di\Di;
use Minds\Entities\User;

class Subscriptions
{
    /** @var CassandraClient */
    protected $db;

    /** @var User */
    protected $user;

    /**
     * Subscriptions constructor.
     * @param CassandraClient $db
     */
    public function __construct(
        $db = null
    )
    {
        $this->db = $db ?: Di::_()->get('Database\Cassandra\Cql');
    }

    /**
     * @param User $user
     * @return Subscriptions
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param $opts
     * @return string[]
     * @throws \Exception
     */
    public function getList($opts)
    {
        $opts = array_merge([
            'limit' => 5000,
        ], $opts);

        if (!$this->user) {
            throw new \Exception('Invalid user');
        }

        $cql = "SELECT * FROM friends WHERE key = ? LIMIT ?";
        $values = [
            (string) $this->user->guid,
            intval($opts['limit'] ?: 1)
        ];

        $prepared = new Custom();
        $prepared->query($cql, $values);

        try {
            $rows = $this->db->request($prepared);

            $result = [];

            foreach ($rows as $row) {
                $result[] = $row['column1'];
            }

            return $result;
        } catch (\Exception $e) {
            error_log($e);
            return [];
        }
    }
}
