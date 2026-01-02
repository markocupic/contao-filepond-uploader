<?php

declare(strict_types=1);

/*
 * This file is part of Contao Filepond Uploader.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-filepond-uploader
 */

namespace Markocupic\ContaoFilepondUploader\Migration\Version100;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class ChangeColumnType extends AbstractMigration
{
    private const MIGRATION_TEXT = "Column type of tl_form_field.chunkSize changed from 'varchar(255)' to 'integer'.";

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function shouldRun(): bool
    {
        $doMigration = false;

        $schemaManager = $this->connection->createSchemaManager();

        // If the database table itself does not exist, we should do nothing
        if ($schemaManager->tablesExist(['tl_form_field'])) {
            $columns = $schemaManager->listTableColumns('tl_form_field');

            if (isset($columns['chunksize'])) {
                $result = $this->connection->fetchOne('SELECT * FROM tl_form_field WHERE chunkSize = ""');

                if ($result) {
                    $doMigration = true;
                }
            }
        }

        return $doMigration;
    }

    /**
     * @throws Exception
     */
    public function run(): MigrationResult
    {
        $this->connection->update('tl_form_field', ['chunkSize' => 2000000], ['chunkSize' => '']);

        return new MigrationResult(
            true,
            self::MIGRATION_TEXT,
        );
    }
}
