<?php

namespace App\Services;

use App\Models\TrainerType;

class TrainerTypeService extends TitleMasterService
{
    public function __construct(PrefixCodeService $prefixCodeService)
    {
        parent::__construct($prefixCodeService, TrainerType::class, 'trainer_type');
    }
}
