<?php

declare(strict_types=1);

namespace Modules\Cms\Services;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Modules\Cms\Contracts\PanelContract;
use Modules\Cms\Datas\LinkData;
use Modules\Cms\Models\Panels\XotBasePanel;
use Request;
use Spatie\LaravelData\DataCollection;

/**
 * Class PanelTabService.
 */
class PanelTabService
{
    /**
     * PanelTabService constructor.
     */
    public function __construct(protected XotBasePanel &$xotBasePanel)
    {
    }

    /**
     * @return DataCollection<LinkData>
     */
    public function getItemTabs(): DataCollection
    {
        return $this->getBreadTabs($this->xotBasePanel);
    }

    /**
     * @return DataCollection<LinkData>
     */
    public function getRowTabs(): DataCollection
    {
        return $this->getBreadTabs($this->xotBasePanel);
    }

    /**
     * @return DataCollection<LinkData>
     */
    public function getBreadTabs(PanelContract $panelContract): DataCollection
    {
        [$containers, $items] = params2ContainerItem();
        // dddx( [$bread,$containers, $items]);
        $tabs = $panelContract->tabs();
        $row = [];
        if ('' !== $panelContract->guid()) {
            foreach ($tabs as $tab) {
                $tab_panel = $panelContract->relatedName($tab);
                if (Gate::allows('index', $tab_panel)) {
                    $trans_key = $panelContract->getTradMod().'.tab.'.Str::snake($tab);

                    $tmp = [
                        'title' => trans($trans_key.'.label'),
                        'icon' => trans($trans_key.'.icon'),
                        'url' => $tab_panel->url('index'),
                        'active' => \in_array($tab, $containers, true),
                    ];
                    $row[] = $tmp;
                }
            }
        }

        return LinkData::collection($row);
    }

    public function getTabs(): array
    {
        $breads = $this->xotBasePanel->getBreads();

        $data = [];
        foreach ($breads as $bread) {
            $data[] = $this->getBreadTabs($bread);
        }

        return $data;
    }

    public function getTabsOld(): array
    {
        \Request::capture();
        $routename = (string) \Route::currentRouteName();
        $act = last(explode('.', $routename));
        // $routename = \Route::current()->getName();
        $route_current = \Route::current();
        $route_params = [];
        if (null !== $route_current) {
            $route_params = $route_current->parameters();
        }
        [$containers, $items] = params2ContainerItem($route_params);
        $data = [];
        // $items[]=$this->row;
        if (! \is_array($items)) {
            return [];
        }
        // array_unique($items);
        $parents = $this->xotBasePanel->getParents();
        if ('' !== $this->xotBasePanel->guid()) {
            $parents->push($this->xotBasePanel);
        }
        // dddx($parents);

        foreach ($parents as $k => $panel) {
            // $item = $panel->getRow();
            $tabs = [];
            if (! \is_object($panel)) {
                return $tabs;
            }
            $tabs = $panel->tabs();
            $row = [];
            // *
            if (0 === $k) {
                if (Gate::allows('index', $panel)) {
                    $tmp = new \stdClass();
                    // $tmp->title = '<< Back '; //.'['.get_class($item).']';
                    $tmp->title = 'Back'; // .'['.get_class($item).']';
                    $tmp->url = $panel->url('index');
                    $tmp->active = false;
                    $row[] = $tmp;
                }
                // -----------------------
                $tmp = new \stdClass();
                $url = \in_array($act, ['index_edit', 'edit', 'update'], true) ? $panel->url('edit') : $panel->url('show');
                $tmp->url = $url;
                $tmp->title = 'Content'; // .'['.request()->url().']['.$url.']';
                /*
                if ($url_test = 1) {
                    $tmp->active = request()->url() == $url;
                } else {
                    $tmp->active = request()->routeIs('admin.containers.'.$act);
                }
                */
                $tmp->active = request()->url() === $url;
                if (null !== $panel->guid()) {
                    $row[] = $tmp;
                }
                // ----------------------
            }
            // */

            foreach ($tabs as $tab) {
                // dddx($tabs);
                $tmp = new \stdClass();

                if (! \is_array($tab)) {
                    // $tmp = new \stdClass();
                    $tmp->title = $tab;
                    $tmp->panel = $panel;

                    $tab_act = \in_array($act, ['index_edit', 'edit', 'update'], true) ? 'index_edit' : 'index';
                    $tmp->url = $panel->relatedUrl($tab, $tab_act);
                    $tmp->active = \in_array($tab, $containers, true);
                } else {
                    //  dddx($tmp);
                    // $tmp = new \stdClass();
                    $tmp->title = $tab['title'];
                    $panel1 = $panel;
                    if (isset($tab['related'])) {
                        $panel1 = $panel1->related($tab['related']);
                    }
                    if (isset($tab['container_action'])) {
                        $tmp->url = $panel1->urlContainerAction($tab['container_action']);
                    }
                    // $tmp->url = $tab['page'];
                    $tmp->active = false;
                }
                $row[] = $tmp;
            }

            $data[] = $row;
        }
        // dddx([$data, $tabs]);

        return $data;
    }
}
