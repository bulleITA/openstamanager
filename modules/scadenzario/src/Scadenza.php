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

namespace Modules\Scadenzario;

use Common\SimpleModelTrait;
use Illuminate\Database\Eloquent\Model;
use Modules\Fatture\Fattura;

class Scadenza extends Model
{
    use SimpleModelTrait;

    protected $table = 'co_scadenze';

    protected $dates = [
        'scadenza',
        'data_pagamento',
    ];

    public static function build(Gruppo $gruppo, $importo, $data_scadenza, $tipo = 'fattura', $is_pagato = false)
    {
        $model = new static();

        $model->gruppo()->associate($gruppo);

        $model->scadenza = $data_scadenza;
        $model->da_pagare = $importo;
        $model->tipo = $tipo;

        $model->pagato = $is_pagato ? $importo : 0;
        $model->data_pagamento = $is_pagato ? $data_scadenza : null;

        $model->save();

        return $model;
    }

    public function save(array $options = [])
    {
        $result = parent::save($options);

        // Trigger per il gruppo al cambiamento della scadenza
        $this->gruppo->triggerScadenza($this);

        return $result;
    }

    // Relazioni Eloquent

    public function gruppo()
    {
        return $this->belongsTo(Gruppo::class, 'id_gruppo');
    }

    public function fattura()
    {
        return $this->gruppo->fattura();
    }
}
