<?php

include_once __DIR__.'/core.php';

// Lettura parametri iniziali
if (!empty($id_plugin)) {
    $info = Plugins::get($id_plugin);

    $directory = '/plugins/'.$info['directory'];
    $permesso = $info['idmodule_to'];
} else {
    $info = Modules::get($id_module);

    $directory = '/modules/'.$info['directory'];
    $permesso = $id_module;
}

$upload_dir = $docroot.'/files/'.basename($directory);

$dbo->query('START TRANSACTION');

// GESTIONE UPLOAD
if (filter('op') == 'link_file' || filter('op') == 'unlink_file') {
    // Controllo sui permessi di scrittura per il modulo
    if (Modules::getPermission($id_module) != 'rw') {
        $_SESSION['errors'][] = tr('Non hai permessi di scrittura per il modulo _MODULE_', [
            '_MODULE_' => '"'.Modules::get($id_module)['name'].'"',
        ]);
    }

    // Controllo sui permessi di scrittura per il file system
    elseif (!directory($upload_dir)) {
        $_SESSION['errors'][] = tr('Non hai i permessi di scrittura nella cartella _DIR_!', [
            '_DIR_' => '"files"',
        ]);
    }

    // Gestione delle operazioni
    else {
        // UPLOAD
        if (filter('op') == 'link_file' && !empty($_FILES) && !empty($_FILES['blob']['name'])) {
            $nome = filter('nome_allegato');
            $nome = !empty($nome) ? $nome : $_FILES['blob']['name'];

            $src = $_FILES['blob']['tmp_name'];
            $f = pathinfo($_FILES['blob']['name']);

            /*
            $allowed = [
                // Image formats
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'jpe' => 'image/jpeg',
                'gif' => 'image/gif',
                'png' => 'image/png',
                'bmp' => 'image/bmp',
                'tif' => 'image/tiff',
                'tiff' => 'image/tiff',
                'ico' => 'image/x-icon',
                // Video formats
                'asx' => 'video/asf',
                'asf' => 'video/asf',
                'wax' => 'video/asf',
                'wmv' => 'video/asf',
                'wmx' => 'video/asf',
                'avi' => 'video/avi',
                'divx' => 'video/divx',
                'flv' => 'video/x-flv',
                'mov' => 'video/quicktime',
                'qt' => 'video/quicktime',
                'mpg' => 'video/mpeg',
                'mpeg' => 'video/mpeg',
                'mpe' => 'video/mpeg',
                'mp4' => 'video/mp4',
                'm4v' => 'video/mp4',
                'ogv' => 'video/ogg',
                'mkv' => 'video/x-matroska',
                // Text formats
                'txt' => 'text/plain',
                'csv' => 'text/csv',
                'tsv' => 'text/tab-separated-values',
                'ics' => 'text/calendar',
                'rtx' => 'text/richtext',
                'css' => 'text/css',
                'htm' => 'text/html',
                'html' => 'text/html',
                // Audio formats
                'mp3' => 'audio/mpeg',
                'm4a' => 'audio/mpeg',
                'm4b' => 'audio/mpeg',
                'mp' => 'audio/mpeg',
                'm4b' => 'audio/mpeg',
                'ra' => 'audio/x-realaudio',
                'ram' => 'audio/x-realaudio',
                'wav' => 'audio/wav',
                'ogg' => 'audio/ogg',
                'oga' => 'audio/ogg',
                'mid' => 'audio/midi',
                'midi' => 'audio/midi',
                'wma' => 'audio/wma',
                'mka' => 'audio/x-matroska',
                // Misc application formats
                'rtf' => 'application/rtf',
                'js' => 'application/javascript',
                'pdf' => 'application/pdf',
                'swf' => 'application/x-shockwave-flash',
                'class' => 'application/java',
                'tar' => 'application/x-tar',
                'zip' => 'application/zip',
                'gz' => 'application/x-gzip',
                'gzip' => 'application/x-gzip',
                'rar' => 'application/rar',
                '7z' => 'application/x-7z-compressed',
                // MS Office formats
                'doc' => 'application/msword',
                'pot' => 'application/vnd.ms-powerpoint',
                'pps' => 'application/vnd.ms-powerpoint',
                'ppt' => 'application/vnd.ms-powerpoint',
                'wri' => 'application/vnd.ms-write',
                'xla' => 'application/vnd.ms-excel',
                'xls' => 'application/vnd.ms-excel',
                'xlt' => 'application/vnd.ms-excel',
                'xlw' => 'application/vnd.ms-excel',
                'mdb' => 'application/vnd.ms-access',
                'mpp' => 'application/vnd.ms-project',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
                'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
                'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
                'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
                'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
                'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
                'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
                'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
                'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
                'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
                'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
                'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
                'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
                'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
                'onetoc' => 'application/onenote',
                'onetoc2' => 'application/onenote',
                'onetmp' => 'application/onenote',
                'onepkg' => 'application/onenote',
                // OpenOffice formats
                'odt' => 'application/vnd.oasis.opendocument.text',
                'odp' => 'application/vnd.oasis.opendocument.presentation',
                'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
                'odg' => 'application/vnd.oasis.opendocument.graphics',
                'odc' => 'application/vnd.oasis.opendocument.chart',
                'odb' => 'application/vnd.oasis.opendocument.database',
                'odf' => 'application/vnd.oasis.opendocument.formula',
                // WordPerfect formats
                'wp' => 'application/wordperfect',
                'wpd' => 'application/wordperfect',
            ];


            if (in_array($f['extension'], array_keys($allowed))) {
            */
            do {
                $filename = random_string().'.'.$f['extension'];
            } while (file_exists($upload_dir.'/'.$filename));

            // Creazione file fisico
            if (move_uploaded_file($src, $upload_dir.'/'.$filename)) {
                $dbo->insert('zz_files', [
                        'nome' => $nome,
                        'filename' => $filename,
                        'original' => $_FILES['blob']['name'],
                        'id_module' => $id_module,
                        'id_record' => $id_record,
                    ]);

                $_SESSION['infos'][] = tr('File caricato correttamente!');
            } else {
                $_SESSION['errors'][] = tr('Errore durante il caricamento del file!');
            }
            /*
            } else {
                $_SESSION['errors'][] = tr('Tipologia di file non permessa!');
            }
            */
        }

        // DELETE
        elseif (filter('op') == 'unlink_file' && filter('filename') !== null) {
            $filename = filter('filename');

            $rs = $dbo->fetchArray('SELECT * FROM zz_files WHERE id_module='.prepare($id_module).' AND id='.prepare(filter('id')).' AND filename='.prepare($filename));

            if (delete($upload_dir.'/'.$filename)) {
                $query = 'DELETE FROM zz_files WHERE id_module='.prepare($id_module).' AND id='.prepare(filter('id')).' AND filename='.prepare($filename);
                if ($dbo->query($query)) {
                    $_SESSION['infos'][] = tr('File _FILE_ eliminato!', [
                        '_FILE_' => '"'.$rs[0]['nome'].'"',
                    ]);
                }
            } else {
                $_SESSION['errors'][] = tr("Errore durante l'eliminazione del file _FILE_ in _DIR_!", [
                    '_FILE_' => '"'.$rs[0]['nome'].'"',
                    '_DIR_' => '"files/'.$module_dir.'/"',
                ]);
            }
        }

        redirect(ROOTDIR.'/editor.php?id_module='.$id_module.'&id_record='.$id_record);
    }
} elseif (filter('op') == 'download_file') {
    $rs = $dbo->fetchArray('SELECT * FROM zz_files WHERE id_module='.prepare($id_module).' AND id='.prepare(filter('id')).' AND filename='.prepare(filter('filename')));

    download($upload_dir.'/'.$rs[0]['filename'], $rs[0]['original']);
}

if (Modules::getPermission($permesso) == 'r' || Modules::getPermission($permesso) == 'rw') {
    if (!empty($info['script'])) {
        // Inclusione di eventuale plugin personalizzato
        if (file_exists($docroot.'/modules/'.$info['module_dir'].'/plugins/custom/'.$info['script'])) {
            include $docroot.'/modules/'.$info['module_dir'].'/plugins/custom/'.$info['script'];
        } elseif (file_exists($docroot.'/modules/'.$info['module_dir'].'/plugins/'.$info['script'])) {
            include $docroot.'/modules/'.$info['module_dir'].'/plugins/'.$info['script'];
        }

        return;
    }

    // Caricamento helper modulo (verifico se ci sono helper personalizzati)
    if (file_exists($docroot.$directory.'/custom/modutil.php')) {
        include_once $docroot.$directory.'/custom/modutil.php';
    } elseif (file_exists($docroot.$directory.'/modutil.php')) {
        include_once $docroot.$directory.'/modutil.php';
    }

    // Lettura risultato query del modulo
    if (file_exists($docroot.$directory.'/custom/init.php')) {
        include $docroot.$directory.'/custom/init.php';
    } elseif (file_exists($docroot.$directory.'/init.php')) {
        include $docroot.$directory.'/init.php';
    }

    if (Modules::getPermission($permesso) == 'rw') {
        // Esecuzione delle operazioni di gruppo
        $id_records = post('id_records');
        $id_records = is_array($id_records) ? $id_records : explode(';', $id_records);
        $id_records = array_filter($id_records, function ($var) {return !empty($var); });
        $id_records = array_unique($id_records);

        $bulk = null;
        if (file_exists($docroot.$directory.'/custom/bulk.php')) {
            $bulk = include $docroot.$directory.'/custom/bulk.php';
        } elseif (file_exists($docroot.$directory.'/bulk.php')) {
            $bulk = include $docroot.$directory.'/bulk.php';
        }
        $bulk = (array) $bulk;

        if (in_array(post('op'), array_keys($bulk))) {
            redirect(ROOTDIR.'/controller.php?id_module='.$id_module, 'js');
        } else {
            // Esecuzione delle operazioni del modulo
            if (file_exists($docroot.$directory.'/custom/actions.php')) {
                include $docroot.$directory.'/custom/actions.php';
            } elseif (file_exists($docroot.$directory.'/actions.php')) {
                include $docroot.$directory.'/actions.php';
            }

            // Operazioni generiche per i campi personalizzati
            if (post('op') != null && post('op') != 'delete') {
                $customs = $dbo->fetchArray('SELECT `id`, `name` FROM `zz_fields` WHERE `id_module` = '.prepare($id_module));

                $values = [];
                foreach ($customs as $custom) {
                    if (isset($post[$custom['name']])) {
                        $values[$custom['id']] = $post[$custom['name']];
                    }
                }

                // Inserimento iniziale
                if (post('op') == 'add') {
                    foreach ($values as $key => $value) {
                        $dbo->insert('zz_field_record', [
                            'id_record' => $id_record,
                            'id_field' => $key,
                            'value' => $value,
                        ]);
                    }
                }

                // Aggiornamento
                elseif (post('op') == 'update') {
                    foreach ($values as $key => $value) {
                        $dbo->update('zz_field_record', [
                            'value' => $value,
                        ], [
                            'id_record' => $id_record,
                            'id_field' => $key,
                        ]);
                    }
                }
            }

            // Eliminazione
            elseif (post('op') == 'delete') {
                $dbo->query('DELETE FROM `zz_field_record` WHERE `id_record` = '.prepare($id_record).' AND `id_field` IN (SELECT `id` FROM `zz_fields` WHERE `id_module` = '.prepare($id_module).')');
            }
        }
    }
}

$dbo->query('COMMIT');
