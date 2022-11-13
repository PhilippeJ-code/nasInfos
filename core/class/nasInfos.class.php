<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once __DIR__  . '/../../../../core/php/core.inc.php';

class nasInfos extends eqLogic
{
    // Statut des dépendances
    //
    public static function dependancy_info()
    {
        $return = array();
        $return['log'] = 'nasInfos_dep';
        $cmd = "dpkg -l php*-snmp*| grep snmp";
        exec($cmd, $output, $returnVar);
        if ($output[0] != "") {
            $return['state'] = 'ok';
        } else {
            $return['state'] = 'nok';
        }
        return $return;
    }

    // Installation des dépendances
    //
    public function dependancy_install()
    {
        log::add('nasInfos', 'info', 'Installation des dependances php-snmp');
        passthru('sudo apt install php-snmp -y >> ' . log::getPathToLog('printerStatus_dep') . ' 2>&1 &');
    }

    public function rafraichir()
    {
        $adresseIp = $this->getConfiguration('adresse_ip', '');
        $community = $this->getConfiguration('community', '');

        $array = array();

        $json_file = __DIR__ . '/../../data/conversions.json';

        $string = file_get_contents($json_file);
        $array = json_decode($string, true);

        if (($adresseIp === '') || (community === '')) {
            return;
        }

        $ping_check=exec('/bin/ping -c2 -q -w2 '.$adresseIp.' | grep transmitted | cut -f3 -d"," | cut -f1 -d"," | cut -f1 -d"%"');
        if ($ping_check != 0) {
            $this->getCmd(null, 'state')->event('Hors ligne');
            return;
        } else {
            $this->getCmd(null, 'state')->event('En ligne');
        }

        try {
            snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
            foreach ($this->getCmd('info') as $cmd) {
                $logicalId = $cmd->getLogicalId();
                if (($logicalId != 'refresh') && ($logicalId != 'state')) {
                    $oid = $cmd->getConfiguration('oid', '');
                    if ($oid !== '') {
                        $value = snmp2_get($adresseIp, $community, $oid, 250000, 1);

                        $n = count($array);
                        for ($i=0; $i<$n; $i++) {
                            $oidConv = $array[$i]['oid'];
                            $defaut = $array[$i]['defaut'];
                            if ($oid == $oidConv) {
                                $nn = count($array[$i]['conversions']);

                                $bFind = false;
                                for ($j=0; $j<$nn; $j++) {
                                    if ($value == $array[$i]['conversions'][$j]['de']) {
                                        $value = $array[$i]['conversions'][$j]['vers'];
                                        $bFind = true;
                                        break;
                                    }
                                }
                                if ($bFind == false) {
                                    $value = $defaut;
                                }
                            }
                        }

                        if ($value !== false) {
                            $cmd->event($value);
                        }
                    }
                }
            }
        } catch (Throwable $t) {
            log::add('nasInfos', 'error', $t->getMessage());
        } catch (Exception $e) {
            log::add('nasInfos', 'error', $e->getMessage());
        }

        return;
    }

    public function importer($nomNas)
    {
        $array = array();

        $json_file = __DIR__ . '/../../data/'.$nomNas.'.json';

        $string = file_get_contents($json_file);
        $array = json_decode($string, true);

        foreach ($this->getCmd('info') as $cmd) {
            $logicalId = $cmd->getLogicalId();
            if (($logicalId != 'refresh') && ($logicalId != 'state')) {
                $cmd->remove();
            }
        }

        $n = count($array);
        for ($i=0; $i<$n; $i++) {
            $obj = new nasInfosCmd();
            $obj->setName($array[$i]['name']);
            $obj->setEqLogic_id($this->getId());
            $obj->setConfiguration('oid', $array[$i]['oid']);
            $obj->setType('info');
            $obj->setSubType($array[$i]['subType']);
            $obj->save();
        }

        $this->save();

        return $array;
    }

    public function exporter($nomNas)
    {
        $array = array();

        $json_file = __DIR__ . '/../../data/'.$nomNas.'.json';

        foreach ($this->getCmd('info') as $cmd) {
            $logicalId = $cmd->getLogicalId();
            if (($logicalId != 'refresh') && ($logicalId != 'state')) {
                $array[] = array('name'=>$cmd->getName(),'oid'=>$cmd->getConfiguration('oid', ''),'subType'=>$cmd->getSubType());
            }
        }

        $json = json_encode($array, JSON_PRETTY_PRINT);
        file_put_contents($json_file, $json);

        return array();
    }

    public static function periodique()
    {
        foreach (self::byType('nasInfos') as $nasInfos) {
            if ($nasInfos->getIsEnable() == 1) {
                $cmd = $nasInfos->getCmd(null, 'refresh');
                if (!is_object($cmd)) {
                    continue;
                }
                $cmd->execCmd();
            }
        }
    }

    public static function cron()
    {
        self::periodique();
    }

    public static function cron5()
    {
        self::periodique();
    }

    public static function cron10()
    {
        self::periodique();
    }

    public static function cron15()
    {
        self::periodique();
    }

    public static function cron30()
    {
        self::periodique();
    }

    public static function cronHourly()
    {
        self::periodique();
    }

    public static function cronDaily()
    {
        self::periodique();
    }


    // Fonction exécutée automatiquement avant la création de l'équipement
    //
    public function preInsert()
    {
    }

    // Fonction exécutée automatiquement après la création de l'équipement
    //
    public function postInsert()
    {
    }

    // Fonction exécutée automatiquement avant la mise à jour de l'équipement
    //
    public function preUpdate()
    {
    }

    // Fonction exécutée automatiquement après la mise à jour de l'équipement
    //
    public function postUpdate()
    {
    }

    // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
    //
    public function preSave()
    {
    }

    // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
    //
    public function postSave()
    {
        $obj = $this->getCmd(null, 'refresh');
        if (!is_object($obj)) {
            $obj = new nasInfosCmd();
            $obj->setName(__('Rafraichir', __FILE__));
        }
        $obj->setEqLogic_id($this->getId());
        $obj->setLogicalId('refresh');
        $obj->setType('action');
        $obj->setSubType('other');
        $obj->save();

        $obj = $this->getCmd(null, 'state');
        if (!is_object($obj)) {
            $obj = new nasInfosCmd();
            $obj->setName(__('Etat', __FILE__));
            $obj->setIsVisible(1);
            $obj->setIsHistorized(0);
        }
        $obj->setEqLogic_id($this->getId());
        $obj->setType('info');
        $obj->setSubType('string');
        $obj->setLogicalId('state');
        $obj->save();
    }

    // Fonction exécutée automatiquement avant la suppression de l'équipement
    //
    public function preRemove()
    {
    }

    // Fonction exécutée automatiquement après la suppression de l'équipement
    //
    public function postRemove()
    {
    }
}

class nasInfosCmd extends cmd
{
    // Exécution d'une commande
    //
    public function execute($_options = array())
    {
        $eqlogic = $this->getEqLogic();
        switch ($this->getLogicalId()) {
            case 'refresh':
                $info = $eqlogic->rafraichir();
                break;
        }
    }
}
