<?php

namespace App\Filament\Resources\ExpenseResource\RelationManagers;

use App\Enums\PaymentStatus;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = "Pagamentos";

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário'),
                Tables\Columns\TextColumn::make('value')
                    ->formatStateUsing(fn($state)  => "R$ " . number_format($state, 2, ',', '.'))
                    ->label('Valor'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Pago em')
                    ->dateTime('d/m/y H:i')
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('alter_status')
                    ->label('Alterar Status')
                    ->form([
                        Select::make('status')
                            ->options(PaymentStatus::class)
                            ->required()
                    ])
                    ->modalWidth(MaxWidth::Large)
                    ->action(function ($record, $data) {
                        if ($record->expense->user_id != auth()->id()) {
                            Notification::make('cannot_alter_status')
                                ->title('Você não pode alterar o status!')
                                ->danger()
                                ->send();

                            return;
                        }

                        if ($data['status'] == PaymentStatus::PAID->value) {
                            $data['paid_at'] = now();
                        } else {
                            $data['paid_at'] = null;
                        }

                        $record->update($data);

                        Notification::make('cannot_alter_status')
                                ->title('Status alterado com sucesso!')
                                ->success()
                                ->send();
                    }),
            ])
            ->bulkActions([
            ]);
    }
}
