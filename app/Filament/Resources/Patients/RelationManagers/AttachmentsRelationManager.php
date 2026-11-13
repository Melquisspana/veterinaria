<?php

namespace App\Filament\Resources\Patients\RelationManagers;

use App\Enums\AttachmentCategory;
use App\Models\Attachment;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $title = 'Fotos y archivos';

    protected static ?string $modelLabel = 'archivo';

    protected static ?string $pluralModelLabel = 'archivos';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            FileUpload::make('file_path')
                ->label('Archivo')
                ->disk(config('vet.uploads_disk'))
                ->directory('attachments')
                ->storeFileNamesIn('original_name')
                ->acceptedFileTypes(['image/*', 'application/pdf'])
                ->maxSize(10240)
                ->openable()
                ->downloadable()
                ->required()
                ->columnSpanFull(),
            Hidden::make('original_name'),
            TextInput::make('title')
                ->label('Título')
                ->placeholder('Ej.: Hemograma, Rx tórax, herida día 3')
                ->required()
                ->maxLength(255),
            Select::make('category')
                ->label('Categoría')
                ->options(AttachmentCategory::class)
                ->default(AttachmentCategory::Photo)
                ->required()
                ->native(false),
            Textarea::make('description')
                ->label('Descripción')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('file_path')
                    ->label('')
                    ->disk(config('vet.uploads_disk'))
                    ->square()
                    ->imageSize(56)
                    ->defaultImageUrl(asset('images/file-placeholder.svg'))
                    ->getStateUsing(fn (Attachment $record): ?string => $record->isImage() ? $record->file_path : null),
                TextColumn::make('title')->label('Título')->searchable()->description(fn (Attachment $record): ?string => $record->description),
                TextColumn::make('category')->label('Categoría')->badge(),
                TextColumn::make('created_at')->label('Subido')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->label('Categoría')->options(AttachmentCategory::class),
            ])
            ->headerActions([
                CreateAction::make()->label('Subir archivo'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Abrir')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Attachment $record): string => $record->url(), shouldOpenInNewTab: true),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
