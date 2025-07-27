<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Model
 */
abstract class EnovaModel extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'sqlsrv';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected static function booted()
    {
        static::checkAndFailoverConnection();
    }

    /**
     * Sprawdza połączenie z hostem Primary i przełącza na Backup w razie problemów.
     */
    protected static function checkAndFailoverConnection()
    {
        $connectionName = (new static)->connection ?? config('database.default');
        $primaryHost = env('DB_ENOOVA_HOST');
        $backupHost = env('DB_ENOVA_HOST_BACKUP');

        // Sprawdzamy tylko dla połączenia zdalnego MSSQL
        if ($connectionName !== 'sqlsrv' || !$primaryHost || !$backupHost) {
            return;
        }

        // Ustaw host na Primary
        config(["database.connections.{$connectionName}.host" => $primaryHost]);
        try {
            DB::connection($connectionName)->getPdo();
        } catch (\Exception $e) {
            // Przełącz na host zapasowy
            config(["database.connections.{$connectionName}.host" => $backupHost]);
        }
    }

    /**
     * Override save method to prevent saving
     */
    public function save(array $options = [])
    {
        throw new \Exception(Lang::get('models.read_only.save'));
    }

    /**
     * Override update method to prevent updating
     */
    public function update(array $attributes = [], array $options = [])
    {
        throw new \Exception(Lang::get('models.read_only.update'));
    }

    /**
     * Override delete method to prevent deleting
     */
    public function delete()
    {
        throw new \Exception(Lang::get('models.read_only.delete'));
    }

    /**
     * Override create method to prevent creating
     */
    public static function create(array $attributes = [])
    {
        throw new \Exception(Lang::get('models.read_only.create'));
    }

    /**
     * Override forceFill method to prevent mass assignment
     */
    public function forceFill(array $attributes)
    {
        throw new \Exception(Lang::get('models.read_only.force_fill'));
    }
}
