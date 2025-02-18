<?php

declare(strict_types=1);

namespace Modules\Cms\Filament\Resources;

use Filament\Forms;
// use Modules\Cms\Filament\Resources\PageContentResource\RelationManagers;
use Filament\Forms\Form;
// use Filament\Forms;
use Filament\Resources\Concerns\Translatable;
use Illuminate\Support\Str;
use Modules\Cms\Filament\Fields\PageContentBuilder;
use Modules\Cms\Filament\Resources\PageContentResource\Pages;
use Modules\Cms\Models\PageContent;
use Modules\Xot\Filament\Resources\XotBaseResource;

// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope;

class PageContentResource extends XotBaseResource
{
    use Translatable;

    protected static ?string $model = PageContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getTranslatableLocales(): array
    {
        return ['it', 'en'];
    }

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make()->columns(2)->schema([
                Forms\Components\TextInput::make('name')
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
            ]),

            Forms\Components\Section::make('Content')->schema([
                PageContentBuilder::make('blocks')

                    // ->required()
                    ->columnSpanFull(),
            ]),
        ];
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPageContents::route('/'),
            'create' => Pages\CreatePageContent::route('/create'),
            'view' => Pages\ViewPageContent::route('/{record}'),
            'edit' => Pages\EditPageContent::route('/{record}/edit'),
        ];
    }
}
