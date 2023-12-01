<?php

declare(strict_types=1);

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Builder;
// use Modules\User\Models\Traits\HasTeams;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Tenant\Services\TenantService;
use Z3d0X\FilamentFabricator\Models\Page as ModelsPage;

class Page extends ModelsPage
{
    use HasFactory;

    // use HasTeams;
    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
        'slug',
        'blocks',
        'layout',
        'parent_id',
        'tenant_name',
    ];

    protected $casts = [
        'blocks' => 'array',
        'parent_id' => 'integer',
    ];

    /**
     * @var string
     */
    protected $connection = 'cms';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant_name', function (Builder $builder) {
            $builder->where('tenant_name', 'like', TenantService::getName());
        });
    }
}
