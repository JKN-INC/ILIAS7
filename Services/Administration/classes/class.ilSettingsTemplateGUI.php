<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Administration\SettingsTemplateGUIRequest;

/**
 * Settings template
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSettingsTemplateGUI
{
    protected ilCtrlInterface $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;

    private ilSettingsTemplateConfig $config;
    protected \ILIAS\DI\Container $dic;
    protected ilRbacSystem $rbacsystem;
    protected ilPropertyFormGUI $form;
    protected ilSettingsTemplate $settings_template;
    protected SettingsTemplateGUIRequest $request ;

    public function __construct(ilSettingsTemplateConfig $a_config)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->dic = $DIC;
        $this->rbacsystem = $this->dic->rbac()->system();
        $this->ctrl = $this->dic->ctrl();
        $this->tpl = $this->dic["tpl"];
        $this->toolbar = $this->dic->toolbar();
        $this->lng = $this->dic->language();
        $ilCtrl = $this->dic->ctrl();

        $this->request = new SettingsTemplateGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $ilCtrl->saveParameter($this, array("templ_id"));
        $this->setConfig($a_config);
        $this->readSettingsTemplate();
    }

    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;

        $cmd = $ilCtrl->getCmd("listSettingsTemplates");
        $this->$cmd();
    }

    public function setConfig(ilSettingsTemplateConfig $a_val) : void
    {
        $this->config = $a_val;
    }

    public function getConfig() : ilSettingsTemplateConfig
    {
        return $this->config;
    }

    public function readSettingsTemplate() : void
    {
        if ($this->getConfig()) {
            $this->settings_template = new ilSettingsTemplate(
                $this->request->getTemplateId(),
                $this->getConfig()
            );
        } else {
            $this->settings_template = new ilSettingsTemplate(
                $this->request->getTemplateId()
            );
        }
    }

    public function listSettingsTemplates() : void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if ($this->rbacsystem->checkAccess('write', $this->request->getRefId())) {
            $ilToolbar->addButton(
                $lng->txt("adm_add_settings_template"),
                $ilCtrl->getLinkTarget($this, "addSettingsTemplate")
            );
        }

        $table = new ilSettingsTemplateTableGUI(
            $this,
            "listSettingsTemplates",
            $this->getConfig()->getType()
        );

        $tpl->setContent($table->getHTML());
    }

    public function addSettingsTemplate() : void
    {
        $tpl = $this->tpl;

        $this->initSettingsTemplateForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    public function editSettingsTemplate() : void
    {
        $tpl = $this->tpl;

        $this->initSettingsTemplateForm("edit");
        $this->getSettingsTemplateValues();
        $tpl->setContent($this->form->getHTML());
    }

    public function initSettingsTemplateForm(string $a_mode = "edit") : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(200);
        $ti->setRequired(true);
        // begin-patch lok
        if ($this->settings_template->getAutoGenerated()) {
            $ti->setDisabled(true);
        }
        // end-patch lok
        $this->form->addItem($ti);

        // description
        $ti = new ilTextAreaInputGUI($lng->txt("description"), "description");
        // begin-patch lok
        if ($this->settings_template->getAutoGenerated()) {
            $ti->setDisabled(true);
        }
        $this->form->addItem($ti);

        // hidable tabs
        $tabs = $this->getConfig()->getHidableTabs();
        if (is_array($tabs) && count($tabs) > 0) {
            $sec = new ilFormSectionHeaderGUI();
            $sec->setTitle($lng->txt("adm_hide_tabs"));
            $this->form->addItem($sec);

            foreach ($tabs as $t) {
                // hide tab $t?
                $cb = new ilCheckboxInputGUI($t["text"], "tab_" . $t["id"]);
                $this->form->addItem($cb);
            }
        }

        // settings
        $settings = $this->getConfig()->getSettings();
        if (is_array($settings) && count($settings) > 0) {
            $sec = new ilFormSectionHeaderGUI();
            $sec->setTitle($lng->txt("adm_predefined_settings"));
            $this->form->addItem($sec);

            foreach ($settings as $s) {
                // setting
                $cb = new ilCheckboxInputGUI($s["text"], "set_" . $s["id"]);
                $this->form->addItem($cb);

                switch ($s["type"]) {
                    case ilSettingsTemplateConfig::TEXT:

                        $ti = new ilTextInputGUI($lng->txt("adm_value"), "value_" . $s["id"]);
                        //$ti->setMaxLength();
                        //$ti->setSize();
                        $cb->addSubItem($ti);
                        break;

                    case ilSettingsTemplateConfig::BOOL:
                        $cb2 = new ilCheckboxInputGUI($lng->txt("adm_value"), "value_" . $s["id"]);
                        $cb->addSubItem($cb2);
                        break;

                    case ilSettingsTemplateConfig::SELECT:
                        $si = new ilSelectInputGUI($lng->txt("adm_value"), "value_" . $s["id"]);
                        $si->setOptions($s["options"]);
                        $cb->addSubItem($si);
                        break;

                                        case ilSettingsTemplateConfig::CHECKBOX:
                                                $chbs = new ilCheckboxGroupInputGUI($lng->txt("adm_value"), "value_" . $s["id"]);
                                                foreach ($s['options'] as $key => $value) {
                                                    $chbs->addOption($c = new ilCheckboxInputGUI($value, $key));
                                                    $c->setValue($key);
                                                }
                                                $cb->addSubItem($chbs);
                                                break;
                }

                if ($s['hidable']) {
                    // hide setting
                    $cb_hide = new ilCheckboxInputGUI($lng->txt("adm_hide"), "hide_" . $s["id"]);
                    $cb->addSubItem($cb_hide);
                }
            }
        }

        if ($this->rbacsystem->checkAccess('write', $this->request->getRefId())) {
            // save and cancel commands
            if ($a_mode === "create") {
                $this->form->addCommandButton("saveSettingsTemplate", $lng->txt("save"));
                $this->form->addCommandButton("listSettingsTemplates", $lng->txt("cancel"));
                $this->form->setTitle($lng->txt("adm_add_settings_template"));
            } else {
                $this->form->addCommandButton("updateSettingsTemplate", $lng->txt("save"));
                $this->form->addCommandButton("listSettingsTemplates", $lng->txt("cancel"));
                $this->form->setTitle($lng->txt("adm_edit_settings_template"));
            }
        }

        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    public function getSettingsTemplateValues() : void
    {
        $values = array();

        $values["title"] = $this->settings_template->getTitle();
        $values["description"] = $this->settings_template->getDescription();

        // save tabs to be hidden
        $tabs = $this->settings_template->getHiddenTabs();
        foreach ($tabs as $t) {
            $values["tab_" . $t] = true;
        }

        // save settings values
        $set = $this->settings_template->getSettings();
        foreach ($this->getConfig()->getSettings() as $s) {
            if (isset($set[$s["id"]])) {
                $values["set_" . $s["id"]] = true;

                if ($s['type'] === ilSettingsTemplateConfig::CHECKBOX) {
                    if (!is_array($set[$s["id"]]["value"])) {
                        $ar = unserialize($set[$s["id"]]["value"], ['allowed_classes' => false]);
                    } else {
                        $ar = $set[$s["id"]]["value"];
                    }
                    $values["value_" . $s["id"]] = is_array($ar) ? $ar : array();
                } else {
                    $values["value_" . $s["id"]] = $set[$s["id"]]["value"];
                }
                                
                $values["hide_" . $s["id"]] = $set[$s["id"]]["hide"];
            }
        }
        $this->form->setValuesByArray($values);
    }

    public function saveSettingsTemplate() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->initSettingsTemplateForm("create");
        if ($this->form->checkInput()) {
            $settings_template = new ilSettingsTemplate();
            $settings_template->setType($this->getConfig()->getType());

            $this->setValuesFromForm($settings_template);
            $settings_template->create();

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listSettingsTemplates");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }

    public function updateSettingsTemplate() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        $this->initSettingsTemplateForm("edit");
        if ($this->form->checkInput()) {
            $this->setValuesFromForm($this->settings_template);
            $this->settings_template->update();

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listSettingsTemplates");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }

    public function setValuesFromForm(ilSettingsTemplate $a_set_templ) : void
    {
        // perform update
        $a_set_templ->setTitle($this->form->getInput("title"));
        $a_set_templ->setDescription($this->form->getInput("description"));

        // save tabs to be hidden
        $a_set_templ->removeAllHiddenTabs();
        foreach ($this->getConfig()->getHidableTabs() as $t) {
            if ($this->request->getTab($t["id"])) {
                $a_set_templ->addHiddenTab($t["id"]);
            }
        }

        // save settings values
        $a_set_templ->removeAllSettings();
        foreach ($this->getConfig()->getSettings() as $s) {
            if ($this->request->getSetting($s["id"])) {
                $a_set_templ->setSetting(
                    $s["id"],
                    $this->request->getValue($s["id"]),
                    $this->request->getHide($s["id"])
                );
            }
        }
    }

    public function confirmSettingsTemplateDeletion() : void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        if (count($this->request->getTemplateIds()) === 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listSettingsTemplates");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("adm_sure_delete_settings_template"));
            $cgui->setCancel($lng->txt("cancel"), "listSettingsTemplates");
            $cgui->setConfirm($lng->txt("delete"), "deleteSettingsTemplate");

            foreach ($this->request->getTemplateIds() as $i) {
                $cgui->addItem("tid[]", (string) $i, ilSettingsTemplate::lookupTitle($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function deleteSettingsTemplate() : void
    {
        $ilCtrl = $this->ctrl;

        foreach ($this->request->getTemplateIds() as $i) {
            $templ = new ilSettingsTemplate($i);
            $templ->delete();
        }
        $this->tpl->setOnScreenMessage('success', "msg_obj_modified");
        $ilCtrl->redirect($this, "listSettingsTemplates");
    }
}
