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

use Modules\Fatture\Fattura;

$module = Modules::get('Scadenzario');
$id_module = $module['id'];

$total = Util\Query::readQuery($module);

// Lettura parametri modulo
$module_query = $total['query'];

if(!empty(get('data_inizio')) AND !empty(get('data_fine'))){
    $module_query = str_replace('1=1', '1=1 AND `data` BETWEEN "'.get('data_inizio').'" AND "'.get('data_fine').'"', $module_query);

    $date_start = get('data_inizio');
    $date_end = get('data_fine');
}

if(get('is_pagata')=='true'){
    $module_query = str_replace('AND ABS(`co_scadenziario`.`pagato`) < ABS(`co_scadenziario`.`da_pagare`) ', '', $module_query);
}

if(get('is_riba')=='true'){
    $module_query = str_replace('1=1', '1=1 AND co_pagamenti.codice_modalita_pagamento_fe="MP12"', $module_query);
}

if(get('is_cliente')=='true'){
    $module_query = str_replace('1=1', '1=1 AND co_tipidocumento.dir="entrata"', $module_query);
}

if(get('is_fornitore')=='true'){
    $module_query = str_replace('1=1', '1=1 AND co_tipidocumento.dir="uscita"', $module_query);
}

// Scelgo la query in base alla scadenza
if (isset($id_record)) {
    $record = $dbo->fetchOne('SELECT * FROM co_scadenziario WHERE id = '.prepare($id_record));
    $documento = Fattura::find($record['iddocumento']);
    if (!empty($documento)) {
        $module_query = str_replace('1=1', '1=1 AND co_scadenziario.iddocumento='.prepare($documento->id), $module_query);
    } else {
        $module_query = str_replace('1=1', '1=1 AND co_scadenziario.id='.prepare($id_record), $module_query);
    }
}

// Scadenze
$records = $dbo->fetchArray($module_query);
