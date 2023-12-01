<?php

declare(strict_types=1);

namespace Modules\Cms\View\Composers;

use Modules\Cms\Actions\Module\GetModelsMenuByModuleNameAction;
use Modules\Cms\Datas\LinkData;
use Modules\Cms\Datas\NavbarMenuData;
use Modules\Cms\Models\Menu;
use Modules\Cms\Services\RouteService;
use Modules\User\Services\ProfileService;
use Modules\Xot\Datas\XotData;
use Spatie\LaravelData\DataCollection;

class ThemeComposer
{
    /**
     * ---.
     */
    public function getArea(): ?string
    {
        $params = getRouteParameters();

        return $params['module'] ?? null;
    }

    public function getModelsMenuByModuleName(string $module_name = null): DataCollection
    {
        if (null == $module_name) {
            $module_name = $this->getArea();
        }

        if (null == $module_name) {
            throw new \Exception('['.__LINE__.']['.__FILE__.']');
            // $module_name = '';
        }

        return app(GetModelsMenuByModuleNameAction::class)->execute($module_name);
    }

    public function getModuleMenuByModuleName(string $module_name = null): DataCollection
    {
        $xotData = XotData::make();
        $profile = $xotData->getProfileModel();
        // $profile = ProfileService::make()->getProfile();
        $menu_name = $module_name;

        if (null == $module_name) {
            $module_name = $this->getArea();
            $menu_name = 'module_'.$module_name;
        }

        $menu = Menu::firstOrNew(
            ['name' => $menu_name]
        );

        // dddx($menu->items);

        $items = $menu->items->filter(function ($item) use ($profile): bool {
            $roles = array_map('trim', explode(',', (string) $item->roles));
            $roles[] = 'superadmin';

            return (bool) $profile->hasAnyRole($roles);
        })->map(
            static fn ($item): array => [
                'title' => $item->label,
                'url' => $item->link,
                'active' => (bool) $item->active,
                'icon' => $item->icon,
            ]
        );

        return NavbarMenuData::collection($items->all());
    }

    /**
     * @return DataCollection<LinkData>
     */
    public function getDashboardMenu(): DataCollection
    {
        $xotData = XotData::make();
        $profile = $xotData->getProfileModel();
        // $profile = ProfileService::make();

        return $profile->getAreasLinkDataColl();
    }

    public function getRouteAct(): string
    {
        return RouteService::getAct();
    }
}
