<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.r.l.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

include_once __DIR__.'/../../core.php';

$google = setting('Google Maps API key');

/*
if (!empty($google)) {
    echo '
<script src="//maps.googleapis.com/maps/api/js?libraries=places&key='.$google.'"></script>';
}
*/

echo '
<form action="" method="post" role="form" id="form_sedi">
    <input type="hidden" name="id_plugin" value="'.$id_plugin.'">
    <input type="hidden" name="id_parent" value="'.$id_parent.'">
    <input type="hidden" name="id_record" value="'.$record['id'].'">
	<input type="hidden" name="backto" value="record-edit">
	<input type="hidden" name="op" value="updatesede">

	<div class="row">
		<div class="col-md-12">
			{[ "type": "text", "label": "'.tr('Nome sede').'", "name": "nomesede", "required": 1, "value": "$nomesede$" ]}
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			{[ "type": "text", "label": "'.tr('Indirizzo').'", "name": "indirizzo", "id": "indirizzo_", "required": 0, "value": "$indirizzo$" ]}
		</div>

		<div class="col-md-6">
            {[ "type": "text", "label": "'.($record['tipo_anagrafica'] == 'Ente pubblico' ? tr('Codice unico ufficio') : tr('Codice destinatario')).'", "name": "codice_destinatario", "required": 0, "class": "text-center text-uppercase alphanumeric-mask", "value": "$codice_destinatario$", "maxlength": '.($record['tipo_anagrafica'] == 'Ente pubblico' ? '6' : '7').', "help": "'.tr('<b>Attenzione</b>: per impostare il codice specificare prima \'Tipologia\' e \'Nazione\' dell\'anagrafica:<br><ul><li>Ente pubblico (B2G/PA) - Codice Univoco Ufficio (www.indicepa.gov.it), 6 caratteri</li><li>Azienda (B2B) - Codice Destinatario, 7 caratteri</li><li>Privato (B2C) - viene utilizzato il Codice Fiscale</li></ul>').'", "readonly": "'.intval($record['iso2'] ? $record['iso2'] != 'IT' : 0).'" ]}
        </div>
	</div>

	<div class="row">
		<div class="col-md-6">
			{[ "type": "text", "label": "'.tr('Città').'", "name": "citta", "id": "citta_", "value": "$citta$", "required": 1 ]}
		</div>

		<div class="col-md-2">
			{[ "type": "text", "label": "'.tr('C.A.P.').'", "name": "cap", "value": "$cap$" ]}
		</div>

		<div class="col-md-2">
			{[ "type": "text", "label": "'.tr('Provincia').'", "name": "provincia", "value": "$provincia$", "maxlength": 2, "class": "text-center provincia-mask text-uppercase", "extra": "onkeyup=\"this.value = this.value.toUpperCase();\"" ]}
		</div>

		<div class="col-md-2">
			{[ "type": "number", "label": "'.tr('Km').'", "name": "km", "value": "$km$" ]}
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			{[ "type": "select", "label": "'.tr('Nazione').'", "name": "id_nazione", "value": "$id_nazione$", "ajax-source": "nazioni" ]}
		</div>

		<div class="col-md-6">
			{[ "type": "text", "label": "'.tr('Telefono').'", "name": "telefono", "value": "$telefono$" ]}
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			{[ "type": "text", "label": "'.tr('Fax').'", "name": "fax", "value": "$fax$" ]}
		</div>

		<div class="col-md-6">
			{[ "type": "text", "label": "'.tr('Cellulare').'", "name": "cellulare", "value": "$cellulare$" ]}
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			{[ "type": "text", "label": "'.tr('Indirizzo email').'", "name": "email", "value": "$email$", "class": "email-mask", "validation": "email" ]}
		</div>

        <div class="col-md-3">
            {[ "type": "checkbox", "label": "'.tr('Opt-out per newsletter').'", "name": "disable_newsletter", "id": "disable_newsletter_m", "value": "'.empty($record['enable_newsletter']).'", "help": "'.tr("Blocco per l'invio delle email.").'" ]}
        </div>

		<div class="col-md-3">
			{[ "type": "select", "label": "'.tr('Zona').'", "name": "idzona", "ajax-source": "zone",  "value": "$idzona$", "placeholder": "'.tr('Nessuna zona').'", "icon-after": "add|'.Modules::get('Zone')['id'].'" ]}
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			{[ "type": "textarea", "label": "'.tr('Note').'", "name": "note", "value": "$note$" ]}
		</div>
	</div>';

if (!empty($google)) {
    echo '
	<div class="row">
		<div class="col-md-6" id="geocomplete">
			{[ "type": "text", "label": "'.tr('Indirizzo Google').'", "name": "gaddress", "value": "$gaddress$", "extra": "data-geo=\'formatted_address\'" ]}
		</div>

		<div class="col-md-2">
			{[ "type": "text", "label": "'.tr('Latitudine').'", "name": "lat", "id": "lat_", "value": "$lat$", "extra": "data-geo=\'lat\'", "class": "text-right" ]}
		</div>

		<div class="col-md-2">
			{[ "type": "text", "label": "'.tr('Longitudine').'", "name": "lng", "id": "lng_", "value": "$lng$", "extra": "data-geo=\'lng\'", "class": "text-right" ]}
		</div>';

    // Vedi su google maps
    if (!empty($record['indirizzo']) || (empty($record['citta']))) {
        echo '
			<div  class="btn-group col-md-2"  >
				<label>&nbsp;</label><br>
				<a class="btn btn-info" title="'.tr('Mostra la sede su Google Maps').'" onclick="window.open(\'https://maps.google.com/maps/search/\'+encodeURI( $(\'#indirizzo_\').val() )+\', \'+encodeURI( $(\'#citta_\').val() ) );">&nbsp;<i class="fa fa-map-marker">&nbsp;</i></a>
			';

        echo '
				<a title="'.tr('Calcola percoso da sede legale a questa sede').'" class="btn btn-primary btn-secondary" onclick="window.open(\'https://maps.google.com/maps/dir/\'+encodeURI( $(\'#indirizzo_\').val() )+\', \'+encodeURI( $(\'#citta_\').val() )+\'/\'+encodeURI( $(\'#indirizzo\').val() )+\',\'+encodeURI( $(\'#citta\').val() )+\',8z\');"><i class="fa fa-car"></i></a>
			</div>';
    }

    echo '
    </div>';

    if (!empty($record['gaddress']) || (!empty($record['lat']) && !empty($record['lng']))) {
        echo '
    <div id="map" style="height:400px; width:100%"></div><br>';
    }
} else {
    echo '
    <div class="alert alert-info">
        '.Modules::link('Impostazioni', null, tr('Per abilitare la visualizzazione delle anagrafiche nella mappa, inserire la Google Maps API Key nella scheda Impostazioni'), true, null, true, null, '&search=Google Maps API key').'.
    </div>';
}

// Permetto eliminazione tipo sede solo se non è utilizzata da nessun'altra parte nel gestionale
$elementi = $dbo->fetchArray('SELECT `zz_users`.`idgruppo` AS `id`, "Utente" AS tipo, NULL AS dir FROM `zz_user_sedi` INNER JOIN `zz_users` ON `zz_user_sedi`.`id_user`=`zz_users`.`id` WHERE `zz_user_sedi`.`idsede` = '.prepare($id_record).'
UNION
SELECT `an_referenti`.`id` AS `id`, "Referente" AS tipo, NULL AS dir FROM `an_referenti` WHERE `an_referenti`.`idsede` = '.prepare($id_record).'
UNION
SELECT `co_documenti`.`id` AS `id`, "Fattura" AS tipo, `co_tipidocumento`.`dir` AS dir FROM `co_documenti` JOIN `co_tipidocumento` ON `co_tipidocumento`.`id` = `co_documenti`.`idtipodocumento` WHERE `co_documenti`.`idsede_destinazione` = '.prepare($id_record).'
ORDER BY `id`');

if (!empty($elementi)) {
    echo '
	<div class="box box-warning collapsable collapsed-box">
		<div class="box-header with-border">
			<h3 class="box-title"><i class="fa fa-warning"></i> '.tr('Campi collegati: _NUM_', [
				'_NUM_' => count($elementi),
			]).'</h3>
			<div class="box-tools pull-right">
				<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
			</div>
		</div>
		<div class="box-body">
			<ul>';

		foreach ($elementi as $elemento) {
			$descrizione = $elemento['tipo'];
			$id = $elemento['id'];
			if (in_array($elemento['tipo'], ['Fattura'])) {
				$modulo = ($elemento['dir'] == 'entrata') ? 'Fatture di vendita' : 'Fatture di acquisto';
				$link = Modules::link($modulo, $id, $descrizione);
			} elseif (in_array($elemento['tipo'], ['Referente'])) {
				$link = Plugins::link('Referenti', $id_parent, $descrizione);
			} else {
				$link = Modules::link('Utenti e permessi', $id, $descrizione);
			}

			echo '
				<li>'.$link.'</li>';
		}

		echo '
			</ul>
		</div>
	</div>';

    $disabled = 'disabled';
}

echo '
	<!-- PULSANTI -->
	<div class="row">
		<div class="col-md-12">
            <button type="button" class="btn btn-danger '.$disabled.'" onclick="rimuoviSede(this)">
                <i class="fa fa-trash"></i> '.tr('Elimina').'
            </button>

			<button type="submit" class="btn btn-primary pull-right"><i class="fa fa-edit"></i> '.tr('Modifica').'</button>
		</div>
	</div>
</form>';

echo '
<script>$(document).ready(init)</script>

<script>
if(window.google){
    initGeocomplete();
} else {
    $.getScript("//maps.googleapis.com/maps/api/js?libraries=places&key='.$google.'", function() {
        initGeocomplete();
    });
}

function initGeocomplete() {
    $("#form_sedi #geocomplete input").geocomplete({
        map: $("#form_sedi #map").length ? "#map" : false,
        location: $("#form_sedi #gaddress").val() ? $("#form_sedi #gaddress").val() : [$("#form_sedi #lat_").val(), $("#form_sedi #lng_").val()],
        details: ".details",
        detailsAttribute: "data-geo"
    }).bind("geocode:result", function (event, result) {
        $("#form_sedi #lat_").val(result.geometry.location.lat());
        $("#form_sedi #lng_").val(result.geometry.location.lng());
    });
}

function rimuoviSede(button) {
    let hash = window.location.href.split("#")[1];

    confirmDelete(button).then(function () {
        redirect(globals.rootdir + "/editor.php", {
            backto: "record-edit",
            hash: hash,
            op: "deletesede",
            id: "'.$record['id'].'",
            id_plugin: "'.$id_plugin.'",
            id_module: "'.$id_module.'",
            id_parent: "'.$id_parent.'",
        });
    }).catch(swal.noop);
}

</script>';
