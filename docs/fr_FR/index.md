# Plugin nasInfos pour Jeedom

    Ce plugin permet de récupérer les informations de votre NAS, cette récupération de données s'effectue par le biais 
    de SNMP.

## 1. Configuration du plugin

    Rien de particulier dans la configuration de ce plugin, vous pouvez choisir le cron qui vous convient le mieux
    pour la cyclique de récupération des données. Une action "Rafraichir" vous permettra de récupérer les données 
    à la demande.

## 2. Configuration de l'équipement

    Vous devez spécifier l'adresse IP de votre serveur NAS ainsi que la communauté SNMP sur laquelle seront lus
    les Oids qui seront configurés dans les commandes de l'équipement

    Pour permettre un échange entre utilisateurs du plugin, le plugin permet l'exportation/importation des commandes 
    de l'équipement.

    Pour l'exportation, on spécifie le nom de fichier qui contiendra les données exportées et on clique sur le bouton
    "Exécuter" à droite du nom.

    Pour l'importation, on choisit le fichier à importer et on clique sur le bouton "Exécuter" à droite de la boite déroulante.

    L'onglet "Commandes" contient déjà une action "Refresh" qui permettra le rafraichissement des données à la demande ainsi qu'une
    information "Etat" qui donne le résultat du ping à l'adresse IP spécifiée dans l'équipement. "En ligne" ou "Hors ligne".

    Il est possible d'ajouter des commandes ( seules les commandes infos seront traitées ) et de spécifier l'oid correspondant.