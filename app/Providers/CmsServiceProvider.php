<?php

/**
 * @see https://github.com/laravel/framework/discussions/49574
 */

declare(strict_types=1);

namespace Modules\Cms\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Laravel\Folio\Folio;
use Livewire\Volt\Volt;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Tenant\Services\TenantService;
use Modules\Xot\Actions\Livewire\RegisterLivewireComponentsAction;
use Modules\Xot\Datas\XotData;
use Modules\Xot\Providers\XotBaseServiceProvider;
use Modules\Xot\Services\LivewireService;
use Nwidart\Modules\Facades\Module;
use Webmozart\Assert\Assert;

/**
 * Undocumented class.
 */
class CmsServiceProvider extends XotBaseServiceProvider
{
    public string $name = 'Cms';

    public XotData $xot;

    protected string $module_dir = __DIR__;

    protected string $module_ns = __NAMESPACE__;

    public function boot(): void
    {
        parent::boot();

        $this->xot = XotData::make();

        if ($this->xot->register_pub_theme) {
            $this->registerNamespaces('pub_theme');

            $this->registerThemeConfig('pub_theme');
            $this->registerThemeLivewireComponents();
        }

        Assert::string($timezone = config('app.timezone') ?? 'Europe/Berlin');

        date_default_timezone_set($timezone);
    }

    public function register(): void
    {
        parent::register();

        $this->xot = XotData::make();
        // $configFileName = 'xra';
        // $this->mergeConfigFrom(__DIR__.sprintf('/../config/%s.php', $configFileName), $configFileName);

        if ($this->xot->register_pub_theme) {
            Assert::isArray($paths = config('view.paths'));
            $theme_path = app(\Modules\Xot\Actions\File\FixPathAction::class)->execute(base_path('Themes/'.$this->xot->pub_theme.'/resources/views'));
            $paths = array_merge([$theme_path], $paths);
            Config::set('view.paths', $paths);
            Config::set('livewire.view_path', $theme_path.'/livewire');
            Config::set('livewire.class_namespace', 'Themes\\'.$this->xot->pub_theme.'\Http\Livewire');
            $this->registerFolio();
        }
    }

    public function registerFolio(): void
    {
        $middleware = TenantService::config('middleware');
        if (! is_array($middleware)) {
            $middleware = [];
        }
        $base_middleware = Arr::get($middleware, 'base', []);

        $theme_path = XotData::make()->getPubThemeViewPath('pages');
        Folio::path($theme_path)
            ->uri(LaravelLocalization::setLocale() ?? app()->getLocale())
            ->middleware([
                '*' => $base_middleware,
            ]);

        /**
         * @var Collection<Module>
         */
        $modules = Module::collections();
        $paths = [];
        $paths[] = $theme_path;
        foreach ($modules as $module) {
            $path = $module->getPath().'/resources/views/pages';
            if (! File::exists($path)) {
                continue;
            }
            $paths[] = $path;
            Folio::path($path)
                ->uri(LaravelLocalization::setLocale() ?? app()->getLocale())
                ->middleware([
                    '*' => [
                    ],
                ]);
        }

        Volt::mount($paths);
    }

    /**
     * Undocumented function.
     */
    public function registerThemeLivewireComponents(): void
    {
        // $prefix=$this->module_name.'::';
        $prefix = '';

        /*
        app(RegisterLivewireComponentsAction::class)
            ->execute(
                base_path('Themes/'.$this->xot->pub_theme.'/Http/Livewire'),
                'Themes\\'.$this->xot->pub_theme,
                $prefix,
            );
        */
    }

    /**
     * Undocumented function.
     */
    public function registerNamespaces(string $theme_type): void
    {
        $xot = $this->xot;

        Assert::string($theme = $xot->{$theme_type});

        $resource_path = 'Themes/'.$theme.'/resources';
        $lang_dir = app(\Modules\Xot\Actions\File\FixPathAction::class)->execute(base_path($resource_path.'/lang'));

        $theme_dir = app(\Modules\Xot\Actions\File\FixPathAction::class)->execute(base_path($resource_path.'/views'));

        app('view')->addNamespace($theme_type, $theme_dir);
        $this->loadTranslationsFrom($lang_dir, $theme_type);
    }

    public function registerThemeConfig(string $theme_type): void
    {
        $xot = $this->xot;

        Assert::string($theme = $xot->{$theme_type});

        $config_path = base_path('Themes/'.$theme.'/Config');
        if (! File::exists($config_path)) {
            return;
        }

        $files = File::files($config_path);
        foreach ($files as $file) {
            $name = $file->getFilenameWithoutExtension();
            $real_path = $file->getRealPath();
            if (false === $real_path) {
                throw new \Exception('['.__LINE__.']['.class_basename(self::class).']');
            }

            $data = File::getRequire($real_path);
            Config::set($theme_type.'::'.$name, $data);
        }

        // ---------------------
    }
}
