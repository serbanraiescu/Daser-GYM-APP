<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';
    protected static string|\UnitEnum|null $navigationGroup = 'Site & Conținut';
    protected static ?string $modelLabel = 'Pagină Dinamică';
    protected static ?string $pluralModelLabel = 'Pagini Dinamice';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Pagina')
                    ->tabs([
                        Tab::make('Setări de Bază')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('title')
                                        ->label('Titlu Pagină')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                                        
                                    TextInput::make('slug')
                                        ->label('URL Slug')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->helperText('Exemplu: regulament-intern. Va genera adresa firstgym.ro/p/regulament-intern'),
                                ]),

                                RichEditor::make('content')
                                    ->label('Conținut Pagină')
                                    ->required()
                                    ->columnSpanFull()
                                    ->toolbarButtons([
                                        'attachFiles', 'blockquote', 'bold', 'bulletList', 'codeBlock', 'h2', 'h3', 'italic', 'link', 'orderedList', 'redo', 'strike', 'undo',
                                    ]),

                                Grid::make(3)->schema([
                                    Toggle::make('is_active')
                                        ->label('Pagină Publicată')
                                        ->default(true),
                                    Toggle::make('show_in_footer')
                                        ->label('Afișează link în Footer')
                                        ->default(false),
                                ])
                            ]),
                            
                        Tab::make('AIO & SEO Optimizer')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Section::make('Tag-uri Meta')
                                    ->description('Aceste informații invizibile ajută motoarele de căutare să înțeleagă despre ce e vorba. Sunt folosite și de asistenții AI.')
                                    ->schema([
                                        Textarea::make('meta_description')
                                            ->label('Meta Descriere')
                                            ->rows(3)
                                            ->helperText('Scrieți o descriere de ~150 de caractere care să fie citită de roboți și de utilizatorii pe Google.'),
                                    ]),

                                Section::make('Schema Markup (JSON-LD)')
                                    ->description('Date structurate pentru AI. Recomandăm definirea paginii corecte.')
                                    ->schema([
                                        Select::make('schema_type')
                                            ->label('Tipul Paginii (Schema)')
                                            ->options([
                                                'WebPage' => 'Pagină Standard (WebPage)',
                                                'FAQPage' => 'Întrebări Frecvente (FAQPage)',
                                                'AboutPage' => 'Despre Noi (AboutPage)',
                                                'ContactPage' => 'Informații Contact (ContactPage)',
                                            ])
                                            ->default('WebPage')
                                            ->required(),

                                        Repeater::make('faq_data')
                                            ->label('Generator Inteligent de Răspunsuri Q&A')
                                            ->helperText('Folosiți această secțiune pentru a "hrăni" modelele AI cu întrebări frecvente. Se vor genera automat tag-urile JSON-LD FAQPage în fundal.')
                                            ->schema([
                                                TextInput::make('question')
                                                    ->label('Întrebare (pentru AI)')
                                                    ->required(),
                                                Textarea::make('answer')
                                                    ->label('Răspuns Clar')
                                                    ->required()
                                                    ->rows(2),
                                            ])
                                            ->columns(1)
                                            ->collapsed(),
                                    ])
                            ]),
                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titlu')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('URL')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => "/p/{$state}")
                    ->color('gray'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Publicat')
                    ->boolean(),
                Tables\Columns\IconColumn::make('show_in_footer')
                    ->label('În Footer')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('view_live')
                    ->label('Vezi pe site')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Page $record): string => "/p/{$record->slug}")
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
