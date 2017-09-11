<?php

include_once __DIR__.'/../../core.php';

$report_name = 'ddt_'.$numero.'.pdf';

$autofill = [
    'count' => 0,
    'words' => 70,
    'rows' => 16,
    'additional' => 15,
    'columns' => $mostra_prezzi ? 6 : 2,
];

$v_iva = [];
$v_totale = [];

$totale_ddt = 0.00;
$totale_imponibile = 0.00;
$totale_iva = 0.00;
$sconto = 0.00;
$sconto_generico = 0.00;

// Intestazione tabella per righe
echo "
<table class='table table-striped' id='contents'>
    <thead>
        <tr>
            <th class='text-center'>".tr('Descrizione', [], ['upper' => true])."</th>
            <th class='text-center' style='width:7%'>".tr('Q.TÀ', [], ['upper' => true]).'</th>';

if ($mostra_prezzi) {
    echo "
            <th class='text-center' style='width:15%'>".tr('Prezzo u.', [], ['upper' => true])."</th>
            <th class='text-center' style='width:15%'>".tr('Importo', [], ['upper' => true])."</th>
            <th class='text-center' style='width:10%'>".tr('Sconto', [], ['upper' => true])."</th>
            <th class='text-center' style='width:7%'>".tr('IVA', [], ['upper' => true]).' (%)</th>';
}

            echo '
        </tr>
    </thead>

    <tbody>';

// Righe
$rs_gen = $dbo->fetchArray("SELECT *, (SELECT percentuale FROM co_iva WHERE id=idiva) AS perc_iva FROM `dt_righe_ddt` WHERE idddt='$idddt'");
$imponibile_gen = 0.0;
$iva_gen = 0.0;

foreach ($rs_gen as $r) {
    $autofill['count'] += ceil(strlen($r['descrizione']) / $autofill['words']);
    $autofill['count'] += substr_count($r['descrizione'], PHP_EOL);

    $descrizione = $r['descrizione'];
    $qta = $r['qta'];
    $subtot = $r['subtotale'] / $r['qta'];
    $subtotale = $r['subtotale'];
    $sconto = $r['sconto'];
    $iva = $r['iva'];

    if (empty($r['sconto_globale'])) {
        $sconto_generico = $r['subtotale'];
        $iva_gen += $r['iva'];
    } else {
        echo '
        <tr>
            <td>
                '.nl2br($descrizione);

        // Aggiunta riferimento a ordine
        if (!empty($r['idordine'])) {
            $rso = $dbo->fetchArray('SELECT numero, numero_esterno, data FROM or_ordini WHERE id='.prepare($r['idordine']));
            $numero = !empty($rso[0]['numero_esterno']) ? $rso[0]['numero_esterno'] : $rso[0]['numero'];
            echo '
                <br/><small>'.tr('Rif. ordine n<sup>o</sup>_NUM_ del _DATE_', [
                    '_NUM_' => $numero,
                    '_DATE_' => Translator::dateToLocale($rso[0]['data']),
                ]).'</small>';
            $autofill['count'] += 0.4;
        }

        echo '
            </td>';

        echo "
            <td class='center' valign='top'>
                ".Translator::numberToLocale($qta, 2).'
            </td>';

        if ($mostra_prezzi) {
            echo "
            <td align='right' class='' valign='top'>
                ".Translator::numberToLocale($subtot, 2).' &euro;
            </td>';

            // Imponibile
            echo "
            <td align='right' class='' valign='top'>
                ".Translator::numberToLocale($subtotale, 2).' &euro;
            </td>';

            // Sconto
            echo "
            <td align='right' class='' valign='top'>
                ".Translator::numberToLocale($r['sconto_unitario'], 2).($r['tipo_sconto'] == 'PRC' ? '%' : ' &euro;').'
            </td>';

            // Iva
            echo "
            <td align='center' valign='top'>";

            if ($r['perc_iva'] > 0) {
                echo '
                '.Translator::numberToLocale($r['perc_iva'], 2);
            }

            echo '
            </td>';
        }
        echo '
        </tr>';

        $imponibile_gen += $subtotale;
        $iva_gen += $iva;
        $sconto += $sconto;
    }
}

echo '
        |autofill|
    </tbody>
</table>';

// Info per il footer
$imponibile_ddt = $imponibile_gen;
$totale_iva = $iva_gen;
$totale_ddt = $imponibile_gen;
