<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use App\Models\Expense;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_expense')
                ->label('Adicionar despesa')
                ->form([
                    TextInput::make('description')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255),
                    TextInput::make('total')
                        ->required()
                        ->numeric(),
                ])
                ->modalWidth(MaxWidth::ExtraLarge)
                ->action(function (array $data){
                    /** @var User */
                    $user = auth()->user();

                    $user->expenses()->create($data);

                    Notification::make('expense_created')
                        ->title('Despesa criada com sucesso!')
                        ->success()
                        ->send();
                })
        ];
    }
}
