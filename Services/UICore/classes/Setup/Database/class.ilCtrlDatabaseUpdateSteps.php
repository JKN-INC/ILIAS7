<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlDatabaseUpdateSteps holds the database update-
 * steps affecting ilCtrl tables.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    /**
     * @var ilDBInterface|null
     */
    private ?ilDBInterface $database = null;

    /**
     * @inheritDoc
     */
    public function prepare(ilDBInterface $db) : void
    {
        $this->database = $db;
    }

    /**
     * Deletes the table 'ctrl_calls' from the database, as it is
     * no longer needed.
     */
    public function step_1() : void
    {
        $this->abortIfNotPrepared();
        if ($this->database->tableExists('ctrl_calls')) {
            $this->database->dropTable('ctrl_calls');
        }
    }

    /**
     * Deletes the table 'ctrl_classfile' from the database, as it is
     * no longer needed.
     */
    public function step_2() : void
    {
        $this->abortIfNotPrepared();
        if ($this->database->tableExists('ctrl_classfile')) {
            $this->database->dropTable('ctrl_classfile');
        }
    }

    /**
     * Deletes the table 'ctrl_structure' from the database, as it is
     * no longer needed.
     */
    public function step_3() : void
    {
        $this->abortIfNotPrepared();
        if ($this->database->tableExists('ctrl_structure')) {
            $this->database->dropTable('ctrl_structure');
        }
    }

    /**
     * Halts the execution of these update steps if no database was
     * provided.
     *
     * @throws LogicException if the database update steps were not
     *                        yet prepared.
     */
    private function abortIfNotPrepared() : void
    {
        if (null === $this->database) {
            throw new LogicException(self::class . "::prepare() must be called before db-update-steps execution.");
        }
    }
}