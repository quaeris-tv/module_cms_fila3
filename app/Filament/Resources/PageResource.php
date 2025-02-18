<?php

declare(strict_types=1);

namespace Modules\Cms\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Illuminate\Support\Str;
use Modules\Cms\Filament\Fields\LeftSidebarContent;
use Modules\Cms\Filament\Fields\PageContent;
use Modules\Cms\Filament\Resources\PageResource\Pages;
use Modules\Cms\Models\Page;
use Modules\Xot\Filament\Resources\XotBaseResource;

class PageResource extends XotBaseResource
{
    use Translatable;

    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationGroup = 'Site';

    public static function getTranslatableLocales(): array
    {
        return ['it', 'en'];
    }

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->columnSpan(1)
                        ->required()
                        ->lazy()
                        ->afterStateUpdated(static function (Forms\Set $set, Forms\Get $get, string $state): void {
                            if ($get('slug')) {
                                return;
                            }
                            $set('slug', Str::slug($state));
                        }),

                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->columnSpan(1)
                        ->afterStateUpdated(static fn (Forms\Set $set, string $state) => $set('slug', Str::slug($state))),
                ]
                ),
            /*
                Forms\Components\Actions::make([
                    InlinePreviewAction::make()

                        ->builderName('content'),
                ])
                    ->columnSpanFull()
                    ->alignEnd(),
                */
            Forms\Components\Section::make('Page Content')
                ->schema([
                    PageContent::make('content_blocks')

                        ->required()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Sidebar Content')
                ->schema([
                    LeftSidebarContent::make('sidebar_blocks')

                        // ->required()
                        ->columnSpanFull(),
                ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
