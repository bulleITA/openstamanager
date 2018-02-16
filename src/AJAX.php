<?php

/**
 * Classe per la gestione delle funzioni AJAX richiamabili del progetto.
 *
 * @since 2.4
 */
class AJAX
{
    /**
     * Controlla se è in corso una richiesta AJAX generata dal progetto.
     *
     * @return bool
     */
    public static function isAjaxRequest()
    {
        return \Whoops\Util\Misc::isAjaxRequest() && filter('ajax') !== null;
    }

    protected static function find($file, $permissions = true)
    {
        $dirname = substr($file, 0, strrpos($file, '/') + 1);

        // Individuazione delle cartelle accessibili
        if (!empty($permissions)) {
            $modules = Modules::getAvailableModules();
        } else {
            $modules = Modules::getModules();
        }

        $dirs = array_unique(array_column($modules, 'directory'));
        $pieces = array_chunk($dirs, 5);

        // Individuazione dei file esistenti
        $list = [];
        foreach ($pieces as $piece) {
            // File nativi
            $files = glob(DOCROOT.'/modules/{'.implode(',', $piece).'}/'.$file, GLOB_BRACE);

            // File personalizzati
            $custom_files = glob(DOCROOT.'/modules/{'.implode(',', $piece).'}/custom/'.$file, GLOB_BRACE);

            // Pulizia dei file nativi che sono stati personalizzati
            foreach ($custom_files as $key => $value) {
                $index = array_search(str_replace('custom/'.$dirname, $dirname, $value), $files);
                if ($index !== false) {
                    unset($files[$index]);
                }
            }

            $list = array_merge($list, $files, $custom_files);
        }

        asort($list);

        return $list;
    }

    public static function select($resource, $elements = [], $search = null)
    {
        if (!isset($elements)) {
            $elements = [];
        }
        $elements = (!is_array($elements)) ? explode(',', $elements) : $elements;

        $files = self::find('ajax/select.php', false);

        // File di gestione predefinita
        array_unshift($files, DOCROOT.'/ajax_select.php');

        foreach ($files as $file) {
            $results = self::getSelectResults($file, $resource, $elements, $search);
            if (isset($results)) {
                break;
            }
        }

        return $results;
    }

    public static function completeResults($query, $where, $filter = [], $search = [], $custom = [])
    {
        if (str_contains($query, '|filter|')) {
            $query = str_replace('|filter|', !empty($filter) ? 'WHERE '.implode(' OR ', $filter) : '', $query);
        } elseif (!empty($filter)) {
            $where[] = '('.implode(' OR ', $filter).')';
        }

        if (!empty($search)) {
            $where[] = '('.implode(' OR ', $search).')';
        }

        $query = str_replace('|where|', !empty($where) ? 'WHERE '.implode(' AND ', $where) : '', $query);

        $database = Database::getConnection();
        $rs = $database->fetchArray($query);

        $results = [];
        foreach ($rs as $r) {
            $result = [];
            foreach ($custom as $key => $value) {
                $result[$key] = $r[$value];
            }

            $results[] = $result;
        }

        return $results;
    }

    protected static function getSelectResults($file, $resource, $elements = [], $search = null)
    {
        $superselect = self::getSelectInfo();

        $where = [];
        $filter = [];
        $search_fields = [];

        $custom = [
            'id' => 'id',
            'text' => 'descrizione',
        ];

        // Database
        $database = Database::getConnection();
        $dbo = $database;

        require $file;

        if (!isset($results) && !empty($query)) {
            $results = self::completeResults($query, $where, $filter, $search_fields, $custom);
        }

        return $results;
    }

    protected static function getSelectInfo()
    {
        return !empty($_SESSION['superselect']) ? $_SESSION['superselect'] : [];
    }

    public static function search($term)
    {
        if (strlen($term) < 2) {
            return;
        }

        $files = self::find('ajax/search.php');

        // File di gestione predefinita
        array_unshift($files, DOCROOT.'/ajax_search.php');

        $results = [];
        foreach ($files as $file) {
            $module_results = self::getSearchResults($file, $term);

            $results = array_merge($results, $module_results);
        }

        return $results;
    }

    protected static function getSearchResults($file, $term)
    {
        // Database
        $database = Database::getConnection();
        $dbo = $database;

        // Ricerca anagrafiche per ragione sociale per potere mostrare gli interventi, fatture,
        // ordini, ecc della persona ricercata
        $idanagrafiche = ['-1'];
        $ragioni_sociali = ['-1'];
        $rs = $dbo->fetchArray('SELECT idanagrafica, ragione_sociale FROM an_anagrafiche WHERE ragione_sociale LIKE "%'.$term.'%"');

        for ($a = 0; $a < sizeof($rs); ++$a) {
            $idanagrafiche[] = $rs[$a]['idanagrafica'];
            $ragioni_sociali[$rs[$a]['idanagrafica']] = $rs[$a]['ragione_sociale'];
        }

        $results = [];

        require $file;

        $results = (array) $results;
        foreach ($results as $key => $value) {
            $results[$key]['value'] = $key;
        }

        return $results;
    }

    public static function complete($resource)
    {
        $files = self::find('ajax/complete.php');

        // File di gestione predefinita
        array_unshift($files, DOCROOT.'/ajax_complete.php');

        foreach ($files as $file) {
            $result = self::getCompleteResults($file, $resource);
            if (!empty($result)) {
                break;
            }
        }

        return $result;
    }

    protected static function getCompleteResults($file, $resource)
    {
        // Database
        $database = Database::getConnection();
        $dbo = $database;

        ob_start();
        require $file;
        $result = ob_get_clean();

        return $result;
    }
}
