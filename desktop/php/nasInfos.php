<?php
  if (!isConnect('admin')) 
  {
	 throw new Exception('{{401 - Accès non autorisé}}');
  }

  $plugin = plugin::byId('nasInfos');
  sendVarToJS('eqType', $plugin->getId());
  $eqLogics = eqLogic::byType($plugin->getId());

?>

<div class="row row-overflow">
  <div class="col-xs-12 eqLogicThumbnailDisplay">
    <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction logoPrimary" data-action="add">
        <i class="fas fa-plus-circle"></i>
        <br>
        <span>{{Ajouter}}</span>
      </div>
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
        <br>
        <span>{{Configuration}}</span>
      </div>
    </div>
    <legend><i class="fas fa-table"></i> {{Mes équipements}}</legend>
	  <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
    <div class="eqLogicThumbnailContainer">
      <?php

        // Affiche la liste des équipements
        //
        foreach ($eqLogics as $eqLogic) 
        {
	        $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
	        echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
	        echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
	        echo '<br>';
	        echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
	        echo '</div>';
        }
      ?>
    </div>
  </div>

  <div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
	   		<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
      <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
    </ul>
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <br/>
        <form class="form-horizontal">
          <fieldset>
            <legend><i class="fas fa-wrench"></i> {{Général}}</legend>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" >{{Objet parent}}</label>
              <div class="col-sm-3">
                <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                  <option value="">{{Aucun}}</option>
                  <?php
                    foreach (jeeObject::all() as $object) 
                    {
	                    echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                    }
                  ?>
                </select>
              </div>
            </div>
	          <div class="form-group">
              <label class="col-sm-3 control-label">{{Catégorie}}</label>
              <div class="col-sm-9">
                <?php
                  foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) 
                  {
                    echo '<label class="checkbox-inline">';
                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                    echo '</label>';
                  }
                ?>
              </div>
            </div>
	          <div class="form-group">
		          <label class="col-sm-3 control-label"></label>
		          <div class="col-sm-9">
			          <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
			          <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
		          </div>
	          </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Adresse IP du NAS}}</label>
              <div class="col-sm-4">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="adresse_ip"
                  placeholder="{{Adresse IP}}" />
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Communauté SNMP}}</label>
              <div class="col-sm-4">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="community"
                  placeholder="{{Community}}" />
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Importation}}</label>
              <div class="col-sm-4">
                <select class="configKey form-control" data-l1key="visuel" id="idListeNas">

                  <?php

                    $path = '/var/www/html/plugins/nasInfos/data/';
                    $dp = opendir($path);
                    $i=0;
                    while ($file = readdir($dp)) {
                        $len = strlen($file);
                        if ($len > 5) {
                            $ext = substr($file, $len-5, 5);
                            if ($ext === '.json') {
                              $template = substr($file, 0, $len-5);
                              if ( $template !== 'conversions') {
                                $ListTemplates[$i]=$template;
                                $i++;
                              }
                            }
                        }
                    }
            
                    closedir($dp);
                    sort($ListTemplates);
                    $i=0;
                    while ($i < count($ListTemplates)) {
                        echo '<option value="'.$ListTemplates[$i].'">'.$ListTemplates[$i].'</option>';
                        $i++;
                    }
                  ?>
                </select>
              </div>
              <div class="col-sm-4">
                <a class="btn btn-primary" id="idImporter"><i class="fa fa-cloud-upload-alt"></i> {{Exécuter}}</a>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Exportation}}</label>
              <div class="col-sm-4">
                <input type="text" id="idNomNas" class="form-control" placeholder="{{Nom Nas}}" />
              </div>
              <div class="col-sm-4">
                <a class="btn btn-primary" id="idExporter"><i class="fa fa-save"></i> {{Exécuter}}</a>
              </div>
            </div>
          </fieldset>
        </form>
      </div>
      <div role="tabpanel" class="tab-pane" id="commandtab">
        <a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/><br/>
        <table id="table_cmd" class="table table-bordered table-condensed">
        <thead>
            <tr>
              <th>{{Id}}</th>
              <th>{{Nom}}</th>
              <th>{{Type}}</th>
              <th>{{OID}}</th>
              <th>{{Paramètres}}</th>
              <th>{{Etat}}</th>
              <th>{{Action}}</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, nom_du_plugin) -->
<?php include_file('desktop', 'nasInfos', 'js', 'nasInfos');?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js');?>
