<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlCommandClass1TestGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilCtrlCommandClass1TestGUI: ilCtrlBaseClass1TestGUI, ilCtrlBaseClass2TestGUI
 * @ilCtrl_Calls      ilCtrlCommandClass1TestGUI: ilCtrlCommandClass2TestGUI
 */
class ilCtrlCommandClass1TestGUI implements ilCtrlSecurityInterface
{
    private ilCtrlInterface $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
    }

    /**
     * @inheritDoc
     */
    public function getUnsafeGetCommands() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getSafePostCommands() : array
    {
        return [];
    }

    public function executeCommand() : string
    {
        switch ($this->ctrl->getNextClass($this)) {
            case strtolower(ilCtrlCommandClass2TestGUI::class):
                return $this->ctrl->forwardCommand(new ilCtrlCommandClass2TestGUI());

            default:
                $cmd = $this->ctrl->getCmd();
                return $this->{$cmd}();
        }
    }

    private function index() : string
    {
        return "Hello World!";
    }
}