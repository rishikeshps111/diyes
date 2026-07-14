<?php

namespace App\Services;

use App\Models\TrainerCategory;

class TrainerCategoryService extends TitleMasterService
{
    public function __construct(PrefixCodeService $prefixCodeService)
    {
        parent::__construct($prefixCodeService, TrainerCategory::class, 'trainer_category');
    }
}
