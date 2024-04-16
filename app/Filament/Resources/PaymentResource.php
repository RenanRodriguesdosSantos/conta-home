<?php

namespace App\Filament\Resources;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $modelLabel = 'pagamento';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense.user.name')
                    ->label('Criado por'),
                TextColumn::make('user.name')
                    ->label('Pagador'),
                TextColumn::make('value')
                    ->formatStateUsing(fn($state)  => "R$ " . number_format($state, 2, ',', '.'))
                    ->label('Valor'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('paid_at')
                    ->label('Pago em')
                    ->dateTime('d/m/y H:i')
            ])
            ->filters([
                SelectFilter::make('expense_user')
                    ->label('Criado por')
                    ->options(User::pluck('name','id'))
                    ->query(function (Builder $query, $data) {
                        if ($data['value']) {
                            $query->whereHas('expense', fn ($query) => $query->where('user_id', $data['value']));
                        }
                    }),
                SelectFilter::make('user')
                    ->label('Pagador')
                    ->relationship('user', 'name'),
                SelectFilter::make('status')
                    ->options(PaymentStatus::class)
                    ->default(PaymentStatus::PENDING->value),
            ])
            ->actions([
                Tables\Actions\Action::make('alter_status')
                    ->label('Alterar status')
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
                    })
                    ->hidden(fn ($record) => $record->expense->user_id != auth()->id()),
            ])
            ->recordAction('alter_status')
            ->bulkActions([])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
